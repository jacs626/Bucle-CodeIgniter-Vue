<?php

namespace App\Modules\History\Transformers;

use App\Modules\History\Entities\History;

class HistoryTransformer
{
    public function transform(History $history): array
    {
        return [
            'id' => $history->id,
            'entity_id' => $history->entity_id,
            'block_id' => $history->block_id,
            'date' => $history->date?->format('Y-m-d H:i:s'),
            'status' => $history->status,
            'note' => $history->note,
            'block_name' => $history->block_name,
            'block_type' => $history->block_type,
            'entity_name' => $history->entity_name,
            'entity_type' => $history->entity_type,
            'executed_at' => $history->executed_at?->format('Y-m-d H:i:s'),
            'created_at' => $history->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $history->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function transformCollection(array $histories): array
    {
        return array_map(fn($history) => $this->transform($history), $histories);
    }
}