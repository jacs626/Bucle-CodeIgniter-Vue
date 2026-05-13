<script setup lang="ts">
import { ref, computed } from 'vue'
import type { Category, Entity } from '@/types'
import { idsMatch } from '@/utils/id'

interface Props {
  categories: Category[]
  entities: Entity[]
  selectedEntityId?: number | null
  selectedCategoryId?: number | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'entitySelect', entityId: number): void
  (e: 'categorySelect', categoryId: number): void
  (e: 'categoryEdit', category: Category): void
}>()

const categoryOpen = ref(false)
const entityOpen = ref(false)

const filteredEntities = computed(() => {
  if (!props.selectedCategoryId) {
    return props.entities
  }
  return props.entities.filter((e) => idsMatch(e.category_id, props.selectedCategoryId))
})

const selectedCategory = computed(() => {
  return props.categories.find((c) => idsMatch(c.id, props.selectedCategoryId))
})

const activeEntity = computed(() => {
  return props.entities.find((e) => idsMatch(e.id, props.selectedEntityId))
})

const handleCategorySelect = (catId: number) => {
  categoryOpen.value = false
  emit('categorySelect', catId)
}

const handleEntitySelect = (entityId: number) => {
  entityOpen.value = false
  emit('entitySelect', entityId)
}

const handleCategoryEdit = (cat: Category) => {
  categoryOpen.value = false
  emit('categoryEdit', cat)
}
</script>

<template>
  <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 dark:from-slate-800 to-white dark:to-slate-900">
    <div class="flex items-center gap-3">
      <div class="flex-1 grid grid-cols-2 gap-3">
        <div class="relative">
          <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">
            Categoría
          </label>
          <button
            type="button"
            @click="categoryOpen = !categoryOpen; entityOpen = false"
            class="w-full flex items-center justify-between px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-sm hover:border-indigo-300 dark:hover:border-indigo-500 transition-colors"
          >
            <span class="truncate flex items-center gap-2">
              <template v-if="selectedCategory">
                <span>{{ selectedCategory.icon }}</span>
                <span class="text-slate-700 dark:text-slate-200">{{ selectedCategory.name }}</span>
              </template>
              <span v-else class="text-slate-400 dark:text-slate-500">Seleccionar</span>
            </span>
            <span v-if="categoryOpen" class="text-slate-400 dark:text-slate-500">▼</span>
            <span v-else class="text-slate-400 dark:text-slate-500">▶</span>
          </button>
          <div
            v-if="categoryOpen"
            class="absolute z-50 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg max-h-48 overflow-y-auto"
          >
            <div
              v-for="cat in categories"
              :key="cat.id"
              class="flex items-center justify-between px-3 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer"
              :class="idsMatch(selectedCategoryId, cat.id) ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'text-slate-700 dark:text-slate-200'"
              @click="handleCategorySelect(cat.id)"
            >
              <div class="flex items-center gap-2">
                <span>{{ cat.icon }}</span>
                <span>{{ cat.name }}</span>
              </div>
              <button
                type="button"
                class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                @click.stop="handleCategoryEdit(cat)"
              >
                ✏️
              </button>
            </div>
          </div>
        </div>

        <div class="relative">
          <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">
            Entidad
          </label>
          <button
            type="button"
            @click="entityOpen = !entityOpen; categoryOpen = false"
            class="w-full flex items-center justify-between px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-sm hover:border-indigo-300 dark:hover:border-indigo-500 transition-colors"
          >
            <span class="truncate flex items-center gap-2">
              <template v-if="activeEntity">
                <span class="text-slate-700 dark:text-slate-200 truncate">{{ activeEntity.name }}</span>
              </template>
              <span v-else class="text-slate-400 dark:text-slate-500">Seleccionar</span>
            </span>
            <span v-if="entityOpen" class="text-slate-400 dark:text-slate-500 flex-shrink-0">▼</span>
            <span v-else class="text-slate-400 dark:text-slate-500 flex-shrink-0">▶</span>
          </button>
          <div
            v-if="entityOpen"
            class="absolute z-50 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg max-h-48 overflow-y-auto"
          >
            <div
              v-if="filteredEntities.length === 0"
              class="px-3 py-2 text-sm text-slate-400 dark:text-slate-500"
            >
              No hay entidades en esta categoría
            </div>
            <button
              v-for="entity in filteredEntities"
              :key="entity.id"
              type="button"
              @click="handleEntitySelect(entity.id)"
              class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer"
              :class="idsMatch(selectedEntityId, entity.id) ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'text-slate-700 dark:text-slate-200'"
            >
              <span class="truncate">{{ entity.name }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="activeEntity" class="mt-3 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
      <span>{{ activeEntity.name }}</span>
    </div>
  </div>
</template>