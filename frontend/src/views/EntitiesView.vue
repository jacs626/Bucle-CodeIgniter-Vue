<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { entitiesApi } from '@/api'
import type { Entity } from '@/types'

const entities = ref<Entity[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)

const loadEntities = async () => {
  try {
    isLoading.value = true
    const response = await entitiesApi.getAll()
    entities.value = response.data.data || []
  } catch (e) {
    error.value = 'Failed to load entities'
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadEntities()
})
</script>

<template>
  <div class="entities-view">
    <div class="page-header">
      <h2>Entities</h2>
      <button class="btn-primary">Add Entity</button>
    </div>

    <div v-if="isLoading" class="loading">Loading entities...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else class="content">
      <div v-if="entities.length === 0" class="empty-state">
        <p>No entities found. Create your first entity!</p>
      </div>
      <div v-else class="entities-table">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Type</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="entity in entities" :key="entity.id">
              <td>{{ entity.id }}</td>
              <td>{{ entity.name }}</td>
              <td>{{ entity.type }}</td>
              <td>
                <span :class="['status', entity.is_active ? 'active' : 'inactive']">
                  {{ entity.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.entities-view {
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

.entities-table {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 16px;
  text-align: left;
}

th {
  background: #f9fafb;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

td {
  font-size: 14px;
  color: #1f2937;
  border-top: 1px solid #e5e7eb;
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