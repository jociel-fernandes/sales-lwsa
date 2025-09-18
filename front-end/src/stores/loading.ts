import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useLoadingStore = defineStore('loading', () => {
  const counter = ref(0)

  const isLoading = computed(() => counter.value > 0)

  // keep track of timeouts for each start() call so we can clear them on stop()
  const timeouts: number[] = []
  const envTimeout = parseInt(import.meta.env.VITE_LOADING_TIMEOUT_MS || '')
  const TIMEOUT_MS = Number.isFinite(envTimeout) && envTimeout > 0 ? envTimeout : 15000

  const timedOut = ref(false)

  function start() {
    counter.value += 1
    // schedule safety stop
    const id = window.setTimeout(() => {
      counter.value = Math.max(0, counter.value - 1)
      timedOut.value = true
    }, TIMEOUT_MS)
    timeouts.push(id)
  }

  function stop() {
    counter.value = Math.max(0, counter.value - 1)
    // clear the last scheduled timeout
    const id = timeouts.pop()
    if (typeof id !== 'undefined') {
      clearTimeout(id)
    }
    if (counter.value === 0) timedOut.value = false
  }

  function reset() {
    counter.value = 0
    // clear all pending timeouts
    while (timeouts.length) {
      const id = timeouts.pop()!
      clearTimeout(id)
    }
    timedOut.value = false
  }

  return { counter, isLoading, timedOut, start, stop, reset }
})
