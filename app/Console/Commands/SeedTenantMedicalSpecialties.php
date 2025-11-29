<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;

class SeedTenantMedicalSpecialties extends Command
{
    protected $signature = 'tenant:seed-specialties {tenant?} {--force} {--list}';

    protected $description = 'Executa o seeder de especialidades médicas para uma tenant específica. Use --list para ver todas as tenants.';

    public function handle()
    {
        // Se --list foi passado ou nenhum tenant foi informado, lista as tenants
        if ($this->option('list') || !$this->argument('tenant')) {
            return $this->listTenants();
        }

        $tenantIdentifier = $this->argument('tenant');
        $force = $this->option('force');

        // Verifica se é um UUID válido ou busca por subdomain
        $tenant = null;
        
        // Valida se é um UUID válido (formato: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx)
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $tenantIdentifier)) {
            // Busca por ID (UUID)
            $tenant = Tenant::where('id', $tenantIdentifier)->first();
        }
        
        // Se não encontrou por UUID ou não é UUID, busca por subdomain
        if (!$tenant) {
            $tenant = Tenant::where('subdomain', $tenantIdentifier)->first();
        }

        if (!$tenant) {
            $this->error("❌ Tenant não encontrada: {$tenantIdentifier}");
            $this->info("💡 Tente usar o ID UUID ou o subdomain da tenant.");
            return Command::FAILURE;
        }

        $this->info("🔍 Tenant encontrada: {$tenant->trade_name} ({$tenant->subdomain})");
        $this->info("📋 ID: {$tenant->id}");

        // Configura a conexão do tenant
        $this->info("⚙️ Configurando conexão do tenant...");

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
            $this->info("✅ Conexão com banco do tenant estabelecida!");
        } catch (\Throwable $e) {
            $this->error("❌ Erro ao conectar no banco do tenant: {$e->getMessage()}");
            return Command::FAILURE;
        }

        // Verifica se já existem especialidades (opcional)
        if (!$force) {
            $existingCount = DB::connection('tenant')
                ->table('medical_specialties')
                ->count();

            if ($existingCount > 0) {
                if (!$this->confirm("⚠️ Já existem {$existingCount} especialidades no banco. Deseja continuar? (duplicatas serão ignoradas)")) {
                    $this->info("❌ Operação cancelada.");
                    return Command::FAILURE;
                }
            }
        }

        // Configura variáveis para o seeder
        config([
            'tenant.current_subdomain' => $tenant->subdomain,
            'tenant.current_id'        => $tenant->id,
        ]);

        // Executa o seeder
        $this->info("🏥 Executando seeder de especialidades médicas...");

        try {
            Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--class'    => 'Database\\Seeders\\Tenant\\TenantMedicalSpecialtiesSeeder',
                '--force'    => true,
            ]);

            $output = Artisan::output();
            if (!empty(trim($output))) {
                $this->line($output);
            }

            // Conta quantas foram inseridas
            $finalCount = DB::connection('tenant')
                ->table('medical_specialties')
                ->count();

            $this->info("✅ Seeder executado com sucesso!");
            $this->info("📊 Total de especialidades médicas no banco: {$finalCount}");

            Log::info("✅ Seeder de especialidades médicas executado manualmente para tenant", [
                'tenant_id' => $tenant->id,
                'tenant_subdomain' => $tenant->subdomain,
                'total_especialidades' => $finalCount,
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Erro ao executar seeder: {$e->getMessage()}");
            Log::error("❌ Erro ao executar seeder de especialidades para tenant", [
                'tenant_id' => $tenant->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Lista todas as tenants disponíveis
     */
    private function listTenants(): int
    {
        $tenants = Tenant::orderBy('trade_name')->get();

        if ($tenants->isEmpty()) {
            $this->info("ℹ️ Nenhuma tenant encontrada.");
            return Command::SUCCESS;
        }

        $this->info("📋 Tenants disponíveis:\n");
        
        $headers = ['#', 'Subdomain', 'Trade Name', 'Legal Name', 'Status', 'Database'];
        $rows = [];

        foreach ($tenants as $index => $tenant) {
            $rows[] = [
                $index + 1,
                $tenant->subdomain,
                $tenant->trade_name ?? '-',
                $tenant->legal_name,
                $tenant->status,
                $tenant->db_name ?? '-',
            ];
        }

        $this->table($headers, $rows);

        $this->info("\n💡 Para executar o seeder, use:");
        $this->line("   php artisan tenant:seed-specialties {subdomain}");
        $this->line("   ou");
        $this->line("   php artisan tenant:seed-specialties {uuid}");

        return Command::SUCCESS;
    }
}
