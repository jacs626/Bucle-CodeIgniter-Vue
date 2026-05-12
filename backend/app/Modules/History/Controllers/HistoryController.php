<?php

namespace App\Modules\History\Controllers;

use App\Controllers\BaseController;
use App\Modules\History\Services\HistoryService;

class HistoryController extends BaseController
{
    protected HistoryService $historyService;

    public function __construct()
    {
        $this->historyService = new HistoryService();
    }

    public function index()
    {
        $histories = $this->historyService->getAll();

        return $this->respondWithCollection($histories);
    }

    public function show($id = null)
    {
        $history = $this->historyService->findById($id);

        if (!$history) {
            return $this->respondNotFound('History not found');
        }

        return $this->respondSuccess($history);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $validation = $this->historyService->validate($data);

        if (!$validation['valid']) {
            return $this->respondValidationError($validation['errors']);
        }

        $history = $this->historyService->create($data);

        return $this->respondCreated($history, 'History created successfully');
    }

    public function delete($id = null)
    {
        $history = $this->historyService->findById($id);

        if (!$history) {
            return $this->respondNotFound('History not found');
        }

        $this->historyService->delete($id);

        return $this->respondDeleted('History deleted successfully');
    }
}