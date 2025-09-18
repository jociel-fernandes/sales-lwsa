import axios from 'axios'

const API_BASE = import.meta.env.VITE_API_BASE || 'http://localhost:8007'

// rawHttp: instance without interceptors to fetch CSRF cookie safely
const rawHttp = axios.create({ baseURL: API_BASE, withCredentials: true })

const http = axios.create({
  baseURL: API_BASE,
  withCredentials: true, // important for sanctum cookies
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

// import the loading store dynamically in runtime to avoid circular deps
import { useLoadingStore } from '@/stores/loading'
import { useNotificationStore } from '@/stores/notification'

// small helper to read cookie
function readCookie(name: string) {
  if (typeof document === 'undefined') return null
  const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)'))
  return match ? match[2] : null
}

// ensureCsrf fetches /sanctum/csrf-cookie using rawHttp (no interceptors)
// and sets the axios default header X-XSRF-TOKEN. Uses a dedupe promise so
// concurrent requests wait for the same network call.
let csrfPromise: Promise<void> | null = null
async function ensureCsrf(): Promise<void> {
  if (csrfPromise) return csrfPromise

  csrfPromise = (async () => {
    try {
      await rawHttp.get('/sanctum/csrf-cookie')
      if (typeof document !== 'undefined') {
        const raw = readCookie('XSRF-TOKEN')
        if (raw) {
          const token = decodeURIComponent(raw)
          ;((http.defaults.headers as unknown) as { common?: Record<string, string> }).common = ((http.defaults.headers as unknown) as { common?: Record<string, string> }).common || {}
          const common = ((http.defaults.headers as unknown) as { common?: Record<string, string> }).common!
          common['X-XSRF-TOKEN'] = token
        }
      }
    } finally {
      // clear promise so subsequent calls can refetch if needed
      csrfPromise = null
    }
  })()

  return csrfPromise
}

// Request interceptor: ensure XSRF header is present for all requests
http.interceptors.request.use(async (config) => {
  try {
    const url = config.url || ''
    // don't act on the csrf-cookie request itself (allow full URLs as well)
    if (url.includes('/sanctum/csrf-cookie')) return config

    // For mutating requests ensure CSRF cookie is present and up-to-date
    try {
      const method = (config.method || '').toLowerCase()
      if (method === 'post' || method === 'put' || method === 'patch' || method === 'delete') {
        try {
          await ensureCsrf()
        } catch {
          // ignore
        }
      } else {
        // non-mutating: ensure cookie exists but don't force
        try {
          const raw = typeof document !== 'undefined' ? readCookie('XSRF-TOKEN') : null
          if (!raw) await ensureCsrf()
        } catch {
          // ignore
        }
      }
    } catch {
      // ignore
    }

    // set the header unconditionally (if cookie exists). Add dev logs to help diagnose missing tokens
    try {
      const finalRaw = typeof document !== 'undefined' ? readCookie('XSRF-TOKEN') : null
      const hdrs = (config.headers || {}) as Record<string, string>
        // If sending FormData, let axios set the proper multipart boundary
        try {
          const dataAny = (config as unknown as { data?: unknown }).data
          if (typeof FormData !== 'undefined' && dataAny instanceof FormData) {
            delete hdrs['Content-Type']
          }
        } catch {}
      if (finalRaw) {
        const token = decodeURIComponent(finalRaw)
        hdrs['X-XSRF-TOKEN'] = token
      }
      hdrs['X-Requested-With'] = hdrs['X-Requested-With'] || 'XMLHttpRequest'
      ;(config.headers as unknown) = hdrs as unknown

      // dev logging was removed to avoid leaking token info; keep no-op here
    } catch {
      // ignore
    }
  } catch {
    // ignore errors in interceptor
  }
  return config
})

// Start loading for mutating requests
http.interceptors.request.use((config) => {
  try {
    const method = (config.method || '').toLowerCase()
    if (method === 'put' || method === 'patch' || method === 'delete') {
      try {
        const loading = useLoadingStore()
        loading.start()
      } catch {
        // ignore
      }
    }
  } catch {}
  return config
})

// Response interceptor: handle 401 globally
http.interceptors.response.use(
  (res: import('axios').AxiosResponse) => {
    try {
      // notify if backend returned a non-success code with a message
      try {
        const status = res?.status
        if (status && ![200, 201, 204].includes(status)) {
          const notif = useNotificationStore()
          const dataMessage = res?.data?.message
          notif.push(dataMessage || 'Erro no Processamento', 'error')
        }
      } catch {}
      const method = (res?.config?.method || '').toLowerCase()
      if (method === 'put' || method === 'patch' || method === 'delete') {
        try {
          const loading = useLoadingStore()
          loading.stop()
        } catch {
          // ignore
        }
      }
    } catch {}
    return res
  },
  async (error: any) => {
    // stop loading for mutating requests
    try {
      const method = (error?.config?.method || '').toLowerCase()
      if (method === 'put' || method === 'patch' || method === 'delete') {
        try {
          const loading = useLoadingStore()
          loading.stop()
        } catch {
          // ignore
        }
      }
    } catch {}
    const status = error?.response?.status
    // If the server returned a message, show it (except for 401 which is handled below).
    // Do NOT show raw backend messages for 404 GET responses — components should
    // handle 'not found' flows and show friendly notifications instead.
    try {
      const dataMessage = error?.response?.data?.message
      const method = (error?.config?.method || '').toLowerCase()
      if (dataMessage && status !== 401 && !(status === 404 && method === 'get')) {
        const notif = useNotificationStore()
        notif.push(dataMessage, 'error')
      } else if (status && ![200, 201, 204, 401, 404].includes(status)) {
        const notif = useNotificationStore()
        notif.push('Erro no Processamento', 'error')
      }
    } catch {}

    if (status === 401) {
      try {
        // dynamic import to avoid cyclic deps
        const { useAuthStore } = await import('../stores/auth')
        const { default: router } = await import('../router')
        const auth = useAuthStore()
        // do NOT call auth.logout() here to avoid triggering the logout request
        // which could return 401 and cause a loop. Instead, clear the local user
        // state and redirect to the login page.
        try {
          type MaybePiniaStore = {
            $reset?: () => void
            $patch?: (state: Record<string, unknown>) => void
            user?: unknown
          }
          const store = auth as unknown as MaybePiniaStore

          if (typeof store.$reset === 'function') {
            store.$reset()
          } else if (typeof store.$patch === 'function') {
            store.$patch({ user: null })
          } else {
            try {
              store.user = null
            } catch {
              // ignore
            }
          }
        } catch {
          // ignore store clearing errors
        }
        router.replace({ name: 'login' })
      } catch {
        // ignore
      }
    }
    return Promise.reject(error)
  }
)

export default http
export { ensureCsrf, readCookie }
