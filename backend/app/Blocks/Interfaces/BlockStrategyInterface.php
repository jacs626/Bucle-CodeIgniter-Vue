<?php

namespace App\Blocks\Interfaces;

interface BlockStrategyInterface
{
    public function getType(): string;

    public function execute(array $block): array;

    public function validate(array $block): bool;

    public function getDefaults(): array;
}
