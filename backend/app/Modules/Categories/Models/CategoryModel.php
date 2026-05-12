<?php

namespace App\Modules\Categories\Models;

use CodeIgniter\Model;
use App\Modules\Categories\Entities\Category;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $returnType = Category::class;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'name',
        'icon',
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
}