import { defineStore } from 'pinia'
import { ref } from 'vue'
import authService from '../services/authService'

type User = Record<string, unknown> | null

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User>(null)
  const loading = ref(false)
  const fetched = ref(false)

  async function fetchUser() {
    loading.value = true
    try {
      const u = await authService.me()
      user.value = u
    } finally {
      fetched.value = true
      loading.value = false
    }
  }

  /**
   * Check whether current user has a role. Supports common shapes:
   * - roles: string[]
   * - role: string
   * - is_admin: boolean
   */
  function hasRole(roleName: string): boolean {
    if (!user.value) return false
    const u = user.value as Record<string, unknown>
    const roles = u.roles as unknown
    const target = String(roleName).toLowerCase()
    
    
  // roles may be absent if backend returned a Resource envelope; ensure callers set user via authService.me()
  if (Array.isArray(roles)) {
      // roles may be string[] or object[] like [{ name: 'admin' }]
      return (roles as Array<unknown>).some(r => {

        if (typeof r === 'string') return r.toLowerCase() === target
        if (r && typeof r === 'object') {
          const rn = (r as Record<string, unknown>).name || (r as Record<string, unknown>).role || (r as Record<string, unknown>).title
          return typeof rn === 'string' && rn.toLowerCase() === target
        }
        return false
      })
    }
    const roleField = u.role as unknown
    if (typeof roleField === 'string') return roleField.toLowerCase() === target
    const isAdmin = u.is_admin as unknown
    if (typeof isAdmin === 'boolean') return isAdmin && target === 'admin'
    return false
  }

  /**
   * Check whether current user has a permission.
   * Expects user.permissions to be string[] but is defensive.
   */
  function hasPermission(permissionName: string): boolean {
    if (!user.value) return false
    const u = user.value as Record<string, unknown>
    const perms = u.permissions as unknown
    const target = String(permissionName).toLowerCase()
    if (Array.isArray(perms)) {
      // permissions may be string[] or object[] like [{ name: 'manage.sales' }]
      return (perms as Array<unknown>).some(p => {
        if (typeof p === 'string') return p.toLowerCase() === target
        if (p && typeof p === 'object') {
          const pn = (p as Record<string, unknown>).name || (p as Record<string, unknown>).permission || (p as Record<string, unknown>).title
          return typeof pn === 'string' && pn.toLowerCase() === target
        }
        return false
      })
    }
    return false
  }

  async function login(payload: { email: string; password: string }) {
    loading.value = true
    try {
      await authService.login(payload)
      await fetchUser()
      return true
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  async function forgotPassword(email: string) {
    loading.value = true
    try {
      await authService.forgotPassword(email)
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true
    try {
      await authService.logout()
      user.value = null
      try {
        const { default: router } = await import('../router')
        router.replace({ name: 'login' })
      } catch {
        // ignore routing errors
      }
    } finally {
      loading.value = false
    }
  }

  const isAuthenticated = () => !!user.value

  return { user, loading, fetched, fetchUser, login, forgotPassword, logout, isAuthenticated, hasRole, hasPermission }
})
