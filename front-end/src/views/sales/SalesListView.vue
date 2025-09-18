<template>
  <div class="p-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Vendas</h1>
      <div class="flex items-center space-x-2">
            <router-link :to="{ name: 'sales.create' }" class="bg-blue-600 text-white px-3 py-1 rounded">Novo</router-link>
          </div>
    </div>

    <div class="mb-4 border rounded p-3 bg-gray-50">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div v-if="isAdmin">
          <label class="block text-sm">Vendedor (filtro)</label>
          <div class="relative">
            <input v-model="sellerQuery" @input="searchSellers" @focus="onSellerFocus" @blur="onSellerBlur" placeholder="Buscar vendedor" class="border px-2 py-1 rounded w-full mb-1" />
            <div v-if="showDropdown && sellers.length" class="absolute left-0 right-0 bg-white border mt-1 rounded shadow z-10 max-h-48 overflow-auto">
              <button v-for="s in sellers" :key="s.id" type="button" class="w-full text-left px-3 py-2 hover:bg-gray-100" @click="selectSeller(s)">
                <div class="font-medium">{{ s.name }}</div>
                <div class="text-xs text-gray-500">{{ s.email }}</div>
              </button>
            </div>
          </div>
          <div v-if="selectedSellerName" class="mt-1 text-sm">Selecionado: <strong>{{ selectedSellerName }}</strong></div>
        </div>

        <div>
          <label class="block text-sm">Data inicial</label>
          <input type="date" v-model="filters.dateFrom" :max="today" class="border px-2 py-1 rounded w-full" />
        </div>

        <div>
          <label class="block text-sm">Data final</label>
          <input type="date" v-model="filters.dateTo" :max="today" class="border px-2 py-1 rounded w-full" />
        </div>
      </div>

      <div class="mt-3 flex items-center space-x-2">
        <button @click="applyFilters" class="bg-blue-600 text-white px-3 py-1 rounded">Filtrar</button>
        <button @click="clearFilters" class="px-3 py-1 rounded border">Limpar</button>
      </div>
    </div>

    <table class="w-full table-auto border-collapse">
      <thead>
        <tr class="text-left">
          <th v-if="!isSeller" class="p-2">Vendedor</th>
          <th class="p-2">Data</th>
          <th class="p-2">Valor</th>
          <th class="p-2">Comissão</th>
          <th class="p-2">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id" class="border-t">
          <td v-if="!isSeller" class="p-2">{{ item.seller?.name || '—' }}</td>
          <td class="p-2">{{ formatDate(item.date) }}</td>
          <td class="p-2">{{ formatCurrency(item.value) }}</td>
          <td class="p-2">{{ item.commission_formatted || formatCurrency(item.commission) }}</td>
          <td class="p-2">
            <router-link :to="{ name: 'sales.edit', params: { id: item.id } }" class="action-btn action-edit mr-2" :title="`Editar`">
              <IconEdit />
            </router-link>
            <button @click="() => item.id && remove(item.id)" class="action-btn action-delete" :title="`Excluir`" aria-label="Excluir">
              <IconTrash />
            </button>
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
      title="Confirmar exclusão"
      message="Tem certeza que deseja excluir esta venda?"
      confirm-text="Excluir"
      cancel-text="Cancelar"
      @confirm="confirmRemove"
      @cancel="cancelRemove"
      @close="cancelRemove"
    />
  </div>
</template>

<script lang="ts">
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import salesService from '@/services/salesService'
import sellerService from '@/services/sellerService'
import { useAuthStore } from '@/stores/auth'
import { useSalesFilters } from '@/stores/salesFilters'
import { formatDate, formatCurrency, todayStr } from '@/utils/format'
import { extractErrorMessage } from '@/utils/errors'
import { useNotificationStore } from '@/stores/notification'
import IconEdit from '@/components/icons/IconEdit.vue'
import IconTrash from '@/components/icons/IconTrash.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'

export default {
  name: 'SalesListView',
  components: { IconEdit, IconTrash, ConfirmModal },
  setup() {
  type Sale = { id?: number; seller?: { name?: string }; date?: string; value?: number; commission?: number; commission_formatted?: string }
  const items = ref<Sale[]>([])
  const filters = useSalesFilters()
  const dateFrom = filters.dateFrom
  const dateTo = filters.dateTo
    const meta = ref({ total: 0, per_page: 15, current_page: 1, last_page: 1, from: 0, to: 0 })
  const notif = useNotificationStore()
  const route = useRoute()
  const router = useRouter()
    const auth = useAuthStore()

    type Seller = { id: number; name?: string; email?: string }
  const sellers = ref<Seller[]>([])
  const sellerQuery = ref('')
  const selectedSellerName = ref<string | null>(null)
    const showDropdown = ref(false)
  const isAdmin = auth.hasRole('admin')
  const isSeller = auth.hasRole('sellers')

      const today = todayStr()

  let searchTimeout: number | undefined
    async function searchSellers() {
      if (searchTimeout) window.clearTimeout(searchTimeout)
      searchTimeout = window.setTimeout(async () => {
        try {
          const payload = await sellerService.list(1, sellerQuery.value)
          sellers.value = payload.data || []
          showDropdown.value = (sellers.value && sellers.value.length > 0)
        } catch {
          sellers.value = []
          showDropdown.value = false
        }
      }, 250)
    }

    async function doSearchNow() {
      if (searchTimeout) window.clearTimeout(searchTimeout)
      try {
        const payload = await sellerService.list(1, sellerQuery.value)
        sellers.value = payload.data || []
        showDropdown.value = (sellers.value && sellers.value.length > 0)
      } catch {
        sellers.value = []
        showDropdown.value = false
      }
    }

    function onSellerFocus() { doSearchNow() }
    function onSellerBlur() { window.setTimeout(() => { showDropdown.value = false }, 150) }
    function selectSeller(s: Seller) {
      filters.selectedSellerId = s.id
      selectedSellerName.value = s.name || s.email || String(s.id)
      sellerQuery.value = ''
      showDropdown.value = false
      // apply filter immediately when a seller is selected
      try { applyFilters() } catch { /* ignore */ }
    }

    

  async function fetch(page = 1) {
      try {
        const params: Record<string, unknown> = {}
        if (filters.selectedSellerId) params.seller_id = filters.selectedSellerId
        if (filters.dateFrom) params.date_from = filters.dateFrom
        if (filters.dateTo) params.date_to = filters.dateTo
        const payload = await salesService.list(page, params)
        items.value = (payload && payload.data) || []
        meta.value = {
          total: (payload && payload.total) || 0,
          per_page: (payload && payload.per_page) || 15,
          current_page: (payload && payload.current_page) || page,
          last_page: (payload && payload.last_page) || 1,
          from: (payload && payload.from) || 0,
          to: (payload && payload.to) || 0,
        }
      } catch (err: unknown) {
        notif.push(extractErrorMessage(err, 'Erro ao carregar vendas'), 'error')
      }
    }

    function changePage(p: number) {
  filters.page = p
  const query: Record<string, string> = {}
  if (filters.selectedSellerId) query.seller_id = String(filters.selectedSellerId)
  if (filters.dateFrom) query.date_from = filters.dateFrom
  if (filters.dateTo) query.date_to = filters.dateTo
      if (p && p > 1) query.page = String(p)
      else query.page = '1'
      router.push({ query })
    }

    const showConfirm = ref(false)
    const pendingRemoveId = ref<number | null>(null)

    function askRemove(id: number) {
      pendingRemoveId.value = id
      showConfirm.value = true
    }

    async function confirmRemove() {
      const id = pendingRemoveId.value
      if (!id) { showConfirm.value = false; return }
      try {
        await salesService.remove(id)
        console.log(notif, 'Sale removed, refreshing list');
        notif.push('Venda removida', 'success')
        changePage(meta.value.current_page)
      } catch (err: unknown) {
        notif.push(extractErrorMessage(err, 'Erro ao remover'), 'error')
      } finally {
        showConfirm.value = false
        pendingRemoveId.value = null
      }
    }

    function cancelRemove() {
      showConfirm.value = false
      pendingRemoveId.value = null
    }

    async function restoreFiltersFromRoute() {
  filters.hydrateFromQuery(route.query as Record<string, unknown>)
  meta.value.current_page = filters.page

      if (filters.selectedSellerId) {
        try {
          const s = await sellerService.get(Number(filters.selectedSellerId))
          selectedSellerName.value = s?.name || s?.email || String(filters.selectedSellerId)
        } catch {
          selectedSellerName.value = null
        }
      }

      fetch(filters.page)
    }

    onMounted(() => { restoreFiltersFromRoute() })

  function onSalesUpdated() { try { changePage(1) } catch {} }
    window.addEventListener('sales:updated', onSalesUpdated)

    onBeforeUnmount(() => window.removeEventListener('sales:updated', onSalesUpdated))

    watch(() => route.fullPath, () => {
      restoreFiltersFromRoute()
    })

    function applyFilters() {
      const todayStr = today
      if (filters.dateFrom && filters.dateFrom > todayStr) filters.dateFrom = todayStr
      if (filters.dateTo && filters.dateTo > todayStr) filters.dateTo = todayStr

      filters.page = 1
      const query = filters.toQuery()
      router.push({ query })
    }

    function clearFilters() {
      sellerQuery.value = ''
      selectedSellerName.value = null
      filters.clear()
      router.push({ query: {} })
    }

    return {
      items,
      fetch,
      meta,
      changePage,
  remove: askRemove,
  showConfirm,
  confirmRemove,
  cancelRemove,
      formatDate,
      formatCurrency,
      // filters
      filters,
      sellers,
      sellerQuery,
      searchSellers,
      doSearchNow,
      onSellerFocus,
      onSellerBlur,
      selectSeller,
      showDropdown,
      selectedSellerName,
      today,
      dateFrom,
      dateTo,
      clearFilters,
      isAdmin,
      applyFilters,
      isSeller,
    }
  },
}
</script>

<style scoped>
.action-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; }
.action-btn:hover { background: rgba(0,0,0,0.03); }
.action-edit { color: #2563eb; }
.action-delete { color: #dc2626; }
</style>
