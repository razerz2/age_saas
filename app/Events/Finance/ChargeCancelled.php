<?php

namespace App\Events\Finance;

use App\Models\Tenant\FinancialCharge;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado quando uma cobrança é cancelada
 */
class ChargeCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public FinancialCharge $charge
    ) {}
}

