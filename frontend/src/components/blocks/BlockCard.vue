<script setup lang="ts">
import { computed } from 'vue'
import type { Block } from '@/types'
import StatusBadge from '@/components/common/StatusBadge.vue'

interface Props {
  block: Block
  isSelected?: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'click'): void
  (e: 'edit'): void
}>()

const typeLabels: Record<string, string> = {
  task: 'Tarea',
  reminder: 'Recordatorio',
  payment: 'Pago',
  workflow: 'Flujo',
  step: 'Paso',
  note: 'Nota',
}

const typeColors: Record<string, { bg: string; text: string; border: string }> = {
  task: { bg: 'bg-blue-50 dark:bg-blue-900/30', text: 'text-blue-700 dark:text-blue-400', border: 'border-blue-200 dark:border-blue-800' },
  reminder: { bg: 'bg-amber-50 dark:bg-amber-900/30', text: 'text-amber-700 dark:text-amber-400', border: 'border-amber-200 dark:border-amber-800' },
  payment: { bg: 'bg-emerald-50 dark:bg-emerald-900/30', text: 'text-emerald-700 dark:text-emerald-400', border: 'border-emerald-200 dark:border-emerald-800' },
  workflow: { bg: 'bg-purple-50 dark:bg-purple-900/30', text: 'text-purple-700 dark:text-purple-400', border: 'border-purple-200 dark:border-purple-800' },
  step: { bg: 'bg-slate-50 dark:bg-slate-700', text: 'text-slate-700 dark:text-slate-300', border: 'border-slate-200 dark:border-slate-600' },
  note: { bg: 'bg-yellow-50 dark:bg-yellow-900/30', text: 'text-yellow-700 dark:text-yellow-400', border: 'border-yellow-200 dark:border-yellow-800' },
}

const scheduleInfo = computed(() => {
  if (!props.block.schedule) return null
  const schedule = props.block.schedule as Record<string, unknown>
  if (schedule.date) {
    const date = new Date(schedule.date as string)
    const day = date.getDate()
    const months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic']
    return { text: `${day} ${months[date.getMonth()]}`, type: 'date' }
  }
  if (schedule.time) {
    return { text: `A las ${schedule.time}`, type: 'time' }
  }
  return null
})

const blockStatus = computed(() => {
  return props.block.is_active ? 'pending' : 'done'
})
</script>

<template>
  <div
    @click="emit('click')"
    class="w-full text-left p-5 rounded-2xl border-2 transition-all duration-200 cursor-pointer"
    :class="isSelected
      ? 'border-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20 shadow-lg shadow-indigo-100 dark:shadow-indigo-900/20'
      : 'border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-200 dark:hover:border-slate-600 hover:shadow-md'"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="flex-1 min-w-0">
        <h4 class="font-semibold text-slate-800 dark:text-white truncate text-base">{{ block.name }}</h4>
        <p v-if="block.data?.description" class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 line-clamp-2 leading-relaxed">
          {{ block.data.description }}
        </p>
      </div>
      <div class="flex items-center gap-2 pt-0.5">
        <button
          v-if="$attrs.onEdit"
          type="button"
          @click.stop="emit('edit')"
          class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
        >
          ✏️
        </button>
        <StatusBadge :status="blockStatus" size="sm" />
      </div>
    </div>

    <div class="mt-4 flex items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <span
          class="px-2.5 py-1 text-xs font-semibold rounded-lg border"
          :class="[typeColors[block.type]?.bg || typeColors.task.bg, typeColors[block.type]?.text || typeColors.task.text, typeColors[block.type]?.border || typeColors.task.border]"
        >
          {{ typeLabels[block.type] || block.type }}
        </span>
        <span
          v-if="scheduleInfo"
          class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded-lg"
        >
          {{ scheduleInfo.text }}
        </span>
      </div>
      <span
        class="text-slate-400 dark:text-slate-500 transition-transform"
        :class="isSelected ? 'translate-x-1 text-indigo-500 dark:text-indigo-400' : ''"
      >→</span>
    </div>
  </div>
</template>