
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import authService from '@/services/authService'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'

type LocalUser = { name?: string; email?: string }

const auth = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const saving = ref(false)
const error = ref<string | null>(null)
const notif = useNotificationStore()

onMounted(() => {
  if (auth.user) {
    const u = auth.user as LocalUser
    name.value = u.name || ''
    email.value = u.email || ''
  }
})

const canSave = computed(() => name.value.trim().length > 0 && (password.value === passwordConfirmation.value))

async function save() {
  error.value = null
  if (!canSave.value) return
  saving.value = true
  try {
    const payload: Record<string, unknown> = { name: name.value }
    if (password.value) {
      payload['password'] = password.value
      payload['password_confirmation'] = passwordConfirmation.value
    }
    await authService.updateUser(payload)
    // refresh local user
    await auth.fetchUser()
  } catch (err: unknown) {
    let msg = 'Erro ao salvar'
    try {
      if (err && typeof err === 'object') {
        msg = JSON.stringify(err)
      } else if (typeof err === 'string') {
        msg = err
      }
    } catch {
      // ignore stringify errors
    }
    error.value = msg
  } finally {
    saving.value = false
  }
  notif.push('Perfil salvo com sucesso', 'success')
}

// logout handler removed: Profile view now uses a Cancel button that navigates back
</script>

<template>
  <section>
    <h1 class="text-2xl font-semibold mb-4">Perfil</h1>

    <div class="p-6 bg-white rounded shadow max-w-xl">
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Nome</label>
        <input v-model="name" class="mt-1 block w-full rounded border px-3 py-2" />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input v-model="email" class="mt-1 block w-full rounded border px-3 py-2" disabled />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Nova senha</label>
        <input type="password" v-model="password" class="mt-1 block w-full rounded border px-3 py-2" />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Confirmação da senha</label>
        <input type="password" v-model="passwordConfirmation" class="mt-1 block w-full rounded border px-3 py-2" />
      </div>

      <div class="flex items-center gap-3">
        <button @click="save" :disabled="!canSave || saving" class="px-4 py-2 bg-blue-600 text-white rounded">Salvar</button>
  <button @click="() => router.back()" class="px-4 py-2 bg-gray-200 rounded">Cancelar</button>
      </div>

      <p v-if="error" class="text-red-600 mt-3">{{ error }}</p>
      <p v-if="!canSave && password !== passwordConfirmation" class="text-yellow-600 mt-2">As senhas não conferem</p>
      
    </div>
    </section>
</template>

<style scoped></style>

