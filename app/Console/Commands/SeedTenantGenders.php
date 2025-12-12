<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeedTenantGenders extends Command
{
    protected $signature = 'tenant:seed-genders {tenant?} {--all : Executar em todos os tenants} {--force}';

    protected $description = 'Executa o seeder de gêneros para uma tenant específica ou todas.';

    public function handle()
    {
        $tenantIdentifier = $this->argument('tenant');
        $allOption = $this->option('all');
        $force = $this->option('force');

        $tenants = [];

        if ($allOption || !$tenantIdentifier) {
            $tenants = Tenant::all();
            $this->info("🚀 Executando seeder de gêneros em todos os {$tenants->count()} tenants...");
        } else {
            // Busca primeiro por subdomain, depois por ID
            $tenant = Tenant::where('subdomain', $tenantIdentifier)->first();
            if (!$tenant) {
                $tenant = Tenant::where('id', $tenantIdentifier)->first();
            }

            if (!$tenant) {
                $this->error("❌ Tenant não encontrado: {$tenantIdentifier}");
                return Command::FAILURE;
            }

            $tenants = collect([$tenant]);
            $this->info("🚀 Executando seeder de gêneros no tenant: {$tenant->subdomain}");
        }

        if ($tenants->isEmpty()) {
            $this->warn("⚠️  Nenhum tenant encontrado para processar.");
            return Command::FAILURE;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($tenants as $tenant) {
            $this->info("📋 Processando: {$tenant->subdomain} ({$tenant->id})");

            try {
                // Configura a conexão do tenant
                config([
                    'database.connections.tenant.host'     => $tenant->db_host,
                    'database.connections.tenant.port'     => $tenant->db_port,
                    'database.connections.tenant.database' => $tenant->db_name,
                    'database.connections.tenant.username' => $tenant->db_username,
                    'database.connections.tenant.password' => $tenant->db_password,
                ]);

                DB::purge('tenant');
                DB::reconnect('tenant');

                // Testa a conexão
                try {
                    DB::connection('tenant')->getPdo();
                } catch (\Throwable $e) {
                    $this->error("  ❌ Falha na conexão: {$e->getMessage()}");
                    $failCount++;
                    continue;
                }

                // Verifica se já existem gêneros (opcional)
                if (!$force) {
                    $existingCount = DB::connection('tenant')
                        ->table('genders')
                        ->count();

                    if ($existingCount > 0) {
                        $this->warn("  ⚠️ Já existem {$existingCount} gêneros no banco. Duplicatas serão ignoradas.");
                    }
                }

                // Configura variáveis para o seeder
                config([
                    'tenant.current_subdomain' => $tenant->subdomain,
                    'tenant.current_id'        => $tenant->id,
                ]);

                // Executa o seeder
                Artisan::call('db:seed', [
                    '--database' => 'tenant',
                    '--class'    => 'Database\\Seeders\\Tenant\\GenderSeeder',
                    '--force'    => true,
                ]);

                $output = Artisan::output();
                if (!empty(trim($output))) {
                    $this->line("  " . trim($output));
                }

                // Conta quantos foram inseridos
                $finalCount = DB::connection('tenant')
                    ->table('genders')
                    ->count();

                $this->info("  ✅ Seeder executado com sucesso! Total de gêneros: {$finalCount}");
                $successCount++;

                Log::info("✅ Seeder de gêneros executado para tenant", [
                    'tenant_id' => $tenant->id,
                    'tenant_subdomain' => $tenant->subdomain,
                    'total_genders' => $finalCount,
                ]);

            } catch (\Throwable $e) {
                $this->error("  ❌ Erro: {$e->getMessage()}");
                $failCount++;
                Log::error("❌ Erro ao executar seeder de gêneros para tenant", [
                    'tenant_id' => $tenant->id,
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            $this->newLine();
        }

        // Estatísticas finais
        $this->info("📊 Estatísticas:");
        $this->info("  ✅ Sucesso: {$successCount}");
        $this->info("  ❌ Falhas: {$failCount}");

        return $failCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
