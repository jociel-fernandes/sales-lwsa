<script setup lang="ts">
import { computed } from 'vue'
import { useNotificationStore } from '@/stores/notification'

const store = useNotificationStore()
// usar diretamente o ref do store para garantir reatividade simples no template
const items = store.items

// posição configurável: bottom-center (default), bottom-right, top-right, top-center
const envAny: any = (import.meta as any)?.env || {}
const position = (envAny.VITE_TOAST_POSITION || (globalThis as any)?.__APP_TOAST_POS || 'bottom-center') as string

const containerClass = computed(() => {
  switch (position) {
    case 'bottom-right':
      return 'fixed bottom-4 right-4 z-[99999] flex flex-col gap-2 pointer-events-auto'
    case 'top-right':
      return 'fixed top-4 right-4 z-[99999] flex flex-col gap-2 pointer-events-auto'
    case 'top-center':
      return 'fixed top-4 left-1/2 -translate-x-1/2 z-[99999] flex flex-col items-center gap-2 px-2 w-full pointer-events-auto'
    case 'bottom-center':
    default:
      return 'fixed bottom-4 left-1/2 -translate-x-1/2 z-[99999] flex flex-col items-center gap-2 px-2 w-full pointer-events-auto'
  }
})

// Detectar ambiente de teste (Vitest): via MODE === 'test' ou presença global de vi
const isTest = ((import.meta as any)?.env?.MODE === 'test') || !!((globalThis as any)?.vi)
const useTeleport = computed(() => !isTest)

function cls(level: string) {
  switch (level) {
    case 'success': return 'bg-green-500'
    case 'warning': return 'bg-yellow-500'
    case 'error': return 'bg-red-500'
    default: return 'bg-gray-700'
  }
}
</script>

<template>
  <teleport v-if="useTeleport" to="body">
    <div :class="containerClass">
      <div class="w-full max-w-md pointer-events-auto">
        <transition-group name="toast" tag="div">
          <div v-for="it in items" :key="it.id" class="w-full shadow-lg rounded overflow-hidden mb-2" data-testid="toast">
            <div :class="['p-3 text-white flex gap-3 items-start', cls(it.level)]">
              <div class="shrink-0 mt-0.5">
                <svg v-if="it.level === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15 5 11.586a1 1 0 111.414-1.414L8.414 12.172l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <svg v-else-if="it.level === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-2.293-6.293a1 1 0 011.414 0L10 11.586l.879-.879a1 1 0 111.414 1.414L11.414 13l.879.879a1 1 0 01-1.414 1.414L10 14.414l-.879.879a1 1 0 01-1.414-1.414L8.586 13l-.879-.879a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                <svg v-else-if="it.level === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l6.518 11.586c.75 1.333-.213 3.015-1.742 3.015H3.481c-1.53 0-2.493-1.682-1.743-3.015L8.257 3.1zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-.993.883L9 6v4a1 1 0 001.993.117L11 10V6a1 1 0 00-1-1z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016.832 3H3.168a2 2 0 00-1.165 2.884z" />
                </svg>
              </div>
              <div class="flex-1 text-sm" data-testid="toast-msg">{{ it.message }}</div>
              <button class="ml-3 text-xs opacity-80" @click="store.dismiss(it.id)">Fechar</button>
            </div>
          </div>
        </transition-group>
      </div>
    </div>
  </teleport>
  <div v-else :class="containerClass">
    <div class="w-full max-w-md pointer-events-auto">
      <transition-group name="toast" tag="div">
        <div v-for="it in items" :key="it.id" class="w-full shadow-lg rounded overflow-hidden mb-2" data-testid="toast">
          <div :class="['p-3 text-white flex gap-3 items-start', cls(it.level)]">
            <div class="shrink-0 mt-0.5">
              <svg v-if="it.level === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15 5 11.586a1 1 0 111.414-1.414L8.414 12.172l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              <svg v-else-if="it.level === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-2.293-6.293a1 1 0 011.414 0L10 11.586l.879-.879a1 1 0 111.414 1.414L11.414 13l.879.879a1 1 0 01-1.414 1.414L10 14.414l-.879.879a1 1 0 01-1.414-1.414L8.586 13l-.879-.879a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
              <svg v-else-if="it.level === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l6.518 11.586c.75 1.333-.213 3.015-1.742 3.015H3.481c-1.53 0-2.493-1.682-1.743-3.015L8.257 3.1zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-.993.883L9 6v4a1 1 0 001.993.117L11 10V6a1 1 0 00-1-1z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016.832 3H3.168a2 2 0 00-1.165 2.884z" />
              </svg>
            </div>
            <div class="flex-1 text-sm" data-testid="toast-msg">{{ it.message }}</div>
            <button class="ml-3 text-xs opacity-80" @click="store.dismiss(it.id)">Fechar</button>
          </div>
        </div>
      </transition-group>
    </div>
  </div>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 220ms ease }
.toast-enter-from { opacity: 0; transform: translateY(10px) }
.toast-leave-to { opacity: 0; transform: translateY(10px) }
</style>
