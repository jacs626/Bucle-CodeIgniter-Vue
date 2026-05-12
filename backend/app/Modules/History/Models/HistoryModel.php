<?php

namespace App\Modules\History\Models;

use CodeIgniter\Model;
use App\Modules\History\Entities\History;

class HistoryModel extends Model
{
    protected $table = 'history';
    protected $primaryKey = 'id';
    protected $returnType = History::class;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'entity_type',
        'entity_id',
        'action',
        'changes',
        'user_id',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $casts = [
        'id' => 'integer',
        'entity_id' => 'integer',
        'changes' => 'array',
        'user_id' => '?integer',
    ];

    protected $validationRules = [
        'entity_type' => 'required|string|max_length[50]',
        'entity_id' => 'required|integer',
        'action' => 'required|string|max_length[50]',
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}