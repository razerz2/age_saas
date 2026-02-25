<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use App\Services\Tenant\CampaignAutomationRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Throwable;

class RunAutomatedCampaigns extends Command
{
    protected $signature = 'campaigns:run-automated {--tenant=* : Tenant id/subdomain (pode repetir)} {--dry-run : Apenas simula elegibilidade sem criar lock/run}';
    protected $description = 'Run automated campaigns for eligible tenants';

    public function __construct(
        private readonly CampaignAutomationRunner $automationRunner
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

        $this->info(sprintf(
            'Iniciando automações de campanhas para %d tenant(s)%s...',
            $tenants->count(),
            $dryRun ? ' (dry-run)' : ''
        ));

        $totals = $this->emptyStats();
        $tenantErrors = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenant->makeCurrent();

                $stats = $this->automationRunner->runForTenant($tenant, $dryRun);
                $totals = $this->mergeStats($totals, $stats);

                $this->line(sprintf(
                    '[%s] evaluated=%d eligible=%d started=%d dry=%d locked=%d channels=%d invalid=%d schedule=%d errors=%d',
                    $tenant->subdomain ?: $tenant->id,
                    $stats['evaluated'],
                    $stats['eligible'],
                    $stats['started'],
                    $stats['dry_run'],
                    $stats['skipped_locked'],
                    $stats['skipped_channels'],
                    $stats['skipped_invalid'],
                    $stats['skipped_schedule'],
                    $stats['errors'],
                ));
            } catch (Throwable $exception) {
                $tenantErrors++;
                Log::error('campaign_automation_tenant_failed', [
                    'tenant_id' => $tenant->id,
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
            ['Métrica', 'Valor'],
            [
                ['Tenants processados', (string) $tenants->count()],
                ['Tenants com erro', (string) $tenantErrors],
                ['Campanhas avaliadas', (string) $totals['evaluated']],
                ['Campanhas elegíveis', (string) $totals['eligible']],
                ['Runs iniciados', (string) $totals['started']],
                ['Dry-run simuladas', (string) $totals['dry_run']],
                ['Pulos por lock', (string) $totals['skipped_locked']],
                ['Pulos por canais indisponíveis', (string) $totals['skipped_channels']],
                ['Pulos por config inválida', (string) $totals['skipped_invalid']],
                ['Pulos por janela de horário', (string) $totals['skipped_schedule']],
                ['Erros em campanha', (string) $totals['errors']],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * @param array<int,mixed> $tenantFilters
     * @return Collection<int,Tenant>
     */
    private function resolveTenants(array $tenantFilters): Collection
    {
        $filters = collect($tenantFilters)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values();

        $tenants = Tenant::query()
            ->where('status', 'active')
            ->orderBy('subdomain')
            ->get();

        if ($filters->isEmpty()) {
            return $tenants;
        }

        return $tenants->filter(function (Tenant $tenant) use ($filters) {
            return $filters->contains((string) $tenant->id)
                || $filters->contains((string) $tenant->subdomain);
        })->values();
    }

    /**
     * @return array{
     *   evaluated:int,eligible:int,started:int,dry_run:int,
     *   skipped_invalid:int,skipped_schedule:int,skipped_channels:int,
     *   skipped_locked:int,errors:int
     * }
     */
    private function emptyStats(): array
    {
        return [
            'evaluated' => 0,
            'eligible' => 0,
            'started' => 0,
            'dry_run' => 0,
            'skipped_invalid' => 0,
            'skipped_schedule' => 0,
            'skipped_channels' => 0,
            'skipped_locked' => 0,
            'errors' => 0,
        ];
    }

    /**
     * @param array<string,int> $left
     * @param array<string,int> $right
     * @return array<string,int>
     */
    private function mergeStats(array $left, array $right): array
    {
        foreach ($left as $key => $value) {
            $left[$key] = $value + (int) ($right[$key] ?? 0);
        }

        return $left;
    }
}
