<?php

namespace App\Modules\Entities\Services;

use App\Modules\Entities\Models\EntityModel;
use App\Modules\Entities\Entities\EntityData;
use App\Modules\Entities\Transformers\EntityTransformer;
use CodeIgniter\Database\Exceptions\DatabaseException;

class EntityService
{
    protected EntityModel $entityModel;
    protected EntityTransformer $transformer;

    public function __construct()
    {
        $this->entityModel = new EntityModel();
        $this->transformer = new EntityTransformer();
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
        $entity = new EntityData($data);

        $id = $this->entityModel->insert($entity);

        if ($id === false) {
            throw new DatabaseException('Failed to create entity');
        }

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $entity = new EntityData($data);
        $entity->id = $id;

        $this->entityModel->update($id, $entity);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
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
}