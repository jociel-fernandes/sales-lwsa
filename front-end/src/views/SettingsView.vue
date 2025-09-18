<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Configurações</h1>

    <div v-if="loaded" class="max-w-2xl">
      <form @submit.prevent="save">
        <div v-for="(item, idx) in settings" :key="item.id" class="mb-4">
          <label class="block mb-1 font-medium">{{ item.label || item.input }}</label>
          <!-- name pattern: input[idx] and value[idx] -->
          <input type="hidden" :name="`input[${idx}]`" :value="item.input" />
          <input v-model="values[idx]" :name="`value[${idx}]`" type="text" class="border p-2 w-full" />
        </div>

        <div class="flex gap-2">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Atualizar</button>
        </div>
      </form>
    </div>
    <div v-else>Carregando...</div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import settingsService from '@/services/settingsService';
import { useNotificationStore } from '@/stores/notification'

type Setting = { id: number; input: string; value: string | null; label?: string }

const settings = ref<Setting[]>([]);
const values = ref<string[]>([]);
const loaded = ref(false);
const notif = useNotificationStore()

async function load() {
  const res = await settingsService.list();
  settings.value = res as Setting[];
  values.value = settings.value.map((s: Setting) => s.value ?? '');
  loaded.value = true;
}

async function save() {
  // Build FormData with arrays: input[idx] = setting.input, value[idx] = values[idx]
  const form = new FormData();
  settings.value.forEach((item: Setting, idx: number) => {
    form.append(`input[${idx}]`, item.input);
    form.append(`value[${idx}]`, values.value[idx] ?? '');
  });

  try {
    await settingsService.store(form);
    notif.push('Atualizado', 'success')
  } catch (err) {
    notif.push('Erro ao atualizar', 'error')
  }
}

// cancel removed per UX request

onMounted(load);
</script>
<style scoped></style>
