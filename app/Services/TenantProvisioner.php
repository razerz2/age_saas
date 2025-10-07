<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use PDO;

class TenantProvisioner
{
    /**
     * Cria o banco, usuário e executa as migrations do tenant.
     */
    public static function createDatabase($tenant)
    {
        try {
            Log::info("Criando banco: {$tenant->db_name}");

            // Criar banco e usuário no Postgres principal
            DB::connection('pgsql')->statement("CREATE DATABASE \"{$tenant->db_name}\"");
            DB::connection('pgsql')->statement("CREATE USER {$tenant->db_user} WITH PASSWORD '{$tenant->db_password_enc}'");
            DB::connection('pgsql')->statement("GRANT ALL PRIVILEGES ON DATABASE \"{$tenant->db_name}\" TO {$tenant->db_user}");

            // Tornar o usuário do tenant dono do banco
            DB::connection('pgsql')->statement("ALTER DATABASE \"{$tenant->db_name}\" OWNER TO {$tenant->db_user}");

            // DSN seguro (port opcional)
            $port = $tenant->db_port ?: 5432;
            $dsn = "pgsql:host={$tenant->db_host};port={$port};dbname={$tenant->db_name}";

            // Conectar no DB recém-criado como admin e alterar schema
            $pdo = new PDO(
                $dsn,
                env('DB_USERNAME', 'postgres'), // admin do .env
                env('DB_PASSWORD', 'secret')
            );
            $pdo->exec("ALTER SCHEMA public OWNER TO {$tenant->db_user}");

            // Configurar conexão dinâmica
            config([
                'database.connections.tenant.host' => $tenant->db_host,
                'database.connections.tenant.port' => $port,
                'database.connections.tenant.database' => $tenant->db_name,
                'database.connections.tenant.username' => $tenant->db_user,
                'database.connections.tenant.password' => $tenant->db_password_enc,
            ]);

            Log::info("Rodando migrations no banco {$tenant->db_name}");

            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--database' => 'tenant',
                '--force' => true,
            ]);

            Log::info("✅ Banco {$tenant->db_name}, usuário {$tenant->db_user} e migrations criados com sucesso!");
        } catch (\Throwable $e) {
            Log::error("Erro ao criar banco do tenant", [
                'tenant' => $tenant->id,
                'erro'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Exclui o banco, usuário e registro do tenant.
     */
    public static function destroyTenant($tenant)
    {
        try {
            Log::info("🔹 Removendo tenant {$tenant->id}");

            // 1. Derrubar conexões e excluir banco
            DB::connection('pgsql')->statement("
                SELECT pg_terminate_backend(pid)
                FROM pg_stat_activity
                WHERE datname = '{$tenant->db_name}'
            ");
            DB::connection('pgsql')->statement("DROP DATABASE IF EXISTS \"{$tenant->db_name}\"");

            // 2. Revogar privilégios globais
            DB::connection('pgsql')->statement("REVOKE ALL PRIVILEGES ON SCHEMA public FROM {$tenant->db_user}");
            DB::connection('pgsql')->statement("REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA public FROM {$tenant->db_user}");
            DB::connection('pgsql')->statement("REVOKE ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public FROM {$tenant->db_user}");
            DB::connection('pgsql')->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE ALL ON TABLES FROM {$tenant->db_user}");
            DB::connection('pgsql')->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE ALL ON SEQUENCES FROM {$tenant->db_user}");

            // 3. Excluir usuário
            DB::connection('pgsql')->statement("DROP ROLE IF EXISTS {$tenant->db_user}");

            // 4. Excluir registro do tenant
            $tenant->delete();

            Log::info("✅ Tenant {$tenant->id} removido com sucesso (DB + User + Registro).");
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao excluir tenant", [
                'tenant' => $tenant->id,
                'erro'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    /**
     * Edita o banco, usuário e registro do tenant.
     */
    public static function updateTenant($tenant, array $data)
    {
        try {
            Log::info("🔹 Atualizando tenant {$tenant->id}");

            // 1. Alterar senha do usuário do banco
            if (!empty($data['db_password_enc']) && $data['db_password_enc'] !== $tenant->db_password_enc) {
                DB::connection('pgsql')->statement("ALTER USER {$tenant->db_user} WITH PASSWORD '{$data['db_password_enc']}'");
                $tenant->db_password_enc = $data['db_password_enc'];
                Log::info("Senha do usuário {$tenant->db_user} alterada.");
            }

            // 2. Renomear banco de dados
            if (!empty($data['db_name']) && $data['db_name'] !== $tenant->db_name) {
                DB::connection('pgsql')->statement("ALTER DATABASE \"{$tenant->db_name}\" RENAME TO \"{$data['db_name']}\"");
                $tenant->db_name = $data['db_name'];
                Log::info("Banco renomeado para {$data['db_name']}.");
            }

            // 3. Renomear usuário do banco
            if (!empty($data['db_user']) && $data['db_user'] !== $tenant->db_user) {
                // Criar novo usuário
                DB::connection('pgsql')->statement("CREATE USER {$data['db_user']} WITH PASSWORD '{$tenant->db_password_enc}'");
                DB::connection('pgsql')->statement("GRANT ALL PRIVILEGES ON DATABASE \"{$tenant->db_name}\" TO {$data['db_user']}");
                DB::connection('pgsql')->statement("ALTER DATABASE \"{$tenant->db_name}\" OWNER TO {$data['db_user']}");

                // Dropar usuário antigo
                DB::connection('pgsql')->statement("DROP ROLE IF EXISTS {$tenant->db_user}");

                $tenant->db_user = $data['db_user'];
                Log::info("Usuário do banco renomeado para {$data['db_user']}.");
            }

            // 4. Atualizar demais campos do tenant
            $tenant->fill($data);
            $tenant->save();

            Log::info("✅ Tenant {$tenant->id} atualizado com sucesso!");
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao atualizar tenant", [
                'tenant' => $tenant->id,
                'erro'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
