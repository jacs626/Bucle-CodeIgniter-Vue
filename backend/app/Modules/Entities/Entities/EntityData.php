<?php

namespace App\Modules\Entities\Entities;

use CodeIgniter\Entity\Entity;

class EntityData extends Entity
{
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