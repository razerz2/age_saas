<?php

namespace App\Services\Finance\Reconciliation;

use App\Models\Tenant\FinancialTransaction;
use App\Models\Tenant\FinancialCharge;
use App\Models\Tenant\FinancialCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionReconciliationService
{
    /**
     * Cria transação a partir de cobrança paga
     */
    public function reconcileFromCharge(FinancialCharge $charge): array
    {
        // Verificar se já existe transação para esta cobrança
        $existingTransaction = FinancialTransaction::where('appointment_id', $charge->appointment_id)
            ->where('type', 'income')
            ->where('status', 'paid')
            ->first();

        if ($existingTransaction) {
            Log::info('Transação já existe para esta cobrança', [
                'charge_id' => $charge->id,
                'transaction_id' => $existingTransaction->id,
            ]);
            return ['status' => 'skipped', 'message' => 'Transação já existe', 'transaction_id' => $existingTransaction->id];
        }

        // Obter conta padrão
        $accountId = tenant_setting('finance.default_account_id');

        // Obter categoria padrão de receita
        $category = FinancialCategory::where('type', 'income')
            ->where('active', true)
            ->first();

        $appointment = $charge->appointment;

        $transaction = FinancialTransaction::create([
            'type' => 'income',
            'description' => $this->generateDescription($charge),
            'amount' => $charge->amount,
            'date' => $charge->paid_at ?? now(),
            'status' => 'paid',
            'account_id' => $accountId,
            'category_id' => $category?->id,
            'appointment_id' => $charge->appointment_id,
            'patient_id' => $charge->patient_id,
            'doctor_id' => $appointment?->doctor_id,
            'created_by' => null, // Sistema
        ]);

        Log::channel('finance')->info('Transação criada a partir de cobrança', [
            'tenant' => tenant()->subdomain ?? 'unknown',
            'charge_id' => $charge->id,
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'appointment_id' => $charge->appointment_id,
            'patient_id' => $charge->patient_id,
            'doctor_id' => $appointment?->doctor_id,
        ]);

        return ['status' => 'success', 'transaction_id' => $transaction->id];
    }

    /**
     * Cria transação de estorno
     */
    public function reconcileRefund(FinancialCharge $charge): array
    {
        // Buscar transação original
        $originalTransaction = FinancialTransaction::where('appointment_id', $charge->appointment_id)
            ->where('type', 'income')
            ->where('status', 'paid')
            ->first();

        if (!$originalTransaction) {
            Log::warning('Transação original não encontrada para estorno', [
                'charge_id' => $charge->id,
            ]);
            return ['status' => 'skipped', 'message' => 'Transação original não encontrada'];
        }

        // Verificar se já existe estorno
        $existingRefund = FinancialTransaction::where('appointment_id', $charge->appointment_id)
            ->where('type', 'expense')
            ->where('description', 'like', '%Estorno%')
            ->first();

        if ($existingRefund) {
            Log::info('Estorno já existe', [
                'charge_id' => $charge->id,
                'refund_transaction_id' => $existingRefund->id,
            ]);
            return ['status' => 'skipped', 'message' => 'Estorno já existe'];
        }

        // Obter categoria de estorno
        $refundCategory = FinancialCategory::where('type', 'expense')
            ->where('name', 'like', '%Estorno%')
            ->where('active', true)
            ->first();

        // Criar transação de despesa (estorno)
        $refundTransaction = FinancialTransaction::create([
            'type' => 'expense',
            'description' => "Estorno - {$originalTransaction->description}",
            'amount' => $charge->amount,
            'date' => now(),
            'status' => 'paid',
            'account_id' => $originalTransaction->account_id,
            'category_id' => $refundCategory?->id,
            'appointment_id' => $charge->appointment_id,
            'patient_id' => $charge->patient_id,
            'doctor_id' => $originalTransaction->doctor_id,
            'created_by' => null, // Sistema
        ]);

        Log::info('Transação de estorno criada', [
            'charge_id' => $charge->id,
            'original_transaction_id' => $originalTransaction->id,
            'refund_transaction_id' => $refundTransaction->id,
        ]);

        return ['status' => 'success', 'refund_transaction_id' => $refundTransaction->id];
    }

    /**
     * Gera descrição para transação
     */
    protected function generateDescription(FinancialCharge $charge): string
    {
        $appointment = $charge->appointment;
        $patient = $charge->patient;

        if ($charge->billing_type === 'reservation') {
            return "Reserva de Agendamento - {$patient->full_name}";
        }

        return "Pagamento de Consulta - {$patient->full_name}";
    }
}

