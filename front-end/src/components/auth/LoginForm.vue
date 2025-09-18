<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useRouter, useRoute } from 'vue-router'

const email = ref('')
const password = ref('')
const error = ref<string | null>(null)
const loading = ref(false)

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

async function submit() {
  error.value = null
  loading.value = true
  try {
    await auth.login({ email: email.value, password: password.value })
    const redirect = (route.query.redirect as string) || '/'
    await router.replace(redirect)
  } catch (e: unknown) {
    const err = e as { message?: string }
    error.value = err?.message || 'Erro ao efetuar login.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-card">
    <form @submit.prevent="submit">
      <div class="auth-field">
        <label for="email">E-mail</label>
        <input id="email" v-model="email" type="email" required class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      </div>

      <div class="auth-field">
        <label for="password">Senha</label>
        <input id="password" v-model="password" type="password" required class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      </div>

      <div class="flex justify-end">
        <button type="submit" :disabled="loading" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-60">Entrar</button>
      </div>
      <div class="mt-3 text-right">
        <router-link to="/forgot-password" class="text-sm text-indigo-600 hover:underline">Esqueci minha senha</router-link>
      </div>
    </form>
  </div>
</template>

<style scoped>
.auth-form { max-width: 360px; margin: 0 auto; }
.error { color: #b00020; margin: 8px 0 }
</style>
