<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useCategoriesStore } from '@/stores/categoriesStore'
import { useToast } from '@/composables/useToast'
import type { Category, CategoryFormData } from '@/types'
import CategoryList from '@/components/categories/CategoryList.vue'
import CategoryForm from '@/components/categories/CategoryForm.vue'

type ViewMode = 'list' | 'create' | 'edit'

const categoriesStore = useCategoriesStore()
const { showToast } = useToast()

const currentView = ref<ViewMode>('list')
const selectedCategory = ref<Category | null>(null)
const isSubmitting = ref(false)
const showDeleteConfirm = ref(false)
const categoryToDelete = ref<Category | null>(null)

const loadCategories = async () => {
  try {
    await categoriesStore.fetchCategories()
  } catch {
    showToast('Error al cargar las categorías', 'error')
  }
}

onMounted(() => {
  loadCategories()
})

const handleCreateClick = () => {
  selectedCategory.value = null
  currentView.value = 'create'
}

const handleEdit = (category: Category) => {
  selectedCategory.value = category
  currentView.value = 'edit'
}

const handleDelete = (category: Category) => {
  categoryToDelete.value = category
  showDeleteConfirm.value = true
}

const handleConfirmDelete = async () => {
  if (!categoryToDelete.value) return

  try {
    await categoriesStore.deleteCategory(categoryToDelete.value.id)
    showToast('Categoría eliminada correctamente', 'success')
  } catch {
    showToast('Error al eliminar la categoría', 'error')
  } finally {
    showDeleteConfirm.value = false
    categoryToDelete.value = null
  }
}

const handleCancelDelete = () => {
  showDeleteConfirm.value = false
  categoryToDelete.value = null
}

const handleSubmit = async (data: CategoryFormData) => {
  isSubmitting.value = true

  try {
    if (currentView.value === 'create') {
      await categoriesStore.createCategory(data)
      showToast('Categoría creada correctamente', 'success')
      currentView.value = 'list'
    } else if (currentView.value === 'edit' && selectedCategory.value) {
      await categoriesStore.updateCategory(selectedCategory.value.id, data)
      showToast('Categoría actualizada correctamente', 'success')
      currentView.value = 'list'
    }
  } catch {
    showToast('Error al guardar la categoría', 'error')
  } finally {
    isSubmitting.value = false
  }
}

const handleCancel = () => {
  currentView.value = 'list'
  selectedCategory.value = null
}

const goBack = () => {
  currentView.value = 'list'
  selectedCategory.value = null
}
</script>

<template>
  <div class="categories-view">
    <div class="page-header">
      <div class="header-left">
        <button
          v-if="currentView !== 'list'"
          class="btn-back"
          @click="goBack"
        >
          ← Volver
        </button>
        <h2>{{ currentView === 'list' ? 'Categorías' : currentView === 'create' ? 'Nueva Categoría' : 'Editar Categoría' }}</h2>
      </div>
      <button
        v-if="currentView === 'list'"
        class="btn-primary"
        @click="handleCreateClick"
      >
        + Nueva Categoría
      </button>
    </div>

    <div class="page-content">
      <CategoryList
        v-if="currentView === 'list'"
        :categories="categoriesStore.categories"
        :is-loading="categoriesStore.isLoading"
        @edit="handleEdit"
        @delete="handleDelete"
      />

      <CategoryForm
        v-else
        :category="selectedCategory"
        :is-submitting="isSubmitting"
        @submit="handleSubmit"
        @cancel="handleCancel"
      />
    </div>

    <Teleport to="body">
      <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="handleCancelDelete">
        <div class="modal">
          <div class="modal-header">
            <h3>Confirmar eliminación</h3>
          </div>
          <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar la categoría <strong>{{ categoryToDelete?.name }}</strong>?</p>
            <p class="warning">Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-actions">
            <button class="btn-cancel" @click="handleCancelDelete">Cancelar</button>
            <button class="btn-delete" @click="handleConfirmDelete">Eliminar</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.categories-view {
  max-width: 1200px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-back {
  background: none;
  border: none;
  color: #4f46e5;
  font-size: 14px;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 6px;
  transition: background 0.2s ease;
}

.btn-back:hover {
  background: #f3f4f6;
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

.page-content {
  min-height: 400px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 16px 0;
}

.modal-body p {
  font-size: 14px;
  color: #374151;
  margin: 0 0 8px 0;
}

.modal-body .warning {
  color: #dc2626;
  font-size: 13px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.btn-cancel,
.btn-delete {
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 500;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel {
  background: #fff;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn-cancel:hover {
  background: #f3f4f6;
}

.btn-delete {
  background: #dc2626;
  border: none;
  color: #fff;
}

.btn-delete:hover {
  background: #b91c1c;
}
</style>