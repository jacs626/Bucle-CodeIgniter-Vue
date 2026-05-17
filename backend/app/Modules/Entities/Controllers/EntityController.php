<?php

namespace App\Modules\Entities\Controllers;

use App\Modules\Entities\Services\EntityService;
use CodeIgniter\RESTful\ResourceController;

class EntityController extends ResourceController
{
    protected EntityService $service;

    public function __construct()
    {
        $this->service = new EntityService();
    }

    public function index()
    {
        $entities = $this->service->getAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Entidades obtenidas correctamente',
            'data' => $entities,
        ]);
    }

    public function show($id = null)
    {
        $entity = $this->service->findById((int) $id);

        if (!$entity) {
            return $this->failNotFound('Entidad no encontrada');
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Entidad encontrada',
            'data' => $entity,
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $validation = $this->service->validate($data);

        if (!$validation['valid']) {
            return $this->failValidationErrors($validation['errors']);
        }

        $entity = $this->service->create($data);

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Entidad creada correctamente',
            'data' => $entity,
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $entity = $this->service->findById((int) $id);

        if (!$entity) {
            return $this->failNotFound('Entidad no encontrada');
        }

        $validation = $this->service->validate($data);

        if (!$validation['valid']) {
            return $this->failValidationErrors($validation['errors']);
        }

        $updated = $this->service->update((int) $id, $data);

        return $this->respond([
            'status' => 'success',
            'message' => 'Entidad actualizada correctamente',
            'data' => $updated,
        ]);
    }

    public function delete($id = null)
    {
        $entity = $this->service->findById((int) $id);

        if (!$entity) {
            return $this->failNotFound('Entidad no encontrada');
        }

        $this->service->delete((int) $id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Entidad eliminada correctamente',
        ]);
    }

    public function getByCategory($categoryId = null)
    {
        if (!$categoryId) {
            return $this->failValidationErrors(['category_id' => 'El ID de categoría es requerido']);
        }

        $entities = $this->service->getByCategory((int) $categoryId);

        return $this->respond([
            'status' => 'success',
            'message' => 'Entidades obtenidas por categoría',
            'data' => $entities,
        ]);
    }
}