<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Tenant\RecurringAppointment;
use App\Services\Tenant\GoogleCalendarService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RenewRecurringGoogleCalendarEventsCommand extends Command
{
    protected $signature = 'google-calendar:renew-recurring-events';
    protected $description = 'Renova eventos recorrentes no Google Calendar que estão próximos do fim (para recorrências sem data fim)';

    public function handle()
    {
        $this->info("🔄 Iniciando renovação de eventos recorrentes no Google Calendar...");

        $tenants = Tenant::all();
        $totalRenewed = 0;
        $totalErrors = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenant->makeCurrent();
                
                $this->info("📋 Processando tenant: {$tenant->name}");

                $renewed = $this->processTenantRecurringEvents($tenant);
                $totalRenewed += $renewed['renewed'];
                $totalErrors += $renewed['errors'];

            } catch (\Exception $e) {
                Log::error("Erro ao processar tenant {$tenant->id}: " . $e->getMessage());
                $totalErrors++;
                continue;
            }
        }

        $this->info("✅ Renovação concluída!");
        $this->info("   - Eventos renovados: {$totalRenewed}");
        $this->info("   - Erros: {$totalErrors}");

        return Command::SUCCESS;
    }

    private function processTenantRecurringEvents(Tenant $tenant): array
    {
        $renewed = 0;
        $errors = 0;

        // Buscar recorrências ativas sem data fim que têm eventos no Google Calendar
        $recurringAppointments = RecurringAppointment::with(['doctor', 'rules'])
            ->where('active', true)
            ->where('end_type', 'none') // Apenas recorrências sem data fim
            ->whereNotNull('google_recurring_event_ids')
            ->get();

        $googleCalendarService = app(GoogleCalendarService::class);

        foreach ($recurringAppointments as $recurring) {
            try {
                // Verificar se o médico tem token Google
                if (!$recurring->doctor || !$recurring->doctor->googleCalendarToken) {
                    continue;
                }

                // Verificar se o evento está próximo do fim
                // A data fim do evento é start_date + 1 ano
                // Renovamos quando faltam 30 dias ou menos para a data fim
                $eventEndDate = Carbon::parse($recurring->start_date)->addYear();
                $daysUntilEnd = Carbon::now()->diffInDays($eventEndDate, false); // false = não retorna valor absoluto
                
                // Renovar se faltam 30 dias ou menos (ou já passou)
                if ($daysUntilEnd <= 30) {
                    $this->info("   Renovando recorrência {$recurring->id} (data fim: {$eventEndDate->format('d/m/Y')}, faltam {$daysUntilEnd} dias)");
                    
                    if ($googleCalendarService->renewRecurringEvent($recurring)) {
                        $renewed++;
                    } else {
                        $errors++;
                    }
                }

            } catch (\Exception $e) {
                Log::error("Erro ao renovar recorrência {$recurring->id}: " . $e->getMessage());
                $errors++;
            }
        }

        return [
            'renewed' => $renewed,
            'errors' => $errors,
        ];
    }
}

