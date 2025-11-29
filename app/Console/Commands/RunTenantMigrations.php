<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class RunTenantMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate {--tenant= : ID ou subdomain do tenant específico} {--all : Executar em todos os tenants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa migrations pendentes nos bancos dos tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantOption = $this->option('tenant');
        $allOption = $this->option('all');

        if (!$tenantOption && !$allOption) {
            $this->error('Você precisa especificar --tenant=ID ou --all para executar em todos os tenants.');
            return 1;
        }

        $tenants = [];

        if ($allOption) {
            $tenants = Tenant::all();
            $this->info("Executando migrations em todos os {$tenants->count()} tenants...");
        } else {
            // Busca primeiro por subdomain, depois por ID
            $tenant = Tenant::where('subdomain', $tenantOption)->first();
            if (!$tenant) {
                $tenant = Tenant::where('id', $tenantOption)->first();
            }

            if (!$tenant) {
                $this->error("Tenant não encontrado: {$tenantOption}");
                return 1;
            }

            $tenants = collect([$tenant]);
            $this->info("Executando migrations no tenant: {$tenant->subdomain} ({$tenant->id})");
        }

        foreach ($tenants as $tenant) {
            $this->info("\n📦 Processando tenant: {$tenant->subdomain}");

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
                DB::connection('tenant')->getPdo();
                $this->info("✅ Conexão estabelecida: {$tenant->db_name}");

                // Executar migrations
                $this->info("🔄 Executando migrations...");
                
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path'     => 'database/migrations/tenant',
                    '--force'    => true,
                ]);

                $output = Artisan::output();
                
                if (!empty(trim($output))) {
                    $this->line($output);
                }

                $this->info("✅ Migrations executadas com sucesso para {$tenant->subdomain}");

            } catch (\Exception $e) {
                $this->error("❌ Erro ao executar migrations no tenant {$tenant->subdomain}:");
                $this->error($e->getMessage());
                continue;
            }
        }

        $this->info("\n🎉 Concluído!");
        return 0;
    }
}
