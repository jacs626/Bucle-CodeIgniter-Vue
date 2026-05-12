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
        'category_id',
        'name',
        'description',
        'type',
        'metadata',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|string|max_length[255]',
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getWithCategory()
    {
        return $this->select('entities.*, categories.name as category_name, categories.icon as category_icon')
            ->join('categories', 'categories.id = entities.category_id', 'left');
    }
}