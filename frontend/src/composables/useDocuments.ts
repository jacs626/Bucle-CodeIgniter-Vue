import { ref, computed } from 'vue'
import { documentsApi } from '@/api'
import type { Document } from '@/types'
import { useToast } from './useToast'

export interface DocumentFormData {
  entity_id: number | null
  title: string
  url?: string
  type?: string
}

export function useDocuments() {
  const { showToast } = useToast()

  const documents = ref<Document[]>([])
  const currentDocument = ref<Document | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const hasDocuments = computed(() => documents.value.length > 0)

  const fetchDocuments = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await documentsApi.getAll()
      documents.value = response.data.data || []
    } catch (e) {
      error.value = 'Error al cargar los documentos'
      showToast('Error al cargar los documentos', 'error')
      console.error(e)
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

      if (!currentDocument.value) {
        error.value = 'Documento no encontrado'
        showToast('Documento no encontrado', 'error')
      }

      return currentDocument.value
    } catch (e) {
      error.value = 'Error al cargar el documento'
      showToast('Error al cargar el documento', 'error')
      console.error(e)
      return null
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
      showToast('Documento creado correctamente', 'success')
      return created
    } catch (e) {
      error.value = 'Error al crear el documento'
      showToast('Error al crear el documento', 'error')
      console.error(e)
      return null
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

      showToast('Documento actualizado correctamente', 'success')
      return updated
    } catch (e) {
      error.value = 'Error al actualizar el documento'
      showToast('Error al actualizar el documento', 'error')
      console.error(e)
      return null
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
      showToast('Documento eliminado correctamente', 'success')
      return true
    } catch (e) {
      error.value = 'Error al eliminar el documento'
      showToast('Error al eliminar el documento', 'error')
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const getDocumentById = (id: number): Document | undefined => {
    return documents.value.find((d) => d.id === id)
  }

  return {
    documents,
    currentDocument,
    isLoading,
    error,
    hasDocuments,
    fetchDocuments,
    fetchDocument,
    createDocument,
    updateDocument,
    deleteDocument,
    getDocumentById,
  }
}