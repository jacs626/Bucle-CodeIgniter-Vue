<?php

namespace App\Modules\Entities\Models;

use CodeIgniter\Model;
use App\Modules\Entities\Entities\Entity;

class EntityModel extends Model
{
    protected $table = 'entities';
    protected $primaryKey = 'id';
    protected $returnType = Entity::class;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'name',
        'description',
        'type',
        'category_id',
        'metadata',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $casts = [
        'id' => 'integer',
        'category_id' => '?integer',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    protected $validationRules = [
        'name' => 'required|string|max_length[255]',
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}