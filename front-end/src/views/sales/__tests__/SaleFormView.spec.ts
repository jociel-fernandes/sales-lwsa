import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import SaleFormView from '../SaleFormView.vue'

// mock services and stores
vi.mock('@/services/sellerService', () => ({
  default: {
    list: vi.fn(async () => ({ data: [{ id: 1, name: 'Alice', email: 'alice@example.com' }] })),
    me: vi.fn(async () => ({ id: 2, name: 'Bob', email: 'bob@example.com' })),
  },
}))

vi.mock('@/services/salesService', () => ({
  default: {
    create: vi.fn(async () => ({ id: 10 })),
    update: vi.fn(async () => ({})),
  },
}))

// simple mock stores
vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({ hasRole: (r: string) => r === 'admin' }),
}))

// shared spy for notifications so component and test see the same instance
const pushSpy = vi.fn()
vi.mock('@/stores/notification', () => ({
  useNotificationStore: () => ({ push: pushSpy }),
}))

// mock vue-router injections and components
vi.mock('vue-router', () => ({
  useRoute: () => ({ params: {}, query: {} }),
  useRouter: () => ({ push: vi.fn(), replace: vi.fn() }),
}))

describe('SaleFormView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('shows dropdown on focus and selecting a seller fills the form', async () => {
  const wrapper = mount(SaleFormView, { global: { stubs: { 'router-link': true } } })

    // find seller input
    const input = wrapper.find('input[placeholder="Buscar vendedor"]')
    expect(input.exists()).toBe(true)

    // focus should trigger immediate search and show dropdown
    await input.trigger('focus')
    // wait a tick for async
    await wrapper.vm.$nextTick()

  // dropdown item should appear — find button with text Alice
  const aliceBtn = wrapper.findAll('button').find((b: any) => b.text().includes('Alice'))
  expect(aliceBtn).toBeTruthy()

  // click to select
  await aliceBtn!.trigger('click')
  await wrapper.vm.$nextTick()

  // ensure seller_id populated via component vm
  type VmShape = { form: { seller_id?: number } }
  const vm = wrapper.vm as unknown as VmShape
  expect(vm.form.seller_id).toBe(1)
  })

  it('blocks save when date is in the future', async () => {
  const wrapper = mount(SaleFormView, { global: { stubs: { 'router-link': true } } })
    // set a future date
    const dt = new Date()
    dt.setDate(dt.getDate() + 2)
    const future = dt.toISOString().slice(0, 10)

  type VmShape2 = { form: { date: string; value: number }; save: () => Promise<void> }
  const vm2 = wrapper.vm as unknown as VmShape2
  vm2.form.date = future
  vm2.form.value = 100

  // call save
  await vm2.save()

  expect(pushSpy).toHaveBeenCalled()
  // ensure salesService.create was not called
  const salesService = (await import('@/services/salesService')).default
  expect(salesService.create).not.toHaveBeenCalled()
  })
})
