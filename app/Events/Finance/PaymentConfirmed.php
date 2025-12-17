<?php

namespace App\Events\Finance;

use App\Models\Tenant\FinancialCharge;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado quando um pagamento é confirmado
 */
class PaymentConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public FinancialCharge $charge,
        public string $paymentMethod,
        public ?string $eventId = null, // Para idempotência
        public ?float $grossAmount = null, // Valor bruto do pagamento
        public ?float $gatewayFee = null // Taxa do gateway (se disponível)
    ) {}
}

