import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { documentsApi } from '@/api'
import type { Document } from '@/types'

interface DocumentFormData {
  entity_id: number | null
  title: string
  url?: string
  type?: string
}

export const useDocumentsStore = defineStore('documents', () => {
  const documents = ref<Document[]>([])
  const currentDocument = ref<Document | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const hasDocuments = computed(() => documents.value.length > 0)
  const documentCount = computed(() => documents.value.length)

  const fetchDocuments = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await documentsApi.getAll()
      documents.value = response.data.data || []
    } catch (e) {
      error.value = 'Error al cargar los documentos'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const fetchDocument = async (id: number) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await documentsApi.getById(id)
      currentDocument.value = response.data.data || null
      return currentDocument.value
    } catch (e) {
      error.value = 'Error al cargar el documento'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const createDocument = async (data: DocumentFormData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await documentsApi.create(data)
      const created = response.data.data!
      documents.value.push(created)
      return created
    } catch (e) {
      error.value = 'Error al crear el documento'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const updateDocument = async (id: number, data: DocumentFormData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await documentsApi.update(id, data)
      const updated = response.data.data!

      const index = documents.value.findIndex((d) => d.id === id)
      if (index !== -1) {
        documents.value[index] = updated
      }

      return updated
    } catch (e) {
      error.value = 'Error al actualizar el documento'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const deleteDocument = async (id: number) => {
    isLoading.value = true
    error.value = null

    try {
      await documentsApi.delete(id)
      documents.value = documents.value.filter((d) => d.id !== id)
      return true
    } catch (e) {
      error.value = 'Error al eliminar el documento'
      console.error(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const getDocumentById = (id: number): Document | undefined => {
    return documents.value.find((d) => d.id === id)
  }

  const setCurrentDocument = (document: Document | null) => {
    currentDocument.value = document
  }

  const clearError = () => {
    error.value = null
  }

  return {
    documents,
    currentDocument,
    isLoading,
    error,
    hasDocuments,
    documentCount,
    fetchDocuments,
    fetchDocument,
    createDocument,
    updateDocument,
    deleteDocument,
    getDocumentById,
    setCurrentDocument,
    clearError,
  }
})