<?php

namespace App\Modules\Entities\Services;

use App\Modules\Entities\Models\EntityModel;
use App\Modules\Entities\Entities\EntityData;
use App\Modules\Entities\Transformers\EntityTransformer;
use App\Modules\Blocks\Models\BlockModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class EntityService
{
    protected EntityModel $entityModel;
    protected EntityTransformer $transformer;
    protected BlockModel $blockModel;

    public function __construct()
    {
        $this->entityModel = new EntityModel();
        $this->transformer = new EntityTransformer();
        $this->blockModel = new BlockModel();
    }

    public function getAll(): array
    {
        $entities = $this->entityModel->findAll();

        return $this->transformer->transformCollection($entities);
    }

    public function findById(?int $id): ?array
    {
        if ($id === null) {
            return null;
        }

        $entity = $this->entityModel->find($id);

        if (!$entity) {
            return null;
        }

        return $this->transformer->transform($entity);
    }

    public function create(array $data): array
    {
        $entityData = $this->prepareData($data);
        $entity = new EntityData($entityData);

        $id = $this->entityModel->insert($entity);

        if ($id === false) {
            throw new DatabaseException('Failed to create entity');
        }

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $entityData = $this->prepareData($data);
        $entity = new EntityData($entityData);
        $entity->id = $id;

        $this->entityModel->update($id, $entity);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $blocks = $this->blockModel->where('entity_id', $id)->findAll();
        foreach ($blocks as $block) {
            $this->blockModel->delete($block->id);
        }

        return $this->entityModel->delete($id);
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'El nombre es requerido';
        } elseif (strlen($data['name']) > 255) {
            $errors['name'] = 'El nombre debe tener menos de 255 caracteres';
        }

        if (isset($data['type']) && strlen($data['type']) > 100) {
            $errors['type'] = 'El tipo debe tener menos de 100 caracteres';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function getAllWithCategory(): array
    {
        $entities = $this->entityModel->getWithCategory()->findAll();

        return $this->transformer->transformCollection($entities);
    }

    public function getByCategory(int $categoryId): array
    {
        $entities = $this->entityModel->where('category_id', $categoryId)->findAll();

        return $this->transformer->transformCollection($entities);
    }

    protected function prepareData(array $data): array
    {
        $prepared = [];

        if (isset($data['name'])) $prepared['name'] = $data['name'];
        if (isset($data['description'])) $prepared['description'] = $data['description'];
        if (isset($data['type'])) $prepared['type'] = $data['type'];
        if (array_key_exists('category_id', $data)) $prepared['category_id'] = $data['category_id'];
        if (isset($data['is_active'])) $prepared['is_active'] = $data['is_active'];

        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $prepared['metadata'] = json_encode($data['metadata']);
        }

        return $prepared;
    }
}