<?php

namespace App\Listeners\Finance;

use App\Events\Finance\PaymentConfirmed;
use App\Services\Finance\FinanceRecorderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Listener que cria FinancialTransaction quando pagamento é confirmado
 */
class CreateTransactionOnPaymentConfirmed
{
    /**
     * Handle the event.
     */
    public function handle(PaymentConfirmed $event): void
    {
        $charge = $event->charge;
        
        // Verificar idempotência por origin_id (charge) e event_id
        if ($event->eventId) {
            $existing = DB::table('financial_transactions')
                ->where('origin_type', 'charge')
                ->where('origin_id', $charge->id)
                ->whereJsonContains('metadata->event_id', $event->eventId)
                ->exists();
                
            if ($existing) {
                Log::info('Transação já criada para este evento (idempotência)', [
                    'event_id' => $event->eventId,
                    'charge_id' => $charge->id,
                ]);
                return;
            }
        }

        try {
            $charge->load(['appointment', 'patient']);
            
            if (!$charge->appointment) {
                Log::warning('Cobrança sem agendamento, não criando transação', [
                    'charge_id' => $charge->id,
                ]);
                return;
            }

            // Calcular valores brutos e líquidos
            $grossAmount = $event->grossAmount ?? $charge->amount;
            $gatewayFee = $event->gatewayFee ?? 0;
            $netAmount = $grossAmount - $gatewayFee;

            // Criar transação diretamente (não usar recordAppointmentIncome para manter origem como 'charge')
            $transaction = FinancialTransaction::create([
                'type' => 'income',
                'origin_type' => 'charge',
                'origin_id' => $charge->id,
                'direction' => 'credit',
                'description' => "Pagamento de consulta - {$charge->appointment->patient->full_name}",
                'amount' => $netAmount,
                'gross_amount' => $grossAmount,
                'gateway_fee' => $gatewayFee,
                'net_amount' => $netAmount,
                'date' => $charge->paid_at ? $charge->paid_at->toDateString() : now()->toDateString(),
                'status' => 'paid',
                'account_id' => tenant_setting('finance.default_account_id'),
                'category_id' => tenant_setting('finance.default_category_id'),
                'appointment_id' => $charge->appointment_id,
                'patient_id' => $charge->patient_id,
                'doctor_id' => $charge->appointment->calendar?->doctor_id,
                'created_by' => null, // Sistema
            ]);

            // Adicionar metadata se event_id foi fornecido
            if ($event->eventId) {
                DB::table('financial_transactions')
                    ->where('id', $transaction->id)
                    ->update([
                        'metadata' => json_encode(['event_id' => $event->eventId]),
                    ]);
            }

            Log::info('Transação criada a partir de pagamento confirmado', [
                'transaction_id' => $transaction->id,
                'charge_id' => $charge->id,
                'appointment_id' => $charge->appointment_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao criar transação a partir de pagamento confirmado', [
                'charge_id' => $charge->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

