<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class RunTenantMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate {--tenant= : ID específico do tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa migrations pendentes em todos os tenants ou em um tenant específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            // Executa apenas para um tenant específico
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("Tenant com ID {$tenantId} não encontrado.");
                return 1;
            }
            $this->runMigrationForTenant($tenant);
        } else {
            // Executa para todos os tenants
            $tenants = Tenant::all();
            $this->info("Executando migrations para {$tenants->count()} tenant(s)...");

            foreach ($tenants as $tenant) {
                $this->runMigrationForTenant($tenant);
            }
        }

        $this->info("✅ Migrations executadas com sucesso!");
        return 0;
    }

    private function runMigrationForTenant(Tenant $tenant)
    {
        $this->info("📦 Executando migrations para tenant: {$tenant->subdomain} ({$tenant->id})");

        try {
            // Configura conexão do tenant
            config([
                'database.connections.tenant.host'     => $tenant->db_host,
                'database.connections.tenant.port'     => $tenant->db_port,
                'database.connections.tenant.database' => $tenant->db_name,
                'database.connections.tenant.username' => $tenant->db_username,
                'database.connections.tenant.password' => $tenant->db_password,
            ]);

            // Limpa cache e reconecta
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Testa conexão
            DB::connection('tenant')->getPdo();

            // Executa migrations
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path'     => 'database/migrations/tenant',
                '--force'    => true,
            ]);

            $this->info("✅ Migrations executadas para {$tenant->subdomain}");
        } catch (\Exception $e) {
            $this->error("❌ Erro ao executar migrations para {$tenant->subdomain}: {$e->getMessage()}");
        }
    }
}

