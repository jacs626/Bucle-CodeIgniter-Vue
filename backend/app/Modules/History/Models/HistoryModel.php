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
        'entity_id',
        'block_id',
        'date',
        'status',
        'note',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}