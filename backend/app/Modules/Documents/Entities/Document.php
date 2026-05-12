<?php

namespace App\Modules\Documents\Entities;

use CodeIgniter\Entity\Entity;

class Document extends Entity
{
    protected $datamap = [
        'id' => 'id',
        'entity_id' => 'entity_id',
        'title' => 'title',
        'url' => 'url',
        'type' => 'type',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at',
    ];
}