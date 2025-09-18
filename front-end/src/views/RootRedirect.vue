<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

onMounted(async () => {
  try {
    await auth.fetchUser()
  } catch {
    // ignore
  }

  if (auth.isAuthenticated()) {
    router.replace({ name: 'dashboard' })
  } else {
    router.replace({ name: 'login' })
  }
})
</script>

<template><div /></template>
