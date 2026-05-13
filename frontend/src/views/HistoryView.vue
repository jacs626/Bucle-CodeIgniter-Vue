<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useHistoryStore } from '@/stores/historyStore'
import { useEntitiesStore } from '@/stores/entitiesStore'
import StatusBadge from '@/components/common/StatusBadge.vue'

const historyStore = useHistoryStore()
const entitiesStore = useEntitiesStore()

const filter = ref<'all' | 'done' | 'missed'>('all')
const isLoading = ref(true)

const filteredHistory = computed(() => {
  const all = historyStore.history
  if (filter.value === 'all') return all
  return all.filter(h => h.status === filter.value)
})

const formatDate = (date: string | null) => {
  if (!date) return 'Sin fecha'
  return new Date(date).toLocaleString('es-ES', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatTimeAgo = (date: string | null) => {
  if (!date) return ''
  const now = new Date()
  const d = new Date(date)
  const diffMs = now.getTime() - d.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)

  if (diffMins < 1) return 'hace un momento'
  if (diffMins < 60) return `hace ${diffMins}m`
  if (diffHours < 24) return `hace ${diffHours}h`
  if (diffDays < 7) return `hace ${diffDays}d`
  return d.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' })
}

onMounted(async () => {
  await Promise.all([
    historyStore.fetchHistory(),
    entitiesStore.fetchEntities(),
  ])
  isLoading.value = false
})
</script>

<template>
  <div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Historial</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Registro completo de todas las ejecuciones</p>
      </div>
    </div>

    <div class="flex gap-2 mb-4">
      <button
        @click="filter = 'all'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          filter === 'all' 
            ? 'bg-indigo-600 text-white' 
            : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'
        ]"
      >
        Todos
      </button>
      <button
        @click="filter = 'done'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          filter === 'done' 
            ? 'bg-emerald-600 text-white' 
            : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'
        ]"
      >
        Completados
      </button>
      <button
        @click="filter = 'missed'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          filter === 'missed' 
            ? 'bg-red-600 text-white' 
            : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'
        ]"
      >
        Perdidos
      </button>
    </div>

    <div v-if="isLoading" class="text-center text-slate-500 py-12">
      Cargando historial...
    </div>

    <div v-else-if="filteredHistory.length === 0" class="text-center py-12">
      <div class="text-4xl mb-4">📜</div>
      <p class="text-slate-500 dark:text-slate-400">No hay registros de historial</p>
    </div>

    <div v-else class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <div class="divide-y divide-slate-100 dark:divide-slate-700">
        <div 
          v-for="item in filteredHistory" 
          :key="item.id"
          class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div 
                class="w-10 h-10 rounded-lg flex items-center justify-center text-lg"
                :class="[
                  item.block_type === 'payment' ? 'bg-emerald-100 dark:bg-emerald-900/50' :
                  item.block_type === 'reminder' ? 'bg-amber-100 dark:bg-amber-900/50' :
                  item.block_type === 'workflow' ? 'bg-purple-100 dark:bg-purple-900/50' :
                  'bg-blue-100 dark:bg-blue-900/50'
                ]"
              >
                {{ item.block_type === 'payment' ? '💰' : item.block_type === 'reminder' ? '🔔' : item.block_type === 'workflow' ? '📋' : '✓' }}
              </div>
              <div>
                <p class="font-medium text-slate-800 dark:text-slate-200">
                  {{ item.block_name || 'Bloque sin nombre' }}
                </p>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  {{ item.entity_name || 'Sin entidad' }}
                </p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-right">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  {{ formatDate(item.date) }}
                </p>
                <p class="text-xs text-slate-400 dark:text-slate-500">
                  {{ formatTimeAgo(item.date) }}
                </p>
              </div>
              <StatusBadge :status="item.status === 'done' ? 'done' : 'missed'" size="sm" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>