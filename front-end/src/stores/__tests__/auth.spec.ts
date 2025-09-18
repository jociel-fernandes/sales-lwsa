import { setActivePinia, createPinia } from 'pinia'
import { describe, beforeEach, test, expect } from 'vitest'
import { useAuthStore } from '../auth'

describe('auth store helpers', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  test('hasRole returns true for roles array', () => {
    const store = useAuthStore()
    store.user = { roles: ['admin', 'sellers'] } as any
    expect(store.hasRole('admin')).toBe(true)
    expect(store.hasRole('other')).toBe(false)
  })

  test('hasRole works with object-shaped roles', () => {
    const store = useAuthStore()
    store.user = { roles: [{ id: 1, name: 'admin' }, { id: 2, name: 'sellers' }] } as any
    expect(store.hasRole('admin')).toBe(true)
    expect(store.hasRole('sellers')).toBe(true)
    expect(store.hasRole('nope')).toBe(false)
  })

  test('hasPermission returns true for permissions array', () => {
    const store = useAuthStore()
    store.user = { permissions: ['manage.sales', 'read.reports'] } as any
    expect(store.hasPermission('manage.sales')).toBe(true)
    expect(store.hasPermission('nope')).toBe(false)
  })

  test('hasPermission works with object-shaped permissions', () => {
    const store = useAuthStore()
    store.user = { permissions: [{ id: 1, name: 'manage.sales' }] } as any
    expect(store.hasPermission('manage.sales')).toBe(true)
    expect(store.hasPermission('nope')).toBe(false)
  })
})
