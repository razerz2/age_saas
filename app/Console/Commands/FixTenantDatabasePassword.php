<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\DB;

class FixTenantDatabasePassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:fix-db-password {slug : O subdomain do tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza a senha do usuário PostgreSQL do tenant para corresponder à senha armazenada no banco';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $slug = $this->argument('slug');
        
        $tenant = Tenant::where('subdomain', $slug)->first();
        
        if (!$tenant) {
            $this->error("Tenant '{$slug}' não encontrado.");
            return 1;
        }
        
        $this->info("Atualizando senha do PostgreSQL para o tenant: {$tenant->subdomain}");
        $this->info("Usuário: {$tenant->db_username}");
        $this->info("Banco: {$tenant->db_name}");
        
        try {
            // Escapar a senha corretamente para PostgreSQL (duplicar aspas simples)
            $escapedPassword = str_replace("'", "''", $tenant->db_password);
            
            // Atualizar senha do usuário PostgreSQL
            // Nota: PostgreSQL não suporta prepared statements para ALTER USER, então precisamos escapar manualmente
            $sql = "ALTER USER \"{$tenant->db_username}\" WITH PASSWORD '{$escapedPassword}'";
            DB::connection('pgsql')->statement($sql);
            
            $this->info("✅ Senha do PostgreSQL atualizada com sucesso!");
            
            // Aguardar um pouco para o PostgreSQL processar a alteração
            $this->info("Aguardando processamento...");
            sleep(2);
            
            // Testar conexão
            $this->info("Testando conexão...");
            
            \Config::set('database.connections.tenant.host', $tenant->db_host ?: '127.0.0.1');
            \Config::set('database.connections.tenant.port', $tenant->db_port ?: '5432');
            \Config::set('database.connections.tenant.database', $tenant->db_name);
            \Config::set('database.connections.tenant.username', $tenant->db_username);
            \Config::set('database.connections.tenant.password', $tenant->db_password ?? '');
            
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            $pdo = DB::connection('tenant')->getPdo();
            $this->info("✅ Conexão testada com sucesso!");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erro ao atualizar senha: {$e->getMessage()}");
            return 1;
        }
    }
}
