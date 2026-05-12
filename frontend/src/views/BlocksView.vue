<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { blocksApi } from '@/api'
import type { Block } from '@/types'

const blocks = ref<Block[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)

const loadBlocks = async () => {
  try {
    isLoading.value = true
    const response = await blocksApi.getAll()
    blocks.value = response.data.data || []
  } catch (e) {
    error.value = 'Failed to load blocks'
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadBlocks()
})
</script>

<template>
  <div class="blocks-view">
    <div class="page-header">
      <h2>Blocks</h2>
      <button class="btn-primary">Add Block</button>
    </div>

    <div v-if="isLoading" class="loading">Loading blocks...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else class="content">
      <div v-if="blocks.length === 0" class="empty-state">
        <p>No blocks found. Create your first block!</p>
      </div>
      <div v-else class="blocks-grid">
        <div v-for="block in blocks" :key="block.id" class="block-card">
          <div class="block-type">{{ block.type }}</div>
          <h3>{{ block.name }}</h3>
          <span :class="['status', block.is_active ? 'active' : 'inactive']">
            {{ block.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.blocks-view {
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

.blocks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.block-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.block-type {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.block-card h3 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.status.active {
  background: #d1fae5;
  color: #065f46;
}

.status.inactive {
  background: #fee2e2;
  color: #991b1b;
}
</style>