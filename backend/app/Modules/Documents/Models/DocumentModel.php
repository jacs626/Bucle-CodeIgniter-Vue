<?php

namespace App\Modules\Documents\Models;

use CodeIgniter\Model;
use App\Modules\Documents\Entities\Document;

class DocumentModel extends Model
{
    protected $table = 'documents';
    protected $primaryKey = 'id';
    protected $returnType = Document::class;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'title',
        'content',
        'file_path',
        'file_type',
        'file_size',
        'entity_id',
        'is_published',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $casts = [
        'id' => 'integer',
        'entity_id' => '?integer',
        'file_size' => 'integer',
        'is_published' => 'boolean',
    ];

    protected $validationRules = [
        'title' => 'required|string|max_length[255]',
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}