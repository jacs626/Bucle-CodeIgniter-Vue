<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { documentsApi } from '@/api'
import type { Document } from '@/types'

const documents = ref<Document[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)

const loadDocuments = async () => {
  try {
    isLoading.value = true
    const response = await documentsApi.getAll()
    documents.value = response.data.data || []
  } catch (e) {
    error.value = 'Failed to load documents'
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadDocuments()
})

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

<template>
  <div class="documents-view">
    <div class="page-header">
      <h2>Documents</h2>
      <button class="btn-primary">Upload Document</button>
    </div>

    <div v-if="isLoading" class="loading">Loading documents...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else class="content">
      <div v-if="documents.length === 0" class="empty-state">
        <p>No documents found. Upload your first document!</p>
      </div>
      <div v-else class="documents-grid">
        <div v-for="doc in documents" :key="doc.id" class="document-card">
          <div class="document-icon">📄</div>
          <div class="document-info">
            <h3>{{ doc.title }}</h3>
            <div class="document-meta">
              <span>{{ doc.file_type || 'Unknown' }}</span>
              <span>{{ doc.file_size ? formatFileSize(doc.file_size) : '' }}</span>
            </div>
          </div>
          <span :class="['status', doc.is_published ? 'published' : 'draft']">
            {{ doc.is_published ? 'Published' : 'Draft' }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.documents-view {
  max-width: 1200px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-header h2 {
  font-size: 24px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.btn-primary {
  background: #4f46e5;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-primary:hover {
  background: #4338ca;
}

.loading, .error {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.error {
  color: #dc2626;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #fff;
  border-radius: 12px;
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.document-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.document-icon {
  font-size: 32px;
}

.document-info {
  flex: 1;
}

.document-info h3 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.document-meta {
  display: flex;
  gap: 12px;
  font-size: 12px;
  color: #6b7280;
}

.status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.status.published {
  background: #d1fae5;
  color: #065f46;
}

.status.draft {
  background: #fee2e2;
  color: #991b1b;
}
</style>