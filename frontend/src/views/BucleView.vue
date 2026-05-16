<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick, inject } from "vue";
import { useCategoriesStore } from "@/stores/categoriesStore";
import { useEntitiesStore } from "@/stores/entitiesStore";
import { useBlocksStore } from "@/stores/blocksStore";
import { useHistoryStore } from "@/stores/historyStore";
import { useToast } from "@/composables/useToast";
import EntitySelector from "@/components/common/EntitySelector.vue";
import EntityDetailPanel from "@/components/common/EntityDetailPanel.vue";
import CRUDPanel from "@/components/common/CRUDPanel.vue";
import BlockCard from "@/components/blocks/BlockCard.vue";
import BlockDetailPanel from "@/components/blocks/BlockDetailPanel.vue";
import type { Category, Entity, Block } from "@/types";
import { idsMatch, toNum } from "@/utils/id";

const detailPanelOpen = inject<{ value: boolean }>("detailPanelOpen");

const categoriesStore = useCategoriesStore();
const entitiesStore = useEntitiesStore();
const blocksStore = useBlocksStore();
const historyStore = useHistoryStore();
const { showToast } = useToast();

const selectedCategoryId = ref<number | null>(null);
const selectedEntityId = ref<number | null>(null);
const editingCategory = ref<Category | null>(null);
const editingBlock = ref<Block | null>(null);
const selectedBlock = ref<Block | null>(null);
const isLoading = ref(true);

const selectedEntity = computed(() => {
  return entitiesStore.entities.find((e) => idsMatch(e.id, selectedEntityId.value)) || null;
});

const entityBlocks = computed(() => {
  if (!selectedEntityId.value) return [];
  return blocksStore.blocks.filter((b) => idsMatch(b.entity_id, selectedEntityId.value));
});

const workflowBlocks = computed(() => {
  return entityBlocks.value.filter((b) => b.type === "workflow" || b.type === "step");
});

onMounted(async () => {
  try {
    await Promise.all([
      categoriesStore.fetchCategories(),
      entitiesStore.fetchEntities(),
      blocksStore.fetchBlocks(),
      historyStore.fetchHistory(),
    ]);
    const firstCategory = categoriesStore.categories[0];
    if (firstCategory) {
      selectedCategoryId.value = firstCategory.id;
    }
  } catch {
    showToast("Error al cargar datos", "error");
  } finally {
    isLoading.value = false;
  }
});

watch(selectedCategoryId, (catId) => {
  nextTick(() => {
    if (catId) {
      const entitiesInCategory = entitiesStore.entities.filter((e) =>
        idsMatch(e.category_id, catId),
      );
      const firstEntity = entitiesInCategory[0];
      if (firstEntity) {
        selectedEntityId.value = toNum(firstEntity.id);
      } else {
        selectedEntityId.value = null;
      }
    } else {
      selectedEntityId.value = null;
    }
  });
});

const handleCategorySelect = (categoryId: number) => {
  selectedCategoryId.value = categoryId;
};

const handleEntitySelect = (entityId: number) => {
  selectedEntityId.value = entityId;
};

const handleCategoryEdit = (category: Category) => {
  editingCategory.value = category;
};

const handleBlockEdit = (block: Block) => {
  editingBlock.value = block;
};

const handleBlockClick = (block: Block) => {
  if (selectedBlock.value && idsMatch(selectedBlock.value.id, block.id)) {
    selectedBlock.value = null;
    detailPanelOpen && (detailPanelOpen.value = false);
  } else {
    selectedBlock.value = block;
    detailPanelOpen && (detailPanelOpen.value = true);
  }
};

const handleBlockEditFromDetail = (block: Block) => {
  editingBlock.value = block;
  selectedBlock.value = null;
  detailPanelOpen && (detailPanelOpen.value = false);
};

const handleCloseBlockDetail = () => {
  selectedBlock.value = null;
  detailPanelOpen && (detailPanelOpen.value = false);
};

const handleRefreshHistory = async () => {
  await historyStore.fetchHistory();
};

const handleMarkBlockDone = async () => {
  const block = selectedBlock.value;
  if (!block) return;

  await historyStore.createHistory({
    entity_id: block.entity_id,
    block_id: block.id,
    date: new Date().toISOString(),
    status: "done",
  });

  const hasRepeatingSchedule = block.schedule && (block.schedule.type === "interval" || block.schedule.type === "weekly");
  if (!hasRepeatingSchedule) {
    await blocksStore.updateBlock(block.id, { ...block, is_active: false } as any);
  }

  showToast("Bloque marcado como completado", "success");
  selectedBlock.value = null;
  await blocksStore.fetchBlocks();
  await historyStore.fetchHistory();
};

const handleCategoryCreated = (category: Category) => {
  categoriesStore.categories.push(category);
};

const handleCategoryUpdated = (category: Category) => {
  const index = categoriesStore.categories.findIndex((c) => c.id === category.id);
  if (index !== -1) {
    categoriesStore.categories[index] = category;
  }
};

const handleEntityCreated = async (entity: Entity) => {
  await entitiesStore.fetchEntities();
  selectedEntityId.value = entity.id;
};

const handleEntityUpdated = async (entity: Entity) => {
  await entitiesStore.fetchEntities();
  selectedEntityId.value = entity.id;
};

const handleEntityDeleted = async (entityId: number) => {
  await entitiesStore.fetchEntities();
  selectedEntityId.value = null;
};

const handleCategoryDeleted = async (categoryId: number) => {
  await categoriesStore.fetchCategories();
  await entitiesStore.fetchEntities();
  await blocksStore.fetchBlocks();
  selectedCategoryId.value = null;
};

const handleBlockCreated = async () => {
  await blocksStore.fetchBlocks();
  showToast("Bloque creado correctamente", "success");
};

const handleBlockUpdated = async (block: Block) => {
  await blocksStore.fetchBlocks();
  showToast("Bloque actualizado correctamente", "success");
};

const handleBlockDeleted = async (blockId: number) => {
  await blocksStore.fetchBlocks();
  showToast("Bloque eliminado correctamente", "success");
};

const handleEditEntity = () => {
  editingCategory.value = selectedEntity.value as unknown as Category;
};

const handleDeleteEntity = async (entity: Entity) => {
  if (confirm(`¿Eliminar "${entity.name}"?`)) {
    try {
      await entitiesStore.deleteEntity(entity.id)
      showToast('Entidad eliminada', 'success')
      selectedEntityId.value = null
    } catch {
      showToast('Error al eliminar entidad', 'error')
    }
  }
};

const handleCloseEdit = () => {
  editingCategory.value = null;
  editingBlock.value = null;
};

interface TypeGroups {
  workflow: Block[];
  task: Block[];
  reminder: Block[];
  payment: Block[];
  other: Block[];
}

const typeGroups = computed<TypeGroups>(() => {
  return {
    workflow: entityBlocks.value.filter((b) => b.type === "workflow" || b.type === "step"),
    task: entityBlocks.value.filter((b) => b.type === "task"),
    reminder: entityBlocks.value.filter((b) => b.type === "reminder"),
    payment: entityBlocks.value.filter((b) => b.type === "payment"),
    other: entityBlocks.value.filter(
      (b) => !["workflow", "step", "task", "reminder", "payment"].includes(b.type),
    ),
  };
});
</script>

<template>
  <div class="flex flex-col h-full">
    <EntitySelector
      :categories="categoriesStore.categories"
      :entities="entitiesStore.entities"
      :selected-entity-id="selectedEntityId"
      :selected-category-id="selectedCategoryId"
      @entity-select="handleEntitySelect"
      @category-select="handleCategorySelect"
      @category-edit="handleCategoryEdit"
    />

    <EntityDetailPanel
      v-if="selectedEntity"
      :entity="selectedEntity"
      :blocks="entityBlocks"
      @edit="handleEditEntity"
      @delete="handleDeleteEntity"
    />

    <div class="flex-1 flex">
      <div class="flex-1 overflow-y-auto p-4 space-y-6">
        <div v-if="isLoading" class="flex items-center justify-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <template v-else>
          <CRUDPanel
            :selected-entity="selectedEntity"
            :editing-category="editingCategory"
            :editing-block="editingBlock"
            @entity-created="handleEntityCreated"
            @entity-updated="handleEntityUpdated"
            @entity-deleted="handleEntityDeleted"
            @category-created="handleCategoryCreated"
            @category-updated="handleCategoryUpdated"
            @category-deleted="handleCategoryDeleted"
            @block-created="handleBlockCreated"
            @block-updated="handleBlockUpdated"
            @block-deleted="handleBlockDeleted"
            @close="handleCloseEdit"
          />

          <div v-if="selectedEntity && entityBlocks.length > 0" class="space-y-6">
            <div v-if="typeGroups.workflow.length > 0">
              <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">🔄</span>
                <h3 class="font-semibold text-slate-800 dark:text-white">Flujos de trabajo</h3>
                <span
                  class="px-2 py-0.5 text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full"
                >
                  {{ typeGroups.workflow.length }}
                </span>
              </div>
              <div class="space-y-3">
                <BlockCard
                  v-for="block in typeGroups.workflow"
                  :key="block.id"
                  :block="block"
                  @click="handleBlockClick(block)"
                  @edit="handleBlockEdit(block)"
                />
              </div>
            </div>

            <div v-if="typeGroups.task.length > 0">
              <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">✅</span>
                <h3 class="font-semibold text-slate-800 dark:text-white">Tareas</h3>
                <span
                  class="px-2 py-0.5 text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full"
                >
                  {{ typeGroups.task.length }}
                </span>
              </div>
              <div class="space-y-3">
                <BlockCard
                  v-for="block in typeGroups.task"
                  :key="block.id"
                  :block="block"
                  @click="handleBlockClick(block)"
                  @edit="handleBlockEdit(block)"
                />
              </div>
            </div>

            <div v-if="typeGroups.reminder.length > 0">
              <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">🔔</span>
                <h3 class="font-semibold text-slate-800 dark:text-white">Recordatorios</h3>
                <span
                  class="px-2 py-0.5 text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full"
                >
                  {{ typeGroups.reminder.length }}
                </span>
              </div>
              <div class="space-y-3">
                <BlockCard
                  v-for="block in typeGroups.reminder"
                  :key="block.id"
                  :block="block"
                  @click="handleBlockClick(block)"
                  @edit="handleBlockEdit(block)"
                />
              </div>
            </div>

            <div v-if="typeGroups.payment.length > 0">
              <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">💰</span>
                <h3 class="font-semibold text-slate-800 dark:text-white">Pagos</h3>
                <span
                  class="px-2 py-0.5 text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full"
                >
                  {{ typeGroups.payment.length }}
                </span>
              </div>
              <div class="space-y-3">
                <BlockCard
                  v-for="block in typeGroups.payment"
                  :key="block.id"
                  :block="block"
                  @click="handleBlockClick(block)"
                  @edit="handleBlockEdit(block)"
                />
              </div>
            </div>

            <div v-if="typeGroups.other.length > 0">
              <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">📋</span>
                <h3 class="font-semibold text-slate-800 dark:text-white">Otros</h3>
                <span
                  class="px-2 py-0.5 text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full"
                >
                  {{ typeGroups.other.length }}
                </span>
              </div>
              <div class="space-y-3">
                <BlockCard
                  v-for="block in typeGroups.other"
                  :key="block.id"
                  :block="block"
                  @click="handleBlockClick(block)"
                  @edit="handleBlockEdit(block)"
                />
              </div>
            </div>
          </div>

          <div
            v-else-if="selectedEntity && entityBlocks.length === 0"
            class="text-center py-12 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700"
          >
            <p class="text-slate-500 dark:text-slate-400">No hay bloques en esta entidad</p>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">
              Usa el botón "Bloque" para agregar uno
            </p>
          </div>

          <div
            v-else-if="!selectedEntity"
            class="text-center py-12 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700"
          >
            <p class="text-slate-500 dark:text-slate-400">
              Selecciona una categoría y entidad para ver los bloques
            </p>
          </div>
        </template>
      </div>
    </div>

    <BlockDetailPanel
      v-if="selectedBlock"
      class="fixed right-0 top-0 bottom-0 z-50 shadow-2xl"
      :block="selectedBlock"
      :entity="selectedEntity"
      :all-blocks="workflowBlocks"
      :history="historyStore.history"
      @close="handleCloseBlockDetail"
      @refresh-history="handleRefreshHistory"
      @mark-done="handleMarkBlockDone"
      @edit="handleBlockEditFromDetail"
    />
  </div>
</template>
