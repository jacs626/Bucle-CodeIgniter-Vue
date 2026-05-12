<script setup lang="ts">
interface Props {
  status: 'pending' | 'done' | 'missed' | 'active'
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'sm',
})

const statusConfig = {
  pending: {
    label: 'Pendiente',
    icon: '⏳',
    className: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
  },
  done: {
    label: 'Hecho',
    icon: '✅',
    className: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
  },
  missed: {
    label: 'Perdido',
    icon: '❌',
    className: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800',
  },
  active: {
    label: 'Activo',
    icon: '🔵',
    className: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800',
  },
}

const config = statusConfig[props.status] || statusConfig.pending

const sizeClasses = {
  sm: 'px-2.5 py-1 text-xs gap-1',
  md: 'px-3 py-1.5 text-sm gap-1.5',
  lg: 'px-4 py-2 text-base gap-2',
}
</script>

<template>
  <span :class="`inline-flex items-center rounded-full font-semibold border ${config.className} ${sizeClasses[size]}`">
    <span class="flex-shrink-0">{{ config.icon }}</span>
    {{ config.label }}
  </span>
</template>