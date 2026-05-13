<?php

namespace App\Blocks\Strategies;

class PaymentStrategy extends AbstractBlockStrategy
{
    protected function getTypeName(): string
    {
        return 'payment';
    }

    public function execute(array $block): array
    {
        $data = $this->sanitizeBlock($block);

        $data['config'] = [
            'title' => $block['config']['title'] ?? 'Payment',
            'amount' => $block['config']['amount'] ?? 0,
            'currency' => $block['config']['currency'] ?? 'USD',
            'due_date' => $block['config']['due_date'] ?? null,
            'recipient' => $block['config']['recipient'] ?? null,
            'recurring' => $block['config']['recurring'] ?? false,
            'frequency' => $block['config']['frequency'] ?? null,
        ];

        $data['status'] = $this->calculateStatus($data['config'], $block['paid'] ?? false);

        return $data;
    }

    public function validate(array $block): bool
    {
        if (!parent::validate($block)) {
            return false;
        }

        $validCurrencies = ['USD', 'EUR', 'GBP', 'MXN', 'COP'];
        $currency = $block['config']['currency'] ?? 'USD';

        if (!in_array($currency, $validCurrencies)) {
            return false;
        }

        $amount = $block['config']['amount'] ?? 0;
        return is_numeric($amount) && $amount > 0;
    }

    private function calculateStatus(array $config, bool $paid): string
    {
        if ($paid) {
            return 'paid';
        }

        if (empty($config['due_date'])) {
            return 'no_due_date';
        }

        $dueDate = strtotime($config['due_date']);
        $now = time();

        if ($dueDate < $now) {
            return 'overdue';
        }

        if ($dueDate < $now + 259200) {
            return 'due_soon';
        }

        return 'pending';
    }

    public function getDefaults(): array
    {
        return array_merge(parent::getDefaults(), [
            'config' => [
                'title' => 'New Payment',
                'amount' => 0,
                'currency' => 'USD',
                'due_date' => null,
                'recipient' => null,
                'recurring' => false,
                'frequency' => null,
            ],
            'paid' => false,
        ]);
    }
}
