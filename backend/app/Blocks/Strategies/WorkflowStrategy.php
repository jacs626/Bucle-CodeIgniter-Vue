<?php

namespace App\Blocks\Strategies;

class WorkflowStrategy extends AbstractBlockStrategy
{
    protected function getTypeName(): string
    {
        return 'workflow';
    }

    public function execute(array $block): array
    {
        $data = $this->sanitizeBlock($block);

        $data['config'] = [
            'title' => $block['config']['title'] ?? 'Workflow',
            'steps' => $block['config']['steps'] ?? [],
            'current_step' => $block['config']['current_step'] ?? 0,
            'automation_rules' => $block['config']['automation_rules'] ?? [],
            'trigger_event' => $block['config']['trigger_event'] ?? null,
        ];

        $data['status'] = $this->calculateStatus($data['config']);

        return $data;
    }

    public function validate(array $block): bool
    {
        if (!parent::validate($block)) {
            return false;
        }

        $steps = $block['config']['steps'] ?? [];
        return is_array($steps) && count($steps) > 0;
    }

    private function calculateStatus(array $config): string
    {
        $steps = $config['steps'];
        $currentStep = $config['current_step'];

        if (empty($steps)) {
            return 'inactive';
        }

        if ($currentStep >= count($steps)) {
            return 'completed';
        }

        return 'in_progress';
    }

    public function getDefaults(): array
    {
        return array_merge(parent::getDefaults(), [
            'config' => [
                'title' => 'New Workflow',
                'steps' => [],
                'current_step' => 0,
                'automation_rules' => [],
                'trigger_event' => null,
            ],
        ]);
    }
}
