<?php

namespace App\Modules\Categories\Transformers;

use App\Modules\Categories\Entities\Category;

class CategoryTransformer
{
    public function transform(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'icon' => $category->icon,
            'created_at' => $category->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $category->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function transformCollection(array $categories): array
    {
        return array_map(fn($category) => $this->transform($category), $categories);
    }
}