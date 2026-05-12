<?php

namespace App\Modules\Categories\Entities;

use CodeIgniter\Entity\Entity;

class Category extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'icon' => '?string',
    ];

    protected $datamap = [
        'id' => 'id',
        'name' => 'name',
        'icon' => 'icon',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at',
    ];
}