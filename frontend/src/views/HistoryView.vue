<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { historyApi } from '@/api'
import type { History } from '@/types'

const history = ref<History[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)

const loadHistory = async () => {
  try {
    isLoading.value = true
    const response = await historyApi.getAll()
    history.value = response.data.data || []
  } catch (e) {
    error.value = 'Failed to load history'
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadHistory()
})
</script>

<template>
  <div class="history-view">
    <div class="page-header">
      <h2>History</h2>
    </div>

    <div v-if="isLoading" class="loading">Loading history...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else class="content">
      <div v-if="history.length === 0" class="empty-state">
        <p>No history records found.</p>
      </div>
      <div v-else class="history-list">
        <div v-for="item in history" :key="item.id" class="history-item">
          <div class="history-action">
            <span class="action-badge">{{ item.action }}</span>
          </div>
          <div class="history-details">
            <span class="entity-type">{{ item.entity_type }}</span>
            <span class="entity-id">#{{ item.entity_id }}</span>
          </div>
          <div class="history-time">
            {{ new Date(item.created_at).toLocaleString() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.history-view {
  max-width: 1200px;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h2 {
  font-size: 24px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
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

.history-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.history-item {
  background: #fff;
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.action-badge {
  padding: 4px 10px;
  background: #e0e7ff;
  color: #4f46e5;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.history-details {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 8px;
}

.entity-type {
  font-size: 14px;
  font-weight: 500;
  color: #1f2937;
}

.entity-id {
  font-size: 14px;
  color: #6b7280;
}

.history-time {
  font-size: 12px;
  color: #9ca3af;
}
</style>