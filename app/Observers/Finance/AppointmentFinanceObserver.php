<?php

namespace App\Observers\Finance;

use App\Models\Tenant\Appointment;
use App\Services\Finance\FinanceRecorderService;
use App\Services\Billing\BillingService;
use Illuminate\Support\Facades\Log;

/**
 * Observer para processar eventos financeiros de agendamentos
 * 
 * REGRAS:
 * - Se finance.enabled = false, não executa nada
 * - Se finance.billing.enabled = false, apenas registra receita (sem cobrança)
 * - Se finance.billing.enabled = true, cria cobrança via BillingService
 * - Nunca chama Asaas diretamente
 * - Nunca cria FinancialTransaction diretamente
 */
class AppointmentFinanceObserver
{
    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        // Regra absoluta: se módulo desabilitado, não executar nada
        if (tenant_setting('finance.enabled') !== 'true') {
            return;
        }

        try {
            // Se billing está desabilitado, apenas registra receita
            if (tenant_setting('finance.billing.enabled') !== 'true') {
                app(FinanceRecorderService::class)
                    ->recordAppointmentIncome($appointment);
                return;
            }

            // Se billing está habilitado, cria cobrança
            app(BillingService::class)
                ->createChargeForAppointment($appointment);
        } catch (\Throwable $e) {
            // Nunca lançar exceção não tratada - falha financeira não deve quebrar o agendamento
            Log::error('Erro ao processar evento financeiro do agendamento', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        // Por enquanto, não processamos atualizações
        // Isso pode ser expandido no futuro se necessário
    }
}

