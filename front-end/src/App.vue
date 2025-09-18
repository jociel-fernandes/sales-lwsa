<script setup lang="ts">
import { RouterView } from 'vue-router'
import LoadingOverlay from './components/LoadingOverlay.vue'
import NotificationToast from './components/NotificationToast.vue'
// App shell: header is placed per-view in authenticated routes
</script>

<template>
        <main class="min-h-screen">
            <LoadingOverlay />
            <NotificationToast />
            <RouterView />
        </main>
</template>

<style scoped>
/* keep minimal scoped styles */
</style>
