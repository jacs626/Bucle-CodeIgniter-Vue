<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useTheme } from '@/composables/useTheme'

const props = defineProps<{
  size?: 'sm' | 'md' | 'lg'
}>()

const { theme } = useTheme()
const isLoaded = ref(false)

onMounted(() => {
  isLoaded.value = true
})

const containerSize = computed(() => {
  switch (props.size) {
    case 'sm': return 'w-8 h-8'
    case 'lg': return 'w-14 h-14'
    default: return 'w-10 h-10'
  }
})

const imgSize = computed(() => {
  switch (props.size) {
    case 'sm': return 'w-5 h-5'
    case 'lg': return 'w-9 h-9'
    default: return 'w-7 h-7'
  }
})

const bgClass = computed(() => {
  return theme.value === 'dark'
    ? 'bg-slate-800 dark:bg-slate-700'
    : 'bg-white'
})
</script>

<template>
  <div
    class="rounded-xl flex items-center justify-center shadow-md border border-slate-200 dark:border-slate-700"
    :class="[containerSize, bgClass]"
  >
    <img
      v-if="isLoaded"
      :class="[imgSize, 'object-contain']"
      src="/android-chrome-512x512.png"
      alt="Logo"
    />
    <span v-else class="text-slate-400 text-sm">🔄</span>
  </div>
</template>