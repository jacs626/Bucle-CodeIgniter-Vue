<?php

namespace App\Modules\History\Entities;

use CodeIgniter\Entity\Entity;

class History extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'entity_type' => 'string',
        'entity_id' => 'integer',
        'action' => 'string',
        'changes' => 'array',
        'user_id' => '?integer',
    ];

    protected $datamap = [
        'id' => 'id',
        'entity_type' => 'entity_type',
        'entity_id' => 'entity_id',
        'action' => 'action',
        'changes' => 'changes',
        'user_id' => 'user_id',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at',
    ];
}