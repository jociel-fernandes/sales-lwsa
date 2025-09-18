<template>
  <div class="p-4">
    <h1 class="text-2xl font-semibold mb-4">{{ isEdit ? 'Editar Seller' : 'Novo Seller' }}</h1>

    <form @submit.prevent="submit">
      <div class="mb-2">
        <label class="block mb-1">Nome</label>
        <input v-model="form.name" required class="border px-2 py-1 rounded w-full" />
        <div v-if="errors.name" class="text-red-600 text-sm mt-1">
          <div v-for="(m, idx) in errors.name" :key="idx">{{ m }}</div>
        </div>
      </div>

      <div class="mb-2">
        <label class="block mb-1">Email</label>
        <input v-model="form.email" type="email" required class="border px-2 py-1 rounded w-full" />
        <div v-if="errors.email" class="text-red-600 text-sm mt-1">
          <div v-for="(m, idx) in errors.email" :key="idx">{{ m }}</div>
        </div>
      </div>

      <div class="flex items-center space-x-2 mt-4">
        <button type="submit" :disabled="submitting" class="bg-blue-600 text-white px-3 py-1 rounded disabled:opacity-60">{{ submitting ? 'Enviando...' : 'Salvar' }}</button>
        <router-link :to="{ name: 'sellers.index' }" class="text-sm">Cancelar</router-link>
        <button v-if="isEdit && isAdmin" type="button" @click="openConfirmResend" class="ml-2 px-3 py-1 rounded border">Reenviar comissão</button>
      </div>
    </form>

    <ConfirmModal
      :show="showConfirm"
      title="Confirmação"
      :message="confirmMessage"
      confirm-text="Confirmar"
      cancel-text="Cancelar"
      @confirm="confirmResend"
      @cancel="closeConfirm"
      @close="closeConfirm"
    />
  </div>
</template>

<script lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import sellerService from '@/services/sellerService'
import { useNotificationStore } from '@/stores/notification'
import { useAuthStore } from '@/stores/auth'
import { extractErrorMessage } from '@/utils/errors'
import ConfirmModal from '@/components/ConfirmModal.vue'

export default {
  name: 'SellerFormView',
  components: { ConfirmModal },
  setup() {
    const route = useRoute()
    const router = useRouter()
  const notif = useNotificationStore()
  const isEdit = computed(() => !!route.params.id)
  const form = ref({ name: '', email: '' })
  const errors = ref<Record<string, string[]>>({})
  const submitting = ref(false)
  const auth = useAuthStore()
  const isAdmin = auth.hasRole('admin')

    async function load() {
      if (!isEdit.value) return
      try {
        const id = Number(route.params.id)
        const res = await sellerService.get(id)
        // sellerService.get returns either the data object or an envelope; handle both
        const data = (res && (res.data || res)) || {}
        form.value = { name: (data.name as string) || '', email: (data.email as string) || '' }
      } catch (e: unknown) {
        const maybeResponse = (e as Record<string, unknown>)?.response as Record<string, unknown> | undefined
        const status = maybeResponse?.status as number | undefined
        if (status === 404) {
          notif.push('Seller não encontrado, retornando à lista', 'warning')
          router.push({ name: 'sellers.index' })
          return
        }
        notif.push(extractErrorMessage(e, 'Erro ao carregar Seller'), 'error')
      }
    }

    async function submit() {
      errors.value = {}
      submitting.value = true
      try {
        if (isEdit.value) {
          const id = Number(route.params.id)
          await sellerService.update(id, form.value)
          notif.push('Seller atualizado', 'success')
          try { window.dispatchEvent(new CustomEvent('sellers:updated', { detail: { action: 'updated', id } })) } catch {}
        } else {
          await sellerService.create(form.value)
          notif.push('Seller criado e convite enviado', 'success')
          try { window.dispatchEvent(new CustomEvent('sellers:updated', { detail: { action: 'created' } })) } catch {}
        }
        router.push({ name: 'sellers.index' })
      } catch (e: unknown) {
        const maybeResponse = (e as Record<string, unknown>)?.response as Record<string, unknown> | undefined
        const status = maybeResponse?.status as number | undefined
        if (status === 422) {
          const respData = maybeResponse?.data as Record<string, unknown> | undefined
          const respErrors = respData?.errors as Record<string, string[]> | undefined
          if (respErrors) {
            errors.value = respErrors
          } else {
            notif.push('Validação falhou', 'error')
          }
        } else {
          notif.push(extractErrorMessage(e, 'Erro ao salvar Seller'), 'error')
        }
      } finally {
        submitting.value = false
      }
    }

    const showConfirm = ref(false)
    const confirmMessage = ref('Confirma o reenvio do e-mail de comissão para este seller?')
    function openConfirmResend() { showConfirm.value = true }
    function closeConfirm() { showConfirm.value = false }
    async function confirmResend() {
      try {
        if (!isEdit.value) return
        const id = Number(route.params.id)
        await sellerService.resendCommissionEmail(id)
        notif.push('E-mail enfileirado/enviado', 'success')
      } catch (e: unknown) {
        notif.push(extractErrorMessage(e, 'Erro ao reenviar e-mail'), 'error')
      } finally {
        closeConfirm()
      }
    }

    onMounted(load)

    return { form, submit, isEdit, errors, submitting, isAdmin, openConfirmResend, showConfirm, confirmMessage, confirmResend, closeConfirm }
  },
}
</script>

