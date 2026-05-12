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
        'entity_id',
        'title',
        'url',
        'type',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'title' => 'required|string|max_length[255]',
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}