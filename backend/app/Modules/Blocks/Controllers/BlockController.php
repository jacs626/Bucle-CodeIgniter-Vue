<?php

namespace App\Modules\Blocks\Controllers;

use App\Controllers\BaseController;
use App\Modules\Blocks\Services\BlockService;

class BlockController extends BaseController
{
    protected BlockService $blockService;

    public function __construct()
    {
        $this->blockService = new BlockService();
    }

    public function index()
    {
        $blocks = $this->blockService->getAll();

        return $this->respondWithCollection($blocks);
    }

    public function show($id = null)
    {
        $block = $this->blockService->findById($id);

        if (!$block) {
            return $this->respondNotFound('Block not found');
        }

        return $this->respondSuccess($block);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $validation = $this->blockService->validate($data);

        if (!$validation['valid']) {
            return $this->respondValidationError($validation['errors']);
        }

        $block = $this->blockService->create($data);

        return $this->respondCreated($block, 'Block created successfully');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $block = $this->blockService->findById($id);

        if (!$block) {
            return $this->respondNotFound('Block not found');
        }

        $validation = $this->blockService->validate($data, $id);

        if (!$validation['valid']) {
            return $this->respondValidationError($validation['errors']);
        }

        $updated = $this->blockService->update($id, $data);

        return $this->respondUpdated($updated, 'Block updated successfully');
    }

    public function delete($id = null)
    {
        $block = $this->blockService->findById($id);

        if (!$block) {
            return $this->respondNotFound('Block not found');
        }

        $this->blockService->delete($id);

        return $this->respondDeleted('Block deleted successfully');
    }
}