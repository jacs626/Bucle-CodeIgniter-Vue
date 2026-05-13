<?php

namespace App\Modules\Blocks\Services;

use App\Modules\Blocks\Models\BlockModel;
use App\Modules\Blocks\Entities\Block;
use App\Modules\Blocks\Transformers\BlockTransformer;
use CodeIgniter\Database\Exceptions\DatabaseException;

class BlockService
{
    protected BlockModel $blockModel;
    protected BlockTransformer $transformer;

    public function __construct()
    {
        $this->blockModel = new BlockModel();
        $this->transformer = new BlockTransformer();
    }

    public function getAll(): array
    {
        $blocks = $this->blockModel->findAll();

        return $this->transformer->transformCollection($blocks);
    }

    public function findById(?int $id): ?array
    {
        if ($id === null) {
            return null;
        }

        $block = $this->blockModel->find($id);

        if (!$block) {
            return null;
        }

        return $this->transformer->transform($block);
    }

    public function getByEntityId(int $entityId): array
    {
        $blocks = $this->blockModel->where('entity_id', $entityId)->findAll();

        return $this->transformer->transformCollection($blocks);
    }

    public function create(array $data): array
    {
        $blockData = $this->prepareData($data);
        $block = new Block($blockData);

        $id = $this->blockModel->insert($block);

        if ($id === false) {
            throw new DatabaseException('Failed to create block');
        }

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $blockData = $this->prepareData($data);
        $block = new Block($blockData);
        $block->id = $id;

        $this->blockModel->update($id, $block);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        return $this->blockModel->delete($id);
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'El nombre es requerido';
        } elseif (strlen($data['name']) > 255) {
            $errors['name'] = 'El nombre debe tener menos de 255 caracteres';
        }

        if (empty($data['type'])) {
            $errors['type'] = 'El tipo es requerido';
        } elseif (strlen($data['type']) > 50) {
            $errors['type'] = 'El tipo debe tener menos de 50 caracteres';
        }

        $validTypes = ['task', 'reminder', 'payment', 'step', 'note', 'workflow'];
        if (!empty($data['type']) && !in_array($data['type'], $validTypes)) {
            $errors['type'] = 'Tipo de block invalido';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    protected function prepareData(array $data): array
    {
        $prepared = [];

        if (isset($data['entity_id'])) $prepared['entity_id'] = $data['entity_id'];
        if (isset($data['name'])) $prepared['name'] = $data['name'];
        if (isset($data['type'])) $prepared['type'] = $data['type'];
        if (isset($data['parent_block_id'])) $prepared['parent_block_id'] = $data['parent_block_id'];
        if (isset($data['order_index'])) $prepared['order_index'] = $data['order_index'];
        if (isset($data['is_active'])) $prepared['is_active'] = $data['is_active'];

        if (isset($data['data']) && is_array($data['data'])) {
            $prepared['data'] = json_encode($data['data']);
        }

        if (isset($data['schedule']) && is_array($data['schedule'])) {
            $prepared['schedule'] = json_encode($data['schedule']);
        }

        return $prepared;
    }
}