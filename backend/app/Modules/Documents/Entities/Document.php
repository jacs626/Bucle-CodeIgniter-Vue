<?php

namespace App\Modules\Documents\Entities;

use CodeIgniter\Entity\Entity;

class Document extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'content' => '?string',
        'file_path' => '?string',
        'file_type' => '?string',
        'file_size' => 'integer',
        'entity_id' => '?integer',
        'is_published' => 'boolean',
    ];

    protected $datamap = [
        'id' => 'id',
        'title' => 'title',
        'content' => 'content',
        'file_path' => 'file_path',
        'file_type' => 'file_type',
        'file_size' => 'file_size',
        'entity_id' => 'entity_id',
        'is_published' => 'is_published',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at',
    ];
}