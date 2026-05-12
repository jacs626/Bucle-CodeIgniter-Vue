<?php

namespace App\Modules\Blocks\Controllers;

use App\Modules\Blocks\Services\BlockService;
use CodeIgniter\RESTful\ResourceController;

class BlockController extends ResourceController
{
    protected BlockService $service;

    public function __construct()
    {
        $this->service = new BlockService();
    }

    public function index()
    {
        $blocks = $this->service->getAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Blocks obtenidos correctamente',
            'data' => $blocks,
        ]);
    }

    public function show($id = null)
    {
        $block = $this->service->findById((int) $id);

        if (!$block) {
            return $this->failNotFound('Block no encontrado');
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Block encontrado',
            'data' => $block,
        ]);
    }

    public function getByEntity($entityId = null)
    {
        $blocks = $this->service->getByEntityId((int) $entityId);

        return $this->respond([
            'status' => 'success',
            'message' => 'Blocks de entidad obtenidos correctamente',
            'data' => $blocks,
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $validation = $this->service->validate($data);

        if (!$validation['valid']) {
            return $this->failValidationErrors($validation['errors']);
        }

        $block = $this->service->create($data);

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Block creado correctamente',
            'data' => $block,
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $block = $this->service->findById((int) $id);

        if (!$block) {
            return $this->failNotFound('Block no encontrado');
        }

        $validation = $this->service->validate($data);

        if (!$validation['valid']) {
            return $this->failValidationErrors($validation['errors']);
        }

        $updated = $this->service->update((int) $id, $data);

        return $this->respond([
            'status' => 'success',
            'message' => 'Block actualizado correctamente',
            'data' => $updated,
        ]);
    }

    public function delete($id = null)
    {
        $block = $this->service->findById((int) $id);

        if (!$block) {
            return $this->failNotFound('Block no encontrado');
        }

        $this->service->delete((int) $id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Block eliminado correctamente',
        ]);
    }
}