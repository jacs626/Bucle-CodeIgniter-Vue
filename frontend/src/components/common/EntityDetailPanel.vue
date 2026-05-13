<script setup lang="ts">
import { computed } from 'vue'
import type { Entity, Block } from '@/types'

const props = defineProps<{
  entity: Entity | null
  blocks: Block[]
}>()

const emit = defineEmits<{
  edit: [entity: Entity]
  delete: [entity: Entity]
}>()

const overdueBlocks = computed(() => {
  if (!props.blocks) return []
  const now = new Date()
  return props.blocks.filter(b => {
    if (b.schedule?.date) {
      const dueDate = new Date(b.schedule.date)
      return dueDate < now && b.is_active
    }
    return false
  })
})

const hasOverdue = computed(() => overdueBlocks.value.length > 0)

const metadataEntries = computed(() => {
  if (!props.entity?.metadata) return []
  const meta = props.entity.metadata
  if (typeof meta === 'string') {
    try {
      meta = JSON.parse(meta)
    } catch {
      return []
    }
  }
  return Object.entries(meta as Record<string, unknown>).filter(([_, v]) => v !== null && v !== undefined && v !== '')
})

const getTypeConfig = (type: string) => {
  const configs: Record<string, { icon: string; gradient: string }> = {
    trip: { icon: '✈️', gradient: 'from-sky-100 dark:from-sky-900/50 to-blue-100 dark:to-blue-900/50' },
    medication: { icon: '💊', gradient: 'from-rose-100 dark:from-rose-900/50 to-pink-100 dark:to-pink-900/50' },
    finance: { icon: '💰', gradient: 'from-emerald-100 dark:from-emerald-900/50 to-green-100 dark:to-green-900/50' },
    home: { icon: '🏠', gradient: 'from-amber-100 dark:from-amber-900/50 to-orange-100 dark:to-orange-900/50' },
    restaurant: { icon: '🍽️', gradient: 'from-rose-100 dark:from-rose-900/50 to-red-100 dark:to-red-900/50' },
  }
  return configs[type] || { icon: '📍', gradient: 'from-indigo-100 dark:from-indigo-900/50 to-purple-100 dark:to-purple-900/50' }
}

const formatValue = (value: unknown): string => {
  if (value === null || value === undefined) return ''
  if (typeof value === 'boolean') return value ? 'Si' : 'No'
  if (typeof value === 'number') return value.toLocaleString()
  return String(value)
}
</script>

<template>
  <div v-if="entity" class="px-4 py-3 bg-gradient-to-r from-white dark:from-slate-800 to-slate-50 dark:to-slate-900 border-b border-slate-200 dark:border-slate-700">
    <div class="flex items-center justify-between gap-4">
      <div class="flex items-center gap-3 flex-1 min-w-0">
        <div :class="`w-10 h-10 rounded-lg bg-gradient-to-br ${getTypeConfig(entity.type).gradient} flex items-center justify-center text-xl flex-shrink-0`">
          {{ getTypeConfig(entity.type).icon }}
        </div>
        <div class="min-w-0 flex-1">
          <h2 class="text-base font-bold text-slate-800 dark:text-white truncate">{{ entity.name }}</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">{{ entity.type }}</p>
        </div>
      </div>

      <div class="flex items-center gap-2 flex-shrink-0">
        <div
          :class="`px-2 py-1 rounded-md border text-xs font-medium flex items-center gap-1 ${
            hasOverdue
              ? 'bg-amber-50 dark:bg-amber-900/30 border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-300'
              : 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300'
          }`"
        >
          <span v-if="hasOverdue" class="text-xs">⚠️</span>
          <span v-else class="text-xs">✓</span>
          <span>{{ hasOverdue ? `${overdueBlocks.length} venc` : 'OK' }}</span>
        </div>
        <button
          type="button"
          @click="emit('edit', entity)"
          class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded transition-colors"
          title="Editar"
        >
          <span class="text-xs">✏️</span>
        </button>
        <button
          type="button"
          @click="emit('delete', entity)"
          class="p-1 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
          title="Eliminar"
        >
          <span class="text-xs">🗑️</span>
        </button>
      </div>
    </div>

    <p v-if="entity.description" class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed line-clamp-2">
      {{ entity.description }}
    </p>

    <div v-if="metadataEntries.length > 0" class="flex flex-wrap gap-1 mt-2">
      <span
        v-for="[key, value] in metadataEntries"
        :key="key"
        class="px-2 py-0.5 text-xs font-medium bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded"
      >
        <span class="text-slate-400 dark:text-slate-500">{{ key }}:</span> {{ formatValue(value) }}
      </span>
    </div>
  </div>
</template>