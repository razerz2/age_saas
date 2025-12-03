<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RunTenantMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate 
                            {--tenant= : ID ou subdomain do tenant específico}
                            {--all : Executar em todos os tenants (padrão se nenhuma opção for fornecida)}
                            {--path= : Caminho específico das migrations (padrão: database/migrations/tenant)}
                            {--pretend : Mostrar o que seria executado sem executar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa migrations pendentes nos bancos dos tenants';

    /**
     * Estatísticas da execução
     */
    private $stats = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'skipped' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantOption = $this->option('tenant');
        $allOption = $this->option('all');
        $migrationPath = $this->option('path') ?: 'database/migrations/tenant';
        $pretend = $this->option('pretend');

        // Se nenhuma opção foi fornecida, assume --all por padrão
        if (!$tenantOption && !$allOption) {
            $allOption = true;
        }

        $tenants = [];

        if ($allOption) {
            $tenants = Tenant::all();
            $this->info("🚀 Executando migrations em todos os {$tenants->count()} tenants...");
            $this->newLine();
        } else {
            // Busca primeiro por subdomain, depois por ID
            $tenant = Tenant::where('subdomain', $tenantOption)->first();
            if (!$tenant) {
                $tenant = Tenant::where('id', $tenantOption)->first();
            }

            if (!$tenant) {
                $this->error("❌ Tenant não encontrado: {$tenantOption}");
                return 1;
            }

            $tenants = collect([$tenant]);
            $this->info("🚀 Executando migrations no tenant: {$tenant->subdomain} ({$tenant->id})");
            $this->newLine();
        }

        if ($tenants->isEmpty()) {
            $this->warn("⚠️  Nenhum tenant encontrado para processar.");
            return 0;
        }

        $this->stats['total'] = $tenants->count();

        // Barra de progresso
        $bar = $this->output->createProgressBar($tenants->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
        $bar->setMessage('Iniciando...');
        $bar->start();

        foreach ($tenants as $tenant) {
            $bar->setMessage("Processando: {$tenant->subdomain}");
            
            try {
                $result = $this->runMigrationForTenant($tenant, $migrationPath, $pretend);
                
                if ($result) {
                    $this->stats['success']++;
                } else {
                    $this->stats['skipped']++;
                }

            } catch (\Exception $e) {
                $this->stats['failed']++;
                Log::error("Erro ao executar migrations no tenant {$tenant->subdomain}", [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            $bar->advance();
        }

        $bar->setMessage('Concluído!');
        $bar->finish();
        $this->newLine(2);

        // Exibir estatísticas
        $this->displayStats();

        return $this->stats['failed'] > 0 ? 1 : 0;
    }

    /**
     * Executa migrations para um tenant específico
     *
     * @param Tenant $tenant
     * @param string $migrationPath
     * @param bool $pretend
     * @return bool
     */
    private function runMigrationForTenant(Tenant $tenant, string $migrationPath, bool $pretend = false): bool
    {
        try {
            // Configurar conexão do tenant
            config([
                'database.connections.tenant.host'     => $tenant->db_host,
                'database.connections.tenant.port'     => $tenant->db_port,
                'database.connections.tenant.database' => $tenant->db_name,
                'database.connections.tenant.username' => $tenant->db_username,
                'database.connections.tenant.password' => $tenant->db_password,
            ]);

            // Limpar cache e reconectar
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Testar conexão
            try {
                DB::connection('tenant')->getPdo();
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("  ❌ Falha na conexão com {$tenant->subdomain}: {$e->getMessage()}");
                return false;
            }

            // Executar migrations
            $options = [
                '--database' => 'tenant',
                '--path'     => $migrationPath,
                '--force'    => true,
            ];

            if ($pretend) {
                $options['--pretend'] = true;
            }

            Artisan::call('migrate', $options);

            $output = Artisan::output();
            
            // Se houver output e não estiver em modo pretend, mostra detalhes
            if (!empty(trim($output)) && !$pretend) {
                $this->newLine();
                $this->line("  📋 {$tenant->subdomain}:");
                $this->line("     " . str_replace("\n", "\n     ", trim($output)));
            }

            return true;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("  ❌ Erro em {$tenant->subdomain}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Exibe estatísticas da execução
     */
    private function displayStats(): void
    {
        $this->info("📊 Estatísticas da execução:");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de tenants', $this->stats['total']],
                ['✅ Sucesso', $this->stats['success']],
                ['⚠️  Ignorados', $this->stats['skipped']],
                ['❌ Falhas', $this->stats['failed']],
            ]
        );

        if ($this->stats['failed'] === 0) {
            $this->info("🎉 Todas as migrations foram executadas com sucesso!");
        } else {
            $this->warn("⚠️  Algumas migrations falharam. Verifique os logs para mais detalhes.");
        }
    }
}
