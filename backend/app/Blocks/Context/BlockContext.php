<?php

namespace App\Blocks\Context;

use App\Blocks\Interfaces\BlockStrategyInterface;
use App\Blocks\Strategies\ReminderStrategy;
use App\Blocks\Strategies\TaskStrategy;
use App\Blocks\Strategies\PaymentStrategy;
use App\Blocks\Strategies\WorkflowStrategy;

class BlockContext
{
    private array $strategies = [];
    private static ?BlockContext $instance = null;

    public function __construct()
    {
        $this->registerDefaultStrategies();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function registerStrategy(BlockStrategyInterface $strategy): self
    {
        $this->strategies[$strategy->getType()] = $strategy;
        return $this;
    }

    public function getStrategy(string $type): ?BlockStrategyInterface
    {
        return $this->strategies[$type] ?? null;
    }

    public function execute(array $block): array
    {
        $type = $block['type'] ?? null;

        if ($type === null) {
            throw new \InvalidArgumentException('Block must have a type');
        }

        $strategy = $this->getStrategy($type);

        if ($strategy === null) {
            throw new \InvalidArgumentException("Unknown block type: {$type}");
        }

        return $strategy->execute($block);
    }

    public function executeAll(array $blocks): array
    {
        return array_map(fn($block) => $this->execute($block), $blocks);
    }

    public function validate(array $block): bool
    {
        $type = $block['type'] ?? null;

        if ($type === null) {
            return false;
        }

        $strategy = $this->getStrategy($type);

        if ($strategy === null) {
            return false;
        }

        return $strategy->validate($block);
    }

    public function getAvailableTypes(): array
    {
        return array_keys($this->strategies);
    }

    public function getDefaults(string $type): array
    {
        $strategy = $this->getStrategy($type);

        if ($strategy === null) {
            throw new \InvalidArgumentException("Unknown block type: {$type}");
        }

        return $strategy->getDefaults();
    }

    private function registerDefaultStrategies(): void
    {
        $this->strategies = [
            'reminder' => new ReminderStrategy(),
            'task' => new TaskStrategy(),
            'payment' => new PaymentStrategy(),
            'workflow' => new WorkflowStrategy(),
        ];
    }
}