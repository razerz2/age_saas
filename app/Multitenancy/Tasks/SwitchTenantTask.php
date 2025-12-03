<?php

namespace App\Multitenancy\Tasks;

use App\Models\Platform\Tenant;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SwitchTenantTask extends SwitchTenantDatabaseTask
{
    public function makeCurrent(SpatieTenant $tenant): void
    {
        Log::info("🟥 SwitchTenantTask::makeCurrent() DISPARADO", [
            'recebido_id'  => $tenant->id,
            'recebido_tipo' => gettype($tenant->id),
            'recebido_obj' => $tenant,
            'caller'       => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8),
        ]);

        $rawId = $tenant->id;

        if (!is_string($rawId) || strlen($rawId) < 10) {
            Log::error("❗ ID INVÁLIDO ENVIADO PARA makeCurrent()", [
                'id_recebido' => $rawId,
                'tipo'        => gettype($rawId),
            ]);
            return;
        }

        $platformTenant = Tenant::find($rawId);

        if (!$platformTenant) {
            Log::error("❗ UUID NÃO EXISTE NA TABELA tenants", [
                'uuid' => $rawId
            ]);
            return;
        }

        Log::info("✅ SwitchTenantTask: Tenant REAL carregado", [
            'uuid' => $platformTenant->id,
            'slug' => $platformTenant->subdomain,
        ]);

        // Valida se as credenciais essenciais estão presentes
        if (empty($platformTenant->db_name)) {
            Log::error("❗ Nome do banco de dados do tenant está vazio", [
                'tenant_id' => $platformTenant->id
            ]);
            return;
        }

        if (empty($platformTenant->db_username)) {
            Log::error("❗ Usuário do banco de dados do tenant está vazio", [
                'tenant_id' => $platformTenant->id
            ]);
            return;
        }

        // Usar host e porta do tenant, com fallback para .env se não estiver definido
        $dbHost = $platformTenant->db_host ?: env('DB_TENANT_HOST', '127.0.0.1');
        $dbPort = $platformTenant->db_port ?: env('DB_TENANT_PORT', '5432');

        // Verificar se as credenciais estão sendo passadas corretamente
        Log::info("🔧 Verificando as credenciais para a conexão com o banco", [
            'host' => $dbHost,
            'port' => $dbPort,
            'database' => $platformTenant->db_name,
            'username' => $platformTenant->db_username,
            'password_set' => !empty($platformTenant->db_password),
        ]);

        // Primeiro purga a conexão
        DB::purge('tenant');

        // Agora, configura os parâmetros corretamente usando os valores do tenant
        Config::set('database.connections.tenant.host', $dbHost);
        Config::set('database.connections.tenant.port', $dbPort);
        Config::set('database.connections.tenant.database', $platformTenant->db_name);
        Config::set('database.connections.tenant.username', $platformTenant->db_username);
        // Garante que a senha seja uma string (mesmo que vazia, mas não null)
        Config::set('database.connections.tenant.password', $platformTenant->db_password ?? '');


        Log::info("🔧 Conexão configurada para tenant", [
            'db' => config('database.connections.tenant')
        ]);

        // Depuração - imprime as configurações de conexão
        //dd(config('database.connections.tenant'));

        // Reconnecta à nova configuração
        DB::reconnect('tenant');

        Log::info("🎯 SwitchTenantTask finalizado para UUID {$platformTenant->id}");
    }

    public function forgetCurrent(): void
    {
        Log::info("🔵 SwitchTenantTask::forgetCurrent() executado");

        DB::purge('tenant');
        DB::setDefaultConnection(config('database.default'));
        DB::reconnect();
    }
}
