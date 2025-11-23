<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TenantProvisioner
{
    /**
     * Cria o banco, o usuário do tenant, executa as migrations e cria o admin padrão.
     */
    public static function createDatabase($tenant)
    {
        try {
            Log::info("🔧 Iniciando criação do banco para tenant {$tenant->id}");

            // --------------------------------------------------------------------
            // 1. Criar banco e usuário no Postgres (conexão principal)
            // --------------------------------------------------------------------
            DB::connection('pgsql')->statement("CREATE DATABASE \"{$tenant->db_name}\"");
            DB::connection('pgsql')->statement("CREATE USER {$tenant->db_username} WITH PASSWORD '{$tenant->db_password}'");
            DB::connection('pgsql')->statement("GRANT ALL PRIVILEGES ON DATABASE \"{$tenant->db_name}\" TO {$tenant->db_username}");
            DB::connection('pgsql')->statement("ALTER DATABASE \"{$tenant->db_name}\" OWNER TO {$tenant->db_username}");

            // --------------------------------------------------------------------
            // 2. Ajustar permissões do schema PUBLIC
            // --------------------------------------------------------------------
            $dsn = "pgsql:host={$tenant->db_host};port={$tenant->db_port};dbname={$tenant->db_name}";
            $pdo = new \PDO($dsn, env('DB_USERNAME'), env('DB_PASSWORD'));

            $pdo->exec("ALTER SCHEMA public OWNER TO {$tenant->db_username}");
            $pdo->exec("GRANT ALL PRIVILEGES ON SCHEMA public TO {$tenant->db_username}");
            $pdo->exec("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO {$tenant->db_username}");
            $pdo->exec("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO {$tenant->db_username}");

            // --------------------------------------------------------------------
            // 3. Configurar conexão dinâmica do tenant
            // --------------------------------------------------------------------
            config([
                'database.connections.tenant.host'     => $tenant->db_host,
                'database.connections.tenant.port'     => $tenant->db_port,
                'database.connections.tenant.database' => $tenant->db_name,
                'database.connections.tenant.username' => $tenant->db_username,
                'database.connections.tenant.password' => $tenant->db_password,
            ]);

            // 🔥 ESSENCIAL — limpar cache e reconectar
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Testar conexão real
            try {
                DB::connection('tenant')->getPdo();
                Log::info("🟢 Conexão com banco do tenant OK: {$tenant->db_name}");
            } catch (Throwable $e) {
                Log::error("🔴 Falha ao conectar no banco recém-criado", [
                    'erro' => $e->getMessage(),
                    'tenant' => $tenant->id
                ]);
                throw $e;
            }

            // --------------------------------------------------------------------
            // 4. Executar migrations do tenant
            // --------------------------------------------------------------------
            Log::info("📦 Executando migrations do tenant...");

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path'     => 'database/migrations/tenant',
                '--force'    => true,
            ]);

            Log::info("✅ Migrations executadas com sucesso!", [
                'tenant_id' => $tenant->id,
                'output'    => Artisan::output(),
            ]);

            // --------------------------------------------------------------------
            // 5. Criar usuário admin do tenant via seeder
            // --------------------------------------------------------------------
            Log::info("👤 Criando usuário admin padrão...");

            // Envia o subdomínio para a seeder via config()
            // Envia o subdomínio e o TENANT ID real para a seeder
            config([
                'tenant.current_subdomain' => $tenant->subdomain,
                'tenant.current_id'        => $tenant->id,  // <-- ADICIONADO
            ]);

            Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--class'    => 'Database\\Seeders\\Tenant\\TenantAdminSeeder',
                '--force'    => true,
            ]);

            Log::info("🟢 Usuário admin criado para tenant {$tenant->id}");

            // --------------------------------------------------------------------
            // 6. Finalização
            // --------------------------------------------------------------------
            Log::info("🏁 Banco do tenant criado com sucesso!", [
                'tenant_id' => $tenant->id,
                'dbname'    => $tenant->db_name,
                'dbuser'    => $tenant->db_username
            ]);
        } catch (Throwable $e) {
            Log::error("❌ ERRO FATAL na criação do banco do tenant", [
                'tenant_id' => $tenant->id,
                'erro'      => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Gera parâmetros de banco antes de salvar o tenant
     */
    public static function prepareDatabaseConfig(string $legalName, ?string $tradeName = null): array
    {
        $slug = Str::slug($tradeName ?: $legalName, '_');

        return [
            'db_host'     => env('DB_HOST', '127.0.0.1'),
            'db_port'     => env('DB_PORT', 5432),
            'db_name'     => 'db_' . $slug,
            'db_username' => 'usr_' . $slug,
            'db_password' => Str::random(16),
        ];
    }

    /**
     * Remove tenant (DB + USER + registro)
     */
    public static function destroyTenant($tenant)
    {
        try {
            Log::info("🧹 Removendo tenant {$tenant->id}");

            DB::connection('pgsql')->statement("
                SELECT pg_terminate_backend(pid)
                FROM pg_stat_activity
                WHERE datname = '{$tenant->db_name}'
            ");

            DB::connection('pgsql')->statement("DROP DATABASE IF EXISTS \"{$tenant->db_name}\"");
            DB::connection('pgsql')->statement("DROP ROLE IF EXISTS {$tenant->db_username}");

            $tenant->delete();

            Log::info("🟢 Tenant removido com sucesso!");
        } catch (Throwable $e) {
            Log::error("❌ Erro ao excluir tenant:", [
                'tenant' => $tenant->id,
                'erro'   => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
