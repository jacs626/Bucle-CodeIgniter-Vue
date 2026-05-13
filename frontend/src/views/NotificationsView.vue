<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useHistoryStore } from '@/stores/historyStore'
import { useBlocksStore } from '@/stores/blocksStore'
import { useToast } from '@/composables/useToast'
import StatusBadge from '@/components/common/StatusBadge.vue'

interface Notification {
  id: number
  blockId: number
  blockName: string
  blockType: string
  entityName: string
  scheduledDate: string
  status: string
  overdue: boolean
}

const historyStore = useHistoryStore()
const blocksStore = useBlocksStore()
const { showToast } = useToast()

const isLoading = ref(true)
const pollingInterval = ref<number | null>(null)

const notifications = computed<Notification[]>(() => {
  const now = new Date()
  return historyStore.history
    .filter(h => h.status !== 'done' && h.date)
    .map(h => {
      const scheduledDate = new Date(h.date!)
      return {
        id: h.id,
        blockId: h.block_id || 0,
        blockName: h.block_name || 'Sin nombre',
        blockType: h.block_type || 'task',
        entityName: h.entity_name || 'Sin entidad',
        scheduledDate: h.date!,
        status: h.status,
        overdue: scheduledDate < now,
      }
    })
})

const overdueCount = computed(() => notifications.value.filter(n => n.overdue).length)
const pendingCount = computed(() => notifications.value.filter(n => !n.overdue).length)

const fetchNotifications = async () => {
  try {
    await Promise.all([
      historyStore.fetchHistory(),
      blocksStore.fetchBlocks(),
    ])
  } catch {
    showToast('Error al cargar notificaciones', 'error')
  }
}

const startPolling = () => {
  pollingInterval.value = window.setInterval(() => {
    fetchNotifications()
  }, 30000)
}

const stopPolling = () => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value)
    pollingInterval.value = null
  }
}

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleString('es-ES', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getTimeAgo = (dateStr: string) => {
  const now = new Date()
  const date = new Date(dateStr)
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)

  if (diffMins < 1) return 'hace un momento'
  if (diffMins < 60) return `hace ${diffMins}m`
  if (diffHours < 24) return `hace ${diffHours}h`
  return `hace ${diffDays}d`
}

const getTypeIcon = (type: string) => {
  switch (type) {
    case 'payment': return '💰'
    case 'reminder': return '🔔'
    case 'workflow': return '📋'
    default: return '✓'
  }
}

const markAsDone = async (notification: Notification) => {
  try {
    await blocksStore.updateBlock(notification.blockId, { status: 'done' })
    await fetchNotifications()
    showToast('Marcado como completado', 'success')
  } catch {
    showToast('Error al marcar como completado', 'error')
  }
}

onMounted(async () => {
  await fetchNotifications()
  isLoading.value = false
  startPolling()
})

onUnmounted(() => {
  stopPolling()
})
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Notificaciones</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Tareas pendientes y recordatorios</p>
      </div>
      <div class="flex items-center gap-4">
        <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 rounded-full text-sm font-medium">
          {{ overdueCount }} vencidas
        </span>
        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium">
          {{ pendingCount }} pendientes
        </span>
      </div>
    </div>

    <div v-if="isLoading" class="text-center text-slate-500 py-12">
      Cargando notificaciones...
    </div>

    <div v-else-if="notifications.length === 0" class="text-center py-12">
      <div class="text-4xl mb-4">🔔</div>
      <p class="text-slate-500 dark:text-slate-400">No hay notificaciones pendientes</p>
    </div>

    <div v-else class="space-y-4">
      <div 
        v-for="notification in notifications" 
        :key="notification.id"
        :class="[
          'p-4 rounded-xl border transition-all',
          notification.overdue 
            ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' 
            : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:shadow-md'
        ]"
      >
        <div class="flex items-center gap-4">
          <span 
            class="w-12 h-12 rounded-xl flex items-center justify-center text-xl"
            :class="notification.overdue ? 'bg-red-100 dark:bg-red-900/50' : 'bg-slate-100 dark:bg-slate-700'"
          >
            {{ getTypeIcon(notification.blockType) }}
          </span>
          
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <h3 class="font-medium text-slate-800 dark:text-slate-200 truncate">
                {{ notification.blockName }}
              </h3>
              <span v-if="notification.overdue" class="px-2 py-0.5 bg-red-500 text-white text-xs rounded-full">
                Vencida
              </span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              {{ notification.entityName }}
            </p>
            <div class="flex items-center gap-3 mt-1 text-xs">
              <span class="text-slate-400">{{ formatDate(notification.scheduledDate) }}</span>
              <span class="text-slate-300">•</span>
              <span :class="notification.overdue ? 'text-red-500' : 'text-slate-400'">
                {{ getTimeAgo(notification.scheduledDate) }}
              </span>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <StatusBadge :status="notification.status === 'done' ? 'done' : notification.overdue ? 'missed' : 'pending'" size="sm" />
            <button 
              v-if="notification.status !== 'done'"
              @click="markAsDone(notification)"
              class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium"
            >
              ✓ Completar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="text-center text-xs text-slate-400 dark:text-slate-500 py-4">
      Las notificaciones se actualizan automáticamente cada 30 segundos
    </div>
  </div>
</template>