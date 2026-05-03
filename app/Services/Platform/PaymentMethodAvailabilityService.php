<?php

namespace App\Services\Platform;

class PaymentMethodAvailabilityService
{
    private const CONFIG_MAP = [
        'PIX' => 'billing.payment_methods.pix_enabled',
        'PIX_RECURRENT' => 'billing.payment_methods.pix_recurrent_enabled',
        'BOLETO' => 'billing.payment_methods.boleto_enabled',
        'CREDIT_CARD' => 'billing.payment_methods.credit_card_enabled',
        'DEBIT_CARD' => 'billing.payment_methods.debit_card_enabled',
    ];

    private const DEFAULTS = [
        'PIX' => true,
        'PIX_RECURRENT' => false,
        'BOLETO' => true,
        'CREDIT_CARD' => true,
        'DEBIT_CARD' => false,
    ];

    public function enabledMethods(): array
    {
        return array_values(array_filter(array_keys(self::CONFIG_MAP), fn (string $method): bool => $this->isEnabled($method)));
    }

    public function isEnabled(string $method): bool
    {
        $normalized = strtoupper(trim($method));
        $configKey = self::CONFIG_MAP[$normalized] ?? null;

        if ($configKey === null) {
            return false;
        }

        $default = (self::DEFAULTS[$normalized] ?? false) ? '1' : '0';

        return sysconfig($configKey, $default) === '1';
    }

    public function options(): array
    {
        return [
            [
                'method' => 'PIX',
                'label' => 'PIX manual',
                'enabled' => $this->isEnabled('PIX'),
            ],
            [
                'method' => 'PIX_RECURRENT',
                'label' => 'PIX recorrente',
                'enabled' => $this->isEnabled('PIX_RECURRENT'),
            ],
            [
                'method' => 'BOLETO',
                'label' => 'Boleto',
                'enabled' => $this->isEnabled('BOLETO'),
            ],
            [
                'method' => 'CREDIT_CARD',
                'label' => 'Cartao de credito recorrente',
                'enabled' => $this->isEnabled('CREDIT_CARD'),
            ],
            [
                'method' => 'DEBIT_CARD',
                'label' => 'Cartao de debito',
                'enabled' => $this->isEnabled('DEBIT_CARD'),
            ],
        ];
    }
}

