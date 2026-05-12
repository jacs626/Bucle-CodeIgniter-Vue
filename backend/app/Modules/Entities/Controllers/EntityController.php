<?php

namespace App\Modules\Entities\Controllers;

use App\Controllers\BaseController;
use App\Modules\Entities\Services\EntityService;

class EntityController extends BaseController
{
    protected EntityService $entityService;

    public function __construct()
    {
        $this->entityService = new EntityService();
    }

    public function index()
    {
        $entities = $this->entityService->getAll();

        return $this->respondWithCollection($entities);
    }

    public function show($id = null)
    {
        $entity = $this->entityService->findById($id);

        if (!$entity) {
            return $this->respondNotFound('Entity not found');
        }

        return $this->respondSuccess($entity);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $validation = $this->entityService->validate($data);

        if (!$validation['valid']) {
            return $this->respondValidationError($validation['errors']);
        }

        $entity = $this->entityService->create($data);

        return $this->respondCreated($entity, 'Entity created successfully');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $entity = $this->entityService->findById($id);

        if (!$entity) {
            return $this->respondNotFound('Entity not found');
        }

        $validation = $this->entityService->validate($data, $id);

        if (!$validation['valid']) {
            return $this->respondValidationError($validation['errors']);
        }

        $updated = $this->entityService->update($id, $data);

        return $this->respondUpdated($updated, 'Entity updated successfully');
    }

    public function delete($id = null)
    {
        $entity = $this->entityService->findById($id);

        if (!$entity) {
            return $this->respondNotFound('Entity not found');
        }

        $this->entityService->delete($id);

        return $this->respondDeleted('Entity deleted successfully');
    }
}