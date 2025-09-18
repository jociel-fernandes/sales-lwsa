<template>
  <div class="p-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Sellers</h1>
      <div>
        <input v-model="q" @keyup.enter="() => fetch()" placeholder="Buscar por nome ou email" class="border px-2 py-1 rounded mr-2" />
        <router-link :to="{ name: 'sellers.create' }" class="bg-blue-600 text-white px-3 py-1 rounded">Novo</router-link>
      </div>
    </div>

    <table class="w-full table-auto border-collapse">
      <thead>
        <tr class="text-left">
          <th class="p-2">Nome</th>
          <th class="p-2">Email</th>
          <th class="p-2">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id" class="border-t">
          <td class="p-2">{{ item.name }}</td>
          <td class="p-2">{{ item.email }}</td>
          <td class="p-2">
            <router-link :to="{ name: 'sellers.edit', params: { id: item.id } }" class="action-btn action-edit mr-2" :title="`Editar ${item.name}`">
              <IconEdit />
            </router-link>
            <button @click="() => item.id && remove(item.id)" class="action-btn action-delete" :title="`Excluir ${item.name}`" aria-label="Excluir">
              <IconTrash />
            </button>
            <button v-if="isAdmin" @click="() => resendEmail(item.id)" class="ml-2 px-2 py-1 rounded border text-sm">Reenviar comissão</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="mt-4 flex items-center justify-between">
      <div>Mostrando {{ meta.from }} - {{ meta.to }} de {{ meta.total }}</div>
      <div class="space-x-2">
        <button :disabled="meta.current_page <= 1" @click="changePage(meta.current_page - 1)">Anterior</button>
        <button :disabled="meta.current_page >= meta.last_page" @click="changePage(meta.current_page + 1)">Próxima</button>
      </div>
    </div>
    <ConfirmModal
      :show="showConfirm"
      title="Confirmação"
      :message="confirmMessage"
      confirm-text="Confirmar"
      cancel-text="Cancelar"
      @confirm="confirmAction"
      @cancel="cancelAction"
      @close="cancelAction"
    />
  </div>
</template>

<script lang="ts">
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import sellerService from '@/services/sellerService'
import { useNotificationStore } from '@/stores/notification'
import { useAuthStore } from '@/stores/auth'
import IconEdit from '@/components/icons/IconEdit.vue'
import IconTrash from '@/components/icons/IconTrash.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'

export default {
  name: 'SellersListView',
  components: { IconEdit, IconTrash, ConfirmModal },
  setup() {
    type Seller = { id?: number; name: string; email: string }
    const items = ref<Seller[]>([])
    const q = ref<string>('')
    const meta = ref({ total: 0, per_page: 15, current_page: 1, last_page: 1, from: 0, to: 0 })
  const notif = useNotificationStore()
  const route = useRoute()
  const auth = useAuthStore()
  const isAdmin = auth.hasRole('admin')

    async function fetch(page = 1) {
      try {
        // sellerService.list returns the Laravel pagination object
        const payload = await sellerService.list(page, q.value)
        items.value = (payload && payload.data) || []
        meta.value = {
          total: (payload && payload.total) || 0,
          per_page: (payload && payload.per_page) || 15,
          current_page: (payload && payload.current_page) || page,
          last_page: (payload && payload.last_page) || 1,
          from: (payload && payload.from) || 0,
          to: (payload && payload.to) || 0,
        }
      } catch {
        notif.push('Erro ao carregar sellers', 'error')
      }
    }

    function changePage(p: number) {
      fetch(p)
    }

    const showConfirm = ref(false)
    const confirmMessage = ref('')
    const pendingAction: { type: 'remove' | 'resend' | null; id: number | null } = { type: null, id: null }

    function askRemove(id: number) {
      pendingAction.type = 'remove'
      pendingAction.id = id
      confirmMessage.value = 'Confirma exclusão deste seller?'
      showConfirm.value = true
    }

    async function doRemove(id: number) {
      await sellerService.remove(id)
      notif.push('Seller removido', 'success')
      fetch(meta.value.current_page)
    }

    onMounted(() => fetch())

    function askResend(id?: number) {
      if (!id) return
      pendingAction.type = 'resend'
      pendingAction.id = id
      confirmMessage.value = 'Confirma o reenvio do e-mail de comissão para este seller?'
      showConfirm.value = true
    }

    async function doResend(id: number) {
      await sellerService.resendCommissionEmail(id)
      notif.push('E-mail enfileirado/enviado', 'success')
    }

    async function confirmAction() {
      try {
        if (pendingAction.type === 'remove' && pendingAction.id) await doRemove(pendingAction.id)
        if (pendingAction.type === 'resend' && pendingAction.id) await doResend(pendingAction.id)
      } catch {
        if (pendingAction.type === 'remove') notif.push('Erro ao remover', 'error')
        if (pendingAction.type === 'resend') notif.push('Erro ao reenviar e-mail', 'error')
      } finally {
        showConfirm.value = false
        pendingAction.type = null
        pendingAction.id = null
      }
    }

    function cancelAction() {
      showConfirm.value = false
      pendingAction.type = null
      pendingAction.id = null
    }

    function onSellersUpdated() {
      try {
        fetch(1)
      } catch {}
    }
    window.addEventListener('sellers:updated', onSellersUpdated)

    onBeforeUnmount(() => {
      window.removeEventListener('sellers:updated', onSellersUpdated)
    })

    // Re-fetch when route changes (for example after redirect from create/edit)
    watch(() => route.fullPath, () => fetch(meta.value.current_page))

    // Optional: re-fetch when search query cleared (user typed and pressed enter already triggers fetch)
    watch(q, (val: string, old: string) => {
      if (old && !val) fetch(1)
    })

    return { items, q, fetch, meta, changePage, remove: askRemove, isAdmin, resendEmail: askResend, showConfirm, confirmMessage, confirmAction, cancelAction }
  },
}
</script>

<style scoped>
.action-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; }
.action-btn:hover { background: rgba(0,0,0,0.03); }
.action-edit { color: #2563eb; }
.action-delete { color: #dc2626; }
</style>

<style scoped>
table th, table td { border-bottom: 1px solid #eee }
</style>
