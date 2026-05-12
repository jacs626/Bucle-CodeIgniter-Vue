<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Stats {
  categories: number
  entities: number
  blocks: number
  documents: number
}

const stats = ref<Stats>({
  categories: 0,
  entities: 0,
  blocks: 0,
  documents: 0,
})

const isLoading = ref(true)

onMounted(async () => {
  await new Promise((resolve) => setTimeout(resolve, 500))
  stats.value = {
    categories: 12,
    entities: 48,
    blocks: 156,
    documents: 32,
  }
  isLoading.value = false
})

const recentActivity = ref([
  { id: 1, action: 'Creada categoría', target: 'Marketing', time: 'hace 2 min' },
  { id: 2, action: 'Actualizada entidad', target: 'Company Profile', time: 'hace 15 min' },
  { id: 3, action: 'Agregado bloque', target: 'Hero Section', time: 'hace 1 hora' },
  { id: 4, action: 'Publicado documento', target: 'Q1 Report', time: 'hace 3 horas' },
])
</script>

<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">¡Bienvenido de nuevo! 👋</h2>
      <p class="text-slate-500 dark:text-slate-400">Esto es lo que está pasando con tus proyectos hoy.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4">
        <div class="text-3xl w-14 h-14 flex items-center justify-center bg-primary-50 dark:bg-primary-900/30 rounded-xl">📁</div>
        <div class="flex flex-col">
          <span class="text-3xl font-bold text-slate-800 dark:text-white">{{ stats.categories }}</span>
          <span class="text-sm text-slate-500 dark:text-slate-400">Categorías</span>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4">
        <div class="text-3xl w-14 h-14 flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/30 rounded-xl">🏢</div>
        <div class="flex flex-col">
          <span class="text-3xl font-bold text-slate-800 dark:text-white">{{ stats.entities }}</span>
          <span class="text-sm text-slate-500 dark:text-slate-400">Entidades</span>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4">
        <div class="text-3xl w-14 h-14 flex items-center justify-center bg-amber-50 dark:bg-amber-900/30 rounded-xl">🧱</div>
        <div class="flex flex-col">
          <span class="text-3xl font-bold text-slate-800 dark:text-white">{{ stats.blocks }}</span>
          <span class="text-sm text-slate-500 dark:text-slate-400">Bloques</span>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4">
        <div class="text-3xl w-14 h-14 flex items-center justify-center bg-blue-50 dark:bg-blue-900/30 rounded-xl">📄</div>
        <div class="flex flex-col">
          <span class="text-3xl font-bold text-slate-800 dark:text-white">{{ stats.documents }}</span>
          <span class="text-sm text-slate-500 dark:text-slate-400">Documentos</span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Actividad Reciente</h3>
        </div>
        <div class="p-6">
          <div v-if="isLoading" class="text-center text-slate-500 py-4">Cargando...</div>
          <ul v-else class="space-y-4">
            <li v-for="item in recentActivity" :key="item.id" class="flex items-center gap-3">
              <span class="w-2 h-2 rounded-full bg-primary-500"></span>
              <div class="flex-1 flex flex-col">
                <span class="text-sm text-slate-800 dark:text-slate-200">{{ item.action }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ item.target }}</span>
              </div>
              <span class="text-xs text-slate-400 dark:text-slate-500">{{ item.time }}</span>
            </li>
          </ul>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Acciones Rápidas</h3>
        </div>
        <div class="p-6 space-y-3">
          <router-link to="/categories" class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
            <span class="text-xl">➕</span>
            <span>Nueva Categoría</span>
          </router-link>
          <router-link to="/entities" class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
            <span class="text-xl">🏢</span>
            <span>Nueva Entidad</span>
          </router-link>
          <router-link to="/documents" class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
            <span class="text-xl">📄</span>
            <span>Subir Documento</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>