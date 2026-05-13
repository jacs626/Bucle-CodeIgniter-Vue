<?php

namespace App\Blocks\Strategies;

class ReminderStrategy extends AbstractBlockStrategy
{
    protected function getTypeName(): string
    {
        return 'reminder';
    }

    public function execute(array $block): array
    {
        $data = $this->sanitizeBlock($block);

        $data['config'] = [
            'title' => $block['config']['title'] ?? 'Reminder',
            'message' => $block['config']['message'] ?? '',
            'remind_at' => $block['config']['remind_at'] ?? null,
            'repeat' => $block['config']['repeat'] ?? null,
        ];

        $data['status'] = $this->calculateStatus($data['config']);

        return $data;
    }

    public function validate(array $block): bool
    {
        if (!parent::validate($block)) {
            return false;
        }

        return !empty($block['config']['message']);
    }

    private function calculateStatus(array $config): string
    {
        if (empty($config['remind_at'])) {
            return 'pending';
        }

        $remindAt = strtotime($config['remind_at']);
        $now = time();

        if ($remindAt < $now) {
            return 'overdue';
        }

        if ($remindAt < $now + 3600) {
            return 'soon';
        }

        return 'scheduled';
    }

    public function getDefaults(): array
    {
        return array_merge(parent::getDefaults(), [
            'config' => [
                'title' => 'New Reminder',
                'message' => '',
                'remind_at' => null,
                'repeat' => null,
            ],
        ]);
    }
}
