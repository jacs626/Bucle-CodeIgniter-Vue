import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { categoriesApi } from '@/api'
import type { Category } from '@/types'

interface CategoryFormData {
  name: string
  icon: string
}

export const useCategoriesStore = defineStore('categories', () => {
  const categories = ref<Category[]>([])
  const currentCategory = ref<Category | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const hasCategories = computed(() => categories.value.length > 0)
  const categoryCount = computed(() => categories.value.length)

  const fetchCategories = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await categoriesApi.getAll()
      categories.value = response.data.data || []
    } catch (e) {
      error.value = 'Error al cargar las categorías'
      console.error(e)
      throw e
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
      return currentCategory.value
    } catch (e) {
      error.value = 'Error al cargar la categoría'
      console.error(e)
      throw e
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
      return created
    } catch (e) {
      error.value = 'Error al crear la categoría'
      console.error(e)
      throw e
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

      return updated
    } catch (e) {
      error.value = 'Error al actualizar la categoría'
      console.error(e)
      throw e
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
      return true
    } catch (e) {
      error.value = 'Error al eliminar la categoría'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const getCategoryById = (id: number): Category | undefined => {
    return categories.value.find((c) => c.id === id)
  }

  const setCurrentCategory = (category: Category | null) => {
    currentCategory.value = category
  }

  const clearError = () => {
    error.value = null
  }

  return {
    categories,
    currentCategory,
    isLoading,
    error,
    hasCategories,
    categoryCount,
    fetchCategories,
    fetchCategory,
    createCategory,
    updateCategory,
    deleteCategory,
    getCategoryById,
    setCurrentCategory,
    clearError,
  }
})