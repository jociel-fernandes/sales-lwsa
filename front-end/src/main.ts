import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

const app = createApp(App)

// Use apenas UMA instância do Pinia em toda a app
const pinia = createPinia()
app.use(pinia)
app.use(router)

// Note: we avoid fetching the current user unconditionally here because
// that can trigger a 401-driven redirect to the login page while visiting
// public routes (for example password reset links). The router guard will
// fetch the user only when navigating to routes that require authentication.

app.mount('#app')
