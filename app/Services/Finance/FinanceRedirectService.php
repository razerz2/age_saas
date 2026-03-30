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
        $origin = (string) ($appointment->origin ?? Appointment::ORIGIN_INTERNAL);
        $isPortalLikeOrigin = in_array($origin, [Appointment::ORIGIN_PORTAL, Appointment::ORIGIN_WHATSAPP_BOT], true);

        if ($origin === Appointment::ORIGIN_PUBLIC) {
            if (tenant_setting('finance.charge_on_public_appointment') !== 'true') {
                return false;
            }
        } elseif ($isPortalLikeOrigin) {
            if (tenant_setting('finance.charge_on_patient_portal') !== 'true') {
                return false;
            }
        } elseif ($origin === Appointment::ORIGIN_INTERNAL) {
            // Agendamentos internos NUNCA redirecionam
            return false;
        } else {
            Log::warning('Origem de agendamento desconhecida para redirecionamento financeiro', [
                'appointment_id' => $appointment->id,
                'origin' => $origin,
            ]);
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
