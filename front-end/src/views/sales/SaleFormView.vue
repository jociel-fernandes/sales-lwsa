<template>
  <div class="p-4 max-w-lg">
    <h1 class="text-2xl font-semibold mb-4">{{ isEdit ? 'Editar Venda' : 'Nova Venda' }}</h1>

    <form @submit.prevent="save">
      <div class="mb-3">
  <label class="block text-sm">Data</label>
  <input type="date" v-model="form.date" :max="today" class="border px-2 py-1 rounded w-full" required />
      </div>

      <div class="mb-3">
        <label class="block text-sm">Valor (R$)</label>
        <input type="number" step="0.01" min="0" v-model.number="form.value" class="border px-2 py-1 rounded w-full" required />
      </div>

      <div class="mb-3" v-if="isAdmin">
        <label class="block text-sm">Vendedor</label>
        <div class="relative">
          <input v-model="sellerQuery" @input="searchSellers" @focus="onSellerFocus" @blur="onSellerBlur" placeholder="Buscar vendedor" class="border px-2 py-1 rounded w-full mb-1" />
          <div v-if="showDropdown && sellers.length" class="absolute left-0 right-0 bg-white border mt-1 rounded shadow z-10 max-h-48 overflow-auto">
            <button v-for="s in sellers" :key="s.id" type="button" class="w-full text-left px-3 py-2 hover:bg-gray-100" @click="selectSeller(s)">
              <div class="font-medium">{{ s.name }}</div>
              <div class="text-xs text-gray-500">{{ s.email }}</div>
            </button>
          </div>
        </div>
        <div v-if="form.seller_id" class="mt-2 text-sm">Selecionado: <strong>{{ selectedSellerLabel }}</strong></div>
      </div>
   <input v-if="!isAdmin" type="hidden" name="seller_id" v-model="form.seller_id" />

      <div class="mb-3">
        <label class="block text-sm">Observações</label>
        <textarea v-model="form.description" class="border px-2 py-1 rounded w-full"></textarea>
      </div>

      <div class="flex items-center space-x-2">
        <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">{{ isEdit ? 'Atualizar' : 'Salvar' }}</button>
        <router-link :to="{ name: 'sales.index' }" class="text-sm">Cancelar</router-link>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import salesService from '@/services/salesService'
import sellerService from '@/services/sellerService'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { todayStr, normalizeDateForInput, isFutureDate } from '@/utils/format'
import { extractErrorMessage } from '@/utils/errors'

export default {
  name: 'SaleFormView',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const notif = useNotificationStore()
    const auth = useAuthStore()

    const isEdit = ref(!!route.params.id)
  type Seller = { id: number; name?: string; email?: string }
  const form = ref({ seller_id: null as number | null, date: '', value: null as number | null, description: '' })
    const sellers = ref<Seller[]>([])
    const sellerQuery = ref('')
    const sellerName = ref<string | null>(null)
    const sellerEmail = ref<string | null>(null)
    const showDropdown = ref(false)

    function selectSeller(s: Seller) {
      form.value.seller_id = s.id
      sellerName.value = s.name || null
      sellerEmail.value = s.email || null
      sellerQuery.value = ''
      showDropdown.value = false
    }

    const selectedSellerLabel = computed(() => {
      const id = form.value.seller_id
  const found = sellers.value.find((s: Seller) => s.id === id)
      if (found) return `${found.name} — ${found.email || ''}`
      if (sellerName.value) return `${sellerName.value}${sellerEmail.value ? ` — ${sellerEmail.value}` : ''}`
      return ''
    })

  const isAdmin = auth.hasRole('admin')
  const today = todayStr()

    async function load() {
      if (isEdit.value && route.params.id) {
        try {
          const payload = await salesService.get(Number(route.params.id))
          const d = payload.date
          form.value.date = normalizeDateForInput(d)
          form.value.value = payload.value
          form.value.description = payload.description
          form.value.seller_id = payload.seller_id || null
          sellerName.value = payload.seller?.name || null
          sellerEmail.value = payload.seller?.email || null
        } catch {
          notif.push('Venda não encontrada', 'warning')
          router.push({ name: 'sales.index' })
        }
      }

      if (!isAdmin) {
          try {
            if (!auth.fetched) {
              try { await auth.fetchUser() } catch { /* ignore fetch failures */ }
            }

            const u = (auth.user as Record<string, unknown> | null)
            if (u) {
                form.value.seller_id = (u as any).id
                sellerName.value = (u as any).name || sellerName.value;
            }

            if (!form.value.seller_id) {
              const me = await sellerService.me()
              sellerName.value = me.name
              form.value.seller_id = me.id
            }
          } catch {
            // ignore
          }
        }
    }

    let searchTimeout: number | undefined
    async function searchSellers() {
      if (searchTimeout) window.clearTimeout(searchTimeout)
      searchTimeout = window.setTimeout(async () => {
        try {
          const payload = await sellerService.list(1, sellerQuery.value)
          sellers.value = payload.data || []
          // show the dropdown when we have results
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

    function onSellerFocus() {
      doSearchNow()
    }

    function onSellerBlur() {
      window.setTimeout(() => { showDropdown.value = false }, 150)
    }

    async function save() {
      if (!form.value.date) return notif.push('Data obrigatória', 'error')
      if (isFutureDate(form.value.date)) return notif.push('A data não pode ser no futuro', 'error')
      if (!form.value.value || Number(form.value.value) <= 0) return notif.push('Valor deve ser maior que zero', 'error')

      try {
        const payload = {
          seller_id: form.value.seller_id,
          date: form.value.date,
          value: form.value.value,
          description: form.value.description,
        }

        if (isEdit.value && route.params.id) {
          await salesService.update(Number(route.params.id), payload)
          notif.push('Venda atualizada', 'success')
          try { window.dispatchEvent(new CustomEvent('sales:updated', { detail: { action: 'updated', id: route.params.id } })) } catch {}
        } else {
          await salesService.create(payload)
          notif.push('Venda criada', 'success')
          try { window.dispatchEvent(new CustomEvent('sales:updated', { detail: { action: 'created' } })) } catch {}
        }
        router.push({ name: 'sales.index' })
      } catch (err: unknown) {
        notif.push(extractErrorMessage(err, 'Erro ao salvar venda'), 'error')
      }
    }

    onMounted(() => load())

    return { isEdit, form, sellers, sellerQuery, searchSellers, save, isAdmin, sellerName, showDropdown, selectSeller, selectedSellerLabel, onSellerFocus, onSellerBlur, today }
  },
}
</script>

<style scoped>
.mb-3 { margin-bottom: 0.75rem }
</style>
