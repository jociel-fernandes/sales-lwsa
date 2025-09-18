<script setup lang="ts">
import AppHeader from '@/components/AppHeader.vue'
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
const drawerOpen = ref(false)
const auth = useAuthStore()
</script>

<template>
  <div class="min-h-screen bg-gray-50 text-gray-800">
    <AppHeader />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col md:flex-row md:gap-6">
        <!-- Sidebar (only visual/navigation) -->
        <aside class="hidden md:block w-64 bg-white border rounded-md p-4 shadow-sm">
          <nav class="flex flex-col gap-2">
            <router-link to="/app/dashboard" class="px-3 py-2 rounded hover:bg-gray-50">Dashboard</router-link>
            <router-link to="/app/profile" class="px-3 py-2 rounded hover:bg-gray-50">Perfil</router-link>
            <template v-if="auth.hasRole('admin')">
              <router-link to="/app/settings" class="px-3 py-2 rounded hover:bg-gray-50">Configurações</router-link>
            </template>
            <template v-if="auth.hasPermission && (auth.hasPermission('manage.sellers') || auth.hasRole('admin'))">
              <router-link to="/app/sellers" class="px-3 py-2 rounded hover:bg-gray-50">Sellers</router-link>
            </template>
            <template v-if="auth.hasPermission && (auth.hasPermission('manage.sales') || auth.hasRole('admin') || auth.hasRole('sellers'))">
              <router-link to="/app/sales" class="px-3 py-2 rounded hover:bg-gray-50">Vendas</router-link>
            </template>
          </nav>
        </aside>

        <!-- Mobile drawer toggle -->
        <div class="md:hidden mb-4">
          <button @click="drawerOpen = !drawerOpen" class="p-2 rounded bg-white border">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <transition name="fade">
            <div v-if="drawerOpen" class="mt-2 bg-white border rounded-md p-3 shadow-sm">
                <nav class="flex flex-col gap-2">
                  <router-link to="/app/dashboard" class="px-3 py-2 rounded hover:bg-gray-50">Dashboard</router-link>
                  <router-link to="/app/profile" class="px-3 py-2 rounded hover:bg-gray-50">Perfil</router-link>
                  <template v-if="auth.hasRole('admin')">
                    <router-link to="/app/settings" class="px-3 py-2 rounded hover:bg-gray-50">Configurações</router-link>
                  </template>
                  <template v-if="auth.hasPermission && (auth.hasPermission('manage.sellers') || auth.hasRole('admin'))">
                    <router-link to="/app/sellers" class="px-3 py-2 rounded hover:bg-gray-50">Sellers</router-link>
                  </template>
                  <template v-if="auth.hasPermission && (auth.hasPermission('manage.sales') || auth.hasRole('admin') || auth.hasRole('sellers'))">
                    <router-link to="/app/sales" class="px-3 py-2 rounded hover:bg-gray-50">Vendas</router-link>
                  </template>
                </nav>
            </div>
          </transition>
        </div>

        <main class="flex-1">
          <div class="bg-white border rounded-md p-6 shadow-sm">
            <router-view />
          </div>
          
        </main>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
