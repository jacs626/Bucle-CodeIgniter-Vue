<?php

namespace App\Modules\Blocks\Entities;

use CodeIgniter\Entity\Entity;

class Block extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'type' => 'string',
        'content' => 'array',
        'entity_id' => '?integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $datamap = [
        'id' => 'id',
        'name' => 'name',
        'type' => 'type',
        'content' => 'content',
        'entity_id' => 'entity_id',
        'order' => 'order',
        'is_active' => 'is_active',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at',
    ];
}