<?php

namespace App\Modules\Entities\Services;

use App\Modules\Entities\Models\EntityModel;
use App\Modules\Entities\Entities\Entity;
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
        $entity = new Entity($data);

        $id = $this->entityModel->insert($entity);

        if ($id === false) {
            throw new DatabaseException('Failed to create entity');
        }

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $entity = new Entity($data);
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
            $errors['name'] = 'Name is required';
        }

        if (isset($data['name']) && strlen($data['name']) > 255) {
            $errors['name'] = 'Name must be less than 255 characters';
        }

        if (isset($data['description']) && strlen($data['description']) > 1000) {
            $errors['description'] = 'Description must be less than 1000 characters';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}