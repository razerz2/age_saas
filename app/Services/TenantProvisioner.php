<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDO;

class TenantProvisioner
{
    /**
     * Cria o banco, usuário e executa as migrations do tenant.
     */
    public static function createDatabase($tenant)
    {
        try {
            Log::info("🔧 Criando banco para tenant {$tenant->id}: {$tenant->db_name}");

            DB::connection('pgsql')->statement("CREATE DATABASE \"{$tenant->db_name}\"");
            DB::connection('pgsql')->statement("CREATE USER {$tenant->db_username} WITH PASSWORD '{$tenant->db_password}'");
            DB::connection('pgsql')->statement("GRANT ALL PRIVILEGES ON DATABASE \"{$tenant->db_name}\" TO {$tenant->db_username}");
            DB::connection('pgsql')->statement("ALTER DATABASE \"{$tenant->db_name}\" OWNER TO {$tenant->db_username}");

            $dsn = "pgsql:host={$tenant->db_host};port={$tenant->db_port};dbname={$tenant->db_name}";
            $pdo = new \PDO($dsn, env('DB_USERNAME', 'postgres'), env('DB_PASSWORD', 'secret'));
            $pdo->exec("ALTER SCHEMA public OWNER TO {$tenant->db_username}");

            config([
                'database.connections.tenant.host'     => $tenant->db_host,
                'database.connections.tenant.port'     => $tenant->db_port,
                'database.connections.tenant.database' => $tenant->db_name,
                'database.connections.tenant.username' => $tenant->db_username,
                'database.connections.tenant.password' => $tenant->db_password,
            ]);

            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--database' => 'tenant',
                '--force' => true,
            ]);

            Log::info("✅ Banco {$tenant->db_name} criado com sucesso!", [
                'tenant_id' => $tenant->id,
                'db_user'   => $tenant->db_username,
                'db_pass'   => $tenant->db_password,
                'db_host'   => $tenant->db_host,
                'db_port'   => $tenant->db_port,
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao criar banco do tenant", [
                'tenant' => $tenant->id,
                'erro'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Gera os dados de conexão (db_name, db_username, db_password, etc)
     * sem salvar no banco.
     */
    public static function prepareDatabaseConfig(string $legalName, ?string $tradeName = null): array
    {
        $slug = Str::slug($tradeName ?: $legalName, '_');

        return [
            'db_host'     => env('DB_HOST', '127.0.0.1'),
            'db_port'     => env('DB_PORT', 5432),
            'db_name'     => 'db_' . $slug,
            'db_username' => 'usr_' . $slug,
            'db_password' => Str::random(16), // senha em texto puro, conforme definido
        ];
    }

    /**
     * Remove banco e usuário do tenant.
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

            Log::info("✅ Tenant {$tenant->id} removido com sucesso (DB + User + Registro).");
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao excluir tenant", [
                'tenant' => $tenant->id ?? 'sem_id',
                'erro'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Atualiza dados do tenant (alterar senha ou renomear banco/usuário).
     */
    public static function updateTenant($tenant, array $data)
    {
        try {
            Log::info("🔹 Atualizando tenant {$tenant->id}");

            if (!empty($data['db_password']) && $data['db_password'] !== $tenant->db_password) {
                DB::connection('pgsql')->statement("ALTER USER {$tenant->db_username} WITH PASSWORD '{$data['db_password']}'");
                $tenant->db_password = $data['db_password'];
                Log::info("🔑 Senha do usuário {$tenant->db_username} alterada.");
            }

            if (!empty($data['db_name']) && $data['db_name'] !== $tenant->db_name) {
                DB::connection('pgsql')->statement("ALTER DATABASE \"{$tenant->db_name}\" RENAME TO \"{$data['db_name']}\"");
                $tenant->db_name = $data['db_name'];
                Log::info("🏷️ Banco renomeado para {$data['db_name']}.");
            }

            if (!empty($data['db_username']) && $data['db_username'] !== $tenant->db_username) {
                DB::connection('pgsql')->statement("CREATE USER {$data['db_username']} WITH PASSWORD '{$tenant->db_password}'");
                DB::connection('pgsql')->statement("GRANT ALL PRIVILEGES ON DATABASE \"{$tenant->db_name}\" TO {$data['db_username']}");
                DB::connection('pgsql')->statement("ALTER DATABASE \"{$tenant->db_name}\" OWNER TO {$data['db_username']}");
                DB::connection('pgsql')->statement("DROP ROLE IF EXISTS {$tenant->db_username}");

                $tenant->db_username = $data['db_username'];
                Log::info("👤 Usuário do banco renomeado para {$data['db_username']}.");
            }

            $tenant->fill($data);
            $tenant->save();

            Log::info("✅ Tenant {$tenant->id} atualizado com sucesso!");
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao atualizar tenant", [
                'tenant' => $tenant->id ?? 'sem_id',
                'erro'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
