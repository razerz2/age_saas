<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MigrateAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate-all 
                            {--path= : Caminho específico das migrations (padrão: database/migrations/tenant)}
                            {--pretend : Mostrar o que seria executado sem executar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa migrations pendentes em TODAS as tenants existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $migrationPath = $this->option('path') ?: 'database/migrations/tenant';
        $pretend = $this->option('pretend');

        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn("⚠️  Nenhum tenant encontrado.");
            return 0;
        }

        $this->info("🚀 Executando migrations em {$tenants->count()} tenant(s)...");
        $this->newLine();

        $stats = [
            'total' => $tenants->count(),
            'success' => 0,
            'failed' => 0,
        ];

        // Barra de progresso
        $bar = $this->output->createProgressBar($tenants->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
        $bar->setMessage('Iniciando...');
        $bar->start();

        foreach ($tenants as $tenant) {
            $bar->setMessage("Processando: {$tenant->subdomain}");
            
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
                    $stats['failed']++;
                    $bar->advance();
                    continue;
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

                $stats['success']++;

            } catch (\Exception $e) {
                $stats['failed']++;
                $this->newLine();
                $this->error("  ❌ Erro em {$tenant->subdomain}: {$e->getMessage()}");
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
        $this->info("📊 Estatísticas:");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de tenants', $stats['total']],
                ['✅ Sucesso', $stats['success']],
                ['❌ Falhas', $stats['failed']],
            ]
        );

        if ($stats['failed'] === 0) {
            $this->info("🎉 Todas as migrations foram executadas com sucesso!");
        } else {
            $this->warn("⚠️  Algumas migrations falharam. Verifique os logs para mais detalhes.");
        }

        return $stats['failed'] > 0 ? 1 : 0;
    }
}

