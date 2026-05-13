<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useEntitiesStore } from '@/stores/entitiesStore'
import { useCategoriesStore } from '@/stores/categoriesStore'
import { useToast } from '@/composables/useToast'
import type { Entity } from '@/types'
import EntityForm from '@/components/common/EntityForm.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'

const entitiesStore = useEntitiesStore()
const categoriesStore = useCategoriesStore()
const { showToast } = useToast()

const showForm = ref(false)
const editingEntity = ref<Entity | null>(null)
const filter = ref<'all' | 'active' | 'inactive'>('all')

const filteredEntities = computed(() => {
  if (filter.value === 'all') return entitiesStore.entities
  if (filter.value === 'active') return entitiesStore.entities.filter(e => e.is_active)
  return entitiesStore.entities.filter(e => !e.is_active)
})

const getCategoryName = (categoryId: number | null) => {
  if (!categoryId) return '-'
  const category = categoriesStore.categories.find(c => c.id === categoryId)
  return category?.name || '-'
}

const getTypeIcon = (type: string) => {
  switch (type) {
    case 'company': return '🏢'
    case 'project': return '📁'
    case 'client': return '👤'
    default: return '📋'
  }
}

const handleSubmit = async (data: any) => {
  try {
    if (editingEntity.value) {
      await entitiesStore.updateEntity(editingEntity.value.id, data)
      showToast('Entidad actualizada correctamente', 'success')
    } else {
      await entitiesStore.createEntity(data)
      showToast('Entidad creada correctamente', 'success')
    }
    showForm.value = false
    editingEntity.value = null
  } catch {
    showToast('Error al guardar la entidad', 'error')
  }
}

const handleDelete = async (entity: Entity) => {
  if (!confirm(`¿Eliminar la entidad "${entity.name}"?`)) return
  try {
    await entitiesStore.deleteEntity(entity.id)
    showToast('Entidad eliminada', 'success')
  } catch {
    showToast('Error al eliminar la entidad', 'error')
  }
}

const openCreate = () => {
  editingEntity.value = null
  showForm.value = true
}

const openEdit = (entity: Entity) => {
  editingEntity.value = entity
  showForm.value = true
}

onMounted(async () => {
  await Promise.all([
    entitiesStore.fetchEntities(),
    categoriesStore.fetchCategories(),
  ])
})
</script>

<template>
  <div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Entidades</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Gestiona tus empresas, clientes y proyectos</p>
      </div>
      <button 
        @click="openCreate"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
      >
        + Nueva Entidad
      </button>
    </div>

    <div class="flex gap-2 mb-4">
      <button
        @click="filter = 'all'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          filter === 'all' 
            ? 'bg-indigo-600 text-white' 
            : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'
        ]"
      >
        Todos ({{ entitiesStore.entities.length }})
      </button>
      <button
        @click="filter = 'active'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          filter === 'active' 
            ? 'bg-emerald-600 text-white' 
            : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'
        ]"
      >
        Activas ({{ entitiesStore.entities.filter(e => e.is_active).length }})
      </button>
      <button
        @click="filter = 'inactive'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          filter === 'inactive' 
            ? 'bg-red-600 text-white' 
            : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'
        ]"
      >
        Inactivas ({{ entitiesStore.entities.filter(e => !e.is_active).length }})
      </button>
    </div>

    <div v-if="entitiesStore.isLoading" class="text-center text-slate-500 py-12">
      Cargando entidades...
    </div>

    <div v-else-if="filteredEntities.length === 0" class="text-center py-12">
      <div class="text-4xl mb-4">🏢</div>
      <p class="text-slate-500 dark:text-slate-400">No hay entidades {{ filter !== 'all' ? filter === 'active' ? 'activas' : 'inactivas' : '' }}</p>
    </div>

    <div v-else class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <table class="w-full">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Entidad</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Categoría</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
          <tr 
            v-for="entity in filteredEntities" 
            :key="entity.id"
            class="hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer"
            @click="openEdit(entity)"
          >
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-lg flex items-center justify-center text-lg bg-slate-100 dark:bg-slate-700">
                  {{ getTypeIcon(entity.type) }}
                </span>
                <div>
                  <p class="font-medium text-slate-800 dark:text-slate-200">{{ entity.name }}</p>
                  <p v-if="entity.description" class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">
                    {{ entity.description }}
                  </p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="text-sm text-slate-600 dark:text-slate-300 capitalize">{{ entity.type || '-' }}</span>
            </td>
            <td class="px-6 py-4">
              <span class="text-sm text-slate-600 dark:text-slate-300">{{ getCategoryName(entity.category_id) }}</span>
            </td>
            <td class="px-6 py-4">
              <StatusBadge :status="entity.is_active ? 'active' : 'inactive'" size="sm" />
            </td>
            <td class="px-6 py-4 text-right">
              <button 
                @click.stop="handleDelete(entity)"
                class="p-2 text-slate-400 hover:text-red-500 transition-colors"
              >
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden">
        <EntityForm
          :entity="editingEntity"
          :categories="categoriesStore.categories"
          @close="showForm = false; editingEntity = null"
          @submit="handleSubmit"
        />
      </div>
    </div>
  </div>
</template>