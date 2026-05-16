<script setup lang="ts">
import { ref, watch, computed } from "vue";
import type { Category, Entity, Block, MetaFieldDefinition } from "@/types";
import { useToast } from "@/composables/useToast";
import { useCategoriesStore } from "@/stores/categoriesStore";
import { useEntitiesStore } from "@/stores/entitiesStore";
import { useBlocksStore } from "@/stores/blocksStore";
import { useSchedule } from "@/composables/useSchedule";
import MetaFieldsEditor from "./MetaFieldsEditor.vue";

interface Props {
  selectedEntity?: Entity | null;
  editingCategory?: Category | null;
  editingBlock?: Block | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: "entityCreated", entity: Entity): void;
  (e: "entityUpdated", entity: Entity): void;
  (e: "entityDeleted", entityId: number): void;
  (e: "categoryCreated", category: Category): void;
  (e: "categoryUpdated", category: Category): void;
  (e: "categoryDeleted", categoryId: number): void;
  (e: "blockCreated"): void;
  (e: "blockUpdated", block: Block): void;
  (e: "blockDeleted", blockId: number): void;
  (e: "close"): void;
}>();

const { showToast } = useToast();
const categoriesStore = useCategoriesStore();
const entitiesStore = useEntitiesStore();
const blocksStore = useBlocksStore();

const activeTab = ref<"category" | "entity" | "block" | null>(null);
const editingCategoryLocal = ref<Category | null>(null);
const editingEntity = ref<Entity | null>(null);
const message = ref<{ type: "success" | "error"; text: string } | null>(null);
const categoryFormData = ref({ name: "", icon: "" });

const entityFormData = ref({
  name: "",
  description: "",
  type: "project",
  category_id: null as number | null,
  metadata: {} as Record<string, unknown>,
});
const entityMetaFields = ref<MetaFieldDefinition[]>([]);

const blockFormData = ref({
  name: "",
  type: "task",
  data: {} as Record<string, unknown>,
  schedule: null as any,
});
const blockMetaFields = ref<MetaFieldDefinition[]>([]);

const workflowSteps = ref<{ title: string; description: string }[]>([
  { title: "", description: "" },
]);

const {
  scheduleExpanded,
  scheduleType,
  scheduleDate,
  scheduleTime,
  scheduleIntervalHours,
  scheduleStartDate,
  scheduleStartTime,
  scheduleDays,
  buildSchedule,
  loadSchedule,
  toggleDay,
} = useSchedule();

watch(
  () => props.editingCategory,
  (cat) => {
    if (cat) {
      editingCategoryLocal.value = cat;
      categoryFormData.value = { name: cat.name, icon: cat.icon ?? "" };
      activeTab.value = "category";
    }
  },
  { immediate: true },
);

watch(
  () => props.editingBlock,
  (block) => {
    if (block) {
      blockFormData.value = {
        name: block.name,
        type: block.type,
        data: (block.data as Record<string, unknown>) || {},
        schedule: block.schedule,
      };
      loadSchedule(block.schedule ?? undefined);
      activeTab.value = "block";
    }
  },
  { immediate: true },
);

watch(
  () => props.selectedEntity,
  (entity) => {
    if (entity && !editingEntity.value) {
      editingEntity.value = entity;
      entityFormData.value = {
        name: entity.name,
        description: entity.description || "",
        type: entity.type || "project",
        category_id: entity.category_id,
        metadata: entity.metadata || {},
      };
      initEntityMetaFields();
    }
  },
  { immediate: true },
);

const initEntityMetaFields = () => {
  const values = entityFormData.value.metadata;
  const keys = Object.keys(values);
  if (keys.length > 0) {
    entityMetaFields.value = keys.map((key) => {
      const value = values[key];
      let type: MetaFieldDefinition["type"] = "string";
      if (value === true || value === false) type = "boolean";
      else if (typeof value === "number") type = "number";
      else if (typeof value === "string" && /^\d{4}-\d{2}-\d{2}$/.test(value)) type = "date";
      return { key, type };
    });
  }
};

const showMessage = (type: "success" | "error", text: string) => {
  message.value = { type, text };
  setTimeout(() => (message.value = null), 3000);
};

const closePanel = () => {
  activeTab.value = null;
  editingCategoryLocal.value = null;
  editingEntity.value = null;
  emit("close");
};

const handleCategorySubmit = async () => {
  try {
    const data = categoryFormData.value;
    if (editingCategoryLocal.value) {
      await categoriesStore.updateCategory(editingCategoryLocal.value.id, data);
      showMessage("success", `"${data.name}" actualizada`);
      emit("categoryUpdated", { ...editingCategoryLocal.value, ...data });
    } else {
      await categoriesStore.createCategory(data);
      showMessage("success", `"${data.name}" creada`);
    }
    closePanel();
  } catch {
    showMessage("error", "Error al guardar categoría");
  }
};

const handleEntitySubmit = async () => {
  try {
    const data = entityFormData.value;
    if (editingEntity.value) {
      const updated = await entitiesStore.updateEntity(editingEntity.value.id, data);
      showMessage("success", `"${data.name}" actualizada`);
      emit("entityUpdated", updated);
    } else {
      const created = await entitiesStore.createEntity(data);
      showMessage("success", `"${created?.name}" creada`);
      emit("entityCreated", created!);
    }
    closePanel();
  } catch {
    showMessage("error", "Error al guardar entidad");
  }
};

const handleBlockSubmit = async () => {
  try {
    if (!props.selectedEntity && !props.editingBlock) return;

    const schedule = buildSchedule();
    const blockData = {
      name: blockFormData.value.name,
      type: blockFormData.value.type,
      data:
        blockFormData.value.type === "workflow"
          ? { steps: workflowSteps.value }
          : blockFormData.value.data,
      schedule,
    };

    if (props.editingBlock) {
      await blocksStore.updateBlock(props.editingBlock.id, blockData);
      showMessage("success", "Bloque actualizado");
      emit("blockUpdated", props.editingBlock);
    } else {
      await blocksStore.createBlock({
        ...blockData,
        entity_id: props.selectedEntity!.id,
      });
      showMessage("success", "Bloque creado");
      emit("blockCreated");
    }
    closePanel();
  } catch {
    showMessage("error", "Error al guardar bloque");
  }
};

const handleCategoryDelete = async () => {
  if (!editingCategoryLocal.value) return;
  if (!confirm(`¿Eliminar "${editingCategoryLocal.value.name}"?`)) return;
  try {
    await categoriesStore.deleteCategory(editingCategoryLocal.value.id);
    showMessage("success", "Categoría eliminada");
    emit("categoryDeleted", editingCategoryLocal.value.id);
    closePanel();
  } catch {
    showMessage("error", "Error al eliminar categoría");
  }
};

const handleEntityDelete = async () => {
  if (!editingEntity.value) return;
  if (!confirm(`¿Eliminar "${editingEntity.value.name}"?`)) return;
  try {
    await entitiesStore.deleteEntity(editingEntity.value.id);
    showMessage("success", "Entidad eliminada");
    emit("entityDeleted", editingEntity.value.id);
    closePanel();
  } catch {
    showMessage("error", "Error al eliminar entidad");
  }
};

const handleBlockDelete = async () => {
  if (!props.editingBlock) return;
  if (!confirm(`¿Eliminar "${props.editingBlock.name}"?`)) return;
  try {
    await blocksStore.deleteBlock(props.editingBlock.id);
    showMessage("success", "Bloque eliminado");
    emit("blockDeleted", props.editingBlock.id);
    closePanel();
  } catch {
    showMessage("error", "Error al eliminar bloque");
  }
};

const addWorkflowStep = () => {
  workflowSteps.value = [...workflowSteps.value, { title: "", description: "" }];
};
const removeWorkflowStep = (index: number) => {
  workflowSteps.value = workflowSteps.value.filter((_, i) => i !== index);
};
const updateWorkflowStep = (index: number, field: "title" | "description", value: string) => {
  const updated = [...workflowSteps.value];
  updated[index] = { ...updated[index], [field]: value };
  workflowSteps.value = updated;
};

const handleEntityMetaChange = (values: Record<string, unknown>) => {
  entityFormData.value.metadata = values;
};

const handleBlockMetaChange = (values: Record<string, unknown>) => {
  blockFormData.value.data = values;
};

const blockTypes = [
  { value: "task", label: "Tarea", icon: "✓" },
  { value: "reminder", label: "Recordatorio", icon: "🔔" },
  { value: "payment", label: "Pago", icon: "💰" },
  { value: "workflow", label: "Flujo", icon: "📋" },
  { value: "note", label: "Nota", icon: "📝" },
];
</script>

<template>
  <div
    class="p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl max-h-[85vh] overflow-y-auto"
  >
    <button
      v-if="activeTab"
      type="button"
      @click="closePanel"
      class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors z-10"
    >
      ✕
    </button>

    <div v-if="!activeTab" class="flex gap-2">
      <button
        type="button"
        @click="
          activeTab = 'entity';
          editingEntity = null;
          entityFormData = {
            name: '',
            description: '',
            type: 'project',
            category_id: null,
            metadata: {},
          };
          entityMetaFields = [];
        "
        class="flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30 transition-all"
      >
        <span>➕</span>
        <span>Nueva Entidad</span>
      </button>
      <button
        type="button"
        @click="activeTab = 'category'"
        class="flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-200 dark:shadow-emerald-900/30 transition-all"
      >
        <span>📁</span>
        <span>Nueva Categoría</span>
      </button>
      <button
        type="button"
        @click="activeTab = 'block'"
        :disabled="!selectedEntity"
        class="flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-xl bg-orange-600 text-white hover:bg-orange-700 disabled:opacity-40 disabled:cursor-not-allowed shadow-lg shadow-orange-200 dark:shadow-orange-900/30 transition-all"
      >
        <span>🧱</span>
        <span>Nuevo Bloque</span>
      </button>
    </div>

    <div
      v-if="message"
      :class="`p-3 mb-4 rounded-xl text-sm font-medium animate-fade-in flex items-center gap-2 ${
        message.type === 'success'
          ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400'
          : 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-400'
      }`"
    >
      <span v-if="message.type === 'success'">✓</span>
      {{ message.text }}
    </div>

    <div v-if="activeTab === 'category'" class="mt-2 space-y-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-200">
          {{ editingCategoryLocal ? "Editar Categoría" : "Nueva Categoría" }}
        </h3>
        <button type="button" @click="closePanel" class="text-slate-400 hover:text-slate-600">
          ← Volver
        </button>
      </div>
      <form @submit.prevent="handleCategorySubmit" class="space-y-4">
        <div>
          <label
            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase"
            >Nombre</label
          >
          <input
            v-model="categoryFormData.name"
            type="text"
            placeholder="Nombre de categoría"
            class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500"
            required
          />
        </div>
        <div>
          <label
            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase"
            >Icono</label
          >
          <input
            v-model="categoryFormData.icon"
            type="text"
            placeholder="📁"
            class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500"
          />
        </div>
        <div class="flex gap-3 pt-2">
          <button
            v-if="editingCategoryLocal"
            type="button"
            @click="handleCategoryDelete"
            class="py-2.5 px-4 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl"
          >
            Eliminar
          </button>
          <button
            type="button"
            @click="closePanel"
            class="flex-1 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 rounded-xl"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="flex-1 py-2.5 text-sm font-bold bg-indigo-600 text-white rounded-xl hover:bg-indigo-700"
          >
            {{ editingCategoryLocal ? "Guardar" : "Crear" }}
          </button>
        </div>
      </form>
    </div>

    <div v-if="activeTab === 'entity'" class="mt-2 space-y-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-200">
          {{ editingEntity ? "Editar Entidad" : "Nueva Entidad" }}
        </h3>
        <button type="button" @click="closePanel" class="text-slate-400 hover:text-slate-600">
          ← Volver
        </button>
      </div>
      <form @submit.prevent="handleEntitySubmit" class="space-y-4">
        <div>
          <label
            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase"
            >Categoría</label
          >
          <select
            v-model="entityFormData.category_id"
            class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500"
          >
            <option :value="null">Sin categoría</option>
            <option v-for="cat in categoriesStore.categories" :key="cat.id" :value="cat.id">
              {{ cat.icon }} {{ cat.name }}
            </option>
          </select>
        </div>
        <div>
          <label
            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase"
            >Nombre *</label
          >
          <input
            v-model="entityFormData.name"
            type="text"
            placeholder="Nombre de entidad"
            class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500"
            required
          />
        </div>
        <div>
          <label
            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase"
            >Descripción</label
          >
          <textarea
            v-model="entityFormData.description"
            placeholder="Descripción breve"
            rows="2"
            class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500"
          ></textarea>
        </div>
        <div>
          <label
            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase"
            >Tipo</label
          >
          <div class="grid grid-cols-2 gap-2">
            <button
              v-for="type in [
                { value: 'trip', label: 'Viaje', icon: '✈️' },
                { value: 'medication', label: 'Medicación', icon: '💊' },
                { value: 'finance', label: 'Finanzas', icon: '💰' },
                { value: 'home', label: 'Hogar', icon: '🏠' },
                { value: 'restaurant', label: 'Restaurante', icon: '🍽️' },
                { value: 'client', label: 'Cliente', icon: '👤' },
              ]"
              :key="type.value"
              type="button"
              @click="entityFormData.type = type.value"
              class="flex items-center gap-2 px-3 py-2 rounded-xl border text-sm font-medium transition-all"
              :class="
                entityFormData.type === type.value
                  ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30'
                  : 'border-slate-200 dark:border-slate-600'
              "
            >
              <span>{{ type.icon }}</span>
              <span>{{ type.label }}</span>
            </button>
          </div>
        </div>
        <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
          <label
            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2 uppercase"
            >Campos Personalizados</label
          >
          <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl max-h-48 overflow-y-auto">
            <MetaFieldsEditor
              :fields="entityMetaFields"
              :values="entityFormData.metadata"
              :editable="true"
              @change="handleEntityMetaChange"
              @fields-change="(f) => (entityMetaFields = f)"
            />
          </div>
        </div>
        <div class="flex gap-3 pt-2">
          <button
            v-if="editingEntity"
            type="button"
            @click="handleEntityDelete"
            class="py-2.5 px-4 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl"
          >
            Eliminar
          </button>
          <button
            type="button"
            @click="closePanel"
            class="flex-1 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 rounded-xl"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="flex-1 py-2.5 text-sm font-bold bg-indigo-600 text-white rounded-xl hover:bg-indigo-700"
          >
            {{ editingEntity ? "Guardar" : "Crear" }}
          </button>
        </div>
      </form>
    </div>

    <div v-if="activeTab === 'block' && selectedEntity" class="mt-2 space-y-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-200">Nuevo Bloque</h3>
        <button type="button" @click="closePanel" class="text-slate-400 hover:text-slate-600">
          ← Volver
        </button>
      </div>
      <form @submit.prevent="handleBlockSubmit" class="space-y-4">
        <div>
          <label
            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase"
            >Tipo</label
          >
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="type in blockTypes"
              :key="type.value"
              type="button"
              @click="blockFormData.type = type.value"
              class="flex flex-col items-center gap-1 p-3 rounded-xl border text-sm font-medium transition-all"
              :class="
                blockFormData.type === type.value
                  ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/30'
                  : 'border-slate-200 dark:border-slate-600'
              "
            >
              <span class="text-xl">{{ type.icon }}</span>
              <span>{{ type.label }}</span>
            </button>
          </div>
        </div>

        <div>
          <label
            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase"
            >Nombre</label
          >
          <input
            v-model="blockFormData.name"
            type="text"
            placeholder="Nombre del bloque"
            class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 focus:ring-2 focus:ring-orange-500"
            required
          />
        </div>

        <div
          v-if="blockFormData.type === 'workflow'"
          class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-bold text-purple-700 dark:text-purple-300"
              >Pasos del Flujo</span
            >
            <button
              type="button"
              @click="addWorkflowStep"
              class="px-3 py-1 bg-purple-600 text-white rounded-lg text-xs font-medium"
            >
              + Agregar paso
            </button>
          </div>
          <div
            v-for="(step, index) in workflowSteps"
            :key="index"
            class="mb-3 p-3 bg-white dark:bg-slate-800 rounded-xl"
          >
            <div class="flex items-center gap-2 mb-2">
              <span
                class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center"
                >{{ index + 1 }}</span
              >
              <input
                type="text"
                :value="step.title"
                @input="
                  (e) => updateWorkflowStep(index, 'title', (e.target as HTMLInputElement).value)
                "
                placeholder="Título del paso"
                class="flex-1 px-3 py-1.5 text-sm border rounded-lg bg-white dark:bg-slate-700"
              />
              <button
                v-if="workflowSteps.length > 1"
                type="button"
                @click="removeWorkflowStep(index)"
                class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg"
              >
                🗑️
              </button>
            </div>
            <textarea
              :value="step.description"
              @input="
                (e) =>
                  updateWorkflowStep(index, 'description', (e.target as HTMLTextAreaElement).value)
              "
              placeholder="Descripción (opcional)"
              rows="2"
              class="w-full px-3 py-1.5 text-sm border rounded-lg bg-white dark:bg-slate-700"
            ></textarea>
          </div>
        </div>

        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
          <button
            type="button"
            @click="scheduleExpanded = !scheduleExpanded"
            class="flex items-center gap-2 text-sm font-semibold"
            :class="scheduleExpanded ? 'text-emerald-600' : 'text-amber-600'"
          >
            <span>{{ scheduleExpanded ? "✓" : "+" }}</span>
            <span>Programación</span>
            <span
              v-if="scheduleExpanded"
              class="px-2 py-0.5 bg-emerald-500 text-white text-xs rounded-full"
              >ACTIVA</span
            >
          </button>

          <div v-if="scheduleExpanded" class="mt-3 space-y-3">
            <select
              v-model="scheduleType"
              class="w-full px-3 py-2 text-sm border rounded-xl bg-white dark:bg-slate-800"
            >
              <option value="fixed">Fecha específica</option>
              <option value="interval">Cada X horas</option>
              <option value="weekly">Días de la semana</option>
            </select>
            <div v-if="scheduleType === 'fixed'" class="grid grid-cols-2 gap-2">
              <input
                type="date"
                v-model="scheduleDate"
                class="px-3 py-2 text-sm border rounded-xl bg-white dark:bg-slate-800"
              />
              <input
                type="time"
                v-model="scheduleTime"
                class="px-3 py-2 text-sm border rounded-xl bg-white dark:bg-slate-800"
              />
            </div>
            <div v-if="scheduleType === 'interval'" class="space-y-2">
              <input
                type="number"
                v-model="scheduleIntervalHours"
                placeholder="Horas entre ejecuciones"
                class="w-full px-3 py-2 text-sm border rounded-xl bg-white dark:bg-slate-800"
              />
              <div class="grid grid-cols-2 gap-2">
                <input
                  type="date"
                  v-model="scheduleStartDate"
                  class="px-3 py-2 text-sm border rounded-xl bg-white dark:bg-slate-800"
                />
                <input
                  type="time"
                  v-model="scheduleStartTime"
                  class="px-3 py-2 text-sm border rounded-xl bg-white dark:bg-slate-800"
                />
              </div>
            </div>
            <div v-if="blockScheduleType === 'weekly'" class="space-y-2">
              <div class="flex flex-wrap gap-1">
                <button
                  v-for="(day, i) in ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']"
                  :key="day"
                  type="button"
                  @click="
                    toggleDay(
                      [
                        'monday',
                        'tuesday',
                        'wednesday',
                        'thursday',
                        'friday',
                        'saturday',
                        'sunday',
                      ][i],
                    )
                  "
                  class="px-3 py-1.5 text-xs rounded-lg font-medium transition-all"
                  :class="
                    scheduleDays.includes(
                      [
                        'monday',
                        'tuesday',
                        'wednesday',
                        'thursday',
                        'friday',
                        'saturday',
                        'sunday',
                      ][i],
                    )
                      ? 'bg-indigo-600 text-white'
                      : 'bg-white dark:bg-slate-800 border'
                  "
                >
                  {{ day }}
                </button>
              </div>
              <input
                type="time"
                v-model="scheduleTime"
                class="w-full px-3 py-2 text-sm border rounded-xl bg-white dark:bg-slate-800"
              />
            </div>
          </div>
        </div>

        <div
          v-if="blockFormData.type !== 'workflow'"
          class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl"
        >
          <label
            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2 uppercase"
            >Datos Extra</label
          >
          <MetaFieldsEditor
            :fields="blockMetaFields"
            :values="blockFormData.data"
            :editable="true"
            @change="handleBlockMetaChange"
            @fields-change="(f) => (blockMetaFields = f)"
          />
        </div>

        <div class="flex gap-3 pt-2">
          <button
            v-if="editingBlock"
            type="button"
            @click="handleBlockDelete"
            class="py-2.5 px-4 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl"
          >
            Eliminar
          </button>
          <button
            type="button"
            @click="closePanel"
            class="flex-1 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 rounded-xl"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="flex-1 py-2.5 text-sm font-bold bg-orange-600 text-white rounded-xl hover:bg-orange-700"
          >
            {{ editingBlock ? "Guardar" : "Crear" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
