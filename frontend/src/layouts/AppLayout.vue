<script setup lang="ts">
import { ref, computed, provide } from 'vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'

const isSidebarOpen = ref(true)
const isDetailPanelOpen = ref(false)
const notificationCount = ref(0)

provide('detailPanelOpen', isDetailPanelOpen)

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const sidebarWidth = computed(() => isSidebarOpen.value ? '256px' : '80px')
const detailPanelWidth = '384px'
</script>

<template>
  <div class="flex h-screen bg-gray-50 dark:bg-slate-900">
    <AppSidebar 
      :is-open="isSidebarOpen"
      :notification-count="notificationCount"
      @toggle-collapse="toggleSidebar"
    />
    
    <div 
      class="flex-1 flex flex-col overflow-hidden transition-all duration-300"
      :style="{ marginLeft: sidebarWidth }"
    >
      <AppHeader @toggle-sidebar="toggleSidebar" />
      
      <main class="flex-1 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>