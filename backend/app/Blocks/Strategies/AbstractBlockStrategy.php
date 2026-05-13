<?php

namespace App\Blocks\Strategies;

use App\Blocks\Interfaces\BlockStrategyInterface;

abstract class AbstractBlockStrategy implements BlockStrategyInterface
{
    abstract protected function getTypeName(): string;

    public function getType(): string
    {
        return $this->getTypeName();
    }

    public function validate(array $block): bool
    {
        return isset($block['type']) && $block['type'] === $this->getType();
    }

    public function getDefaults(): array
    {
        return [
            'type' => $this->getType(),
            'active' => true,
        ];
    }

    protected function sanitizeBlock(array $block): array
    {
        return [
            'id' => $block['id'] ?? null,
            'type' => $block['type'] ?? $this->getType(),
            'active' => $block['active'] ?? true,
            'entity_id' => $block['entity_id'] ?? null,
        ];
    }
}
