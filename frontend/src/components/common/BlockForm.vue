<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { Block } from '@/types'

export interface MetaFieldDefinition {
  key: string
  type: 'string' | 'number' | 'boolean' | 'date' | 'select'
  options?: string[]
  label?: string
}

const props = defineProps<{
  block?: Block | null
  entityId?: number
}>()

const emit = defineEmits<{
  close: []
  submit: [data: Partial<Block>]
  delete?: []
}>()

const blockType = ref(props.block?.type ?? 'task')
const name = ref(props.block?.name ?? '')
const metaValues = ref<Record<string, unknown>>({})
const metaFields = ref<MetaFieldDefinition[]>([])

const scheduleExpanded = ref(!!props.block?.schedule)
const scheduleType = ref<'fixed' | 'interval' | 'weekly'>(props.block?.schedule?.type ?? 'fixed')
const scheduleDate = ref('')
const scheduleTime = ref(props.block?.schedule?.time ?? '')
const scheduleIntervalHours = ref(props.block?.schedule?.intervalHours?.toString() ?? '')
const scheduleStartDate = ref('')
const scheduleStartTime = ref('')
const scheduleDays = ref<string[]>(props.block?.schedule?.daysOfWeek ?? [])

const workflowSteps = ref<{ title: string; description: string }[]>([
  { title: '', description: '' }
])

const isEditing = computed(() => !!props.block)
const blockTypes = [
  { value: 'task', label: 'Tarea' },
  { value: 'reminder', label: 'Recordatorio' },
  { value: 'payment', label: 'Pago' },
  { value: 'workflow', label: 'Flujo' },
]

watch(() => props.block, (newBlock) => {
  if (newBlock) {
    blockType.value = newBlock.type ?? 'task'
    name.value = newBlock.name ?? ''
    if (newBlock.data) {
      metaValues.value = { ...newBlock.data } as Record<string, unknown>
      initMetaFields()
    }
    if (newBlock.schedule) {
      scheduleExpanded.value = true
      scheduleType.value = newBlock.schedule.type ?? 'fixed'
      scheduleTime.value = newBlock.schedule.time ?? ''
      scheduleIntervalHours.value = newBlock.schedule.intervalHours?.toString() ?? ''
      scheduleDays.value = newBlock.schedule.daysOfWeek ?? []
    }
    if (newBlock.type === 'workflow' && Array.isArray(newBlock.data?.steps)) {
      workflowSteps.value = newBlock.data.steps as { title: string; description: string }[]
    }
  }
}, { immediate: true })

const initMetaFields = () => {
  const keys = Object.keys(metaValues.value)
  if (keys.length > 0) {
    metaFields.value = keys.map(key => ({
      key,
      type: inferType(metaValues.value[key]),
    }))
  }
}

const inferType = (value: unknown): MetaFieldDefinition['type'] => {
  if (value === true || value === false) return 'boolean'
  if (typeof value === 'number') return 'number'
  if (typeof value === 'string') {
    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return 'date'
  }
  return 'string'
}

const typeIcons: Record<string, string> = {
  string: 'Aa',
  number: '#',
  boolean: 'on',
  date: '[]',
  select: 'v',
}

const showAddField = ref(false)
const newFieldKey = ref('')
const newFieldType = ref<'string' | 'number' | 'boolean' | 'date' | 'select'>('string')
const newFieldOptions = ref('')
const pendingFieldValue = ref<{ key: string; type: MetaFieldDefinition['type']; options?: string[]; value: unknown } | null>(null)
const editingFieldKey = ref<string | null>(null)
const editFieldKeyValue = ref('')

const addMetaField = () => {
  if (!newFieldKey.value.trim()) return
  const key = newFieldKey.value.trim().toLowerCase().replace(/\s+/g, '_')
  pendingFieldValue.value = {
    key,
    type: newFieldType.value,
    options: newFieldType.value === 'select' ? newFieldOptions.value.split(',').map(s => s.trim()).filter(Boolean) : undefined,
    value: newFieldType.value === 'string' ? '' : newFieldType.value === 'number' ? 0 : newFieldType.value === 'boolean' ? false : '',
  }
  showAddField.value = false
}

const confirmAddField = () => {
  if (!pendingFieldValue.value) return
  metaFields.value = [...metaFields.value, { key: pendingFieldValue.value.key, type: pendingFieldValue.value.type, options: pendingFieldValue.value.options }]
  metaValues.value = { ...metaValues.value, [pendingFieldValue.value.key]: pendingFieldValue.value.value }
  pendingFieldValue.value = null
  newFieldKey.value = ''
  newFieldType.value = 'string'
  newFieldOptions.value = ''
}

const cancelAddField = () => {
  pendingFieldValue.value = null
  newFieldKey.value = ''
  newFieldType.value = 'string'
  newFieldOptions.value = ''
}

const removeMetaField = (key: string) => {
  metaFields.value = metaFields.value.filter(f => f.key !== key)
  const newValues = { ...metaValues.value }
  delete newValues[key]
  metaValues.value = newValues
}

const startEditField = (key: string) => {
  editingFieldKey.value = key
  editFieldKeyValue.value = key
}

const saveEditField = (oldKey: string) => {
  if (!editFieldKeyValue.value.trim() || editFieldKeyValue.value === oldKey) {
    editingFieldKey.value = null
    return
  }
  const newKey = editFieldKeyValue.value.trim().toLowerCase().replace(/\s+/g, '_')
  const newFields = metaFields.value.map(f => f.key === oldKey ? { ...f, key: newKey } : f)
  const newValues: Record<string, unknown> = {}
  for (const [k, v] of Object.entries(metaValues.value)) {
    if (k === oldKey) newValues[newKey] = v
    else newValues[k] = v
  }
  metaFields.value = newFields
  metaValues.value = newValues
  editingFieldKey.value = null
}

const updateMetaValue = (key: string, value: unknown) => {
  metaValues.value = { ...metaValues.value, [key]: value }
}

const addWorkflowStep = () => {
  workflowSteps.value = [...workflowSteps.value, { title: '', description: '' }]
}

const removeWorkflowStep = (index: number) => {
  workflowSteps.value = workflowSteps.value.filter((_, i) => i !== index)
}

const updateWorkflowStep = (index: number, field: 'title' | 'description', value: string) => {
  const updated = [...workflowSteps.value]
  updated[index] = { ...updated[index], [field]: value }
  workflowSteps.value = updated
}

const buildSchedule = () => {
  if (!scheduleExpanded.value) return undefined
  if (scheduleType.value === 'fixed') {
    if (!scheduleDate.value) return undefined
    return { type: 'fixed' as const, date: scheduleDate.value + (scheduleTime.value ? 'T' + scheduleTime.value : ''), time: scheduleTime.value || undefined }
  }
  if (scheduleType.value === 'interval') {
    if (!scheduleIntervalHours.value) return undefined
    const startDateValue = scheduleStartDate.value ? new Date(scheduleStartDate.value + (scheduleStartTime.value ? 'T' + scheduleStartTime.value : ':00')).toISOString() : undefined
    return { type: 'interval' as const, intervalHours: parseInt(scheduleIntervalHours.value), startDate: startDateValue, time: scheduleTime.value || undefined }
  }
  if (scheduleType.value === 'weekly') {
    if (scheduleDays.value.length === 0) return undefined
    return { type: 'weekly' as const, daysOfWeek: scheduleDays.value, time: scheduleTime.value || undefined }
  }
  return undefined
}

const toggleDay = (day: string) => {
  if (scheduleDays.value.includes(day)) {
    scheduleDays.value = scheduleDays.value.filter(d => d !== day)
  } else {
    scheduleDays.value = [...scheduleDays.value, day]
  }
}

const handleSubmit = () => {
  if (!name.value.trim()) return
  if (blockType.value === 'workflow' && workflowSteps.value.some(s => !s.title.trim())) return

  const data: Record<string, unknown> = { ...metaValues.value }
  if (blockType.value === 'workflow') {
    data.steps = workflowSteps.value
  }

  const payload: Partial<Block> = {
    name: name.value.trim(),
    type: blockType.value,
    data: Object.keys(data).length > 0 ? data : undefined,
    schedule: buildSchedule(),
    entity_id: props.entityId ?? null,
    is_active: true,
  }
  emit('submit', payload)
}

const handleDelete = () => {
  if (confirm('¿Estás seguro de eliminar este bloque?')) {
    emit('delete')
  }
}
</script>

<template>
  <div class="p-4 bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-600 rounded-2xl shadow-lg max-h-[85vh] overflow-y-auto">
    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
      <span class="text-orange-600 dark:text-orange-400">Nuevo Bloque</span>
    </h3>

    <div class="space-y-3">
      <select
        v-model="blockType"
        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-slate-900 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
      >
        <option v-for="type in blockTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
      </select>

      <input
        type="text"
        v-model="name"
        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-slate-900 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
        placeholder="Titulo *"
        autofocus
      />

      <div v-if="blockType === 'workflow'" class="p-3 bg-gradient-to-br from-purple-50 dark:from-purple-900/30 to-indigo-50 dark:to-indigo-900/30 rounded-xl border border-purple-100 dark:border-purple-900/50">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-bold text-purple-700 dark:text-purple-300">Pasos del Flujo</span>
          <button type="button" @click="addWorkflowStep" class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-bold">+ Paso</button>
        </div>
        <div v-for="(step, index) in workflowSteps" :key="index" class="mb-3 p-3 bg-white dark:bg-slate-800 rounded-xl border border-purple-100 dark:border-purple-900/50 last:mb-0">
          <div class="flex items-center gap-2 mb-2">
            <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">{{ index + 1 }}</span>
            <button v-if="workflowSteps.length > 1" type="button" @click="removeWorkflowStep(index)" class="ml-auto text-xs text-red-400 hover:text-red-600">del</button>
          </div>
          <input type="text" :value="step.title" @input="(e) => updateWorkflowStep(index, 'title', (e.target as HTMLInputElement).value)" placeholder="Titulo paso *" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-xs mb-1.5 bg-white dark:bg-slate-700" />
          <textarea :value="step.description" @input="(e) => updateWorkflowStep(index, 'description', (e.target as HTMLTextAreaElement).value)" placeholder="Descripcion (opcional)" rows="1" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-xs resize-none bg-white dark:bg-slate-700"></textarea>
        </div>
      </div>

      <button
        type="button"
        @click="scheduleExpanded = !scheduleExpanded"
        class="text-xs font-semibold flex items-center gap-1.5"
        :class="scheduleExpanded ? 'text-emerald-600 dark:text-emerald-400' : 'text-indigo-600 dark:text-indigo-400'"
      >
        <span>{{ scheduleExpanded ? '[-]' : '[+]' }}</span>
        Programacion: {{ scheduleExpanded ? 'ACTIVA' : 'No asignada' }}
      </button>

      <div v-if="scheduleExpanded" class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded-xl space-y-3">
        <select v-model="scheduleType" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-xs bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200">
          <option value="fixed">Una fecha especifica</option>
          <option value="interval">Cada X horas</option>
          <option value="weekly">Dias de la semana</option>
        </select>

        <template v-if="scheduleType === 'fixed'">
          <div class="grid grid-cols-2 gap-2">
            <input type="date" v-model="scheduleDate" class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-xs bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200" />
            <input type="time" v-model="scheduleTime" class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-xs bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200" />
          </div>
        </template>

        <template v-if="scheduleType === 'interval'">
          <div class="space-y-2">
            <input type="number" v-model="scheduleIntervalHours" placeholder="Horas entre ejecuciones" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-xs bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200" />
            <div class="grid grid-cols-2 gap-2">
              <input type="date" v-model="scheduleStartDate" class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-xs bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200" />
              <input type="time" v-model="scheduleStartTime" class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-xs bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200" />
            </div>
          </div>
        </template>

        <template v-if="scheduleType === 'weekly'">
          <div class="space-y-2">
            <div class="flex flex-wrap gap-1.5">
              <button
                v-for="(day, i) in ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom']"
                :key="day"
                type="button"
                @click="toggleDay(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'][i])"
                class="px-2.5 py-1.5 text-xs font-semibold rounded-lg transition-all"
                :class="scheduleDays.includes(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'][i]) ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300'"
              >{{ day }}</button>
            </div>
            <input type="time" v-model="scheduleTime" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-xs bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200" />
          </div>
        </template>
      </div>

      <div v-if="blockType !== 'workflow'" class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded-xl max-h-64 overflow-y-auto">
        <p class="text-xs font-medium text-gray-600 dark:text-gray-300 mb-2">Datos extra (metadata JSON):</p>

        <div v-if="metaFields.length > 0" class="space-y-2 mb-3">
          <div v-for="field in metaFields" :key="field.key" class="group p-2 bg-white dark:bg-slate-800 rounded-lg border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-1">
              <span class="p-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 text-xs rounded">{{ typeIcons[field.type] }}</span>
              <span v-if="editingFieldKey !== field.key" class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ field.key }}</span>
              <input
                v-else
                type="text"
                v-model="editFieldKeyValue"
                @blur="saveEditField(field.key)"
                @keydown.enter="saveEditField(field.key)"
                class="flex-1 px-2 py-0.5 text-xs border border-indigo-300 dark:border-indigo-600 rounded bg-white dark:bg-slate-700"
              />
              <button v-if="editingFieldKey !== field.key" type="button" @click="startEditField(field.key)" class="opacity-0 group-hover:opacity-100 text-xs text-gray-400 hover:text-indigo-600">edit</button>
              <button type="button" @click="removeMetaField(field.key)" class="opacity-0 group-hover:opacity-100 text-xs text-red-400 hover:text-red-600">del</button>
            </div>
            <input v-if="field.type === 'string'" type="text" :value="String(metaValues[field.key] ?? '')" @input="(e) => updateMetaValue(field.key, (e.target as HTMLInputElement).value)" class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700" />
            <input v-else-if="field.type === 'number'" type="number" :value="metaValues[field.key] ?? 0" @input="(e) => updateMetaValue(field.key, Number((e.target as HTMLInputElement).value) || 0)" class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700" />
            <button v-else-if="field.type === 'boolean'" type="button" @click="updateMetaValue(field.key, !metaValues[field.key])" class="px-3 py-1.5 text-xs rounded-lg border" :class="metaValues[field.key] ? 'bg-emerald-100 border-emerald-300 text-emerald-700' : 'bg-gray-100 border-gray-300 text-gray-500'">{{ metaValues[field.key] ? 'Si' : 'No' }}</button>
            <input v-else-if="field.type === 'date'" type="date" :value="String(metaValues[field.key] ?? '')" @input="(e) => updateMetaValue(field.key, (e.target as HTMLInputElement).value)" class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700" />
            <select v-else-if="field.type === 'select'" :value="String(metaValues[field.key] ?? '')" @change="(e) => updateMetaValue(field.key, (e.target as HTMLSelectElement).value)" class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700">
              <option value="">Seleccionar...</option>
              <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
            </select>
          </div>
        </div>

        <div v-if="!showAddField && !pendingFieldValue" class="pt-2 border-t border-gray-200 dark:border-gray-700">
          <button type="button" @click="showAddField = true" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium">+ Agregar campo</button>
        </div>

        <div v-else-if="showAddField" class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg mt-2 space-y-2">
          <div class="grid grid-cols-2 gap-2">
            <input type="text" v-model="newFieldKey" placeholder="Nombre del campo" class="px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700" />
            <select v-model="newFieldType" class="px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700">
              <option value="string">Texto</option>
              <option value="number">Numero</option>
              <option value="boolean">Si/No</option>
              <option value="date">Fecha</option>
              <option value="select">Seleccion</option>
            </select>
          </div>
          <input v-if="newFieldType === 'select'" type="text" v-model="newFieldOptions" placeholder="Opciones separadas por coma" class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700" />
          <div class="flex gap-2">
            <button type="button" @click="addMetaField" :disabled="!newFieldKey.trim()" class="px-3 py-1 text-xs bg-indigo-600 text-white rounded-lg disabled:opacity-50">Siguiente</button>
            <button type="button" @click="showAddField = false; newFieldKey = ''" class="px-3 py-1 text-xs text-gray-500">Cancelar</button>
          </div>
        </div>

        <div v-else-if="pendingFieldValue" class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg mt-2 space-y-2">
          <p class="text-xs text-gray-600 dark:text-gray-400">Valor inicial para "{{ pendingFieldValue.key }}"</p>
          <input v-if="pendingFieldValue.type === 'string'" type="text" @change="(e) => { pendingFieldValue!.value = (e.target as HTMLInputElement).value }" class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700" autofocus />
          <input v-else-if="pendingFieldValue.type === 'number'" type="number" @change="(e) => { pendingFieldValue!.value = Number((e.target as HTMLInputElement).value) || 0 }" class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700" autofocus />
          <div v-else-if="pendingFieldValue.type === 'boolean'" class="flex gap-2">
            <button type="button" @click="pendingFieldValue!.value = true" class="flex-1 px-3 py-1.5 text-xs bg-emerald-100 border border-emerald-300 rounded-lg">Si</button>
            <button type="button" @click="pendingFieldValue!.value = false" class="flex-1 px-3 py-1.5 text-xs bg-gray-100 border border-gray-300 rounded-lg">No</button>
          </div>
          <input v-else-if="pendingFieldValue.type === 'date'" type="date" @change="(e) => { pendingFieldValue!.value = (e.target as HTMLInputElement).value }" class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700" autofocus />
          <select v-else-if="pendingFieldValue.type === 'select'" @change="(e) => { pendingFieldValue!.value = (e.target as HTMLSelectElement).value }" class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700" autofocus>
            <option value="">Seleccionar...</option>
            <option v-for="opt in pendingFieldValue.options" :key="opt" :value="opt">{{ opt }}</option>
          </select>
          <div class="flex gap-2">
            <button type="button" @click="confirmAddField" class="px-3 py-1 text-xs bg-indigo-600 text-white rounded-lg">Agregar</button>
            <button type="button" @click="cancelAddField" class="px-3 py-1 text-xs text-gray-500">Cancelar</button>
          </div>
        </div>
      </div>

      <div class="flex gap-2 pt-2">
        <button
          type="button"
          @click="handleSubmit"
          :disabled="blockType === 'workflow' ? workflowSteps.some(s => !s.title.trim()) : !name.trim()"
          class="flex-1 py-2.5 bg-orange-600 dark:bg-orange-500 text-white rounded-xl text-sm font-bold hover:bg-orange-700 dark:hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >{{ isEditing ? 'Guardar' : 'Crear bloque' }}</button>
        <button v-if="isEditing" type="button" @click="handleDelete" class="px-4 py-2.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl text-sm font-medium hover:bg-red-200">del</button>
        <button type="button" @click="emit('close')" class="px-4 py-2.5 bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-medium hover:bg-gray-200">Cancelar</button>
      </div>
    </div>
  </div>
</template>