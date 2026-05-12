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
        $block = new Block($data);

        $id = $this->blockModel->insert($block);

        if ($id === false) {
            throw new DatabaseException('Failed to create block');
        }

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $block = new Block($data);
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
            $errors['type'] = 'Tipo de block inválido';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}