<?php

namespace App\Modules\Blocks\Models;

use CodeIgniter\Model;
use App\Modules\Blocks\Entities\Block;

class BlockModel extends Model
{
    protected $table = 'blocks';
    protected $primaryKey = 'id';
    protected $returnType = Block::class;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'name',
        'type',
        'content',
        'entity_id',
        'order',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $casts = [
        'id' => 'integer',
        'entity_id' => '?integer',
        'content' => 'array',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $validationRules = [
        'name' => 'required|string|max_length[255]',
        'type' => 'required|string|max_length[50]',
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}