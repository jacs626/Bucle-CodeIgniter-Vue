<?php

namespace App\Modules\Documents\Services;

use App\Modules\Documents\Models\DocumentModel;
use App\Modules\Documents\Entities\Document;
use App\Modules\Documents\Transformers\DocumentTransformer;
use CodeIgniter\Database\Exceptions\DatabaseException;

class DocumentService
{
    protected DocumentModel $documentModel;
    protected DocumentTransformer $transformer;

    public function __construct()
    {
        $this->documentModel = new DocumentModel();
        $this->transformer = new DocumentTransformer();
    }

    public function getAll(): array
    {
        $documents = $this->documentModel->findAll();

        return $this->transformer->transformCollection($documents);
    }

    public function findById(?int $id): ?array
    {
        if ($id === null) {
            return null;
        }

        $document = $this->documentModel->find($id);

        if (!$document) {
            return null;
        }

        return $this->transformer->transform($document);
    }

    public function create(array $data): array
    {
        $document = new Document($data);

        $id = $this->documentModel->insert($document);

        if ($id === false) {
            throw new DatabaseException('Failed to create document');
        }

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $document = new Document($data);
        $document->id = $id;

        $this->documentModel->update($id, $document);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        return $this->documentModel->delete($id);
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        }

        if (isset($data['title']) && strlen($data['title']) > 255) {
            $errors['title'] = 'Title must be less than 255 characters';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}