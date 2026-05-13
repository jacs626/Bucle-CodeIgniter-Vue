import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { historyApi } from '@/api'
import type { History } from '@/types'

interface HistoryFormData {
  entity_id: number | null
  block_id?: number | null
  date?: string | null
  status?: string
  note?: Record<string, unknown>
}

export const useHistoryStore = defineStore('history', () => {
  const histories = ref<History[]>([])
  const currentHistory = ref<History | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const hasHistories = computed(() => histories.value.length > 0)
  const historyCount = computed(() => histories.value.length)

  const fetchHistories = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await historyApi.getAll()
      histories.value = response.data.data || []
    } catch (e) {
      error.value = 'Error al cargar el historial'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const fetchHistory = async (id: number) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await historyApi.getById(id)
      currentHistory.value = response.data.data || null
      return currentHistory.value
    } catch (e) {
      error.value = 'Error al cargar el registro'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const createHistory = async (data: HistoryFormData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await historyApi.create(data)
      const created = response.data.data!
      histories.value.push(created)
      return created
    } catch (e) {
      error.value = 'Error al crear el registro'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const deleteHistory = async (id: number) => {
    isLoading.value = true
    error.value = null

    try {
      await historyApi.delete(id)
      histories.value = histories.value.filter((h) => h.id !== id)
      return true
    } catch (e) {
      error.value = 'Error al eliminar el registro'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const getHistoryById = (id: number): History | undefined => {
    return histories.value.find((h) => h.id === id)
  }

  const setCurrentHistory = (history: History | null) => {
    currentHistory.value = history
  }

  const clearError = () => {
    error.value = null
  }

  return {
    histories,
    currentHistory,
    isLoading,
    error,
    hasHistories,
    historyCount,
    fetchHistories,
    fetchHistory,
    createHistory,
    deleteHistory,
    getHistoryById,
    setCurrentHistory,
    clearError,
  }
})