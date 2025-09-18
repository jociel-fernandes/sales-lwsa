<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="$emit('close')"></div>
    <div role="dialog" aria-modal="true" class="relative bg-white rounded shadow-lg max-w-sm w-full p-4">
      <h2 class="text-lg font-semibold mb-2">{{ title || 'Confirmar' }}</h2>
      <p class="text-sm text-gray-700 mb-4">{{ message }}</p>
      <div class="flex justify-end gap-2">
        <button type="button" class="px-3 py-1 rounded border" @click="$emit('cancel')">{{ cancelText || 'Cancelar' }}</button>
        <button type="button" class="px-3 py-1 rounded bg-red-600 text-white" @click="$emit('confirm')">{{ confirmText || 'Confirmar' }}</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ show: boolean; title?: string; message: string; confirmText?: string; cancelText?: string }>()
defineEmits<{ (e: 'confirm'): void; (e: 'cancel'): void; (e: 'close'): void }>()
</script>

<style scoped>
</style>
