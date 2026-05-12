<script setup lang="ts">
import { ref, computed } from 'vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'

const isSidebarOpen = ref(true)

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const sidebarWidth = computed(() => isSidebarOpen.value ? '256px' : '80px')
</script>

<template>
  <div class="flex h-screen bg-gray-50 dark:bg-slate-900">
    <AppSidebar :is-open="isSidebarOpen" />
    <div 
      class="flex-1 flex flex-col overflow-hidden transition-all duration-300"
      :style="{ marginLeft: sidebarWidth }"
    >
      <AppHeader @toggle-sidebar="toggleSidebar" />
      <main class="flex-1 overflow-y-auto p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>