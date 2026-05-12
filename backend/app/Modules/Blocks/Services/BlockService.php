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
            $errors['name'] = 'Name is required';
        }

        if (isset($data['name']) && strlen($data['name']) > 255) {
            $errors['name'] = 'Name must be less than 255 characters';
        }

        if (empty($data['type'])) {
            $errors['type'] = 'Type is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}