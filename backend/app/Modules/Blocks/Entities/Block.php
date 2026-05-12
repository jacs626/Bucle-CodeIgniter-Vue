<?php

namespace App\Modules\Blocks\Entities;

use CodeIgniter\Entity\Entity;

class Block extends Entity
{
    protected $datamap = [
        'id' => 'id',
        'entity_id' => 'entity_id',
        'name' => 'name',
        'type' => 'type',
        'data' => 'data',
        'schedule' => 'schedule',
        'parent_block_id' => 'parent_block_id',
        'order_index' => 'order_index',
        'is_active' => 'is_active',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at',
    ];
}