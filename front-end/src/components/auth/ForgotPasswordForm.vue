<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useNotificationStore } from '../../stores/notification'

const email = ref('')
const loading = ref(false)

const auth = useAuthStore()
const notif = useNotificationStore()

async function submit() {
  loading.value = true
  try {
    await auth.forgotPassword(email.value)
    notif.push('Se o e-mail existir, um link de recuperação foi enviado.', 'success')
  } catch (e) {
    const err = e as { message?: string }
    notif.push(err?.message || 'Erro ao solicitar recuperação de senha.', 'error')
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
        <input id="email" v-model="email" type="email" required class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2" style="border-color:var(--color-primary)" />
      </div>

      
      <div class="flex justify-end">
        <button type="submit" :disabled="loading" class="bg-[var(--color-primary)] text-white px-4 py-2 rounded-md hover:brightness-90 disabled:opacity-60">Enviar link</button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.auth-form { max-width: 360px; margin: 0 auto; }
.info { color: #006400; margin: 8px 0 }
</style>
