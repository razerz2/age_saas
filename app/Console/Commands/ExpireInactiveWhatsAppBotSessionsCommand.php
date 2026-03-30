<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use App\Services\Tenant\WhatsAppBot\WhatsAppBotInactivityTimeoutService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Throwable;

class ExpireInactiveWhatsAppBotSessionsCommand extends Command
{
    protected $signature = 'whatsapp-bot:expire-inactive-sessions
                            {--tenant=* : Tenant id/subdomain (pode repetir)}
                            {--dry-run : Apenas simula sem enviar mensagens nem persistir}';

    protected $description = 'Encerra sessoes inativas do bot de WhatsApp por tenant e envia mensagem de inatividade';

    public function __construct(
        private readonly WhatsAppBotInactivityTimeoutService $timeoutService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $tenants = $this->resolveTenants((array) $this->option('tenant'));

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant ativo encontrado para processar.');
            return self::SUCCESS;
        }

        $totals = [
            'tenants' => 0,
            'errors' => 0,
            'candidates' => 0,
            'processed' => 0,
            'sent' => 0,
            'failed_send' => 0,
            'skipped' => 0,
            'retry_scheduled' => 0,
        ];

        foreach ($tenants as $tenant) {
            try {
                $tenant->makeCurrent();
                $totals['tenants']++;

                $stats = $this->timeoutService->sweepCurrentTenant($dryRun);

                $totals['candidates'] += (int) ($stats['candidates'] ?? 0);
                $totals['processed'] += (int) ($stats['processed'] ?? 0);
                $totals['sent'] += (int) ($stats['sent'] ?? 0);
                $totals['failed_send'] += (int) ($stats['failed_send'] ?? 0);
                $totals['skipped'] += (int) ($stats['skipped'] ?? 0);
                $totals['retry_scheduled'] += (int) ($stats['retry_scheduled'] ?? 0);

                $this->line(sprintf(
                    '[%s] candidates=%d processed=%d sent=%d failed_send=%d retry_scheduled=%d skipped=%d dry_run=%s',
                    $tenant->subdomain ?: $tenant->id,
                    (int) ($stats['candidates'] ?? 0),
                    (int) ($stats['processed'] ?? 0),
                    (int) ($stats['sent'] ?? 0),
                    (int) ($stats['failed_send'] ?? 0),
                    (int) ($stats['retry_scheduled'] ?? 0),
                    (int) ($stats['skipped'] ?? 0),
                    ((bool) ($stats['dry_run'] ?? false)) ? '1' : '0'
                ));
            } catch (Throwable $exception) {
                $totals['errors']++;

                Log::error('whatsapp_bot.inactivity.command_failed', [
                    'tenant_id' => (string) $tenant->id,
                    'tenant_subdomain' => (string) ($tenant->subdomain ?? ''),
                    'error' => $exception->getMessage(),
                ]);

                $this->error(sprintf(
                    '[%s] falha ao processar tenant: %s',
                    $tenant->subdomain ?: $tenant->id,
                    $exception->getMessage()
                ));
            } finally {
                SpatieTenant::forgetCurrent();
            }
        }

        $this->newLine();
        $this->table(
            ['Metrica', 'Valor'],
            [
                ['Tenants processados', (string) $totals['tenants']],
                ['Tenants com erro', (string) $totals['errors']],
                ['Sessoes candidatas', (string) $totals['candidates']],
                ['Sessoes encerradas', (string) $totals['processed']],
                ['Mensagens enviadas', (string) $totals['sent']],
                ['Falhas de envio', (string) $totals['failed_send']],
                ['Retry agendado', (string) $totals['retry_scheduled']],
                ['Sessoes ignoradas', (string) $totals['skipped']],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * @param array<int, mixed> $tenantFilters
     * @return Collection<int, Tenant>
     */
    private function resolveTenants(array $tenantFilters): Collection
    {
        $filters = collect($tenantFilters)
            ->map(static fn ($item): string => trim((string) $item))
            ->filter()
            ->values();

        $tenants = Tenant::query()
            ->where('status', 'active')
            ->orderBy('subdomain')
            ->get();

        if ($filters->isEmpty()) {
            return $tenants;
        }

        return $tenants->filter(function (Tenant $tenant) use ($filters): bool {
            return $filters->contains((string) $tenant->id)
                || $filters->contains((string) $tenant->subdomain);
        })->values();
    }
}
