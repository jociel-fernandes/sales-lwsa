import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import SalesListView from '../SalesListView.vue'

// Mocks
const pushSpy = vi.fn()
const replaceSpy = vi.fn()

vi.mock('vue-router', () => ({
  useRoute: () => ({ fullPath: '/app/sales?page=2&seller_id=5', query: { page: '2', seller_id: '5' } }),
  useRouter: () => ({ push: pushSpy, replace: replaceSpy }),
}))

const hydrateSpy = vi.fn()
const toQuerySpy = vi.fn(() => ({ page: '1', seller_id: '9' }))
const clearSpy = vi.fn()

vi.mock('@/stores/salesFilters', () => ({
  useSalesFilters: () => ({
    selectedSellerId: null as number | null,
    dateFrom: null as string | null,
    dateTo: null as string | null,
    page: 1,
    hydrateFromQuery: hydrateSpy,
    toQuery: toQuerySpy,
    clear: clearSpy,
  }),
}))

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({ hasRole: (r: string) => r === 'admin', fetched: true }),
}))

const notifSpy = vi.fn()
vi.mock('@/stores/notification', () => ({ useNotificationStore: () => ({ push: notifSpy }) }))

vi.mock('@/services/salesService', () => ({
  default: {
    list: vi.fn(async () => ({ data: [], total: 0, per_page: 15, current_page: 1, last_page: 1, from: 0, to: 0 })),
    remove: vi.fn(async () => ({})),
  },
}))

vi.mock('@/services/sellerService', () => ({
  default: {
    list: vi.fn(async () => ({ data: [{ id: 9, name: 'Carol', email: 'carol@example.com' }] })),
    get: vi.fn(async (id: number) => ({ id, name: 'Seller ' + id, email: 's'+id+'@example.com' })),
  },
}))

describe('SalesListView - filtros e typeahead', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('hidrata filtros do query e faz fetch inicial', async () => {
    const wrapper = mount(SalesListView, { global: { stubs: { 'router-link': true } } })
    await wrapper.vm.$nextTick()
    expect(hydrateSpy).toHaveBeenCalled()
  })

  it('applyFilters empurra query construído pelo store', async () => {
    const wrapper = mount(SalesListView, { global: { stubs: { 'router-link': true } } })
    const vm = wrapper.vm as unknown as { applyFilters: () => void }
    vm.applyFilters()
    expect(toQuerySpy).toHaveBeenCalled()
    expect(pushSpy).toHaveBeenCalledWith({ query: { page: '1', seller_id: '9' } })
  })

  it('typeahead seleciona seller e aplica filtro', async () => {
    const wrapper = mount(SalesListView, { global: { stubs: { 'router-link': true } } })
    const input = wrapper.find('input[placeholder="Buscar vendedor"]')
    await input.setValue('car')
    await input.trigger('focus')
    await new Promise(r => setTimeout(r, 10))
    const btn = wrapper.findAll('button').find(b => b.text().includes('Carol'))
    expect(btn).toBeTruthy()
    await btn!.trigger('click')
    expect(pushSpy).toHaveBeenCalled()
  })
})
