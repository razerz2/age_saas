<?php

namespace App\Services\Finance\Reconciliation;

use App\Models\Tenant\DoctorCommission;
use App\Models\Tenant\FinancialCharge;
use App\Models\Tenant\FinancialTransaction;
use Illuminate\Support\Facades\Log;

class CommissionReconciliationService
{
    /**
     * Cria comissão a partir de cobrança paga
     */
    public function reconcileFromCharge(FinancialCharge $charge): array
    {
        // Verificar se comissões estão habilitadas
        if (tenant_setting('finance.doctor_commission_enabled') !== 'true') {
            return ['status' => 'skipped', 'message' => 'Comissões desabilitadas'];
        }

        $appointment = $charge->appointment;
        if (!$appointment || !$appointment->doctor_id) {
            return ['status' => 'skipped', 'message' => 'Agendamento sem médico'];
        }

        // Buscar transação relacionada
        $transaction = FinancialTransaction::where('appointment_id', $charge->appointment_id)
            ->where('type', 'income')
            ->where('status', 'paid')
            ->first();

        if (!$transaction) {
            Log::warning('Transação não encontrada para criar comissão', [
                'charge_id' => $charge->id,
            ]);
            return ['status' => 'skipped', 'message' => 'Transação não encontrada'];
        }

        // Verificar se já existe comissão
        $existingCommission = DoctorCommission::where('transaction_id', $transaction->id)->first();

        if ($existingCommission) {
            Log::info('Comissão já existe para esta transação', [
                'charge_id' => $charge->id,
                'commission_id' => $existingCommission->id,
            ]);
            return ['status' => 'skipped', 'message' => 'Comissão já existe', 'commission_id' => $existingCommission->id];
        }

        // Calcular comissão
        $percentage = (float) tenant_setting('finance.default_commission_percentage', '0');
        $commissionAmount = ($charge->amount * $percentage) / 100;

        if ($commissionAmount <= 0) {
            return ['status' => 'skipped', 'message' => 'Valor de comissão zero'];
        }

        // Criar comissão
        $commission = DoctorCommission::create([
            'doctor_id' => $appointment->doctor_id,
            'transaction_id' => $transaction->id,
            'percentage' => $percentage,
            'amount' => $commissionAmount,
            'status' => 'pending',
        ]);

        Log::channel('finance')->info('Comissão criada a partir de cobrança', [
            'tenant' => tenant()->subdomain ?? 'unknown',
            'charge_id' => $charge->id,
            'commission_id' => $commission->id,
            'doctor_id' => $appointment->doctor_id,
            'amount' => $commissionAmount,
            'appointment_id' => $charge->appointment_id,
            'transaction_id' => $transaction->id,
        ]);

        return ['status' => 'success', 'commission_id' => $commission->id];
    }

    /**
     * Processa estorno de comissão
     */
    public function reconcileRefund(FinancialCharge $charge): array
    {
        $appointment = $charge->appointment;
        if (!$appointment || !$appointment->doctor_id) {
            return ['status' => 'skipped', 'message' => 'Agendamento sem médico'];
        }

        // Buscar transação original
        $transaction = FinancialTransaction::where('appointment_id', $charge->appointment_id)
            ->where('type', 'income')
            ->where('status', 'paid')
            ->first();

        if (!$transaction) {
            return ['status' => 'skipped', 'message' => 'Transação não encontrada'];
        }

        // Buscar comissão
        $commission = DoctorCommission::where('transaction_id', $transaction->id)->first();

        if (!$commission) {
            return ['status' => 'skipped', 'message' => 'Comissão não encontrada'];
        }

        // Se já está paga, apenas logar para revisão manual
        if ($commission->status === 'paid') {
            Log::warning('Comissão já paga precisa de revisão manual para estorno', [
                'charge_id' => $charge->id,
                'commission_id' => $commission->id,
            ]);
            return ['status' => 'requires_manual_review', 'message' => 'Comissão já paga', 'commission_id' => $commission->id];
        }

        // Se ainda está pendente, pode cancelar
        $commission->update(['status' => 'cancelled']);

        Log::info('Comissão cancelada devido a estorno', [
            'charge_id' => $charge->id,
            'commission_id' => $commission->id,
        ]);

        return ['status' => 'success', 'commission_id' => $commission->id];
    }
}

