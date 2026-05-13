<script setup lang="ts">
import { ref, computed } from 'vue'
import type { MetaFieldDefinition, MetaFieldType } from '@/types'
import { metaFieldTypes, inferFieldType } from '@/types/metaFieldBuilder'

const props = withDefaults(defineProps<{
  fields?: MetaFieldDefinition[]
  values: Record<string, unknown>
  onChange: (values: Record<string, unknown>) => void
  onFieldsChange?: (fields: MetaFieldDefinition[]) => void
  editable?: boolean
}>(), {
  fields: () => [],
  editable: true
})

const localValues = ref<Record<string, unknown>>({ ...props.values })
const localFields = ref<MetaFieldDefinition[]>([])
const showAdd = ref(false)
const pendingField = ref<MetaFieldDefinition | null>(null)
const editingKey = ref<string | null>(null)
const editKeyValue = ref('')
const newKey = ref('')
const newType = ref<MetaFieldType>('string')
const newOptions = ref('')

const availableTypes = computed(() => metaFieldTypes.getTypes())

const initFields = () => {
  if (props.fields.length > 0) {
    localFields.value = [...props.fields]
  } else if (Object.keys(props.values).length > 0) {
    localFields.value = Object.keys(props.values).map(key => ({
      key,
      type: inferFieldType(props.values[key])
    }))
  }
}

initFields()

const emitFieldsChange = () => {
  if (props.onFieldsChange) {
    props.onFieldsChange([...localFields.value])
  }
}

const handleValueChange = (key: string, value: unknown) => {
  localValues.value = { ...localValues.value, [key]: value }
  props.onChange({ ...localValues.value })
}

const handleRenameField = (oldKey: string, newFieldKey: string) => {
  if (!newFieldKey.trim() || newFieldKey === oldKey) {
    editingKey.value = null
    return
  }

  const value = localValues.value[oldKey]
  const newFields = localFields.value.map(f =>
    f.key === oldKey ? { ...f, key: newFieldKey.trim() } : f
  )

  const newValues: Record<string, unknown> = {}
  for (const [k, v] of Object.entries(localValues.value)) {
    if (k === oldKey) {
      newValues[newFieldKey.trim()] = v
    } else {
      newValues[k] = v
    }
  }

  localFields.value = newFields
  localValues.value = newValues
  props.onChange(newValues)
  emitFieldsChange()
  editingKey.value = null
}

const handleRemoveField = (key: string) => {
  const newValues = { ...localValues.value }
  delete newValues[key]
  localFields.value = localFields.value.filter(f => f.key !== key)
  localValues.value = newValues
  props.onChange(newValues)
  emitFieldsChange()
}

const handleStartAddField = () => {
  if (!newKey.value.trim()) return

  const key = newKey.value.trim().toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '')
  const options = newType.value === 'select'
    ? newOptions.value.split(',').map(s => s.trim()).filter(Boolean)
    : undefined

  pendingField.value = { key, type: newType.value, options }
}

const handleConfirmField = (initialValue: unknown) => {
  if (!pendingField.value) return

  const def: MetaFieldDefinition = { ...pendingField.value }
  const typeDef = metaFieldTypes.get(pendingField.value.type)
  const normalizedValue = typeDef ? typeDef.normalize(initialValue) : initialValue

  localFields.value = [...localFields.value, def]
  localValues.value = { ...localValues.value, [pendingField.value.key]: normalizedValue }
  props.onChange({ ...localValues.value })
  emitFieldsChange()

  newKey.value = ''
  newType.value = 'string'
  newOptions.value = ''
  pendingField.value = null
  showAdd.value = false
}

const renderTypeIcon = (type: MetaFieldType) => {
  const def = metaFieldTypes.get(type)
  return def?.icon || '?'
}

const renderInput = (def: MetaFieldDefinition) => {
  const value = localValues.value[def.key]

  switch (def.type) {
    case 'string':
    case 'email':
    case 'url':
    case 'phone':
      return {
        type: def.type === 'email' ? 'email' : def.type === 'url' ? 'url' : def.type === 'phone' ? 'tel' : 'text',
        value: String(value ?? ''),
        placeholder: def.type === 'email' ? 'correo@ejemplo.com' : def.type === 'url' ? 'https://...' : def.type === 'phone' ? '+54 11 1234-5678' : ''
      }
    case 'number':
      return { type: 'number', value: value === undefined ? '' : Number(value), placeholder: '0' }
    case 'decimal':
      return { type: 'number', step: '0.01', value: value === undefined ? '' : Number(value), placeholder: '0.00' }
    case 'boolean':
      return { boolValue: Boolean(value) }
    case 'date':
      return { type: 'date', value: String(value ?? ''), placeholder: '' }
    case 'select':
      return { options: def.options || [], value: String(value ?? '') }
    default:
      return { type: 'text', value: String(value ?? ''), placeholder: '' }
  }
}
</script>

<template>
  <div class="space-y-3">
    <div v-if="localFields.length > 0" class="space-y-2 max-h-64 overflow-y-auto">
      <div
        v-for="def in localFields"
        :key="def.key"
        class="group flex items-start gap-2 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors"
      >
        <div class="flex-1 space-y-1.5 min-w-0">
          <div class="flex items-center gap-2">
            <span class="px-1.5 py-0.5 bg-white dark:bg-slate-700 rounded text-indigo-600 dark:text-indigo-400 text-xs font-bold">
              {{ renderTypeIcon(def.type) }}
            </span>
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400 truncate">
              {{ def.label || def.key }}
            </span>
            <span class="text-[10px] text-slate-300 dark:text-slate-600 uppercase">
              {{ def.type }}
            </span>
            <button
              v-if="editable"
              type="button"
              @click="editingKey = def.key; editKeyValue = def.key"
              class="p-1 text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 opacity-0 group-hover:opacity-100 transition-all"
              title="Editar nombre"
            >
              ✏️
            </button>
          </div>

          <div v-if="editingKey === def.key" class="flex gap-1.5 items-center">
            <input
              type="text"
              v-model="editKeyValue"
              @keydown.enter="handleRenameField(def.key, editKeyValue)"
              @keydown.escape="editingKey = null"
              @blur="handleRenameField(def.key, editKeyValue)"
              class="flex-1 px-2 py-1.5 text-sm border border-indigo-300 dark:border-indigo-700 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-800"
              autofocus
            />
            <button
              type="button"
              @click="handleRenameField(def.key, editKeyValue)"
              class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded"
            >
              ✓
            </button>
            <button
              type="button"
              @click="editingKey = null"
              class="p-1.5 text-slate-400 hover:bg-slate-100 rounded"
            >
              ✕
            </button>
          </div>

          <template v-else>
            <template v-if="def.type === 'boolean'">
              <button
                type="button"
                @click="handleValueChange(def.key, !localValues[def.key])"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg border transition-all font-medium"
                :class="localValues[def.key] 
                  ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300' 
                  : 'bg-slate-50 dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400'"
              >
                <span class="text-lg">{{ localValues[def.key] ? '✓' : '✗' }}</span>
                <span>{{ localValues[def.key] ? 'Sí' : 'No' }}</span>
              </button>
            </template>

            <template v-else-if="def.type === 'select'">
              <select
                :value="String(localValues[def.key] ?? '')"
                @change="(e) => handleValueChange(def.key, (e.target as HTMLSelectElement).value)"
                class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500"
              >
                <option value="">Seleccionar...</option>
                <option v-for="opt in def.options" :key="opt" :value="opt">{{ opt }}</option>
              </select>
            </template>

            <template v-else>
              <input
                :type="renderInput(def).type"
                :value="renderInput(def).value"
                :step="renderInput(def).step"
                :placeholder="renderInput(def).placeholder"
                @input="(e) => {
                  const target = e.target as HTMLInputElement
                  if (def.type === 'number' || def.type === 'decimal') {
                    handleValueChange(def.key, target.value ? Number(target.value) : 0)
                  } else {
                    handleValueChange(def.key, target.value)
                  }
                }"
                class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500"
              />
            </template>
          </template>
        </div>

        <button
          v-if="editable && editingKey !== def.key"
          type="button"
          @click="handleRemoveField(def.key)"
          class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all opacity-0 group-hover:opacity-100"
          title="Eliminar campo"
        >
          🗑️
        </button>
      </div>
    </div>

    <div v-else class="text-center py-6 text-sm text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-700/50 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-600">
      Sin campos definidos
    </div>

    <div v-if="editable" class="pt-3 border-t border-slate-100 dark:border-slate-700">
      <div v-if="!showAdd">
        <button
          type="button"
          @click="showAdd = true"
          class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-all"
        >
          <span class="text-lg">+</span>
          Agregar campo
        </button>
      </div>

      <div v-else-if="!pendingField" class="p-4 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl space-y-4">
        <div class="flex items-center justify-between">
          <h4 class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">Nuevo campo</h4>
          <button
            type="button"
            @click="showAdd = false; newKey = ''; newType = 'string'; newOptions = ''"
            class="text-slate-500 hover:text-slate-700"
          >
            ✕
          </button>
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Nombre del campo</label>
            <input
              type="text"
              v-model="newKey"
              class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500"
              placeholder="mi_campo"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Tipo de dato</label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="typeDef in availableTypes"
                :key="typeDef.type"
                type="button"
                @click="newType = typeDef.type"
                class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-all text-center"
                :class="newType === typeDef.type 
                  ? 'border-indigo-500 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' 
                  : 'border-slate-200 dark:border-slate-600 hover:border-indigo-300'"
              >
                <span class="text-lg">{{ typeDef.icon }}</span>
                <span class="text-xs font-medium">{{ typeDef.label }}</span>
              </button>
            </div>
          </div>

          <div v-if="newType === 'select'">
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Opciones (separadas por coma)</label>
            <input
              type="text"
              v-model="newOptions"
              class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500"
              placeholder="Opción 1, Opción 2, Opción 3"
            />
          </div>

          <button
            type="button"
            @click="handleStartAddField"
            :disabled="!newKey.trim()"
            class="w-full py-2.5 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            Siguiente →
          </button>
        </div>
      </div>

      <div v-else class="p-4 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl space-y-4">
        <div class="flex items-center gap-2">
          <span class="text-lg">{{ metaFieldTypes.get(pendingField.type)?.icon }}</span>
          <h4 class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">
            Valor para "{{ pendingField.key }}"
          </h4>
          <span class="text-xs text-indigo-400">({{ pendingField.type }})</span>
        </div>

        <template v-if="pendingField.type === 'string' || pendingField.type === 'email' || pendingField.type === 'url' || pendingField.type === 'phone'">
          <input
            :type="pendingField.type === 'email' ? 'email' : pendingField.type === 'url' ? 'url' : pendingField.type === 'phone' ? 'tel' : 'text'"
            @change="(e) => handleConfirmField((e.target as HTMLInputElement).value)"
            class="w-full px-3 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800"
            :placeholder="pendingField.type === 'email' ? 'correo@ejemplo.com' : pendingField.type === 'url' ? 'https://...' : pendingField.type === 'phone' ? '+54 11 1234-5678' : 'Ingresa el valor'"
            autofocus
          />
        </template>

        <template v-else-if="pendingField.type === 'number'">
          <input
            type="number"
            @change="(e) => handleConfirmField(Number((e.target as HTMLInputElement).value) || 0)"
            class="w-full px-3 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800"
            placeholder="0"
            autofocus
          />
        </template>

        <template v-else-if="pendingField.type === 'decimal'">
          <input
            type="number"
            step="0.01"
            @change="(e) => handleConfirmField(parseFloat((e.target as HTMLInputElement).value) || 0.00)"
            class="w-full px-3 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800"
            placeholder="0.00"
            autofocus
          />
        </template>

        <template v-else-if="pendingField.type === 'boolean'">
          <div class="flex gap-3">
            <button
              type="button"
              @click="handleConfirmField(true)"
              class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border-2 border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 rounded-lg hover:bg-emerald-100 transition-all font-medium"
            >
              <span class="text-xl">✓</span>
              <span>Sí</span>
            </button>
            <button
              type="button"
              @click="handleConfirmField(false)"
              class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 transition-all font-medium"
            >
              <span class="text-xl">✗</span>
              <span>No</span>
            </button>
          </div>
        </template>

        <template v-else-if="pendingField.type === 'date'">
          <input
            type="date"
            @change="(e) => handleConfirmField((e.target as HTMLInputElement).value)"
            class="w-full px-3 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800"
            autofocus
          />
        </template>

        <template v-else-if="pendingField.type === 'select'">
          <select
            @change="(e) => handleConfirmField((e.target as HTMLSelectElement).value)"
            class="w-full px-3 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800"
            autofocus
          >
            <option value="">Seleccionar...</option>
            <option v-for="opt in pendingField.options" :key="opt" :value="opt">{{ opt }}</option>
          </select>
        </template>

        <button
          type="button"
          @click="pendingField = null; newKey = ''; newType = 'string'; newOptions = ''"
          class="w-full py-2 text-sm text-slate-500 hover:text-slate-700 transition-all"
        >
          ← Volver
        </button>
      </div>
    </div>
  </div>
</template>