<?php

namespace App\Observers;

use App\Models\Tenant\RecurringAppointment;
use App\Services\Tenant\GoogleCalendarService;
use Illuminate\Support\Facades\Log;

class RecurringAppointmentObserver
{
    protected GoogleCalendarService $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Handle the RecurringAppointment "created" event.
     */
    public function created(RecurringAppointment $recurring): void
    {
        // Carregar relacionamentos necessários
        $recurring->load(['doctor', 'patient', 'appointmentType', 'rules']);

        // Sincronizar com Google Calendar como evento recorrente
        // IMPORTANTE: Isso cria um evento recorrente no Google Calendar
        // Para recorrências sem data fim, usa data fim padrão de 1 ano
        try {
            $this->googleCalendarService->syncRecurringEvent($recurring);
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar recorrência com Google Calendar (Observer)', [
                'recurring_id' => $recurring->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the RecurringAppointment "updated" event.
     */
    public function updated(RecurringAppointment $recurring): void
    {
        // Carregar relacionamentos necessários
        $recurring->load(['doctor', 'patient', 'appointmentType', 'rules']);

        // Se recorrência foi desativada (cancelada), atualizar data fim para hoje
        // Isso mantém eventos passados como histórico e remove apenas eventos futuros
        if ($recurring->wasChanged('active') && !$recurring->active) {
            try {
                $this->googleCalendarService->cancelRecurringEvent($recurring);
            } catch (\Exception $e) {
                Log::error('Erro ao cancelar recorrência no Google Calendar (Observer)', [
                    'recurring_id' => $recurring->id,
                    'error' => $e->getMessage(),
                ]);
            }
            return;
        }

        // Se recorrência foi reativada, criar eventos novamente
        if ($recurring->wasChanged('active') && $recurring->active) {
            try {
                $this->googleCalendarService->syncRecurringEvent($recurring);
            } catch (\Exception $e) {
                Log::error('Erro ao sincronizar recorrência reativada com Google Calendar (Observer)', [
                    'recurring_id' => $recurring->id,
                    'error' => $e->getMessage(),
                ]);
            }
            return;
        }

        // Para outras mudanças relevantes (edição), deletar e criar novo
        // ESTRATÉGIA: Deletar eventos antigos e criar novos (mais simples e confiável)
        if ($recurring->wasChanged(['start_date', 'end_date', 'end_type', 'total_sessions'])) {
            try {
                // Deletar eventos antigos primeiro
                $this->googleCalendarService->deleteRecurringEvent($recurring);
                
                // Criar novos eventos com informações atualizadas
                $this->googleCalendarService->syncRecurringEvent($recurring);
            } catch (\Exception $e) {
                Log::error('Erro ao atualizar recorrência no Google Calendar (Observer)', [
                    'recurring_id' => $recurring->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the RecurringAppointment "deleted" event.
     */
    public function deleted(RecurringAppointment $recurring): void
    {
        // Remover evento recorrente do Google Calendar
        try {
            $this->googleCalendarService->deleteRecurringEvent($recurring);
        } catch (\Exception $e) {
            Log::error('Erro ao remover recorrência do Google Calendar (Observer)', [
                'recurring_id' => $recurring->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

