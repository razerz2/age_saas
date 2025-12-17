<?php

namespace App\Services\Finance\Reconciliation;

use App\Models\Tenant\FinancialCharge;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChargeReconciliationService
{
    /**
     * Concilia cobrança como paga
     */
    public function reconcilePaid(FinancialCharge $charge, array $payload): array
    {
        // Nunca reprocessar se já estiver pago
        if ($charge->status === 'paid') {
            Log::info('Charge já está paga, ignorando reconciliação', [
                'charge_id' => $charge->id,
            ]);
            return ['status' => 'skipped', 'message' => 'Charge já está paga'];
        }

        $paymentData = $payload['payment'] ?? [];
        $paymentMethod = $this->extractPaymentMethod($paymentData);
        $paidAt = isset($paymentData['confirmedDate']) 
            ? Carbon::parse($paymentData['confirmedDate']) 
            : now();

        $charge->update([
            'status' => 'paid',
            'paid_at' => $paidAt,
            'payment_method' => $paymentMethod,
        ]);

        Log::channel('finance')->info('Charge conciliada como paga', [
            'tenant' => tenant()->subdomain ?? 'unknown',
            'charge_id' => $charge->id,
            'amount' => $charge->amount,
            'payment_method' => $paymentMethod,
            'appointment_id' => $charge->appointment_id,
            'patient_id' => $charge->patient_id,
        ]);

        return ['status' => 'success', 'charge_id' => $charge->id];
    }

    /**
     * Concilia cobrança como vencida
     */
    public function reconcileOverdue(FinancialCharge $charge): array
    {
        if ($charge->status === 'paid') {
            Log::warning('Tentativa de marcar charge paga como vencida', [
                'charge_id' => $charge->id,
            ]);
            return ['status' => 'skipped', 'message' => 'Charge já está paga'];
        }

        $charge->update(['status' => 'overdue']);

        Log::info('Charge conciliada como vencida', [
            'charge_id' => $charge->id,
        ]);

        return ['status' => 'success', 'charge_id' => $charge->id];
    }

    /**
     * Concilia cobrança como cancelada
     */
    public function reconcileCanceled(FinancialCharge $charge): array
    {
        if ($charge->status === 'paid') {
            Log::warning('Tentativa de cancelar charge paga', [
                'charge_id' => $charge->id,
            ]);
            return ['status' => 'skipped', 'message' => 'Charge já está paga'];
        }

        $charge->update(['status' => 'cancelled']);

        Log::info('Charge conciliada como cancelada', [
            'charge_id' => $charge->id,
        ]);

        return ['status' => 'success', 'charge_id' => $charge->id];
    }

    /**
     * Concilia cobrança como estornada
     */
    public function reconcileRefunded(FinancialCharge $charge): array
    {
        $charge->update(['status' => 'refunded']);

        Log::info('Charge conciliada como estornada', [
            'charge_id' => $charge->id,
        ]);

        return ['status' => 'success', 'charge_id' => $charge->id];
    }

    /**
     * Extrai método de pagamento do payload
     */
    protected function extractPaymentMethod(array $paymentData): ?string
    {
        $billingType = $paymentData['billingType'] ?? null;
        
        return match(strtoupper($billingType ?? '')) {
            'PIX' => 'pix',
            'CREDIT_CARD' => 'credit_card',
            'BOLETO' => 'boleto',
            'DEBIT_CARD' => 'debit_card',
            default => null,
        };
    }
}

