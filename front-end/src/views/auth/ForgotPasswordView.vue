<script setup lang="ts">
</script>

<template>
  <section class="flex items-center justify-center min-h-[70vh]">
    <div class="w-full max-w-4xl">
      <div class="grid grid-cols-1 md:grid-cols-2 bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="hidden md:flex items-center justify-center text-white p-8 motion-fade-up in" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-700) 100%)">
          <div class="text-center px-6">
            <img src="/src/assets/illustration-auth.svg" alt="illustration" class="w-48 mx-auto mb-4" />
            <h2 class="text-2xl font-bold mb-2">Recuperar conta</h2>
            <p class="text-sm opacity-90">Informe seu e-mail e enviaremos um link para redefinir a senha.</p>
          </div>
        </div>
        <div class="p-8">
          <h1 class="text-2xl font-semibold mb-4">Recuperar senha</h1>
          <ForgotPasswordForm />
        </div>
      </div>
    </div>
  </section>
</template>

<script lang="ts">
import ForgotPasswordForm from '../../components/auth/ForgotPasswordForm.vue'
export default { components: { ForgotPasswordForm } }
</script>
