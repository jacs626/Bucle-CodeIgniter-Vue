<?php

namespace App\Modules\Categories\Controllers;

use App\Modules\Categories\Services\CategoryService;
use CodeIgniter\RESTful\ResourceController;

class CategoryController extends ResourceController
{
    protected CategoryService $service;

    public function __construct()
    {
        $this->service = new CategoryService();
    }

    public function index()
    {
        $categories = $this->service->getAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Categorías obtenidas correctamente',
            'data' => $categories,
        ]);
    }

    public function show($id = null)
    {
        $category = $this->service->findById((int) $id);

        if (!$category) {
            return $this->failNotFound('Categoría no encontrada');
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Categoría encontrada',
            'data' => $category,
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $validation = $this->service->validate($data);

        if (!$validation['valid']) {
            return $this->failValidationErrors($validation['errors']);
        }

        $category = $this->service->create($data);

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Categoría creada correctamente',
            'data' => $category,
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $category = $this->service->findById((int) $id);

        if (!$category) {
            return $this->failNotFound('Categoría no encontrada');
        }

        $validation = $this->service->validate($data);

        if (!$validation['valid']) {
            return $this->failValidationErrors($validation['errors']);
        }

        $updated = $this->service->update((int) $id, $data);

        return $this->respond([
            'status' => 'success',
            'message' => 'Categoría actualizada correctamente',
            'data' => $updated,
        ]);
    }

    public function delete($id = null)
    {
        $category = $this->service->findById((int) $id);

        if (!$category) {
            return $this->failNotFound('Categoría no encontrada');
        }

        $this->service->delete((int) $id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Categoría eliminada correctamente',
        ]);
    }
}