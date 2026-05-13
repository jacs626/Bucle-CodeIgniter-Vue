<script setup lang="ts">
import { ref, computed } from 'vue'
import type { Block, Entity, History } from '@/types'
import { getBlockStatus, getRecurrenceStatus, getTimeUntilNextExecution, formatTimeRemaining, toDate } from '@/types'
import { idsMatch } from '@/utils/id'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ProgressIndicator from '@/components/common/ProgressIndicator.vue'

interface Props {
  block: Block | null
  entity?: Entity | null
  allBlocks?: Block[]
  history?: History[]
  onMarkDone?: (block: Block) => void
  onClose?: () => void
  onRefreshHistory?: () => void
}

const props = defineProps<Props>()

const expandedSteps = ref(false)
const workflowStack = ref<string[]>([])

const currentWorkflowId = computed(() => {
  if (workflowStack.value.length > 0) {
    return workflowStack.value[workflowStack.value.length - 1]
  }
  return props.block?.id?.toString() || ''
})

const getStepsForWorkflow = (workflowId: string | number): Block[] => {
  return (props.allBlocks || [])
    .filter((b) => idsMatch(b.parent_block_id, workflowId))
    .sort((a, b) => (a.order_index ?? 0) - (b.order_index ?? 0))
}

const currentSteps = computed(() => getStepsForWorkflow(String(currentWorkflowId.value) || '0'))
const isFullView = computed(() => workflowStack.value.length > 0)

const getBreadcrumbWorkflow = (id: string | number): Block | undefined => {
  if (props.block && idsMatch(props.block.id, id)) return props.block
  return props.allBlocks?.find(b => idsMatch(b.id, id))
}

const blockHistory = computed(() => {
  if (!props.block) return []
  return (props.history || []).filter((h) => idsMatch(h.block_id, props.block!.id))
})

const displayStatus = computed(() => {
  if (!props.block) return 'pending'
  return getBlockStatus(
    props.block.schedule || null,
    blockHistory.value,
    String(props.block.id),
    props.block.status || 'pending'
  )
})

const isWorkflow = computed(() => props.block?.type === 'workflow')

const workflowDoneCount = computed(() => 
  currentSteps.value.filter((b) => b.status === 'done').length
)

const workflowProgress = computed(() => {
  if (currentSteps.value.length === 0) return 0
  return Math.round((workflowDoneCount.value / currentSteps.value.length) * 100)
})

const scheduleInfo = computed(() => {
  if (!props.block?.schedule) return null
  
  const timeRemaining = getTimeUntilNextExecution(props.block.schedule)
  const remainingText = timeRemaining?.nextExecution
    ? ` (${formatTimeRemaining(timeRemaining.days, timeRemaining.hours, timeRemaining.minutes)})`
    : ''

  switch (props.block.schedule.type) {
    case 'fixed':
      if (props.block.schedule.date) {
        const date = toDate(props.block.schedule.date)
        if (date) {
          const day = date.getDate()
          const months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre']
          return {
            text: `Vence: ${day} de ${months[date.getMonth()]}${remainingText}`
          }
        }
      }
      if (props.block.schedule.time) {
        return { text: `A las ${props.block.schedule.time}${remainingText}` }
      }
      return { text: 'Fecha fija' }
    case 'interval': {
      const hours = props.block.schedule.intervalHours || 0
      if (props.block.schedule.startDate) {
        const startDate = toDate(props.block.schedule.startDate)
        return {
          text: `Cada ${hours}h desde ${startDate?.toLocaleDateString('es-ES')}${remainingText}`
        }
      }
      return { text: `Cada ${hours} horas${remainingText}` }
    }
    case 'weekly': {
      if (!props.block.schedule.daysOfWeek?.length) return { text: 'Semanal' }
      const dayMap: Record<string, string> = {
        monday: 'Lun', tuesday: 'Mar', wednesday: 'Mié',
        thursday: 'Jue', friday: 'Vie', saturday: 'Sáb', sunday: 'Dom',
      }
      const days = props.block.schedule.daysOfWeek.map(d => dayMap[d] || d).join(', ')
      return {
        text: props.block.schedule.time ? `${days} ${props.block.schedule.time}${remainingText}` : days
      }
    }
    default:
      return { text: props.block.schedule.type }
  }
})

const typeLabels: Record<string, string> = {
  task: 'Tarea',
  reminder: 'Recordatorio',
  payment: 'Pago',
  workflow: 'Flujo',
  step: 'Paso',
  note: 'Nota',
}

const typeColors: Record<string, string> = {
  task: 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800',
  reminder: 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800',
  payment: 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
  workflow: 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800',
}

const recurrenceStatus = computed(() => {
  if (!props.block?.schedule) return null
  return getRecurrenceStatus(
    props.block.schedule,
    blockHistory.value,
    String(props.block.id),
    props.block.status || 'pending'
  )
})

const handleGoBack = () => {
  workflowStack.value = workflowStack.value.slice(0, -1)
}

const handleToggleStep = () => {
  props.onRefreshHistory?.()
}

const handleMarkDone = () => {
  props.onMarkDone?.(props.block!)
}

const blockTitle = computed(() => {
  return props.block?.data?.title as string || props.block?.name || 'Sin título'
})

const blockDescription = computed(() => {
  return props.block?.data?.description as string || ''
})
</script>

<template>
  <div v-if="block" class="h-full w-96 flex flex-col bg-white dark:bg-slate-800 shadow-xl border-l border-slate-200 dark:border-slate-700">
    <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
      <h2 class="font-bold text-lg text-slate-800 dark:text-white">Detalles</h2>
      <button
        @click="onClose"
        class="p-2 text-slate-400 dark:text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors"
      >
        ✕
      </button>
    </div>

    <div class="flex-1 overflow-y-auto p-5 space-y-5">
      <div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ blockTitle }}</h3>
        <p v-if="blockDescription" class="text-sm text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
          {{ blockDescription }}
        </p>
      </div>

      <div v-if="isWorkflow && currentSteps.length > 0" class="p-4 bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/30 dark:to-indigo-900/30 rounded-2xl border border-purple-100 dark:border-purple-800">
        <div v-if="isFullView && workflowStack.length > 0" class="flex items-center gap-2 mb-3">
          <button
            @click="handleGoBack"
            class="p-1 text-purple-600 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/30 rounded"
          >
            ←
          </button>
          <div class="flex items-center gap-1 text-sm">
            <span
              v-for="(stackId, idx) in workflowStack"
              :key="stackId"
              class="flex items-center"
            >
              <span v-if="idx > 0" class="text-purple-400 mx-1">›</span>
              <span class="text-purple-600 dark:text-purple-400">
                {{ getBreadcrumbWorkflow(stackId)?.data?.title || 'Flujo' }}
              </span>
            </span>
          </div>
        </div>
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-sm font-semibold text-purple-800 dark:text-purple-300">
            {{ isFullView ? `Pasos (${currentSteps.length})` : 'Pasos del Flujo' }}
          </h4>
          <button
            v-if="!isFullView && currentSteps.length > 3"
            @click="expandedSteps = !expandedSteps"
            class="text-xs text-purple-600 dark:text-purple-400 hover:underline"
          >
            {{ expandedSteps ? 'Vista simple' : 'Ver todos' }}
          </button>
        </div>
        <div class="space-y-2">
          <div
            v-for="(step, index) in (expandedSteps || isFullView ? currentSteps : currentSteps.slice(0, 3))"
            :key="step.id"
            class="flex items-center gap-3"
          >
            <button
              @click="handleToggleStep"
              :class="[
                'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-colors',
                step.status === 'done'
                  ? 'bg-emerald-500 text-white hover:bg-emerald-600'
                  : 'bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-emerald-400'
              ]"
            >
              <span v-if="step.status === 'done'">✓</span>
              <span v-else>{{ index + 1 }}</span>
            </button>
            <span :class="[
              'flex-1 text-sm',
              step.status === 'done' ? 'text-emerald-600 dark:text-emerald-400 line-through' : 'text-slate-700 dark:text-slate-300'
            ]">
              {{ step.data?.title || step.name }}
            </span>
          </div>
        </div>
        <p v-if="currentSteps.length > 3 && !expandedSteps && !isFullView" class="text-xs text-purple-500 dark:text-purple-400 mt-2 text-center">
          + {{ currentSteps.length - 3 }} más
        </p>
        <div class="mt-4">
          <ProgressIndicator :value="workflowProgress" size="sm" color="emerald" show-label />
        </div>
      </div>

      <div class="flex items-center gap-3 flex-wrap">
        <StatusBadge :status="displayStatus" size="md" />
        <span :class="['px-3 py-1.5 text-xs font-semibold rounded-full border', typeColors[block.type] || typeColors.task]">
          {{ typeLabels[block.type] || block.type }}
        </span>
      </div>

      <div v-if="block.schedule && scheduleInfo" class="p-4 bg-indigo-50/50 dark:bg-indigo-900/30 rounded-2xl border border-indigo-100 dark:border-indigo-800">
        <p class="text-sm text-indigo-700 dark:text-indigo-300 flex items-center gap-2 font-medium">
          📅 {{ scheduleInfo.text }}
        </p>
      </div>

      <div v-if="block.data?.amount !== undefined" class="p-4 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/30 dark:to-green-900/30 rounded-2xl border border-emerald-100 dark:border-emerald-800">
        <p class="text-sm text-emerald-700 dark:text-emerald-400 flex items-center gap-2 font-medium">
          💰 <span class="font-bold text-base">{{ Number(block.data.amount).toLocaleString('es-ES') }}</span>
          <span v-if="block.data?.currency">{{ block.data.currency }}</span>
        </p>
      </div>

      <div v-if="recurrenceStatus">
        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
          ⚡ Estado de Recurrencia
        </h4>
        <div class="flex items-center gap-2 text-sm mt-2">
          <span class="text-slate-500 dark:text-slate-400">
            Esperadas: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ recurrenceStatus.expected }}</span>
          </span>
          <span class="text-slate-300 dark:text-slate-600">|</span>
          <span class="text-emerald-600 dark:text-emerald-400">
            Completadas: <span class="font-semibold">{{ recurrenceStatus.completed }}</span>
          </span>
          <span v-if="recurrenceStatus.pending > 0" class="text-amber-600 dark:text-amber-400 font-semibold">
            | Pendientes: {{ recurrenceStatus.pending }}
          </span>
        </div>
        <div v-if="recurrenceStatus.pending > 0" class="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-3 py-2 rounded-lg mt-2">
          Hay {{ recurrenceStatus.pending }} ejecuciones pendientes
        </div>
      </div>

      <div v-if="blockHistory.length > 0">
        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
          📜 Historial
        </h4>
        <div class="space-y-2">
          <div
            v-for="h in blockHistory.slice(0, 5)"
            :key="h.id"
            class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700"
          >
            <span class="text-sm text-slate-600 dark:text-slate-400">
              {{ h.date ? new Date(h.date).toLocaleString('es-ES') : 'Sin fecha' }}
            </span>
            <StatusBadge :status="h.status === 'done' ? 'done' : 'missed'" size="sm" />
          </div>
        </div>
      </div>
    </div>

    <div class="p-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
      <button
        @click="handleMarkDone"
        :class="[
          'w-full py-3.5 rounded-xl font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2',
          displayStatus === 'done'
            ? 'bg-emerald-600 text-white'
            : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30'
        ]"
      >
        <span class="w-5 h-5">✓</span>
        {{ displayStatus === 'done' ? 'Completado' : 'Marcar como Completado' }}
      </button>
    </div>
  </div>
</template>