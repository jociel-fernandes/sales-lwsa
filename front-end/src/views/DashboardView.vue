<script setup lang="ts">
</script>

<template>
  <section>
    <h1 class="text-2xl font-semibold mb-4">Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <router-link to="/app/dashboard" class="p-6 bg-white rounded shadow hover:shadow-md flex flex-col gap-2">
        <div class="text-sm text-gray-500">Resumo</div>
        <div class="text-xl font-semibold">KPIs</div>
        <div class="mt-2 text-sm text-gray-600">Visão geral rápida</div>
      </router-link>
      <router-link to="/app/activity" class="p-6 bg-white rounded shadow hover:shadow-md flex flex-col gap-2">
        <div class="text-sm text-gray-500">Atividades</div>
        <div class="text-xl font-semibold">Recentes</div>
        <div class="mt-2 text-sm text-gray-600">Últimas ações dos usuários</div>
      </router-link>
    </div>
    <div class="mt-6 p-6 bg-white rounded shadow">Conteúdo do dashboard administrativo</div>
  </section>
</template>

<style scoped>
</style>
