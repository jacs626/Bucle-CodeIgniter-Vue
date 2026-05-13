<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useBlocksStore } from '@/stores/blocksStore'
import { useEntitiesStore } from '@/stores/entitiesStore'

const blocksStore = useBlocksStore()
const entitiesStore = useEntitiesStore()

const currentDate = ref(new Date())
const selectedDate = ref<Date | null>(null)

const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
const dayNames = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']

const currentMonth = computed(() => currentDate.value.getMonth())
const currentYear = computed(() => currentDate.value.getFullYear())

const monthLabel = computed(() => `${monthNames[currentMonth.value]} ${currentYear.value}`)

const daysInMonth = computed(() => {
  const year = currentYear.value
  const month = currentMonth.value
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const daysCount = lastDay.getDate()
  let startDay = firstDay.getDay() - 1
  if (startDay < 0) startDay = 6
  
  const days: (number | null)[] = []
  for (let i = 0; i < startDay; i++) days.push(null)
  for (let i = 1; i <= daysCount; i++) days.push(i)
  return days
})

const prevMonth = () => {
  currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1)
}

const goToToday = () => {
  currentDate.value = new Date()
}

const isToday = (day: number | null): boolean => {
  if (!day) return false
  const today = new Date()
  return day === today.getDate() && 
         currentMonth.value === today.getMonth() && 
         currentYear.value === today.getFullYear()
}

const isSelected = (day: number | null): boolean => {
  if (!day || !selectedDate.value) return false
  return day === selectedDate.value.getDate() && 
         currentMonth.value === selectedDate.value.getMonth() && 
         currentYear.value === selectedDate.value.getFullYear()
}

const getBlocksForDay = (day: number): typeof blocksStore.blocks => {
  return blocksStore.blocks.filter(block => {
    if (!block.schedule) return false
    const schedule = block.schedule
    if (schedule.type === 'fixed' && schedule.date) {
      const blockDate = new Date(schedule.date)
      return blockDate.getDate() === day && 
             blockDate.getMonth() === currentMonth.value && 
             blockDate.getFullYear() === currentYear.value
    }
    if (schedule.type === 'weekly' && schedule.daysOfWeek?.length) {
      const dayOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][new Date(currentYear.value, currentMonth.value, day).getDay()]
      return schedule.daysOfWeek.includes(dayOfWeek)
    }
    return false
  })
}

const selectDay = (day: number | null) => {
  if (!day) return
  selectedDate.value = new Date(currentYear.value, currentMonth.value, day)
}

const selectedDayBlocks = computed(() => {
  if (!selectedDate.value) return []
  return blocksStore.blocks.filter(block => {
    if (!block.schedule) return false
    const schedule = block.schedule
    if (schedule.type === 'fixed' && schedule.date) {
      const blockDate = new Date(schedule.date)
      return blockDate.toDateString() === selectedDate.value!.toDateString()
    }
    return false
  })
})

onMounted(async () => {
  await blocksStore.fetchBlocks()
  await entitiesStore.fetchEntities()
})
</script>

<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Calendario</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Visualiza tus tareas y recordatorios programados</p>
      </div>
      <button 
        @click="goToToday"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
      >
        Hoy
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
          <button @click="prevMonth" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
            ←
          </button>
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">{{ monthLabel }}</h3>
          <button @click="nextMonth" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
            →
          </button>
        </div>
        
        <div class="p-4">
          <div class="grid grid-cols-7 gap-2 mb-2">
            <div 
              v-for="day in dayNames" 
              :key="day" 
              class="text-center text-xs font-medium text-slate-500 dark:text-slate-400 py-2"
            >
              {{ day }}
            </div>
          </div>
          
          <div class="grid grid-cols-7 gap-2">
            <div 
              v-for="(day, idx) in daysInMonth" 
              :key="idx"
              class="min-h-[80px] p-2 rounded-lg border transition-colors cursor-pointer"
              :class="[
                day 
                  ? isSelected(day) 
                    ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700'
                    : 'bg-white dark:bg-slate-800 border-slate-100 dark:border-slate-700 hover:border-indigo-200 dark:hover:border-indigo-600'
                  : 'border-transparent'
              ]"
              @click="selectDay(day)"
            >
              <div v-if="day" class="flex items-center justify-center mb-1">
                <span 
                  class="w-7 h-7 flex items-center justify-center rounded-full text-sm font-medium"
                  :class="[
                    isToday(day) 
                      ? 'bg-indigo-600 text-white' 
                      : 'text-slate-700 dark:text-slate-300'
                  ]"
                >
                  {{ day }}
                </span>
              </div>
              <div v-if="day && getBlocksForDay(day).length > 0" class="space-y-1">
                <div 
                  v-for="block in getBlocksForDay(day).slice(0, 2)" 
                  :key="block.id"
                  class="text-xs px-1.5 py-0.5 rounded truncate"
                  :class="[
                    block.type === 'payment' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300' :
                    block.type === 'reminder' ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' :
                    'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300'
                  ]"
                >
                  {{ block.data?.title || block.name }}
                </div>
                <div 
                  v-if="getBlocksForDay(day).length > 2" 
                  class="text-xs text-slate-500 dark:text-slate-400 text-center"
                >
                  +{{ getBlocksForDay(day).length - 2 }} más
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
            {{ selectedDate ? selectedDate.toLocaleDateString('es-ES', { day: 'numeric', month: 'long' }) : 'Selecciona un día' }}
          </h3>
        </div>
        <div class="p-4">
          <div v-if="!selectedDate" class="text-center text-slate-500 dark:text-slate-400 py-8">
            Haz clic en un día para ver sus tareas
          </div>
          <div v-else-if="selectedDayBlocks.length === 0" class="text-center text-slate-500 dark:text-slate-400 py-8">
            No hay tareas programadas
          </div>
          <div v-else class="space-y-3">
            <div 
              v-for="block in selectedDayBlocks" 
              :key="block.id"
              class="p-3 rounded-lg border border-slate-100 dark:border-slate-700"
            >
              <div class="flex items-start gap-3">
                <span 
                  class="w-8 h-8 rounded-lg flex items-center justify-center text-sm"
                  :class="[
                    block.type === 'payment' ? 'bg-emerald-100 dark:bg-emerald-900/50' :
                    block.type === 'reminder' ? 'bg-amber-100 dark:bg-amber-900/50' :
                    'bg-blue-100 dark:bg-blue-900/50'
                  ]"
                >
                  {{ block.type === 'payment' ? '💰' : block.type === 'reminder' ? '🔔' : '✓' }}
                </span>
                <div class="flex-1">
                  <h4 class="font-medium text-slate-800 dark:text-white">{{ block.data?.title || block.name }}</h4>
                  <p v-if="block.schedule?.time" class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    🕐 {{ block.schedule.time }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>