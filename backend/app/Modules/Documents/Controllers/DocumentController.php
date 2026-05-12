<?php

namespace App\Modules\Documents\Controllers;

use App\Modules\Documents\Services\DocumentService;

use CodeIgniter\RESTful\ResourceController;

class DocumentController extends ResourceController
{
    protected DocumentService $documentService;

    public function __construct()
    {
        $this->documentService = new DocumentService();
    }

    public function index()
    {
        $documents = $this->documentService->getAll();

        return $this->respond([
            'status' => 'success',
            'data' => $documents,
        ]);
    }

    public function show($id = null)
    {
        $document = $this->documentService->findById($id);

        if (!$document) {
            return $this->failNotFound('Document not found');
        }

        return $this->respond([
            'status' => 'success',
            'data' => $document,
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $validation = $this->documentService->validate($data);

        if (!$validation['valid']) {
            return $this->failValidationErrors($validation['errors']);
        }

        $document = $this->documentService->create($data);

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Document created successfully',
            'data' => $document,
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $document = $this->documentService->findById($id);

        if (!$document) {
            return $this->failNotFound('Document not found');
        }

        $validation = $this->documentService->validate($data, $id);

        if (!$validation['valid']) {
            return $this->failValidationErrors($validation['errors']);
        }

        $updated = $this->documentService->update($id, $data);

        return $this->respond([
            'status' => 'success',
            'message' => 'Document updated successfully',
            'data' => $updated,
        ]);
    }

    public function delete($id = null)
    {
        $document = $this->documentService->findById($id);

        if (!$document) {
            return $this->failNotFound('Document not found');
        }

        $this->documentService->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Document deleted successfully',
        ]);
    }
}