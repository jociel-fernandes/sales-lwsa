import http, { ensureCsrf } from './http'

type Credentials = { email: string; password: string }

async function getCsrfCookie() {
  await ensureCsrf()
}

export async function login(credentials: Credentials) {
  await getCsrfCookie()
  const res = await http.post('/auth/login', credentials)
  return res.data
}

export async function forgotPassword(email: string) {
  await getCsrfCookie()
  const res = await http.post('/auth/forgot-password', { email })
  return res.data
}

export async function resetPassword(payload: { token: string; email: string; password: string; password_confirmation: string }) {
  await getCsrfCookie()
  const res = await http.post('/auth/reset-password', payload)
  return res.data
}

export async function validateResetToken(payload: { token: string; email: string }) {
  await getCsrfCookie()
  const res = await http.post('/api/password/validate', payload)
  return res.data
}

export async function logout() {
  await getCsrfCookie()
  await http.post('/auth/logout')
  try {
    if (typeof document !== 'undefined') {
      document.cookie = 'XSRF-TOKEN=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT'
    }
  } catch {
  }
  try {
    ;((http.defaults.headers as unknown) as { common?: Record<string, string> }).common = ((http.defaults.headers as unknown) as { common?: Record<string, string> }).common || {}
    const common = ((http.defaults.headers as unknown) as { common?: Record<string, string> }).common!
    if ('X-XSRF-TOKEN' in common) delete common['X-XSRF-TOKEN']
  } catch {
  }
}

export async function me() {
  try {
    const res = await http.get('/api/user')
    return res.data && typeof res.data === 'object' && 'data' in res.data ? res.data.data : res.data
  } catch {
    return null
  }
}

export async function updateUser(payload: Record<string, unknown>) {
  const res = await http.put('/api/user', payload)
  return res.data
}

export default { login, forgotPassword, resetPassword, validateResetToken, logout, me, updateUser }
