<script setup lang="ts">
import type { Category } from '@/types'

interface Props {
  categories: Category[]
  isLoading?: boolean
}

interface Emits {
  (e: 'edit', category: Category): void
  (e: 'delete', category: Category): void
}

defineProps<Props>()
const emit = defineEmits<Emits>()

const formatDate = (dateString: string | null): string => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="category-list">
    <div v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando categorías...</p>
    </div>

    <div v-else-if="categories.length === 0" class="empty-state">
      <div class="empty-icon">📁</div>
      <h3>No hay categorías</h3>
      <p>Crea tu primera categoría para comenzar</p>
    </div>

    <div v-else class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Icono</th>
            <th>Fecha de creación</th>
            <th>Última actualización</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id">
            <td class="id-cell">{{ category.id }}</td>
            <td class="name-cell">
              <span class="category-name">{{ category.name }}</span>
            </td>
            <td class="icon-cell">
              <span class="category-icon">{{ category.icon || '📁' }}</span>
            </td>
            <td class="date-cell">{{ formatDate(category.created_at) }}</td>
            <td class="date-cell">{{ formatDate(category.updated_at) }}</td>
            <td class="actions-cell">
              <button
                class="btn-action btn-edit"
                title="Editar"
                @click="emit('edit', category)"
              >
                ✏️
              </button>
              <button
                class="btn-action btn-delete"
                title="Eliminar"
                @click="emit('delete', category)"
              >
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.category-list {
  width: 100%;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #6b7280;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.empty-state p {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.table-container {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #f9fafb;
}

th {
  padding: 14px 16px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e5e7eb;
}

td {
  padding: 14px 16px;
  font-size: 14px;
  color: #1f2937;
  border-bottom: 1px solid #e5e7eb;
}

tbody tr:last-child td {
  border-bottom: none;
}

tbody tr:hover {
  background: #f9fafb;
}

.id-cell {
  font-family: monospace;
  color: #6b7280;
}

.name-cell {
  font-weight: 500;
}

.category-name {
  display: inline-block;
  padding: 4px 12px;
  background: #e0e7ff;
  border-radius: 4px;
  color: #4f46e5;
}

.category-icon {
  font-size: 20px;
}

.date-cell {
  color: #6b7280;
  font-size: 13px;
}

.actions-cell {
  display: flex;
  gap: 8px;
}

.btn-action {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 0.2s ease;
}

.btn-edit {
  background: #fef3c7;
}

.btn-edit:hover {
  background: #fde68a;
}

.btn-delete {
  background: #fee2e2;
}

.btn-delete:hover {
  background: #fecaca;
}
</style>