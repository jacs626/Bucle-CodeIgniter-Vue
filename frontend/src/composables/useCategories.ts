import { ref, computed } from 'vue'
import { categoriesApi } from '@/api'
import type { Category } from '@/types'
import { useToast } from './useToast'

export interface CategoryFormData {
  name: string
  icon: string
}

export function useCategories() {
  const { showToast } = useToast()

  const categories = ref<Category[]>([])
  const currentCategory = ref<Category | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const hasCategories = computed(() => categories.value.length > 0)

  const fetchCategories = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await categoriesApi.getAll()
      categories.value = response.data.data || []
    } catch (e) {
      error.value = 'Error al cargar las categorías'
      showToast('Error al cargar las categorías', 'error')
      console.error(e)
    } finally {
      isLoading.value = false
    }
  }

  const fetchCategory = async (id: number) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await categoriesApi.getById(id)
      currentCategory.value = response.data.data || null

      if (!currentCategory.value) {
        error.value = 'Categoría no encontrada'
        showToast('Categoría no encontrada', 'error')
      }

      return currentCategory.value
    } catch (e) {
      error.value = 'Error al cargar la categoría'
      showToast('Error al cargar la categoría', 'error')
      console.error(e)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const createCategory = async (data: CategoryFormData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await categoriesApi.create(data)
      const created = response.data.data
      categories.value.push(created)
      showToast('Categoría creada correctamente', 'success')
      return created
    } catch (e) {
      error.value = 'Error al crear la categoría'
      showToast('Error al crear la categoría', 'error')
      console.error(e)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const updateCategory = async (id: number, data: CategoryFormData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await categoriesApi.update(id, data)
      const updated = response.data.data

      const index = categories.value.findIndex((c) => c.id === id)
      if (index !== -1) {
        categories.value[index] = updated
      }

      showToast('Categoría actualizada correctamente', 'success')
      return updated
    } catch (e) {
      error.value = 'Error al actualizar la categoría'
      showToast('Error al actualizar la categoría', 'error')
      console.error(e)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const deleteCategory = async (id: number) => {
    isLoading.value = true
    error.value = null

    try {
      await categoriesApi.delete(id)
      categories.value = categories.value.filter((c) => c.id !== id)
      showToast('Categoría eliminada correctamente', 'success')
      return true
    } catch (e) {
      error.value = 'Error al eliminar la categoría'
      showToast('Error al eliminar la categoría', 'error')
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const getCategoryById = (id: number): Category | undefined => {
    return categories.value.find((c) => c.id === id)
  }

  return {
    categories,
    currentCategory,
    isLoading,
    error,
    hasCategories,
    fetchCategories,
    fetchCategory,
    createCategory,
    updateCategory,
    deleteCategory,
    getCategoryById,
  }
}