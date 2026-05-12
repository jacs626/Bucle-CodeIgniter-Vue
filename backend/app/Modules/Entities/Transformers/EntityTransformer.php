<?php

namespace App\Modules\Entities\Transformers;

use App\Modules\Entities\Entities\Entity;

class EntityTransformer
{
    public function transform(Entity $entity): array
    {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'description' => $entity->description,
            'type' => $entity->type,
            'category_id' => $entity->category_id,
            'metadata' => $entity->metadata,
            'is_active' => $entity->is_active,
            'created_at' => $entity->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $entity->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function transformCollection(array $entities): array
    {
        return array_map(fn($entity) => $this->transform($entity), $entities);
    }
}