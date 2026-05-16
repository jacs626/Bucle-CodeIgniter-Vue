<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useBlocksStore } from "@/stores/blocksStore";
import { useEntitiesStore } from "@/stores/entitiesStore";
import { useToast } from "@/composables/useToast";
import type { Block } from "@/types";
import { getTypeIcon } from "@/utils/formatters";
import BlockForm from "@/components/common/BlockForm.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";

const blocksStore = useBlocksStore();
const entitiesStore = useEntitiesStore();
const { showToast } = useToast();

const showForm = ref(false);
const editingBlock = ref<Block | null>(null);
const filter = ref<"all" | "pending" | "done">("all");

const filteredBlocks = computed(() => {
  if (filter.value === "all") return blocksStore.blocks;
  if (filter.value === "pending") return blocksStore.blocks.filter((b) => b.status !== "done");
  return blocksStore.blocks.filter((b) => b.status === "done");
});

const handleSubmit = async (data: Partial<Block>) => {
  try {
    if (editingBlock.value) {
      await blocksStore.updateBlock(editingBlock.value.id, data as any);
      showToast("Bloque actualizado correctamente", "success");
    } else {
      await blocksStore.createBlock(data as any);
      showToast("Bloque creado correctamente", "success");
    }
    showForm.value = false;
    editingBlock.value = null;
  } catch {
    showToast("Error al guardar el bloque", "error");
  }
};

const handleDelete = async (block: Block) => {
  if (!confirm(`¿Eliminar el bloque "${block.name}"?`)) return;
  try {
    await blocksStore.deleteBlock(block.id);
    showToast("Bloque eliminado", "success");
  } catch {
    showToast("Error al eliminar el bloque", "error");
  }
};

const openCreate = () => {
  editingBlock.value = null;
  showForm.value = true;
};

const openEdit = (block: Block) => {
  editingBlock.value = block;
  showForm.value = true;
};

onMounted(async () => {
  await Promise.all([blocksStore.fetchBlocks(), entitiesStore.fetchEntities()]);
});
</script>

<template>
  <div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Bloques</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm">
          Gestiona todos tus bloques y tareas
        </p>
      </div>
      <button
        @click="openCreate"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
      >
        + Nuevo Bloque
      </button>
    </div>

    <div class="flex gap-2 mb-4">
      <button
        @click="filter = 'all'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          filter === 'all'
            ? 'bg-indigo-600 text-white'
            : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
        ]"
      >
        Todos ({{ blocksStore.blocks.length }})
      </button>
      <button
        @click="filter = 'pending'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          filter === 'pending'
            ? 'bg-amber-600 text-white'
            : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
        ]"
      >
        Pendientes ({{ blocksStore.blocks.filter((b) => b.status !== "done").length }})
      </button>
      <button
        @click="filter = 'done'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          filter === 'done'
            ? 'bg-emerald-600 text-white'
            : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
        ]"
      >
        Completados ({{ blocksStore.blocks.filter((b) => b.status === "done").length }})
      </button>
    </div>

    <div v-if="blocksStore.isLoading" class="text-center text-slate-500 py-12">
      Cargando bloques...
    </div>

    <div v-else-if="filteredBlocks.length === 0" class="text-center py-12">
      <div class="text-4xl mb-4">🧱</div>
      <p class="text-slate-500 dark:text-slate-400">
        No hay bloques
        {{ filter !== "all" ? (filter === "pending" ? "pendientes" : "completados") : "" }}
      </p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="block in filteredBlocks"
        :key="block.id"
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:shadow-md transition-shadow cursor-pointer"
        @click="openEdit(block)"
      >
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center gap-3">
            <span
              class="w-10 h-10 rounded-lg flex items-center justify-center text-lg bg-slate-100 dark:bg-slate-700"
            >
              {{ getTypeIcon(block.type) }}
            </span>
            <div>
              <h3 class="font-medium text-slate-800 dark:text-slate-200">{{ block.name }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 uppercase">{{ block.type }}</p>
            </div>
          </div>
          <button
            @click.stop="handleDelete(block)"
            class="p-2 text-slate-400 hover:text-red-500 transition-colors"
          >
            🗑️
          </button>
        </div>

        <p
          v-if="block.data?.description"
          class="text-sm text-slate-500 dark:text-slate-400 mb-3 line-clamp-2"
        >
          {{ block.data.description }}
        </p>

        <div v-if="block.schedule" class="mb-3 text-xs text-slate-500 dark:text-slate-400">
          📅
          {{
            block.schedule.type === "fixed"
              ? block.schedule.date
              : block.schedule.type === "interval"
                ? `Cada ${block.schedule.intervalHours}h`
                : "Semanal"
          }}
        </div>

        <div class="flex items-center justify-between">
          <StatusBadge :status="block.status === 'done' ? 'done' : 'pending'" size="sm" />
          <span v-if="block.order_index" class="text-xs text-slate-400"
            >#{{ block.order_index }}</span
          >
        </div>
      </div>
    </div>

    <div
      v-if="showForm"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden"
      >
        <BlockForm
          :block="editingBlock"
          :entities="entitiesStore.entities"
          @close="
            showForm = false;
            editingBlock = null;
          "
          @submit="handleSubmit"
        />
      </div>
    </div>
  </div>
</template>
