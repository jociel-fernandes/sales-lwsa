import { ref } from 'vue'
import { defineStore } from 'pinia'

export type NotificationLevel = 'info' | 'success' | 'warning' | 'error'

export type NotificationItem = {
  id: number
  message: string
  level: NotificationLevel
  timeout?: number
  createdAt?: number
}

let nextId = 1

export const useNotificationStore = defineStore('notification', () => {
  const items = ref<NotificationItem[]>([])
  const defaultTimeout = Number(import.meta.env.VITE_TOAST_TIMEOUT_MS || '5000')
  // map id -> timeout handle, para conseguir limpar quando o usuário dismiss manualmente
  const timers = new Map<number, ReturnType<typeof setTimeout>>()
  // intervalo de limpeza que checa notificações expiradas como fallback
  let cleanupInterval: ReturnType<typeof setInterval> | null = null

  function push(message: string, level: NotificationLevel = 'info', timeout = defaultTimeout) {
    const id = nextId++
    const now = Date.now()
    const it: NotificationItem = { id, message, level, timeout, createdAt: now }
    items.value.push(it)
    // garantir que timeout seja número inteiro e positivo
    const ms = Number(timeout) || 0
    if (ms > 0) {
      const h = setTimeout(() => {
        // ao expirar, remover e também limpar o timer do mapa
        dismiss(id)
        timers.delete(id)
      }, ms)
      timers.set(id, h)
    }

    // garantir que exista um intervalo de fallback que cheque expirations a cada 500ms
    if (!cleanupInterval) {
      cleanupInterval = setInterval(() => {
        const now = Date.now()
        const expired = items.value.filter((it: NotificationItem) => {
          const t = Number(it.timeout) || 0
          if (!it.createdAt || t <= 0) return false
          return (it.createdAt + t) <= now
        })
        for (const e of expired) {
          dismiss(e.id)
        }
        // se não há mais items, limpamos o interval
        if (items.value.length === 0 && cleanupInterval) {
          clearInterval(cleanupInterval)
          cleanupInterval = null
        }
      }, 500)
    }
    return id
  }

  function dismiss(id: number) {
  items.value = items.value.filter((i: NotificationItem) => i.id !== id)
    const h = timers.get(id)
    if (h) {
      clearTimeout(h)
      timers.delete(id)
    }
  }

  function clear() {
    // limpar timers
    for (const h of timers.values()) {
      clearTimeout(h)
    }
    timers.clear()
  items.value = []
  }

  return { items, push, dismiss, clear, defaultTimeout }
})
