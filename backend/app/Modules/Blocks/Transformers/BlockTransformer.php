<?php

namespace App\Modules\Blocks\Transformers;

use App\Modules\Blocks\Entities\Block;

class BlockTransformer
{
    public function transform(Block $block): array
    {
        return [
            'id' => $block->id,
            'name' => $block->name,
            'type' => $block->type,
            'content' => $block->content,
            'entity_id' => $block->entity_id,
            'order' => $block->order,
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