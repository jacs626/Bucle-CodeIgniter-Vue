<?php

namespace App\Modules\Categories\Services;

use App\Modules\Categories\Models\CategoryModel;
use App\Modules\Categories\Entities\Category;
use App\Modules\Categories\Transformers\CategoryTransformer;
use CodeIgniter\Database\Exceptions\DatabaseException;

class CategoryService
{
    protected CategoryModel $categoryModel;
    protected CategoryTransformer $transformer;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->transformer = new CategoryTransformer();
    }

    public function getAll(): array
    {
        $categories = $this->categoryModel->findAll();

        return $this->transformer->transformCollection($categories);
    }

    public function findById(?int $id): ?array
    {
        if ($id === null) {
            return null;
        }

        $category = $this->categoryModel->find($id);

        if (!$category) {
            return null;
        }

        return $this->transformer->transform($category);
    }

    public function create(array $data): array
    {
        $category = new Category($data);

        $id = $this->categoryModel->insert($category);

        if ($id === false) {
            throw new DatabaseException('Failed to create category');
        }

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $category = new Category($data);
        $category->id = $id;

        $this->categoryModel->update($id, $category);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        return $this->categoryModel->delete($id);
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'El nombre es requerido';
        } elseif (strlen($data['name']) > 255) {
            $errors['name'] = 'El nombre debe tener menos de 255 caracteres';
        }

        if (isset($data['icon']) && strlen($data['icon']) > 100) {
            $errors['icon'] = 'El icono debe tener menos de 100 caracteres';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}