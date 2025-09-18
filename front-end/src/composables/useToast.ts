import { ref } from 'vue'

type Toast = { id: number; msg: string; type?: 'success' | 'error' | 'info' }

const toasts = ref<Toast[]>([])
let nextId = 1

// position controls where to render toasts: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left'
const position = ref<'top-right' | 'top-left' | 'bottom-right' | 'bottom-left'>('top-right')

export function setToastPosition(pos: typeof position.value) {
  position.value = pos
}

export function pushToast(msg: string, type: Toast['type'] = 'info', ttl = 6000) {
  const id = nextId++
  toasts.value.push({ id, msg, type })
  setTimeout(() => {
    const idx = toasts.value.findIndex(t => t.id === id)
    if (idx !== -1) toasts.value.splice(idx, 1)
  }, ttl)
  return id
}

export function removeToast(id: number) {
  const idx = toasts.value.findIndex(t => t.id === id)
  if (idx !== -1) toasts.value.splice(idx, 1)
}

export function useToasts() {
  return { toasts, position }
}
