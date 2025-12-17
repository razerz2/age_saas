<?php

namespace App\Services\Finance;

use App\Models\Tenant\Appointment;
use App\Models\Tenant\FinancialCharge;
use Illuminate\Support\Facades\Log;

class FinanceRedirectService
{
    /**
     * Verifica se deve redirecionar para página de pagamento
     * 
     * @param Appointment $appointment
     * @return bool
     */
    public function shouldRedirectToPayment(Appointment $appointment): bool
    {
        // Regra 1: Módulo financeiro deve estar habilitado
        if (tenant_setting('finance.enabled') !== 'true') {
            return false;
        }

        // Regra 2: Billing mode não deve estar desabilitado
        if (tenant_setting('finance.billing_mode') === 'disabled') {
            return false;
        }

        // Regra 3: Deve existir cobrança pendente para o agendamento
        $charge = FinancialCharge::where('appointment_id', $appointment->id)
            ->where('status', 'pending')
            ->first();

        if (!$charge) {
            return false;
        }

        // Regra 4: Verificar se a origem permite cobrança
        $origin = $appointment->origin ?? 'internal';

        if ($origin === 'public') {
            if (tenant_setting('finance.charge_on_public_appointment') !== 'true') {
                return false;
            }
        } elseif ($origin === 'portal') {
            if (tenant_setting('finance.charge_on_patient_portal') !== 'true') {
                return false;
            }
        } elseif ($origin === 'internal') {
            // Agendamentos internos NUNCA redirecionam
            return false;
        }

        // Regra 5: Status da cobrança deve ser pending
        if ($charge->status !== 'pending') {
            return false;
        }

        // Regra 6: Cobrança não deve estar expirada
        if ($charge->isOverdue()) {
            return false;
        }

        return true;
    }

    /**
     * Obtém a cobrança pendente do agendamento
     * 
     * @param Appointment $appointment
     * @return FinancialCharge|null
     */
    public function getPendingCharge(Appointment $appointment): ?FinancialCharge
    {
        return FinancialCharge::where('appointment_id', $appointment->id)
            ->where('status', 'pending')
            ->first();
    }

    /**
     * Verifica se deve enviar link de pagamento automaticamente
     * 
     * @param FinancialCharge $charge
     * @return bool
     */
    public function shouldSendPaymentLink(FinancialCharge $charge): bool
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            return false;
        }

        if (tenant_setting('finance.auto_send_payment_link') !== 'true') {
            return false;
        }

        // Só envia para agendamentos internos
        if ($charge->origin !== 'internal') {
            return false;
        }

        return true;
    }
}

