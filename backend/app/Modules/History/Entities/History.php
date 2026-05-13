<?php

namespace App\Modules\History\Entities;

use CodeIgniter\Entity\Entity;

class History extends Entity
{
    protected $datamap = [
        'id' => 'id',
        'entity_id' => 'entity_id',
        'block_id' => 'block_id',
        'date' => 'date',
        'status' => 'status',
        'note' => 'note',
        'block_name' => 'block_name',
        'block_type' => 'block_type',
        'entity_name' => 'entity_name',
        'entity_type' => 'entity_type',
        'executed_at' => 'executed_at',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at',
    ];

    protected $casts = [
        'date' => 'datetime',
        'executed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}