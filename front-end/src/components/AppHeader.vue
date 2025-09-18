<script setup lang="ts">
import UserMenu from './UserMenu.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <header class="bg-white border-b">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-3">
          <img src="/favicon.ico" class="w-8 h-8 rounded" alt="logo" />
          <div class="text-lg font-semibold">LWSA Sales</div>
        </div>
      </div>

      <div class="flex items-center gap-4">
          <div class="hidden sm:block text-sm text-gray-600">{{ auth.user ? `Bem vindo ${(auth.user as any).name ?? ''}` : '' }}</div>
          <div v-if="auth.hasPermission('manage.sellers') || auth.hasRole('admin' )" class="ml-4">
            <router-link to="/app/sellers" class="text-sm text-gray-700 hover:text-gray-900">Sellers</router-link>
          </div>
          <div v-if="auth.hasPermission && (auth.hasPermission('manage.sales') || auth.hasRole('admin') || auth.hasRole('sellers'))" class="ml-4">
            <router-link to="/app/sales" class="text-sm text-gray-700 hover:text-gray-900">Vendas</router-link>
          </div>
        <UserMenu />
      </div>
    </nav>
  </header>
</template>

<style scoped></style>
