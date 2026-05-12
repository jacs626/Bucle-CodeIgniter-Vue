<?php

namespace App\Modules\Documents\Transformers;

use App\Modules\Documents\Entities\Document;

class DocumentTransformer
{
    public function transform(Document $document): array
    {
        return [
            'id' => $document->id,
            'title' => $document->title,
            'content' => $document->content,
            'file_path' => $document->file_path,
            'file_type' => $document->file_type,
            'file_size' => $document->file_size,
            'entity_id' => $document->entity_id,
            'is_published' => $document->is_published,
            'created_at' => $document->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $document->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function transformCollection(array $documents): array
    {
        return array_map(fn($document) => $this->transform($document), $documents);
    }
}