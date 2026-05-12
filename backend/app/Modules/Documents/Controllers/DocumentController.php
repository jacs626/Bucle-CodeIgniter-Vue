<?php

namespace App\Modules\Documents\Controllers;

use App\Controllers\BaseController;
use App\Modules\Documents\Services\DocumentService;

class DocumentController extends BaseController
{
    protected DocumentService $documentService;

    public function __construct()
    {
        $this->documentService = new DocumentService();
    }

    public function index()
    {
        $documents = $this->documentService->getAll();

        return $this->respondWithCollection($documents);
    }

    public function show($id = null)
    {
        $document = $this->documentService->findById($id);

        if (!$document) {
            return $this->respondNotFound('Document not found');
        }

        return $this->respondSuccess($document);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $validation = $this->documentService->validate($data);

        if (!$validation['valid']) {
            return $this->respondValidationError($validation['errors']);
        }

        $document = $this->documentService->create($data);

        return $this->respondCreated($document, 'Document created successfully');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $document = $this->documentService->findById($id);

        if (!$document) {
            return $this->respondNotFound('Document not found');
        }

        $validation = $this->documentService->validate($data, $id);

        if (!$validation['valid']) {
            return $this->respondValidationError($validation['errors']);
        }

        $updated = $this->documentService->update($id, $data);

        return $this->respondUpdated($updated, 'Document updated successfully');
    }

    public function delete($id = null)
    {
        $document = $this->documentService->findById($id);

        if (!$document) {
            return $this->respondNotFound('Document not found');
        }

        $this->documentService->delete($id);

        return $this->respondDeleted('Document deleted successfully');
    }
}