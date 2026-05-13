<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useTheme } from '@/composables/useTheme'

interface Props {
  isOpen?: boolean
  notificationCount?: number
}

const props = withDefaults(defineProps<Props>(), {
  notificationCount: 0
})

const emit = defineEmits<{
  'toggle-collapse': []
}>()

const route = useRoute()
const { theme, toggleTheme } = useTheme()

const menuItems = [
  { path: '/', icon: '📊', label: 'Bucle', name: 'dashboard' },
  { path: '/entities', icon: '🏢', label: 'Entidades', name: 'entities' },
  { path: '/blocks', icon: '🧱', label: 'Bloques', name: 'blocks' },
  { path: '/calendar', icon: '📅', label: 'Calendario', name: 'calendar' },
  { path: '/notifications', icon: '🔔', label: 'Notificaciones', name: 'notifications', badge: true },
  { path: '/history', icon: '📜', label: 'Historial', name: 'history' },
  { path: '/documents', icon: '📄', label: 'Documentos', name: 'documents' },
]

const isActive = (name: string) => route.name === name
</script>

<template>
  <aside 
    class="fixed left-0 top-0 bottom-0 flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 transition-all duration-300 z-50"
    :class="isOpen ? 'w-64' : 'w-20'"
  >
    <div class="p-4 border-b border-slate-100 dark:border-slate-700">
      <div class="flex items-center gap-3" :class="isOpen ? '' : 'justify-center'">
        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md flex-shrink-0">
          <span class="text-white text-lg">🔄</span>
        </div>
        <div v-if="isOpen" class="flex flex-col">
          <span class="font-bold text-lg text-slate-800 dark:text-white tracking-tight">Bucle</span>
          <span class="text-xs text-slate-500 dark:text-slate-400">Gestión inteligente</span>
        </div>
      </div>
    </div>

    <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
      <router-link
        v-for="item in menuItems"
        :key="item.name"
        :to="item.path"
        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200"
        :class="[
          isOpen ? '' : 'justify-center',
          isActive(item.name) 
            ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 shadow-sm' 
            : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
        ]"
      >
        <div class="relative">
          <span class="text-lg">{{ item.icon }}</span>
          <span
            v-if="item.badge && notificationCount > 0"
            class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
          >
            {{ notificationCount > 9 ? '9+' : notificationCount }}
          </span>
        </div>
        <span v-if="isOpen">{{ item.label }}</span>
      </router-link>
    </nav>

    <div class="p-3 border-t border-slate-100 dark:border-slate-700 space-y-1">
      <button
        @click="toggleTheme"
        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        :class="isOpen ? '' : 'justify-center'"
      >
        <span class="text-lg">{{ theme === 'dark' ? '☀️' : '🌙' }}</span>
        <span v-if="isOpen">{{ theme === 'dark' ? 'Modo claro' : 'Modo oscuro' }}</span>
      </button>

      <button
        @click="emit('toggle-collapse')"
        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-300 transition-colors"
        :class="isOpen ? '' : 'justify-center'"
      >
        <span class="text-lg">{{ isOpen ? '◀' : '▶' }}</span>
        <span v-if="isOpen">Colapsar</span>
      </button>

      <button
        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
        :class="isOpen ? '' : 'justify-center'"
      >
        <span class="text-lg">🚪</span>
        <span v-if="isOpen">Salir</span>
      </button>
    </div>
  </aside>
</template>