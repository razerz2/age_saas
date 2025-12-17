<?php

namespace App\Services\Finance;

use App\Models\Tenant\Appointment;
use App\Models\Tenant\FinancialTransaction;
use App\Models\Tenant\FinancialAccount;
use App\Models\Tenant\FinancialCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Serviço core para registro de transações financeiras
 * 
 * Responsabilidade única: Registrar receitas/despesas sem qualquer integração externa
 * 
 * REGRAS:
 * - Nunca chamar Asaas ou qualquer gateway
 * - Nunca criar FinancialCharge
 * - Usar tenant connection
 * - Atualizar saldo da conta (se status = paid)
 */
class FinanceRecorderService
{
    /**
     * Registra receita vinculada a um agendamento
     * 
     * @param Appointment $appointment
     * @return FinancialTransaction
     */
    public function recordAppointmentIncome(Appointment $appointment): FinancialTransaction
    {
        $appointment->load(['patient', 'calendar.doctor', 'specialty']);

        // Determinar valor baseado nas configurações
        $amount = $this->calculateAppointmentAmount($appointment);

        if ($amount <= 0) {
            Log::info('Valor do agendamento é zero, não criando transação', [
                'appointment_id' => $appointment->id,
            ]);
            throw new \InvalidArgumentException('Valor do agendamento deve ser maior que zero');
        }

        // Obter conta e categoria padrão
        $accountId = tenant_setting('finance.default_account_id');
        $categoryId = tenant_setting('finance.default_category_id');

        // Buscar categoria de receita se não houver padrão
        if (!$categoryId) {
            $category = FinancialCategory::where('type', 'income')
                ->where('active', true)
                ->first();
            $categoryId = $category?->id;
        }

        return DB::transaction(function () use ($appointment, $amount, $accountId, $categoryId) {
            $transaction = FinancialTransaction::create([
                'type' => 'income',
                'origin_type' => 'appointment',
                'origin_id' => $appointment->id,
                'direction' => 'credit',
                'description' => $this->generateAppointmentDescription($appointment),
                'amount' => $amount,
                'gross_amount' => $amount,
                'gateway_fee' => 0, // Sem billing, não há taxa
                'net_amount' => $amount,
                'date' => $appointment->starts_at->toDateString(),
                'status' => 'paid', // Receita de agendamento é considerada paga
                'account_id' => $accountId,
                'category_id' => $categoryId,
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->calendar?->doctor_id,
                'created_by' => auth()->id(),
            ]);

            // Atualizar saldo da conta se existir
            if ($accountId) {
                $this->updateAccountBalance($accountId);
            }

            Log::info('Receita de agendamento registrada', [
                'transaction_id' => $transaction->id,
                'appointment_id' => $appointment->id,
                'amount' => $amount,
            ]);

            return $transaction;
        });
    }

    /**
     * Registra receita manual
     * 
     * @param array $data
     * @return FinancialTransaction
     */
    public function recordManualIncome(array $data): FinancialTransaction
    {
        return DB::transaction(function () use ($data) {
            $grossAmount = $data['gross_amount'] ?? $data['amount'];
            $gatewayFee = $data['gateway_fee'] ?? 0;
            $netAmount = $grossAmount - $gatewayFee;
            
            $transaction = FinancialTransaction::create([
                'type' => 'income',
                'origin_type' => $data['origin_type'] ?? 'manual',
                'origin_id' => $data['origin_id'] ?? null,
                'direction' => 'credit',
                'description' => $data['description'] ?? 'Receita manual',
                'amount' => $netAmount,
                'gross_amount' => $grossAmount,
                'gateway_fee' => $gatewayFee,
                'net_amount' => $netAmount,
                'date' => $data['date'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'pending',
                'account_id' => $data['account_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'appointment_id' => $data['appointment_id'] ?? null,
                'patient_id' => $data['patient_id'] ?? null,
                'doctor_id' => $data['doctor_id'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Atualizar saldo se status = paid
            if ($transaction->status === 'paid' && $transaction->account_id) {
                $this->updateAccountBalance($transaction->account_id);
            }

            return $transaction;
        });
    }

    /**
     * Registra despesa manual
     * 
     * @param array $data
     * @return FinancialTransaction
     */
    public function recordExpense(array $data): FinancialTransaction
    {
        return DB::transaction(function () use ($data) {
            $grossAmount = $data['gross_amount'] ?? $data['amount'];
            $gatewayFee = $data['gateway_fee'] ?? 0;
            $netAmount = $grossAmount - $gatewayFee;
            
            $transaction = FinancialTransaction::create([
                'type' => 'expense',
                'origin_type' => $data['origin_type'] ?? 'manual',
                'origin_id' => $data['origin_id'] ?? null,
                'direction' => 'debit',
                'description' => $data['description'] ?? 'Despesa manual',
                'amount' => $netAmount,
                'gross_amount' => $grossAmount,
                'gateway_fee' => $gatewayFee,
                'net_amount' => $netAmount,
                'date' => $data['date'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'pending',
                'account_id' => $data['account_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Atualizar saldo se status = paid
            if ($transaction->status === 'paid' && $transaction->account_id) {
                $this->updateAccountBalance($transaction->account_id);
            }

            return $transaction;
        });
    }

    /**
     * Registra estorno (refund)
     * 
     * @param FinancialTransaction $originalTransaction
     * @param string|null $reason
     * @return FinancialTransaction
     */
    public function recordRefund(FinancialTransaction $originalTransaction, ?string $reason = null): FinancialTransaction
    {
        if ($originalTransaction->type !== 'income') {
            throw new \InvalidArgumentException('Apenas receitas podem ser estornadas');
        }

        return DB::transaction(function () use ($originalTransaction, $reason) {
            $refund = FinancialTransaction::create([
                'type' => 'expense',
                'origin_type' => 'refund',
                'origin_id' => $originalTransaction->id,
                'direction' => 'debit',
                'description' => $reason ?? "Estorno: {$originalTransaction->description}",
                'amount' => $originalTransaction->net_amount ?? $originalTransaction->amount,
                'gross_amount' => $originalTransaction->gross_amount ?? $originalTransaction->amount,
                'gateway_fee' => 0, // Estorno não tem taxa adicional
                'net_amount' => $originalTransaction->net_amount ?? $originalTransaction->amount,
                'date' => now()->toDateString(),
                'status' => 'paid',
                'account_id' => $originalTransaction->account_id,
                'category_id' => $originalTransaction->category_id,
                'appointment_id' => $originalTransaction->appointment_id,
                'patient_id' => $originalTransaction->patient_id,
                'doctor_id' => $originalTransaction->doctor_id,
                'created_by' => auth()->id(),
            ]);

            // Atualizar saldo da conta
            if ($refund->account_id) {
                $this->updateAccountBalance($refund->account_id);
            }

            Log::info('Estorno registrado', [
                'refund_id' => $refund->id,
                'original_transaction_id' => $originalTransaction->id,
            ]);

            return $refund;
        });
    }

    /**
     * Calcula valor do agendamento baseado nas configurações
     * 
     * @param Appointment $appointment
     * @return float
     */
    protected function calculateAppointmentAmount(Appointment $appointment): float
    {
        $billingMode = tenant_setting('finance.billing_mode', 'disabled');

        if ($billingMode === 'disabled') {
            return 0;
        }

        // Modo global
        if ($billingMode === 'global') {
            $globalType = tenant_setting('finance.global_billing_type', 'full');
            if ($globalType === 'reservation') {
                return (float) tenant_setting('finance.reservation_amount', '0.00');
            }
            return (float) tenant_setting('finance.full_appointment_amount', '0.00');
        }

        // Modo por médico ou médico/especialidade
        if ($billingMode === 'per_doctor' || $billingMode === 'per_doctor_specialty') {
            $doctorId = $appointment->calendar?->doctor_id;
            $specialtyId = $billingMode === 'per_doctor_specialty' ? $appointment->specialty_id : null;

            if (!$doctorId) {
                return 0;
            }

            $doctorPrice = \App\Models\Tenant\DoctorBillingPrice::findPrice($doctorId, $specialtyId);
            
            if ($doctorPrice) {
                if ($doctorPrice->full_appointment_amount > 0) {
                    return (float) $doctorPrice->full_appointment_amount;
                }
                if ($doctorPrice->reservation_amount > 0) {
                    return (float) $doctorPrice->reservation_amount;
                }
            }

            return 0;
        }

        // Modos legados
        if ($billingMode === 'reservation') {
            return (float) tenant_setting('finance.reservation_amount', '0.00');
        }
        if ($billingMode === 'full') {
            return (float) tenant_setting('finance.full_appointment_amount', '0.00');
        }

        return 0;
    }

    /**
     * Gera descrição para transação de agendamento
     * 
     * @param Appointment $appointment
     * @return string
     */
    protected function generateAppointmentDescription(Appointment $appointment): string
    {
        $doctor = $appointment->calendar?->doctor?->user?->name ?? 'Médico';
        $patient = $appointment->patient->full_name ?? 'Paciente';
        $date = $appointment->starts_at->format('d/m/Y H:i');

        return "Consulta - {$doctor} / {$patient} ({$date})";
    }

    /**
     * Atualiza saldo da conta baseado nas transações pagas
     * 
     * @param string $accountId
     * @return void
     */
    protected function updateAccountBalance(string $accountId): void
    {
        // O saldo é calculado dinamicamente via accessor no model
        // Este método pode ser usado para cache ou outras operações futuras
    }
}

