<?php

namespace App\Modules\History\Entities;

use CodeIgniter\Entity\Entity;

class History extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'entity_id' => '?integer',
        'block_id' => '?integer',
        'date' => 'datetime',
        'status' => 'string',
        'note' => 'array',
    ];

    protected $datamap = [
        'id' => 'id',
        'entity_id' => 'entity_id',
        'block_id' => 'block_id',
        'date' => 'date',
        'status' => 'status',
        'note' => 'note',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at',
    ];
}