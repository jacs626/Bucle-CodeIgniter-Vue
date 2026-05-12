<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const emit = defineEmits<{
  'toggle-sidebar': []
}>()

const pageTitle = computed(() => {
  return (route.meta.title as string) || 'Dashboard'
})
</script>

<template>
  <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6">
    <div class="flex items-center gap-4">
      <button 
        class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
        @click="emit('toggle-sidebar')"
      >
        <span class="text-xl">☰</span>
      </button>
      <h1 class="text-xl font-semibold text-slate-800 dark:text-white">{{ pageTitle }}</h1>
    </div>

    <div class="flex items-center gap-2">
      <button 
        class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
        title="Notificaciones"
      >
        <span class="text-lg">🔔</span>
      </button>
      <button 
        class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
        title="Ajustes"
      >
        <span class="text-lg">⚙️</span>
      </button>
    </div>
  </header>
</template>