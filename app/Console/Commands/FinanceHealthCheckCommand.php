<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use App\Services\Finance\FinanceHealthCheckService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinanceHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:health-check 
                            {--tenant= : Slug do tenant específico}
                            {--json : Saída em JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica saúde do módulo financeiro';

    /**
     * Execute the console command.
     */
    public function handle(FinanceHealthCheckService $healthCheck)
    {
        $tenantSlug = $this->option('tenant');
        $jsonOutput = $this->option('json');

        $tenants = $tenantSlug 
            ? [Tenant::where('subdomain', $tenantSlug)->firstOrFail()]
            : Tenant::where('status', 'active')->get();

        $results = [];

        foreach ($tenants as $tenant) {
            $tenant->makeCurrent();

            // Verificar se módulo está habilitado
            if (tenant_setting('finance.enabled') !== 'true') {
                if (!$jsonOutput) {
                    $this->info("Tenant: {$tenant->subdomain} - Módulo desabilitado");
                }
                continue;
            }

            $health = $healthCheck->runAll();

            if ($jsonOutput) {
                $results[$tenant->subdomain] = $health;
            } else {
                $this->displayHealthCheck($tenant->subdomain, $health);
            }
        }

        if ($jsonOutput) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
        }

        return Command::SUCCESS;
    }

    /**
     * Exibe resultado do health check formatado
     */
    protected function displayHealthCheck(string $tenant, array $health): void
    {
        $this->info("=== Tenant: {$tenant} ===");

        // Webhook
        $webhook = $health['webhook'];
        $status = $webhook['status'] === 'healthy' ? '✓' : '✗';
        $this->line("{$status} Webhook: {$webhook['success']}/{$webhook['total']} sucesso, {$webhook['error_rate']}% erro");

        // Queue
        $queue = $health['queue'];
        $status = $queue['status'] === 'healthy' ? '✓' : '✗';
        $this->line("{$status} Fila: {$queue['pending_jobs']} pendentes, {$queue['failed_jobs_24h']} falhas");

        // Asaas
        $asaas = $health['asaas_connectivity'];
        $status = $asaas['status'] === 'configured' ? '✓' : '✗';
        $this->line("{$status} Asaas: {$asaas['environment']}");

        // Inconsistências
        $inconsistencies = $health['pending_inconsistencies'];
        $status = $inconsistencies['status'] === 'healthy' ? '✓' : '✗';
        $this->line("{$status} Inconsistências: {$inconsistencies['total_issues']} problemas encontrados");

        if ($inconsistencies['total_issues'] > 0) {
            foreach ($inconsistencies['issues'] as $issue) {
                $this->warn("  - {$issue['type']}: {$issue['count']} ({$issue['severity']})");
            }
        }

        $this->newLine();
    }
}

