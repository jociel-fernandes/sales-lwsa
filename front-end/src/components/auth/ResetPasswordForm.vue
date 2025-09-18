<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import ptBR from '@/locales/pt_BR.json'
import { useNotificationStore } from '../../stores/notification'
import { useRoute, useRouter } from 'vue-router'
import authService from '../../services/authService'

const route = useRoute()
const router = useRouter()

const token = ref((route.params.token as string) || (route.query.token as string) || '')
const email = ref((route.query.email as string) || '')
const password = ref('')
const passwordConfirmation = ref('')
const notif = useNotificationStore()
const loading = ref(false)

onMounted(async () => {
  // If a token is provided in the route and we have an email, validate it with the backend.
  // If invalid, notify and redirect to login.
  if (token.value && email.value) {
    try {
      await authService.validateResetToken({ token: token.value, email: email.value })
      // valid -> nothing to do, user can proceed
    } catch {
      // treat any error as invalid token (backend returns 422 for invalid)
      notif.push('Token de redefinição inválido', 'error')
      router.replace({ name: 'login' })
    }
  }
})

// Password rules
const minLength = 9
const hasMinLength = computed(() => password.value.length >= minLength)
const hasUpper = computed(() => /[A-Z]/.test(password.value))
const hasLower = computed(() => /[a-z]/.test(password.value))
// at least a digit or special char
const hasDigitOrSpecial = computed(() => /[0-9!@#\$%\^&\*\(\)\-_=+\[\]{};:'"\\|,.<>\/?`~]/.test(password.value))
// allowed characters: dot, letters, digits and symbols # @ - _ *
const allowedChars = /^[A-Za-z0-9.#@_\-\*]*$/
const hasOnlyAllowed = computed(() => allowedChars.test(password.value))

const passwordValid = computed(() => hasMinLength.value && hasUpper.value && hasLower.value && hasDigitOrSpecial.value && hasOnlyAllowed.value)

// auto-clear notifications when password changes (optional UX)
watch(password, () => {
  // nothing for now; placeholder if needed
})

// auto-sanitize password as user types: remove chars not matching allowedChars
watch(password, (val) => {
  if (!val) return
  // sanitize by removing disallowed chars
  let sanitized = ''
  for (const ch of val.split('')) {
    if (/^[A-Za-z0-9.#@_\-\*]$/.test(ch)) sanitized += ch
  }
  if (sanitized !== val) {
    password.value = sanitized
  }
})

const ruleMinText = computed(() => (ptBR.reset.rule_min_length || 'Mínimo de {min} caracteres').replace('{min}', String(minLength)))


async function submit() {
  loading.value = true
  function safeMessage(err: unknown): string | null {
    if (!err) return null
    if (typeof err === 'object' && err !== null && 'message' in err) {
      const m = (err as Record<string, unknown>).message
      return typeof m === 'string' ? m : null
    }
    return null
  }

  try {
    // simple frontend validation
    if (!email.value) {
      notif.push(ptBR.reset.error_fill_email || 'Preencha o e-mail', 'warning')
      throw new Error('validation')
    }
    if (!passwordValid.value) {
      notif.push(ptBR.reset.error_password_rules || 'A senha não satisfaz as regras de segurança', 'warning')
      throw new Error('validation')
    }
    if (password.value !== passwordConfirmation.value) {
      notif.push(ptBR.reset.error_confirmation_mismatch || 'A confirmação da senha não confere', 'warning')
      throw new Error('validation')
    }

    await authService.resetPassword({ token: token.value, email: email.value, password: password.value, password_confirmation: passwordConfirmation.value })
  // use toast for backend success and ensure it lasts at least the default timeout so it is visible after redirect
  notif.push(ptBR.reset.success_reset || 'Senha redefinida com sucesso. Você pode entrar agora.', 'success', notif.defaultTimeout || 8000)
    // optional: redirect to login after short delay
    setTimeout(() => router.replace({ name: 'login' }), 1200)
  } catch (e) {
    const err = e as { message?: string }
    // client-side validation errors are shown inline
    const svcMsg = safeMessage(err)
    if (svcMsg) {
      notif.push(svcMsg, 'error')
    } else {
      notif.push('Erro ao redefinir senha.', 'error')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-card">
    <form @submit.prevent="submit">
      <div v-if="!token" class="auth-field">
        <label for="token">{{ ptBR.reset.token_label }}</label>
        <input id="token" v-model="token" type="text" required />
      </div>

      <div class="auth-field">
        <label for="email">{{ ptBR.reset.email_label }}</label>
        <input id="email" v-model="email" type="email" required :readonly="!!email" :class="{ 'bg-gray-100': !!email }" />
      </div>

      <div class="auth-field">
        <label for="password">Nova senha</label>
        <input id="password" v-model="password" type="password" required class="border px-2 py-1 rounded w-full" :class="{ 'border-red-500': password && !passwordValid }" />
        <div class="mt-2 text-sm">
          <div class="flex items-center justify-between">
            <div :class="['flex items-center gap-2', hasMinLength ? 'text-green-600' : 'text-gray-600']">
              <span class="w-3 h-3 rounded-full" :style="{ background: hasMinLength ? '#16a34a' : '#d1d5db' }"></span>
              {{ ruleMinText }}
            </div>
            <div class="text-xs text-gray-500" title="{{ ptBR.reset.hint_allowed }}">{{ ptBR.reset.hint_allowed }}</div>
          </div>
          <div :class="['flex items-center gap-2 mt-1', hasUpper ? 'text-green-600' : 'text-gray-600']">
            <span class="w-3 h-3 rounded-full" :style="{ background: hasUpper ? '#16a34a' : '#d1d5db' }"></span>
            {{ ptBR.reset.rule_upper }}
          </div>
          <div :class="['flex items-center gap-2 mt-1', hasLower ? 'text-green-600' : 'text-gray-600']">
            <span class="w-3 h-3 rounded-full" :style="{ background: hasLower ? '#16a34a' : '#d1d5db' }"></span>
            {{ ptBR.reset.rule_lower }}
          </div>
          <div :class="['flex items-center gap-2 mt-1', hasDigitOrSpecial ? 'text-green-600' : 'text-gray-600']">
            <span class="w-3 h-3 rounded-full" :style="{ background: hasDigitOrSpecial ? '#16a34a' : '#d1d5db' }"></span>
            {{ ptBR.reset.rule_digit_special }}
          </div>
          <div :class="['flex items-center gap-2 mt-1', hasOnlyAllowed ? 'text-green-600' : 'text-red-600']">
            <span class="w-3 h-3 rounded-full" :style="{ background: hasOnlyAllowed ? '#16a34a' : '#dc2626' }"></span>
            {{ ptBR.reset.rule_allowed }} [ {{ ptBR.reset.hint_allowed }} ]
          </div>
        </div>
      </div>

      <div class="auth-field">
  <label for="password_confirmation">{{ ptBR.reset.confirmation_label }}</label>
        <input id="password_confirmation" v-model="passwordConfirmation" type="password" required class="border px-2 py-1 rounded w-full" :class="{ 'border-red-500': passwordConfirmation && passwordConfirmation !== password }" />
        <div v-if="passwordConfirmation && passwordConfirmation !== password" class="text-red-600 text-sm mt-1">{{ ptBR.reset.error_confirmation_mismatch }}</div>
      </div>

      

      <div class="flex justify-end">
        <button type="submit" :disabled="loading || !passwordValid" class="btn-primary">{{ ptBR.reset.button_reset }}</button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.auth-form { max-width: 420px; margin: 0 auto; }
</style>
