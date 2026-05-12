<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import type { Category, CategoryFormData } from '@/types'

interface Props {
  category?: Category | null
  isSubmitting?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  category: null,
  isSubmitting: false,
})

const emit = defineEmits<{
  (e: 'submit', data: CategoryFormData): void
  (e: 'cancel'): void
}>()

const formData = ref<CategoryFormData>({
  name: '',
  icon: '',
})

const errors = ref<Record<string, string>>({})

const isEditing = computed(() => !!props.category)

const iconOptions = [
  { value: '📁', label: 'Carpeta' },
  { value: '🏢', label: 'Edificio' },
  { value: '💼', label: 'Maleta' },
  { value: '🎯', label: 'Objetivo' },
  { value: '📊', label: 'Gráfico' },
  { value: '🌐', label: 'Globo' },
  { value: '💡', label: 'Bombilla' },
  { value: '🚀', label: 'Cohete' },
  { value: '⚡', label: 'Relámpago' },
  { value: '🔧', label: 'Herramienta' },
  { value: '📦', label: 'Paquete' },
  { value: '🎨', label: 'Paleta' },
]

watch(
  () => props.category,
  (newCategory) => {
    if (newCategory) {
      formData.value = {
        name: newCategory.name,
        icon: newCategory.icon || '',
      }
    } else {
      formData.value = {
        name: '',
        icon: '',
      }
    }
    errors.value = {}
  },
  { immediate: true }
)

const validateForm = (): boolean => {
  errors.value = {}

  if (!formData.value.name.trim()) {
    errors.value.name = 'El nombre es requerido'
  } else if (formData.value.name.length > 255) {
    errors.value.name = 'El nombre debe tener menos de 255 caracteres'
  }

  if (formData.value.icon && formData.value.icon.length > 100) {
    errors.value.icon = 'El icono debe tener menos de 100 caracteres'
  }

  return Object.keys(errors.value).length === 0
}

const handleSubmit = () => {
  if (validateForm()) {
    emit('submit', { ...formData.value })
  }
}

const handleCancel = () => {
  emit('cancel')
}

const selectIcon = (icon: string) => {
  formData.value.icon = formData.value.icon === icon ? '' : icon
}
</script>

<template>
  <div class="category-form">
    <div class="form-header">
      <h3>{{ isEditing ? 'Editar Categoría' : 'Nueva Categoría' }}</h3>
      <p>{{ isEditing ? 'Actualiza los datos de la categoría' : 'Crea una nueva categoría' }}</p>
    </div>

    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="name">Nombre <span class="required">*</span></label>
        <input
          id="name"
          v-model="formData.name"
          type="text"
          placeholder="Ingresa el nombre de la categoría"
          :class="{ 'has-error': errors.name }"
          :disabled="isSubmitting"
        />
        <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
      </div>

      <div class="form-group">
        <label for="icon">Icono</label>
        <p class="field-hint">Selecciona un icono para la categoría</p>
        <div class="icon-grid">
          <button
            v-for="icon in iconOptions"
            :key="icon.value"
            type="button"
            class="icon-option"
            :class="{ selected: formData.icon === icon.value }"
            :title="icon.label"
            :disabled="isSubmitting"
            @click="selectIcon(icon.value)"
          >
            {{ icon.value }}
          </button>
        </div>
        <span v-if="errors.icon" class="error-message">{{ errors.icon }}</span>
      </div>

      <div class="form-actions">
        <button
          type="button"
          class="btn-cancel"
          :disabled="isSubmitting"
          @click="handleCancel"
        >
          Cancelar
        </button>
        <button
          type="submit"
          class="btn-submit"
          :disabled="isSubmitting"
        >
          <span v-if="isSubmitting" class="spinner-small"></span>
          {{ isEditing ? 'Actualizar' : 'Crear' }}
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.category-form {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-header {
  margin-bottom: 24px;
}

.form-header h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.form-header p {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}

.required {
  color: #dc2626;
}

.field-hint {
  font-size: 12px;
  color: #6b7280;
  margin: 0 0 8px 0;
}

.form-group input {
  width: 100%;
  padding: 10px 14px;
  font-size: 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: #fff;
  color: #1f2937;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  box-sizing: border-box;
}

.form-group input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-group input.has-error {
  border-color: #dc2626;
}

.form-group input:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
}

.form-group input::placeholder {
  color: #9ca3af;
}

.error-message {
  display: block;
  font-size: 12px;
  color: #dc2626;
  margin-top: 4px;
}

.icon-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 8px;
}

.icon-option {
  width: 48px;
  height: 48px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  font-size: 24px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.icon-option:hover {
  border-color: #4f46e5;
  background: #f5f3ff;
}

.icon-option.selected {
  border-color: #4f46e5;
  background: #e0e7ff;
}

.icon-option:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.btn-cancel,
.btn-submit {
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

.btn-cancel:hover:not(:disabled) {
  background: #f3f4f6;
}

.btn-cancel:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-submit {
  background: #4f46e5;
  border: none;
  color: #fff;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-submit:hover:not(:disabled) {
  background: #4338ca;
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>