<?php

namespace App\Modules\History\Services;

use App\Modules\History\Models\HistoryModel;
use App\Modules\History\Entities\History;
use App\Modules\History\Transformers\HistoryTransformer;
use CodeIgniter\Database\Exceptions\DatabaseException;

class HistoryService
{
    protected HistoryModel $historyModel;
    protected HistoryTransformer $transformer;

    public function __construct()
    {
        $this->historyModel = new HistoryModel();
        $this->transformer = new HistoryTransformer();
    }

    public function getAll(): array
    {
        $histories = $this->historyModel
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->transformer->transformCollection($histories);
    }

    public function findById(?int $id): ?array
    {
        if ($id === null) {
            return null;
        }

        $history = $this->historyModel->find($id);

        if (!$history) {
            return null;
        }

        return $this->transformer->transform($history);
    }

    public function create(array $data): array
    {
        $history = new History($data);

        $id = $this->historyModel->insert($history);

        if ($id === false) {
            throw new DatabaseException('Failed to create history');
        }

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        return $this->historyModel->delete($id);
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['entity_type'])) {
            $errors['entity_type'] = 'Entity type is required';
        }

        if (empty($data['entity_id'])) {
            $errors['entity_id'] = 'Entity ID is required';
        }

        if (empty($data['action'])) {
            $errors['action'] = 'Action is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}