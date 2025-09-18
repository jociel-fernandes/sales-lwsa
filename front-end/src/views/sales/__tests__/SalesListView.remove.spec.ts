import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import SalesListView from '../SalesListView.vue'

const pushSpy = vi.fn()

vi.mock('vue-router', () => ({
  useRoute: () => ({ fullPath: '/app/sales', query: {} }),
  useRouter: () => ({ push: pushSpy }),
}))

vi.mock('@/stores/auth', () => ({ useAuthStore: () => ({ hasRole: () => true, fetched: true }) }))
vi.mock('@/stores/notification', () => ({ useNotificationStore: () => ({ push: vi.fn() }) }))

vi.mock('@/stores/salesFilters', () => ({
  useSalesFilters: () => ({
    selectedSellerId: null as number | null,
    dateFrom: null as string | null,
    dateTo: null as string | null,
    page: 1,
    hydrateFromQuery: vi.fn(),
    toQuery: vi.fn(() => ({})),
    clear: vi.fn(),
  }),
}))

vi.mock('@/services/salesService', () => ({
  default: {
    list: vi.fn(async () => ({ data: [], total: 0, per_page: 15, current_page: 1, last_page: 1, from: 0, to: 0 })),
    remove: vi.fn(async () => ({})),
  },
}))

vi.mock('@/services/sellerService', () => ({
  default: {
    list: vi.fn(async () => ({ data: [] })),
    get: vi.fn(async () => ({ id: 1, name: 'S 1', email: 's1@example.com' })),
  },
}))

describe('SalesListView remove via modal', () => {
  beforeEach(() => vi.clearAllMocks())

  it('abre modal e confirma remoção', async () => {
    const wrapper = mount(SalesListView, { global: { stubs: { 'router-link': true } } })
    await wrapper.vm.$nextTick()

    // simula existir um item
    const vm = wrapper.vm as any
    vm.items = [{ id: 123, date: '2025-01-01', value: 10 }]
    await wrapper.vm.$nextTick()

    // clicar em remover deve abrir modal
    vm.remove(123)
    await wrapper.vm.$nextTick()
    expect(vm.showConfirm).toBe(true)

    // confirmar remoção
    await vm.confirmRemove()
    expect(vm.showConfirm).toBe(false)
    expect(pushSpy).toHaveBeenCalled()
  })
})
