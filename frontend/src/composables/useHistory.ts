import { ref, computed } from 'vue'
import { historyApi } from '@/api'
import type { History } from '@/types'
import { useToast } from './useToast'

export interface HistoryFormData {
  entity_id: number | null
  block_id?: number | null
  date?: string | null
  status?: string
  note?: Record<string, unknown>
}

export function useHistory() {
  const { showToast } = useToast()

  const histories = ref<History[]>([])
  const currentHistory = ref<History | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const hasHistories = computed(() => histories.value.length > 0)

  const fetchHistories = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await historyApi.getAll()
      histories.value = response.data.data || []
    } catch (e) {
      error.value = 'Error al cargar el historial'
      showToast('Error al cargar el historial', 'error')
      console.error(e)
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

      if (!currentHistory.value) {
        error.value = 'Registro no encontrado'
        showToast('Registro no encontrado', 'error')
      }

      return currentHistory.value
    } catch (e) {
      error.value = 'Error al cargar el registro'
      showToast('Error al cargar el registro', 'error')
      console.error(e)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const createHistory = async (data: HistoryFormData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await historyApi.create(data)
      const created = response.data.data
      histories.value.push(created)
      showToast('Registro creado correctamente', 'success')
      return created
    } catch (e) {
      error.value = 'Error al crear el registro'
      showToast('Error al crear el registro', 'error')
      console.error(e)
      return null
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
      showToast('Registro eliminado correctamente', 'success')
      return true
    } catch (e) {
      error.value = 'Error al eliminar el registro'
      showToast('Error al eliminar el registro', 'error')
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const getHistoryById = (id: number): History | undefined => {
    return histories.value.find((h) => h.id === id)
  }

  return {
    histories,
    currentHistory,
    isLoading,
    error,
    hasHistories,
    fetchHistories,
    fetchHistory,
    createHistory,
    deleteHistory,
    getHistoryById,
  }
}