<?php

namespace App\Modules\History\Transformers;

use App\Modules\History\Entities\History;

class HistoryTransformer
{
    public function transform(History $history): array
    {
        return [
            'id' => $history->id,
            'entity_type' => $history->entity_type,
            'entity_id' => $history->entity_id,
            'action' => $history->action,
            'changes' => $history->changes,
            'user_id' => $history->user_id,
            'created_at' => $history->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $history->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function transformCollection(array $histories): array
    {
        return array_map(fn($history) => $this->transform($history), $histories);
    }
}