<?php

namespace App\Modules\Entities\Entities;

use CodeIgniter\Entity\Entity;

class Entity extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'description' => '?string',
        'type' => 'string',
        'category_id' => '?integer',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    protected $datamap = [
        'id' => 'id',
        'name' => 'name',
        'description' => 'description',
        'type' => 'type',
        'category_id' => 'category_id',
        'metadata' => 'metadata',
        'is_active' => 'is_active',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at',
    ];
}