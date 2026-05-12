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
        'entity_id',
        'name',
        'type',
        'data',
        'schedule',
        'parent_block_id',
        'order_index',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|string|max_length[255]',
        'type' => 'required|string|max_length[50]',
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}