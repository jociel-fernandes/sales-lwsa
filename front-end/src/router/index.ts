import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/auth/LoginView.vue'
import ForgotPasswordView from '../views/auth/ForgotPasswordView.vue'
import ResetPasswordView from '../views/auth/ResetPasswordView.vue'
import { useAuthStore } from '../stores/auth'
import DashboardView from '../views/DashboardView.vue'
import ProfileView from '../views/ProfileView.vue'
import SettingsView from '../views/SettingsView.vue'
import AuthLayout from '../layouts/AuthLayout.vue'
import RootRedirect from '../views/RootRedirect.vue'

const routes = [
  {
    path: '/',
    name: 'root',
    component: RootRedirect,
  },
  {
    path: '/app',
    component: AuthLayout,
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', name: 'dashboard', component: DashboardView },
      { path: 'profile', name: 'profile', component: ProfileView },
      { path: 'settings', name: 'settings', component: SettingsView, meta: { requiresRole: 'admin' } },
      { path: 'sellers', name: 'sellers.index', component: () => import('../views/sellers/SellersListView.vue'), meta: { requiresRole: 'admin', requiresPermission: 'manage.sellers' } },
      { path: 'sellers/create', name: 'sellers.create', component: () => import('../views/sellers/SellerFormView.vue'), meta: { requiresRole: 'admin',requiresPermission: 'manage.sellers' } },
      { path: 'sellers/:id/edit', name: 'sellers.edit', component: () => import('../views/sellers/SellerFormView.vue'), meta: { requiresRole: 'admin', requiresPermission: 'manage.sellers' } },
  { path: 'sales', name: 'sales.index', component: () => import('../views/sales/SalesListView.vue'), meta: {} },
  { path: 'sales/create', name: 'sales.create', component: () => import('../views/sales/SaleFormView.vue'), meta: {} },
  { path: 'sales/:id/edit', name: 'sales.edit', component: () => import('../views/sales/SaleFormView.vue'), meta: {} },
      { path: '', redirect: { name: 'dashboard' } },
    ],
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPasswordView,
  },
  {
    path: '/password-reset/:token?',
    name: 'password-reset',
    component: ResetPasswordView,
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach(async (to: any, from: any, next: any) => {
  const auth = useAuthStore()

  if (to.name === 'login') {
    if (!auth.fetched) {
      try {
        await auth.fetchUser()
      } catch {
      }
    }

    if (auth.isAuthenticated()) {
      return next({ name: 'dashboard' })
    }
  }

  if (to.meta.requiresAuth) {
    if (!auth.fetched) {
      try {
        await auth.fetchUser()
      } catch {
      }
    }

    if (!auth.isAuthenticated()) {
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }
  }

  const requiredRole = to.meta.requiresRole as string | undefined
  if (requiredRole) {
    if (!auth.fetched) {
      try {
        await auth.fetchUser()
      } catch {
      }
    }

    if (!auth.hasRole(requiredRole)) {
      if (auth.isAuthenticated()) return next({ name: 'dashboard' })
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }
  }

  const requiredPermission = to.meta.requiresPermission as string | undefined
  if (requiredPermission) {
    if (!auth.fetched) {
      try {
        await auth.fetchUser()
      } catch {
      }
    }

    if (!auth.hasPermission(requiredPermission)) {
      if (auth.isAuthenticated()) return next({ name: 'dashboard' })
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }
  }

  return next()
})

export default router
