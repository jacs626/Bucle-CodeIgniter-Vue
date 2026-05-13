<?php

namespace App\Controllers;

use App\Blocks\Context\BlockContext;

class BlocksController extends BaseController
{
    private BlockContext $blockContext;

    public function __construct()
    {
        $this->blockContext = BlockContext::getInstance();
    }

    public function index(): array
    {
        $blocks = $this->getBlocksFromDatabase();

        return $this->response->setJSON([
            'success' => true,
            'data' => $this->blockContext->executeAll($blocks),
        ]);
    }

    public function show(string $id): array
    {
        $block = $this->getBlockFromDatabase($id);

        if ($block === null) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Block not found',
            ], 404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $this->blockContext->execute($block),
        ]);
    }

    public function create(): array
    {
        $data = $this->request->getJSON(true);

        if (!$this->blockContext->validate($data)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid block data',
            ], 400);
        }

        $processedBlock = $this->blockContext->execute($data);

        $this->saveBlockToDatabase($processedBlock);

        return $this->response->setJSON([
            'success' => true,
            'data' => $processedBlock,
        ], 201);
    }

    public function types(): array
    {
        return $this->response->setJSON([
            'success' => true,
            'data' => $this->blockContext->getAvailableTypes(),
        ]);
    }

    public function defaults(string $type): array
    {
        try {
            $defaults = $this->blockContext->getDefaults($type);
            return $this->response->setJSON([
                'success' => true,
                'data' => $defaults,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    private function getBlocksFromDatabase(): array
    {
        return [];
    }

    private function getBlockFromDatabase(string $id): ?array
    {
        return null;
    }

    private function saveBlockToDatabase(array $block): void
    {
    }
}