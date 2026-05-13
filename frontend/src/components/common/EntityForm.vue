<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { Entity, Category, MetaFieldDefinition } from '@/types'
import { metaFieldTypes, inferFieldType } from '@/types/metaFieldBuilder'
import MetaFieldsEditor from './MetaFieldsEditor.vue'

const props = defineProps<{
  entity?: Entity | null
  categories: Category[]
}>()

const emit = defineEmits<{
  close: []
  submit: [data: { name: string; description: string; type: string; category_id: number | null; metadata: Record<string, unknown> }]
}>()

const categoryId = ref(props.entity?.category_id ?? null)
const name = ref(props.entity?.name ?? '')
const description = ref(props.entity?.description ?? '')
const entityType = ref(props.entity?.type ?? '')
const metaValues = ref<Record<string, unknown>>(props.entity?.metadata ?? {})
const metaFields = ref<MetaFieldDefinition[]>([])

const isEditing = computed(() => !!props.entity)

const entityTypes = [
  { value: 'trip', label: 'Viaje', icon: '✈️' },
  { value: 'medication', label: 'Medicación', icon: '💊' },
  { value: 'finance', label: 'Finanzas', icon: '💰' },
  { value: 'home', label: 'Hogar', icon: '🏠' },
  { value: 'restaurant', label: 'Restaurante', icon: '🍽️' },
  { value: 'client', label: 'Cliente', icon: '👤' },
  { value: 'project', label: 'Proyecto', icon: '📁' },
  { value: 'company', label: 'Empresa', icon: '🏢' },
]

watch(() => props.entity, (newEntity) => {
  if (newEntity) {
    categoryId.value = newEntity.category_id ?? null
    name.value = newEntity.name ?? ''
    description.value = newEntity.description ?? ''
    entityType.value = newEntity.type ?? ''
    metaValues.value = newEntity.metadata ?? {}
    initFields()
  }
}, { immediate: true })

const initFields = () => {
  if (props.fields && props.fields.length > 0) {
    metaFields.value = [...props.fields]
  } else {
    const keys = Object.keys(metaValues.value)
    if (keys.length > 0) {
      metaFields.value = keys.map(key => ({
        key,
        type: inferFieldType(metaValues.value[key]),
      }))
    }
  }
}

const handleMetaChange = (values: Record<string, unknown>) => {
  metaValues.value = values
}

const handleFieldsChange = (fields: MetaFieldDefinition[]) => {
  metaFields.value = fields
}

const handleSubmit = () => {
  if (!name.value.trim()) return
  emit('submit', {
    name: name.value.trim(),
    description: description.value.trim(),
    type: entityType.value || '',
    category_id: categoryId.value,
    metadata: metaValues.value,
  })
}
</script>

<template>
  <div class="p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
        <span class="text-xl">🏢</span>
        <span>{{ isEditing ? 'Editar Entidad' : 'Nueva Entidad' }}</span>
      </h3>
      <button
        type="button"
        @click="emit('close')"
        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
      >
        ✕
      </button>
    </div>

    <div class="space-y-4">
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">
          Categoría
        </label>
        <select
          v-model="categoryId"
          class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all"
        >
          <option :value="null">Selecciona una categoría</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.icon }} {{ cat.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">
          Nombre *
        </label>
        <input
          type="text"
          v-model="name"
          class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all"
          placeholder="Nombre de la entidad"
        />
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">
          Descripción
        </label>
        <input
          type="text"
          v-model="description"
          class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all"
          placeholder="Descripción breve (opcional)"
        />
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">
          Tipo de Entidad
        </label>
        <div class="grid grid-cols-2 gap-2">
          <button
            v-for="type in entityTypes"
            :key="type.value"
            type="button"
            @click="entityType = type.value"
            class="flex items-center gap-2 px-3 py-2.5 rounded-xl border text-sm font-medium transition-all"
            :class="entityType === type.value
              ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
              : 'border-slate-200 dark:border-slate-600 hover:border-indigo-300 text-slate-600 dark:text-slate-400'"
          >
            <span>{{ type.icon }}</span>
            <span>{{ type.label }}</span>
          </button>
        </div>
      </div>

      <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-3 uppercase tracking-wide">
          Campos Personalizados (Metadata)
        </label>
        <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
          <MetaFieldsEditor
            :fields="metaFields"
            :values="metaValues"
            :editable="true"
            @change="handleMetaChange"
            @fields-change="handleFieldsChange"
          />
        </div>
      </div>
    </div>

    <div class="flex gap-3 mt-6">
      <button
        type="button"
        @click="handleSubmit"
        :disabled="!name.trim()"
        class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 text-white rounded-xl font-semibold text-sm hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
      >
        <span>{{ isEditing ? '✓ Guardar Cambios' : '+ Crear Entidad' }}</span>
      </button>
      <button
        type="button"
        @click="emit('close')"
        class="px-4 py-3 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors font-medium"
      >
        Cancelar
      </button>
    </div>
  </div>
</template>