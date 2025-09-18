<script setup lang="ts">
</script>

<template>
  <section class="flex items-center justify-center min-h-[70vh]">
    <div class="w-full max-w-4xl">
      <div class="grid grid-cols-1 md:grid-cols-2 bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="hidden md:flex items-center justify-center text-white p-8 motion-fade-up in" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-700) 100%)">
          <div class="text-center px-6">
            <img src="/src/assets/illustration-auth.svg" alt="illustration" class="w-48 mx-auto mb-4" />
            <h2 class="text-2xl font-bold mb-2">Bem-vindo de volta</h2>
            <p class="text-sm opacity-90">Acesse sua conta para gerenciar as vendas.</p>
          </div>
        </div>
        <div class="p-8">
          <h1 class="text-2xl font-semibold mb-4">Login</h1>
          <LoginForm />
        </div>
      </div>
    </div>
  </section>
</template>

<script lang="ts">
import LoginForm from '../../components/auth/LoginForm.vue'
export default { components: { LoginForm } }
</script>
