<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TenantProvisioner
{
    /**
     * Cria o banco, o usuário do tenant, executa as migrations e cria o admin padrão.
     * Retorna a senha gerada para o admin.
     * 
     * @return string Senha gerada para o usuário admin
     */
    public static function createDatabase($tenant): string
    {
        try {
            Log::info("🔧 Iniciando criação do banco para tenant {$tenant->id}");

            // --------------------------------------------------------------------
            // 0. Gerar senha aleatória para o admin (mínimo 10 caracteres com letras, números e símbolos)
            // --------------------------------------------------------------------
            $adminPassword = self::generateSecurePassword();
            
            // Passar senha para o seeder através de config runtime
            config(['tenant.admin_password' => $adminPassword]);

            // --------------------------------------------------------------------
            // 1. Criar banco e usuário no Postgres (conexão principal)
            // 🔒 Com verificação de idempotência para evitar erros em webhooks duplicados
            // --------------------------------------------------------------------
            
            // Verificar se o banco já existe
            try {
                $dbExists = DB::connection('pgsql')->selectOne("
                    SELECT 1 FROM pg_database WHERE datname = ?
                ", [$tenant->db_name]);
            } catch (\Throwable $e) {
                // Se houver erro na consulta, assume que não existe
                $dbExists = null;
            }
            
            if ($dbExists) {
                Log::info("ℹ️ Banco de dados {$tenant->db_name} já existe. Pulando criação do banco.", [
                    'tenant_id' => $tenant->id,
                    'db_name' => $tenant->db_name,
                ]);
            } else {
                try {
                    DB::connection('pgsql')->statement("CREATE DATABASE \"{$tenant->db_name}\"");
                    Log::info("✅ Banco de dados {$tenant->db_name} criado com sucesso.", [
                        'tenant_id' => $tenant->id,
                    ]);
                } catch (\Throwable $e) {
                    // Se o erro for que o banco já existe, apenas loga
                    if (str_contains($e->getMessage(), 'already exists') || 
                        str_contains($e->getMessage(), 'duplicate')) {
                        Log::info("ℹ️ Banco de dados {$tenant->db_name} já existe (erro capturado).", [
                            'tenant_id' => $tenant->id,
                        ]);
                    } else {
                        throw $e; // Re-lança se for outro tipo de erro
                    }
                }
            }
            
            // Verificar se o usuário já existe
            try {
                $userExists = DB::connection('pgsql')->selectOne("
                    SELECT 1 FROM pg_user WHERE usename = ?
                ", [$tenant->db_username]);
            } catch (\Throwable $e) {
                // Se houver erro na consulta, assume que não existe
                $userExists = null;
            }
            
            if ($userExists) {
                Log::info("ℹ️ Usuário {$tenant->db_username} já existe. Atualizando senha e permissões.", [
                    'tenant_id' => $tenant->id,
                    'db_username' => $tenant->db_username,
                ]);
                // Atualizar senha do usuário existente
                try {
                    DB::connection('pgsql')->statement("ALTER USER {$tenant->db_username} WITH PASSWORD '{$tenant->db_password}'");
                } catch (\Throwable $e) {
                    Log::warning("⚠️ Erro ao atualizar senha do usuário (pode ser normal): {$e->getMessage()}");
                }
            } else {
                try {
                    DB::connection('pgsql')->statement("CREATE USER {$tenant->db_username} WITH PASSWORD '{$tenant->db_password}'");
                    Log::info("✅ Usuário {$tenant->db_username} criado com sucesso.", [
                        'tenant_id' => $tenant->id,
                    ]);
                } catch (\Throwable $e) {
                    // Se o erro for que o usuário já existe, apenas loga
                    if (str_contains($e->getMessage(), 'already exists') || 
                        str_contains($e->getMessage(), 'duplicate')) {
                        Log::info("ℹ️ Usuário {$tenant->db_username} já existe (erro capturado).", [
                            'tenant_id' => $tenant->id,
                        ]);
                    } else {
                        throw $e; // Re-lança se for outro tipo de erro
                    }
                }
            }
            
            // Sempre garantir permissões (idempotente)
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
            // 4. Executar migrations do tenant (idempotente - só executa o que falta)
            // --------------------------------------------------------------------
            Log::info("📦 Executando migrations do tenant...");

            try {
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path'     => 'database/migrations/tenant',
                    '--force'    => true,
                ]);

                Log::info("✅ Migrations executadas com sucesso!", [
                    'tenant_id' => $tenant->id,
                    'output'    => Artisan::output(),
                ]);
            } catch (\Throwable $e) {
                // Se já existem migrations aplicadas, isso é esperado e não é erro crítico
                if (str_contains($e->getMessage(), 'already exists') || 
                    str_contains($e->getMessage(), 'duplicate key')) {
                    Log::info("ℹ️ Migrations já aplicadas anteriormente. Continuando...", [
                        'tenant_id' => $tenant->id,
                    ]);
                } else {
                    throw $e; // Re-lança se for outro tipo de erro
                }
            }

            // --------------------------------------------------------------------
            // 5. Criar usuário admin do tenant diretamente (idempotente)
            // --------------------------------------------------------------------
            Log::info("👤 Verificando/criando usuário admin padrão...");

            try {
                // Limpar subdomínio para formato válido de domínio
                $domain = Str::slug($tenant->subdomain);
                $domain = preg_replace('/[^a-z0-9\-]/', '', $domain);
                $domain = !empty($domain) ? $domain : 'tenant';

                // Gerar email dinâmico
                $email = "admin@{$domain}.com";

                // 🔒 Verificar se o usuário admin já existe
                $adminExists = DB::connection('tenant')
                    ->table('users')
                    ->where('email', $email)
                    ->orWhere('role', 'admin')
                    ->exists();

                if ($adminExists) {
                    Log::info("ℹ️ Usuário admin já existe para tenant {$tenant->id}. Pulando criação.", [
                        'email' => $email,
                        'tenant_id' => $tenant->id,
                    ]);
                } else {
                    // Inserir usuário admin diretamente no banco do tenant usando a senha gerada
                    DB::connection('tenant')->table('users')->insert([
                        'tenant_id'  => $tenant->id,
                        'name'       => 'Administrador',
                        'name_full'  => 'Administrador do Sistema',
                        'telefone'   => '00000000000',
                        'email'      => $email,
                        'password'   => Hash::make($adminPassword),
                        'is_doctor'  => false,
                        'status'     => 'active',
                        'role'       => 'admin',
                        'modules'    => json_encode([]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info("🟢 Usuário admin criado para tenant {$tenant->id}", [
                        'email' => $email
                    ]);
                }
            } catch (\Throwable $e) {
                // Se o erro for de duplicata, apenas loga e continua
                if (str_contains($e->getMessage(), 'duplicate key') || 
                    str_contains($e->getMessage(), 'already exists')) {
                    Log::info("ℹ️ Usuário admin já existe. Continuando...", [
                        'tenant_id' => $tenant->id,
                    ]);
                } else {
                    Log::error("❌ Erro ao criar usuário admin", [
                        'erro' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            // --------------------------------------------------------------------
            // 6. Copiar especialidades médicas do catálogo da platform
            // --------------------------------------------------------------------
            Log::info("🏥 Copiando especialidades médicas do catálogo...");

            try {
                // Buscar todas as especialidades médicas do catálogo da platform
                $catalogSpecialties = DB::connection('pgsql')
                    ->table('medical_specialties_catalog')
                    ->where('type', 'medical_specialty')
                    ->orderBy('name')
                    ->get();

                if ($catalogSpecialties->isEmpty()) {
                    Log::warning("⚠️ Nenhuma especialidade médica encontrada no catálogo da platform.");
                } else {
                    // 🚀 Otimização: Inserção em massa (Bulk Insert)
                    // Filtra apenas as que não existem para ser seguro (idempotência)
                    $existingIds = DB::connection('tenant')
                        ->table('medical_specialties')
                        ->pluck('id')
                        ->toArray();

                    $toInsert = [];
                    foreach ($catalogSpecialties as $catalog) {
                        if (in_array($catalog->id, $existingIds)) {
                            continue;
                        }

                        $toInsert[] = [
                            'id'         => $catalog->id,
                            'name'       => $catalog->name,
                            'code'       => $catalog->code,
                            'created_at' => $catalog->created_at ?? now(),
                            'updated_at' => $catalog->updated_at ?? now(),
                        ];
                    }

                    if (!empty($toInsert)) {
                        // Divide em pedaços de 50 para evitar limites do driver
                        foreach (array_chunk($toInsert, 50) as $chunk) {
                            DB::connection('tenant')->table('medical_specialties')->insert($chunk);
                        }
                    }

                    Log::info("🟢 Especialidades médicas copiadas para tenant {$tenant->id}", [
                        'inseridas' => count($toInsert),
                        'ignoradas' => count($existingIds),
                        'total_no_catalogo' => $catalogSpecialties->count(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error("❌ Erro ao copiar especialidades médicas", [
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            // --------------------------------------------------------------------
            // 7. Finalização
            // --------------------------------------------------------------------
            Log::info("🏁 Banco do tenant criado com sucesso!", [
                'tenant_id' => $tenant->id,
                'dbname'    => $tenant->db_name,
                'dbuser'    => $tenant->db_username
            ]);

            // Retornar a senha gerada
            return $adminPassword;
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
     * Gera uma senha segura com mínimo 10 caracteres, incluindo letras, números e símbolos.
     */
    private static function generateSecurePassword(int $length = 12): string
    {
        // Garantir mínimo de 10 caracteres
        $length = max(10, $length);
        
        // Caracteres permitidos
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        // Garantir pelo menos um de cada tipo
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        // Preencher o restante com caracteres aleatórios
        $allChars = $lowercase . $uppercase . $numbers . $symbols;
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Embaralhar a senha
        return str_shuffle($password);
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
