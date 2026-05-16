import { ref } from 'vue'
import type { Block } from '@/types'

export type ScheduleType = 'fixed' | 'interval' | 'weekly'

export interface ScheduleConfig {
  type: ScheduleType
  date?: string
  time?: string
  intervalHours?: number
  startDate?: string
  daysOfWeek?: string[]
}

export function useSchedule() {
  const scheduleExpanded = ref(false)
  const scheduleType = ref<ScheduleType>('fixed')
  const scheduleDate = ref('')
  const scheduleTime = ref('')
  const scheduleIntervalHours = ref('')
  const scheduleStartDate = ref('')
  const scheduleStartTime = ref('')
  const scheduleDays = ref<string[]>([])

  const buildSchedule = (): Block['schedule'] | undefined => {
    if (!scheduleExpanded.value) return undefined

    if (scheduleType.value === 'fixed') {
      if (!scheduleDate.value) return undefined
      return {
        type: 'fixed',
        date: scheduleDate.value + (scheduleTime.value ? 'T' + scheduleTime.value : ''),
        time: scheduleTime.value || undefined,
      }
    }

    if (scheduleType.value === 'interval') {
      if (!scheduleIntervalHours.value) return undefined
      const startDateValue = scheduleStartDate.value
        ? new Date(
            scheduleStartDate.value +
            (scheduleStartTime.value ? 'T' + scheduleStartTime.value + ':00' : ''),
          ).toISOString()
        : undefined
      return {
        type: 'interval',
        intervalHours: parseInt(scheduleIntervalHours.value),
        startDate: startDateValue,
        time: scheduleTime.value || undefined,
      }
    }

    if (scheduleType.value === 'weekly') {
      if (scheduleDays.value.length === 0) return undefined
      return {
        type: 'weekly',
        daysOfWeek: [...scheduleDays.value],
        time: scheduleTime.value || undefined,
      }
    }

    return undefined
  }

  const loadSchedule = (schedule: Block['schedule']) => {
    if (!schedule) {
      scheduleExpanded.value = false
      return
    }
    scheduleExpanded.value = true
    scheduleType.value = schedule.type
    scheduleTime.value = schedule.time ?? ''
    scheduleDays.value = [...(schedule.daysOfWeek ?? [])]

    if (schedule.type === 'fixed' && schedule.date) {
      scheduleDate.value = schedule.date.split('T')[0] ?? ''
    }
    if (schedule.type === 'interval' && schedule.intervalHours) {
      scheduleIntervalHours.value = schedule.intervalHours.toString()
    }
  }

  const toggleDay = (day: string) => {
    if (scheduleDays.value.includes(day)) {
      scheduleDays.value = scheduleDays.value.filter(d => d !== day)
    } else {
      scheduleDays.value = [...scheduleDays.value, day]
    }
  }

  const resetSchedule = () => {
    scheduleExpanded.value = false
    scheduleType.value = 'fixed'
    scheduleDate.value = ''
    scheduleTime.value = ''
    scheduleIntervalHours.value = ''
    scheduleStartDate.value = ''
    scheduleStartTime.value = ''
    scheduleDays.value = []
  }

  return {
    scheduleExpanded,
    scheduleType,
    scheduleDate,
    scheduleTime,
    scheduleIntervalHours,
    scheduleStartDate,
    scheduleStartTime,
    scheduleDays,
    buildSchedule,
    loadSchedule,
    toggleDay,
    resetSchedule,
  }
}