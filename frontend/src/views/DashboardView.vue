<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useEntitiesStore } from '@/stores/entitiesStore'
import { useBlocksStore } from '@/stores/blocksStore'
import { useHistoryStore } from '@/stores/historyStore'

const entitiesStore = useEntitiesStore()
const blocksStore = useBlocksStore()
const historyStore = useHistoryStore()

const isLoading = ref(true)

const stats = computed(() => ({
  categories: 0,
  entities: entitiesStore.entityCount,
  blocks: blocksStore.blocksCount,
  documents: 0,
}))

const recentActivity = computed(() => {
  return historyStore.history.slice(0, 5).map(h => ({
    id: h.id,
    action: h.status === 'done' ? 'Completado' : 'Pendiente',
    target: h.block_name || 'Bloque sin nombre',
    time: h.date ? formatTimeAgo(new Date(h.date)) : 'Sin fecha',
  }))
})

const upcomingBlocks = computed(() => {
  return blocksStore.blocks
    .filter(b => b.schedule && b.status !== 'done')
    .slice(0, 5)
})

function formatTimeAgo(date: Date): string {
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)

  if (diffMins < 1) return 'hace un momento'
  if (diffMins < 60) return `hace ${diffMins} min`
  if (diffHours < 24) return `hace ${diffHours} horas`
  if (diffDays < 7) return `hace ${diffDays} días`
  return date.toLocaleDateString('es-ES')
}

onMounted(async () => {
  await Promise.all([
    entitiesStore.fetchEntities(),
    blocksStore.fetchBlocks(),
    historyStore.fetchHistory(),
  ])
  isLoading.value = false
})
</script>

<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">¡Bienvenido de nuevo! 👋</h2>
      <p class="text-slate-500 dark:text-slate-400">Esto es lo que está pasando con tus proyectos hoy.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4">
        <div class="text-3xl w-14 h-14 flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 rounded-xl">📁</div>
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
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Actividad Reciente</h3>
          <router-link to="/history" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
            Ver todo
          </router-link>
        </div>
        <div class="p-6">
          <div v-if="isLoading" class="text-center text-slate-500 py-4">Cargando...</div>
          <div v-else-if="recentActivity.length === 0" class="text-center text-slate-500 py-8">
            No hay actividad reciente
          </div>
          <ul v-else class="space-y-4">
            <li v-for="item in recentActivity" :key="item.id" class="flex items-center gap-3">
              <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
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
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Próximas Tareas</h3>
          <router-link to="/calendar" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
            Ver calendario
          </router-link>
        </div>
        <div class="p-6">
          <div v-if="upcomingBlocks.length === 0" class="text-center text-slate-500 py-8">
            No hay tareas pendientes
          </div>
          <div v-else class="space-y-3">
            <div 
              v-for="block in upcomingBlocks" 
              :key="block.id"
              class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors"
            >
              <span 
                class="w-8 h-8 rounded-lg flex items-center justify-center text-sm"
                :class="[
                  block.type === 'payment' ? 'bg-emerald-100 dark:bg-emerald-900/50' :
                  block.type === 'reminder' ? 'bg-amber-100 dark:bg-amber-900/50' :
                  'bg-blue-100 dark:bg-blue-900/50'
                ]"
              >
                {{ block.type === 'payment' ? '💰' : block.type === 'reminder' ? '🔔' : '✓' }}
              </span>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">
                  {{ block.data?.title || block.name }}
                </p>
                <p v-if="block.schedule" class="text-xs text-slate-500 dark:text-slate-400">
                  {{ block.schedule.time || 'Sin hora' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Acciones Rápidas</h3>
      </div>
      <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <router-link to="/entities" class="flex flex-col items-center gap-2 p-4 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
          <span class="text-2xl">🏢</span>
          <span class="text-sm font-medium">Nueva Entidad</span>
        </router-link>
        <router-link to="/blocks" class="flex flex-col items-center gap-2 p-4 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
          <span class="text-2xl">🧱</span>
          <span class="text-sm font-medium">Nuevo Bloque</span>
        </router-link>
        <router-link to="/calendar" class="flex flex-col items-center gap-2 p-4 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
          <span class="text-2xl">📅</span>
          <span class="text-sm font-medium">Ver Calendario</span>
        </router-link>
        <router-link to="/documents" class="flex flex-col items-center gap-2 p-4 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
          <span class="text-2xl">📄</span>
          <span class="text-sm font-medium">Subir Doc</span>
        </router-link>
      </div>
    </div>
  </div>
</template>