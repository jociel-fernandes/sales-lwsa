import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref, nextTick } from 'vue'

// Mock the notification store to avoid pinia/testing dependency
type Item = { id: number; message: string; level: string; timeout?: number }
const itemsRef = ref<Item[]>([])
const storeMock = {
  items: itemsRef,
  push: (message: string, level: string = 'info', timeout = 0) => {
    const id = Math.floor(Math.random() * 100000)
    itemsRef.value.push({ id, message, level, timeout })
    return id
  },
  dismiss: (id: number) => {
    itemsRef.value = itemsRef.value.filter((it: Item) => it.id !== id)
  },
}

vi.mock('@/stores/notification', () => ({
  useNotificationStore: () => storeMock,
}))

// Import the component after mocking
import NotificationToast from '../NotificationToast.vue'

describe('NotificationToast', () => {
  it('renders pushed messages', async () => {
    // reset items
    itemsRef.value = []
  const wrapper = mount(NotificationToast)
  storeMock.push('Hello world', 'success', 0)
  await nextTick(); await nextTick()
  const msgs = wrapper.findAll('[data-testid="toast-msg"]').map((n: any) => n.text())
  expect(msgs).toContain('Hello world')
  })

  it('dismiss removes message', async () => {
    itemsRef.value = []
  const wrapper = mount(NotificationToast)
  const id = storeMock.push('To be dismissed', 'info', 0)
  await nextTick(); await nextTick()
  let msgs = wrapper.findAll('[data-testid="toast-msg"]').map((n: any) => n.text())
  expect(msgs).toContain('To be dismissed')
    storeMock.dismiss(id)
  await nextTick(); await nextTick()
  msgs = wrapper.findAll('[data-testid="toast-msg"]').map((n: any) => n.text())
  expect(msgs).not.toContain('To be dismissed')
  })
})
