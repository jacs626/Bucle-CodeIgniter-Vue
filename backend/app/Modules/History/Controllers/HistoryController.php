<?php

namespace App\Modules\History\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Modules\History\Services\HistoryService;

class HistoryController extends ResourceController
{
    protected HistoryService $historyService;

    public function __construct()
    {
        $this->historyService = new HistoryService();
    }

    public function index()
    {
        $histories = $this->historyService->getAll();
        return $this->respond([
            'status' => 'success',
            'message' => 'Historial obtenido correctamente',
            'data' => $histories,
        ]);
    }

    public function show($id = null)
    {
        $history = $this->historyService->findById($id);

        if (!$history) {
            return $this->failNotFound('Historial no encontrado');
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Historial encontrado',
            'data' => $history,
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $validation = $this->historyService->validate($data);

        if (!$validation['valid']) {
            return $this->failValidationErrors($validation['errors']);
        }

        $history = $this->historyService->create($data);
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Historial creado correctamente',
            'data' => $history,
        ]);
    }

    public function delete($id = null)
    {
        $history = $this->historyService->findById($id);

        if (!$history) {
            return $this->failNotFound('Historial no encontrado');
        }

        $this->historyService->delete($id);
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Historial eliminado correctamente',
        ]);
    }
}