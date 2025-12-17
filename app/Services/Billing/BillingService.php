<?php

namespace App\Services\Billing;

use App\Models\Tenant\Appointment;
use App\Models\Tenant\FinancialCharge;
use App\Models\Tenant\DoctorBillingPrice;
use App\Services\Billing\Providers\AsaasBillingProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;

/**
 * Serviço orquestrador de billing
 * 
 * Responsabilidades:
 * - Decidir qual provider usar
 * - Criar FinancialCharge
 * - Chamar provider
 * - Disparar eventos
 * 
 * REGRAS:
 * - Não calcular valores financeiros (usa FinanceRecorderService)
 * - Não criar FinancialTransaction diretamente
 * - Apenas gerencia cobranças externas
 */
class BillingService
{
    protected ?BillingProviderInterface $provider = null;

    /**
     * Obtém o provider configurado
     */
    protected function getProvider(): ?BillingProviderInterface
    {
        if ($this->provider !== null) {
            return $this->provider;
        }

        $providerName = tenant_setting('finance.billing.provider', 'asaas');

        $this->provider = match($providerName) {
            'asaas' => new AsaasBillingProvider(),
            default => null,
        };

        return $this->provider;
    }

    /**
     * Gera ou recupera link de pagamento para uma cobrança
     * 
     * @param FinancialCharge $charge
     * @return string|null
     */
    public function generatePaymentLink(FinancialCharge $charge): ?string
    {
        $provider = $this->getProvider();
        if (!$provider) {
            Log::warning('Provider de billing não configurado para gerar link', [
                'charge_id' => $charge->id,
            ]);
            return null;
        }

        return $provider->generatePaymentLink($charge);
    }

    /**
     * Cria cobrança para um agendamento
     * 
     * @param Appointment $appointment
     * @return FinancialCharge|null
     */
    public function createChargeForAppointment(Appointment $appointment): ?FinancialCharge
    {
        // Verificar se billing está habilitado
        if (tenant_setting('finance.billing.enabled') !== 'true') {
            return null;
        }

        // Verificar se já existe cobrança
        if (FinancialCharge::where('appointment_id', $appointment->id)->exists()) {
            Log::info('Cobrança já existe para o agendamento', [
                'appointment_id' => $appointment->id,
            ]);
            return null;
        }

        // Verificar origem do agendamento
        $origin = $appointment->origin ?? 'internal';
        
        if ($origin === 'public' && tenant_setting('finance.billing.charge_on_public_appointment') !== 'true') {
            return null;
        }

        if ($origin === 'portal' && tenant_setting('finance.billing.charge_on_patient_portal') !== 'true') {
            return null;
        }

        if ($origin === 'internal' && tenant_setting('finance.billing.charge_on_internal_appointment') !== 'true') {
            return null;
        }

        // Determinar valor da cobrança
        $amount = $this->calculateChargeAmount($appointment);
        
        if ($amount <= 0) {
            Log::info('Valor da cobrança é zero ou negativo', [
                'appointment_id' => $appointment->id,
            ]);
            return null;
        }

        $billingType = $this->determineBillingType($appointment);

        try {
            return DB::transaction(function () use ($appointment, $amount, $billingType, $origin) {
                // Criar registro de cobrança
                $charge = FinancialCharge::create([
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'amount' => $amount,
                    'billing_type' => $billingType,
                    'status' => 'pending',
                    'due_date' => $appointment->starts_at->copy()->subDays(1),
                    'origin' => $origin,
                ]);

                // Criar cobrança no provider
                $provider = $this->getProvider();
                if (!$provider) {
                    Log::error('Provider de billing não configurado', [
                        'appointment_id' => $appointment->id,
                    ]);
                    return $charge; // Retorna charge mesmo sem provider
                }

                $result = $provider->createCharge($charge);

                if ($result['error'] ?? false) {
                    Log::error('Erro ao criar cobrança no provider', [
                        'appointment_id' => $appointment->id,
                        'charge_id' => $charge->id,
                        'error' => $result['message'] ?? 'Erro desconhecido',
                    ]);
                    // Não lançar exceção - falha no provider não deve quebrar o agendamento
                    return $charge;
                }

                // Atualizar charge com dados do provider
                $providerData = $result['data'] ?? [];
                $charge->update([
                    'asaas_customer_id' => $providerData['customer'] ?? null,
                    'asaas_charge_id' => $providerData['id'] ?? null,
                    'payment_link' => $providerData['invoiceUrl'] ?? null,
                    'status' => $this->mapProviderStatus($providerData['status'] ?? 'PENDING'),
                ]);

                // Gerar link de pagamento se ainda não foi gerado
                if (!$charge->payment_link && $charge->asaas_charge_id) {
                    $paymentLink = $provider->generatePaymentLink($charge);
                    if ($paymentLink) {
                        $charge->update(['payment_link' => $paymentLink]);
                    }
                }

                // Disparar evento de cobrança criada
                Event::dispatch(new \App\Events\Finance\ChargeCreated($charge));

                // Enviar link de pagamento se configurado
                if ($origin === 'internal' && tenant_setting('finance.billing.auto_send_payment_link') === 'true' && $charge->payment_link) {
                    \App\Services\TenantNotificationService::sendPaymentLink($charge);
                }

                Log::info('Cobrança criada com sucesso', [
                    'appointment_id' => $appointment->id,
                    'charge_id' => $charge->id,
                    'amount' => $amount,
                    'origin' => $origin,
                ]);

                return $charge;
            });
        } catch (\Throwable $e) {
            // Nunca lançar exceção não tratada - falha financeira não deve quebrar o agendamento
            Log::error('Erro ao processar cobrança', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Cancela uma cobrança
     */
    public function cancelCharge(FinancialCharge $charge): bool
    {
        $provider = $this->getProvider();
        
        if ($provider && $charge->asaas_charge_id) {
            $success = $provider->cancelCharge($charge);
            if (!$success) {
                Log::warning('Falha ao cancelar cobrança no provider', [
                    'charge_id' => $charge->id,
                ]);
            }
        }

        $charge->update(['status' => 'cancelled']);

        Event::dispatch(new \App\Events\Finance\ChargeCancelled($charge));

        return true;
    }

    /**
     * Calcula valor da cobrança baseado nas configurações
     */
    protected function calculateChargeAmount(Appointment $appointment): float
    {
        $billingMode = tenant_setting('finance.billing.mode', 'global');

        if ($billingMode === 'global') {
            $globalType = tenant_setting('finance.billing.global_billing_type', 'full');
            if ($globalType === 'reservation') {
                return (float) tenant_setting('finance.billing.reservation_amount', '0.00');
            }
            return (float) tenant_setting('finance.billing.full_appointment_amount', '0.00');
        }

        if ($billingMode === 'per_doctor' || $billingMode === 'per_doctor_specialty') {
            $doctorId = $appointment->calendar?->doctor_id;
            $specialtyId = $billingMode === 'per_doctor_specialty' ? $appointment->specialty_id : null;

            if (!$doctorId) {
                return 0;
            }

            $doctorPrice = DoctorBillingPrice::findPrice($doctorId, $specialtyId);
            
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
            return (float) tenant_setting('finance.billing.reservation_amount', '0.00');
        }
        if ($billingMode === 'full') {
            return (float) tenant_setting('finance.billing.full_appointment_amount', '0.00');
        }

        return 0;
    }

    /**
     * Determina tipo de cobrança (reservation ou full)
     */
    protected function determineBillingType(Appointment $appointment): string
    {
        $billingMode = tenant_setting('finance.billing.mode', 'global');

        if ($billingMode === 'global') {
            return tenant_setting('finance.billing.global_billing_type', 'full');
        }

        if ($billingMode === 'per_doctor' || $billingMode === 'per_doctor_specialty') {
            $doctorId = $appointment->calendar?->doctor_id;
            $specialtyId = $billingMode === 'per_doctor_specialty' ? $appointment->specialty_id : null;

            if ($doctorId) {
                $doctorPrice = DoctorBillingPrice::findPrice($doctorId, $specialtyId);
                if ($doctorPrice) {
                    if ($doctorPrice->full_appointment_amount > 0) {
                        return 'full';
                    }
                    if ($doctorPrice->reservation_amount > 0) {
                        return 'reservation';
                    }
                }
            }
        }

        return 'full';
    }

    /**
     * Mapeia status do provider para status interno
     */
    protected function mapProviderStatus(string $providerStatus): string
    {
        $provider = $this->getProvider();
        
        if ($provider instanceof \App\Services\Billing\Providers\AsaasBillingProvider) {
            return $provider->mapStatus($providerStatus);
        }

        // Fallback genérico
        return match(strtoupper($providerStatus)) {
            'PENDING' => 'pending',
            'RECEIVED', 'CONFIRMED', 'PAID' => 'paid',
            'OVERDUE' => 'expired',
            'REFUNDED', 'CANCELLED' => 'cancelled',
            default => 'pending',
        };
    }
}

