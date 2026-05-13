<?php

namespace App\Blocks\Strategies;

class TaskStrategy extends AbstractBlockStrategy
{
    protected function getTypeName(): string
    {
        return 'task';
    }

    public function execute(array $block): array
    {
        $data = $this->sanitizeBlock($block);

        $data['config'] = [
            'title' => $block['config']['title'] ?? 'Task',
            'description' => $block['config']['description'] ?? '',
            'due_date' => $block['config']['due_date'] ?? null,
            'priority' => $block['config']['priority'] ?? 'medium',
            'assignee_id' => $block['config']['assignee_id'] ?? null,
            'tags' => $block['config']['tags'] ?? [],
        ];

        $data['status'] = $this->calculateStatus($data['config'], $block['completed'] ?? false);

        return $data;
    }

    public function validate(array $block): bool
    {
        if (!parent::validate($block)) {
            return false;
        }

        $validPriorities = ['low', 'medium', 'high', 'urgent'];
        $priority = $block['config']['priority'] ?? 'medium';

        return in_array($priority, $validPriorities);
    }

    private function calculateStatus(array $config, bool $completed): string
    {
        if ($completed) {
            return 'completed';
        }

        if (empty($config['due_date'])) {
            return 'no_due_date';
        }

        $dueDate = strtotime($config['due_date']);
        $now = time();

        if ($dueDate < $now) {
            return 'overdue';
        }

        if ($dueDate < $now + 86400) {
            return 'due_soon';
        }

        return 'active';
    }

    public function getDefaults(): array
    {
        return array_merge(parent::getDefaults(), [
            'config' => [
                'title' => 'New Task',
                'description' => '',
                'due_date' => null,
                'priority' => 'medium',
                'assignee_id' => null,
                'tags' => [],
            ],
            'completed' => false,
        ]);
    }
}
