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
import { getTimeUntilNextExecution, formatTimeRemaining } from "@/types";

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

const isRepeatingBlock = (block: Block) => {
  return block.schedule && (block.schedule.type === "interval" || block.schedule.type === "weekly");
};

const activeCyclicBlocks = computed(() => {
  return entityBlocks.value
    .filter((b) => b.is_active && isRepeatingBlock(b))
    .sort((a, b) => {
      const aTime = getTimeUntilNextExecution(a.schedule!);
      const bTime = getTimeUntilNextExecution(b.schedule!);
      if (!aTime?.nextExecution) return 1;
      if (!bTime?.nextExecution) return -1;
      return aTime.nextExecution.getTime() - bTime.nextExecution.getTime();
    });
});

const activeOtherBlocks = computed(() => {
  return entityBlocks.value
    .filter((b) => b.is_active && !isRepeatingBlock(b))
    .sort((a, b) => {
      const aTime = getTimeUntilNextExecution(a.schedule!);
      const bTime = getTimeUntilNextExecution(b.schedule!);
      if (!aTime?.nextExecution) return 1;
      if (!bTime?.nextExecution) return -1;
      return aTime.nextExecution.getTime() - bTime.nextExecution.getTime();
    });
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

          <div v-if="selectedEntity && (activeCyclicBlocks.length > 0 || activeOtherBlocks.length > 0)" class="space-y-6">
            <div v-if="activeCyclicBlocks.length > 0">
              <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">🔄</span>
                <h3 class="font-semibold text-slate-800 dark:text-white">Tus servicios en ciclo</h3>
              </div>
              <div class="flex gap-4 overflow-x-auto pb-2 snap-x">
                <div
                  v-for="block in activeCyclicBlocks"
                  :key="block.id"
                  class="flex-shrink-0 w-40 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-xl p-3 border border-indigo-100 dark:border-indigo-800 cursor-pointer hover:shadow-md transition-shadow snap-start"
                  @click="handleBlockClick(block)"
                >
                  <div class="text-sm font-semibold text-indigo-800 dark:text-indigo-300 truncate">
                    {{ block.name }}
                  </div>
                  <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">
                    <span v-if="block.schedule?.type === 'interval'">Cada {{ block.schedule.intervalHours }}h</span>
                    <span v-else-if="block.schedule?.type === 'weekly'">
                      {{ block.schedule.daysOfWeek?.map(d => d.slice(0, 3)).join(', ') }}
                    </span>
                  </div>
                  <div class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                    <span v-if="getTimeUntilNextExecution(block.schedule!)?.nextExecution">
                      Próximo en {{ formatTimeRemaining(
                        getTimeUntilNextExecution(block.schedule!).days,
                        getTimeUntilNextExecution(block.schedule!).hours,
                        getTimeUntilNextExecution(block.schedule!).minutes
                      ) }}
                    </span>
                  </div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ getTimeUntilNextExecution(block.schedule!)?.nextExecution?.toLocaleDateString('es-ES') }}
                  </div>
                </div>
              </div>
            </div>

            <div v-if="activeOtherBlocks.length > 0">
              <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">🔔</span>
                <h3 class="font-semibold text-slate-800 dark:text-white">Otros recordatorios</h3>
              </div>
              <div class="space-y-2">
                <div
                  v-for="block in activeOtherBlocks"
                  :key="block.id"
                  class="flex items-center gap-3 p-2 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                  @click="handleBlockClick(block)"
                >
                  <span class="text-lg flex-shrink-0">{{ block.type === 'task' ? '✅' : block.type === 'payment' ? '💰' : block.type === 'reminder' ? '🔔' : block.type === 'note' ? '📝' : '📋' }}</span>
                  <span class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate flex-1">{{ block.name }}</span>
                  <span class="text-xs text-slate-500 dark:text-slate-400">
                    <span v-if="block.schedule?.type === 'fixed'">Vence: {{ new Date(block.schedule.date!).toLocaleDateString('es-ES') }}</span>
                    <span v-else-if="!block.schedule">Sin fecha</span>
                  </span>
                </div>
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
