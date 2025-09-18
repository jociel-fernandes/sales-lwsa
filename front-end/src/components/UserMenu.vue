<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const open = ref(false)
const auth = useAuthStore()
const router = useRouter()

function toggle() {
  open.value = !open.value
}

async function handleLogout() {
  await auth.logout()
}

function goProfile() {
  open.value = false
  router.push({ name: 'profile' })
}

function goSettings() {
  open.value = false
  router.push({ name: 'settings' })
}

function onWindowClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  // if click is outside any element in this component, close
  if (!target.closest('.user-menu-root')) {
    open.value = false
  }
}

onMounted(() => window.addEventListener('click', onWindowClick))
onBeforeUnmount(() => window.removeEventListener('click', onWindowClick))
</script>

<template>
  <div class="relative user-menu-root">
    <button @click.stop="toggle" class="flex items-center gap-2 p-1 rounded hover:bg-gray-100">
  <img src="/avatar.svg" alt="avatar" class="w-8 h-8 rounded-full object-cover" />
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 9l6 6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <div v-if="open" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg z-50">
      <div class="py-1">
        <button @click.stop="goProfile" class="w-full text-left px-4 py-2 hover:bg-gray-50">Perfil</button>
        <button  v-if="auth.hasRole('admin' )" @click.stop="goSettings" class="w-full text-left px-4 py-2 hover:bg-gray-50">Configurações</button>
        <div class="border-t my-1"></div>
        <button @click.stop="handleLogout" class="w-full text-left px-4 py-2 hover:bg-gray-50">Logout</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
