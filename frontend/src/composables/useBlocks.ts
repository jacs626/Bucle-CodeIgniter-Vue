import { ref, computed } from 'vue'
import { blocksApi } from '@/api'
import type { Block } from '@/types'
import { useToast } from './useToast'

export interface BlockFormData {
  entity_id: number | null
  name: string
  type: string
  data?: Record<string, unknown>
  schedule?: Record<string, unknown>
  parent_block_id?: number | null
  order_index?: number
  is_active?: boolean
}

export function useBlocks() {
  const { showToast } = useToast()

  const blocks = ref<Block[]>([])
  const currentBlock = ref<Block | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const hasBlocks = computed(() => blocks.value.length > 0)

  const fetchBlocks = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await blocksApi.getAll()
      blocks.value = response.data.data || []
    } catch (e) {
      error.value = 'Error al cargar los blocks'
      showToast('Error al cargar los blocks', 'error')
      console.error(e)
    } finally {
      isLoading.value = false
    }
  }

  const fetchBlock = async (id: number) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await blocksApi.getById(id)
      currentBlock.value = response.data.data || null

      if (!currentBlock.value) {
        error.value = 'Block no encontrado'
        showToast('Block no encontrado', 'error')
      }

      return currentBlock.value
    } catch (e) {
      error.value = 'Error al cargar el block'
      showToast('Error al cargar el block', 'error')
      console.error(e)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const fetchBlocksByEntity = async (entityId: number) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await blocksApi.getByEntityId(entityId)
      blocks.value = response.data.data || []
    } catch (e) {
      error.value = 'Error al cargar los blocks de la entidad'
      showToast('Error al cargar los blocks de la entidad', 'error')
      console.error(e)
    } finally {
      isLoading.value = false
    }
  }

  const createBlock = async (data: BlockFormData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await blocksApi.create(data)
      const created = response.data.data
      blocks.value.push(created)
      showToast('Block creado correctamente', 'success')
      return created
    } catch (e) {
      error.value = 'Error al crear el block'
      showToast('Error al crear el block', 'error')
      console.error(e)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const updateBlock = async (id: number, data: BlockFormData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await blocksApi.update(id, data)
      const updated = response.data.data

      const index = blocks.value.findIndex((b) => b.id === id)
      if (index !== -1) {
        blocks.value[index] = updated
      }

      showToast('Block actualizado correctamente', 'success')
      return updated
    } catch (e) {
      error.value = 'Error al actualizar el block'
      showToast('Error al actualizar el block', 'error')
      console.error(e)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const deleteBlock = async (id: number) => {
    isLoading.value = true
    error.value = null

    try {
      await blocksApi.delete(id)
      blocks.value = blocks.value.filter((b) => b.id !== id)
      showToast('Block eliminado correctamente', 'success')
      return true
    } catch (e) {
      error.value = 'Error al eliminar el block'
      showToast('Error al eliminar el block', 'error')
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const getBlockById = (id: number): Block | undefined => {
    return blocks.value.find((b) => b.id === id)
  }

  return {
    blocks,
    currentBlock,
    isLoading,
    error,
    hasBlocks,
    fetchBlocks,
    fetchBlock,
    fetchBlocksByEntity,
    createBlock,
    updateBlock,
    deleteBlock,
    getBlockById,
  }
}