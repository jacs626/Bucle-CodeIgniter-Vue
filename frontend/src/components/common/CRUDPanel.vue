<script setup lang="ts">
import { ref, watch } from 'vue'
import type { Category, Entity, Block } from '@/types'
import { useToast } from '@/composables/useToast'
import { useCategoriesStore } from '@/stores/categoriesStore'
import { useEntitiesStore } from '@/stores/entitiesStore'
import { useBlocksStore } from '@/stores/blocksStore'

interface Props {
  selectedEntity?: Entity | null
  editingCategory?: Category | null
  editingBlock?: Block | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'entityCreated', entity: Entity): void
  (e: 'entityUpdated', entity: Entity): void
  (e: 'categoryCreated', category: Category): void
  (e: 'categoryUpdated', category: Category): void
  (e: 'blockCreated'): void
  (e: 'blockUpdated', block: Block): void
  (e: 'close'): void
}>()

const { showToast } = useToast()
const categoriesStore = useCategoriesStore()
const entitiesStore = useEntitiesStore()
const blocksStore = useBlocksStore()

const activeTab = ref<'category' | 'entity' | 'block' | null>(null)
const editingCategory = ref<Category | null>(null)
const editingEntity = ref<Entity | null>(null)
const message = ref<{ type: 'success' | 'error'; text: string } | null>(null)

watch(() => props.editingCategory, (cat) => {
  if (cat) {
    editingCategory.value = cat
    activeTab.value = 'category'
  }
}, { immediate: true })

watch(() => props.editingBlock, (block) => {
  if (block) {
    emit('blockUpdated', block)
    showToast('Bloque actualizado', 'success')
  }
}, { immediate: true })

const showMessage = (type: 'success' | 'error', text: string) => {
  message.value = { type, text }
  setTimeout(() => message.value = null, 3000)
}

const closePanel = () => {
  activeTab.value = null
  editingCategory.value = null
  editingEntity.value = null
  emit('close')
}

const handleCategorySubmit = async (data: { name: string; icon: string }) => {
  try {
    if (editingCategory.value) {
      await categoriesStore.updateCategory(editingCategory.value.id, data)
      showMessage('success', `"${data.name}" actualizada`)
      emit('categoryUpdated', { ...editingCategory.value, ...data })
    } else {
      const created = await categoriesStore.createCategory(data)
      showMessage('success', `"${created.name}" creada`)
      emit('categoryCreated', created)
    }
    closePanel()
  } catch (e) {
    showMessage('error', 'Error al guardar categoría')
  }
}

const handleEntitySubmit = async (data: { name: string; description?: string; type: string; category_id: number | null }) => {
  try {
    if (editingEntity.value) {
      const updated = await entitiesStore.updateEntity(editingEntity.value.id, data)
      showMessage('success', `"${data.name}" actualizada`)
      emit('entityUpdated', updated)
    } else {
      const created = await entitiesStore.createEntity(data)
      showMessage('success', `"${created.name}" creada`)
      emit('entityCreated', created)
    }
    closePanel()
  } catch (e) {
    showMessage('error', 'Error al guardar entidad')
  }
}

const handleBlockSubmit = async (data: { name: string; type: string; data?: Record<string, unknown>; schedule?: Record<string, unknown> }) => {
  try {
    if (props.selectedEntity) {
      await blocksStore.createBlock({
        ...data,
        entity_id: props.selectedEntity.id,
      })
      showMessage('success', 'Bloque creado')
      emit('blockCreated')
      closePanel()
    }
  } catch (e) {
    showMessage('error', 'Error al crear bloque')
  }
}
</script>

<template>
  <div class="p-2 relative">
    <button
      v-if="activeTab"
      type="button"
      @click="closePanel"
      class="absolute top-2 right-2 p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors z-10"
    >
      ✕
    </button>

    <div v-if="!activeTab" class="flex gap-2">
      <button
        type="button"
        @click="activeTab = 'entity'"
        class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-200 dark:shadow-indigo-900/20 transition-all"
      >
        ➕
        Entidad
      </button>
      <button
        type="button"
        @click="activeTab = 'category'"
        class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-200 dark:shadow-emerald-900/20 transition-all"
      >
        📁➕
        Categoría
      </button>
      <button
        type="button"
        @click="activeTab = 'block'"
        :disabled="!selectedEntity"
        class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl bg-orange-600 text-white hover:bg-orange-700 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none shadow-lg shadow-orange-200 dark:shadow-orange-900/20 transition-all"
      >
        🧱
        Bloque
      </button>
    </div>

    <div
      v-if="message"
      :class="`p-3 mb-3 rounded-xl text-sm font-medium animate-fade-in flex items-center gap-2 ${
        message.type === 'success'
          ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800'
          : 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800'
      }`"
    >
      {{ message.text }}
    </div>

    <div v-if="activeTab === 'category'" class="mt-4">
      <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">
        {{ editingCategory ? 'Editar Categoría' : 'Nueva Categoría' }}
      </h3>
      <form @submit.prevent="handleCategorySubmit" class="space-y-3">
        <input
          v-model="editingCategory.name"
          type="text"
          placeholder="Nombre de categoría"
          class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          required
        />
        <input
          v-model="editingCategory.icon"
          type="text"
          placeholder="Icono (ej: 📁)"
          class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <div class="flex gap-2">
          <button
            type="button"
            @click="closePanel"
            class="flex-1 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="flex-1 py-2 px-3 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
          >
            Guardar
          </button>
        </div>
      </form>
    </div>

    <div v-if="activeTab === 'entity'" class="mt-4">
      <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">
        {{ editingEntity ? 'Editar Entidad' : 'Nueva Entidad' }}
      </h3>
      <form @submit.prevent="handleEntitySubmit({ name: editingEntity?.name || '', description: editingEntity?.description || '', type: editingEntity?.type || 'project', category_id: editingEntity?.category_id || null })" class="space-y-3">
        <input
          v-model="editingEntity.name"
          type="text"
          placeholder="Nombre de entidad"
          class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          required
        />
        <textarea
          v-model="editingEntity.description"
          placeholder="Descripción"
          rows="2"
          class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <select
          v-model="editingEntity.category_id"
          class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option :value="null">Sin categoría</option>
          <option v-for="cat in categoriesStore.categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <div class="flex gap-2">
          <button
            type="button"
            @click="closePanel"
            class="flex-1 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="flex-1 py-2 px-3 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
          >
            Guardar
          </button>
        </div>
      </form>
    </div>

    <div v-if="activeTab === 'block' && selectedEntity" class="mt-4">
      <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Nuevo Bloque</h3>
      <form @submit.prevent="handleBlockSubmit({ name: '', type: 'task' })" class="space-y-3">
        <input
          type="text"
          placeholder="Nombre del bloque"
          class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          required
        />
        <select class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="task">Tarea</option>
          <option value="reminder">Recordatorio</option>
          <option value="payment">Pago</option>
          <option value="workflow">Flujo</option>
          <option value="step">Paso</option>
          <option value="note">Nota</option>
        </select>
        <div class="flex gap-2">
          <button
            type="button"
            @click="closePanel"
            class="flex-1 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="flex-1 py-2 px-3 text-xs font-medium bg-orange-600 text-white rounded-lg hover:bg-orange-700"
          >
            Crear
          </button>
        </div>
      </form>
    </div>
  </div>
</template>