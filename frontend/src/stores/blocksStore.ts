import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { blocksApi } from '@/api'
import type { Block } from '@/types'

interface BlockFormData {
  entity_id: number | null
  name: string
  type: string
  data?: Record<string, unknown>
  schedule?: Record<string, unknown>
  parent_block_id?: number | null
  order_index?: number
  is_active?: boolean
}

export const useBlocksStore = defineStore('blocks', () => {
  const blocks = ref<Block[]>([])
  const currentBlock = ref<Block | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const hasBlocks = computed(() => blocks.value.length > 0)
  const blockCount = computed(() => blocks.value.length)

  const fetchBlocks = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await blocksApi.getAll()
      blocks.value = response.data.data || []
    } catch (e) {
      error.value = 'Error al cargar los blocks'
      console.error(e)
      throw e
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
      return currentBlock.value
    } catch (e) {
      error.value = 'Error al cargar el block'
      console.error(e)
      throw e
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
      console.error(e)
      throw e
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
      return created
    } catch (e) {
      error.value = 'Error al crear el block'
      console.error(e)
      throw e
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

      return updated
    } catch (e) {
      error.value = 'Error al actualizar el block'
      console.error(e)
      throw e
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
      return true
    } catch (e) {
      error.value = 'Error al eliminar el block'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const getBlockById = (id: number): Block | undefined => {
    return blocks.value.find((b) => b.id === id)
  }

  const setCurrentBlock = (block: Block | null) => {
    currentBlock.value = block
  }

  const clearError = () => {
    error.value = null
  }

  return {
    blocks,
    currentBlock,
    isLoading,
    error,
    hasBlocks,
    blockCount,
    fetchBlocks,
    fetchBlock,
    fetchBlocksByEntity,
    createBlock,
    updateBlock,
    deleteBlock,
    getBlockById,
    setCurrentBlock,
    clearError,
  }
})