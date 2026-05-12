import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { entitiesApi } from '@/api'
import type { Entity } from '@/types'

interface EntityFormData {
  name: string
  description?: string
  type: string
  category_id: number | null
  metadata?: Record<string, unknown>
  is_active?: boolean
}

export const useEntitiesStore = defineStore('entities', () => {
  const entities = ref<Entity[]>([])
  const currentEntity = ref<Entity | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const hasEntities = computed(() => entities.value.length > 0)
  const entityCount = computed(() => entities.value.length)

  const fetchEntities = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await entitiesApi.getAll()
      entities.value = response.data.data || []
    } catch (e) {
      error.value = 'Error al cargar las entidades'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const fetchEntity = async (id: number) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await entitiesApi.getById(id)
      currentEntity.value = response.data.data || null
      return currentEntity.value
    } catch (e) {
      error.value = 'Error al cargar la entidad'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const createEntity = async (data: EntityFormData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await entitiesApi.create(data)
      const created = response.data.data
      entities.value.push(created)
      return created
    } catch (e) {
      error.value = 'Error al crear la entidad'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const updateEntity = async (id: number, data: EntityFormData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await entitiesApi.update(id, data)
      const updated = response.data.data

      const index = entities.value.findIndex((e) => e.id === id)
      if (index !== -1) {
        entities.value[index] = updated
      }

      return updated
    } catch (e) {
      error.value = 'Error al actualizar la entidad'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const deleteEntity = async (id: number) => {
    isLoading.value = true
    error.value = null

    try {
      await entitiesApi.delete(id)
      entities.value = entities.value.filter((e) => e.id !== id)
      return true
    } catch (e) {
      error.value = 'Error al eliminar la entidad'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const getEntityById = (id: number): Entity | undefined => {
    return entities.value.find((e) => e.id === id)
  }

  const setCurrentEntity = (entity: Entity | null) => {
    currentEntity.value = entity
  }

  const clearError = () => {
    error.value = null
  }

  return {
    entities,
    currentEntity,
    isLoading,
    error,
    hasEntities,
    entityCount,
    fetchEntities,
    fetchEntity,
    createEntity,
    updateEntity,
    deleteEntity,
    getEntityById,
    setCurrentEntity,
    clearError,
  }
})