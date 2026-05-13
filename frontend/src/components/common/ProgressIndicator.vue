<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  value: number
  size?: 'sm' | 'md' | 'lg'
  color?: 'indigo' | 'emerald' | 'purple'
  showLabel?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  color: 'indigo',
  showLabel: false,
})

const percentage = computed(() => Math.min(100, Math.max(0, props.value)))

const colorClasses: Record<string, string> = {
  indigo: 'bg-indigo-500',
  emerald: 'bg-emerald-500',
  purple: 'bg-purple-500',
}

const sizeClasses = {
  sm: { bar: 'h-1.5', container: 'h-1.5 rounded-full', text: 'text-xs' },
  md: { bar: 'h-2.5', container: 'h-2.5 rounded-full', text: 'text-sm' },
  lg: { bar: 'h-3.5', container: 'h-3.5 rounded-full', text: 'text-base' },
} as const

const currentSize = computed(() => sizeClasses[props.size ?? 'md'])
const currentColor = computed(() => colorClasses[props.color ?? 'indigo'])
</script>

<template>
  <div class="flex items-center gap-2">
    <div :class="['flex-1 bg-slate-200 dark:bg-slate-700 overflow-hidden', currentSize.container]">
      <div
        :class="['h-full rounded-full transition-all duration-300', currentColor, currentSize.bar]"
        :style="{ width: `${percentage}%` }"
      />
    </div>
    <span v-if="showLabel" :class="['text-slate-600 dark:text-slate-400', currentSize.text]">
      {{ percentage }}%
    </span>
  </div>
</template>