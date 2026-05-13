<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useDocumentsStore } from '@/stores/documentsStore'

const documentsStore = useDocumentsStore()
const isLoading = ref(true)
const isUploading = ref(false)
const searchQuery = ref('')

const filteredDocuments = computed(() => {
  if (!searchQuery.value) return documentsStore.documents
  const query = searchQuery.value.toLowerCase()
  return documentsStore.documents.filter(doc => 
    (doc.title as string)?.toLowerCase().includes(query)
  )
})

const formatFileSize = (bytes: number): string => {
  if (!bytes || bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getFileIcon = (fileType: string | undefined) => {
  if (!fileType) return '📄'
  const type = fileType.toLowerCase()
  if (type.includes('pdf')) return '📕'
  if (type.includes('image') || type.includes('png') || type.includes('jpg')) return '🖼️'
  if (type.includes('doc') || type.includes('word')) return '📘'
  if (type.includes('sheet') || type.includes('excel')) return '📗'
  return '📄'
}

const handleUploadClick = () => {
  const input = document.createElement('input')
  input.type = 'file'
  input.multiple = true
  input.accept = '*/*'
  input.onchange = async (e: Event) => {
    const files = (e.target as HTMLInputElement).files
    if (files && files.length > 0) {
      isUploading.value = true
      for (const file of files) {
        await documentsStore.uploadDocument(file)
      }
      isUploading.value = false
    }
  }
  input.click()
}

onMounted(async () => {
  await documentsStore.fetchDocuments()
  isLoading.value = false
})
</script>

<template>
  <div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Documentos</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Gestiona tus archivos y documentos</p>
      </div>
      <button 
        @click="handleUploadClick"
        :disabled="isUploading"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <span>{{ isUploading ? 'Subiendo...' : '↑ Subir documento' }}</span>
      </button>
    </div>

    <div class="relative">
      <input 
        v-model="searchQuery"
        type="text"
        placeholder="Buscar documentos..."
        class="w-full px-4 py-3 pl-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      />
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
    </div>

    <div v-if="isLoading" class="text-center text-slate-500 py-12">
      Cargando documentos...
    </div>

    <div v-else-if="filteredDocuments.length === 0" class="text-center py-12">
      <div class="text-4xl mb-4">📄</div>
      <p class="text-slate-500 dark:text-slate-400">
        {{ searchQuery ? 'No se encontraron documentos' : 'No hay documentos aún' }}
      </p>
      <button 
        v-if="!searchQuery"
        @click="handleUploadClick"
        class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm"
      >
        Subir tu primer documento
      </button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div 
        v-for="doc in filteredDocuments" 
        :key="doc.id"
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:shadow-md transition-shadow cursor-pointer"
      >
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-2xl">
            {{ getFileIcon(doc.file_type as string) }}
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-medium text-slate-800 dark:text-slate-200 truncate">
              {{ doc.title || 'Sin nombre' }}
            </h3>
            <div class="flex items-center gap-3 mt-1 text-sm text-slate-500 dark:text-slate-400">
              <span>{{ doc.file_type || 'Tipo desconocido' }}</span>
              <span v-if="doc.file_size">• {{ formatFileSize(doc.file_size as number) }}</span>
            </div>
            <div class="flex items-center gap-2 mt-2">
              <span 
                :class="[
                  'px-2 py-0.5 text-xs font-medium rounded-full',
                  doc.is_published 
                    ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300' 
                    : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400'
                ]"
              >
                {{ doc.is_published ? 'Publicado' : 'Borrador' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>