import { describe, it, expect, beforeEach, afterEach } from 'vitest'
import http from './http'

type ReqConfig = { url?: string; headers?: Record<string, string> }

describe('http request interceptor - XSRF header', () => {
  // minimal mock document type for tests
  type MockDocument = { cookie: string }
  const originalDocument = (globalThis as unknown as { document?: MockDocument }).document

  beforeEach(() => {
    // provide a minimal document object with cookie support
    ;(globalThis as unknown as { document?: MockDocument }).document = (globalThis as unknown as { document?: MockDocument }).document || { cookie: '' }
    ;(globalThis as unknown as { document?: MockDocument }).document!.cookie = ''
  })

  afterEach(() => {
    ;(globalThis as unknown as { document?: MockDocument }).document = originalDocument
  })

  it('injects X-XSRF-TOKEN header when XSRF-TOKEN cookie exists', async () => {
  ;(globalThis as unknown as { document?: MockDocument }).document!.cookie = 'XSRF-TOKEN=' + encodeURIComponent('test-token==')

    const handlers = (http.interceptors.request as unknown as { handlers: Array<{ fulfilled: (c: ReqConfig) => Promise<ReqConfig> }> }).handlers
    expect(handlers.length).toBeGreaterThan(0)
    const fulfilled = handlers[0].fulfilled

    const config: ReqConfig = { url: '/api/some', headers: {} }
    const out = await fulfilled(config)

    expect(out).toBeDefined()
    expect(out.headers).toBeDefined()
    expect(out.headers!['X-XSRF-TOKEN']).toBe('test-token==')
  })

  it('does not inject header for /sanctum/csrf-cookie request', async () => {
  ;(globalThis as unknown as { document?: MockDocument }).document!.cookie = 'XSRF-TOKEN=' + encodeURIComponent('other-token')
    const handlers = (http.interceptors.request as unknown as { handlers: Array<{ fulfilled: (c: ReqConfig) => Promise<ReqConfig> }> }).handlers
    const fulfilled = handlers[0].fulfilled

    const config: ReqConfig = { url: '/sanctum/csrf-cookie', headers: {} }
    const out = await fulfilled(config)

    expect(out.headers!['X-XSRF-TOKEN']).toBeUndefined()
  })
})
