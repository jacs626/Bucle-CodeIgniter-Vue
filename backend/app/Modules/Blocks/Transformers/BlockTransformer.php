<?php

namespace App\Modules\Blocks\Transformers;

use App\Modules\Blocks\Entities\Block;

class BlockTransformer
{
    public function transform(Block $block): array
    {
        return [
            'id' => $block->id,
            'entity_id' => $block->entity_id,
            'name' => $block->name,
            'type' => $block->type,
            'data' => $block->data,
            'schedule' => $block->schedule,
            'parent_block_id' => $block->parent_block_id,
            'order_index' => $block->order_index,
            'is_active' => $block->is_active,
            'created_at' => $block->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $block->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function transformCollection(array $blocks): array
    {
        return array_map(fn($block) => $this->transform($block), $blocks);
    }
}