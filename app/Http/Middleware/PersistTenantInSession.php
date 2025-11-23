<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersistTenantInSession
{
    public function handle(Request $request, Closure $next)
    {
        Log::info("📌 PersistTenantInSession iniciado", [
            'session_slug' => session('tenant_slug'),
            'current_tenant' => Tenant::current()?->id
        ]);

        // Verifica se o tenant já está ativo
        if (Tenant::current()) {
            Log::info("➡️ Tenant já ativo, seguindo.");
            return $next($request);
        }

        // Obtém o slug do tenant da sessão
        $slug = session('tenant_slug');

        // Valida o slug
        if (!is_string($slug) || empty($slug)) {
            Log::warning("⚠️ Session tenant_slug inválido. Removendo.", [
                'slug' => $slug
            ]);
            session()->forget('tenant_slug');
            return $next($request);
        }

        // Busca o tenant pelo slug
        $tenant = Tenant::where('subdomain', $slug)->first();

        // Se encontrar o tenant, ativa e configura o banco de dados
        if ($tenant) {
            Log::info("🔁 Reativando tenant a partir da sessão", [
                'uuid' => $tenant->id,
                'slug' => $tenant->subdomain
            ]);
            $tenant->makeCurrent();

            // Configura a conexão com o banco de dados do tenant
            $this->configureTenantDatabaseConnection($tenant);
        } else {
            Log::warning("⚠️ slug salvo na sessão não existe mais", ['slug' => $slug]);
            session()->forget('tenant_slug');
        }

        return $next($request);
    }

    // Método responsável por configurar a conexão com o banco do tenant
    protected function configureTenantDatabaseConnection(Tenant $tenant)
    {
        Log::info("🔧 Conexão de banco de dados do tenant configurada", [
            'host' => $tenant->db_host,
            'database' => $tenant->db_name,
            'username' => $tenant->db_username
        ]);

        // Configura dinamicamente os detalhes do banco de dados
        Config::set('database.connections.tenant.host', $tenant->db_host);
        Config::set('database.connections.tenant.database', $tenant->db_name);
        Config::set('database.connections.tenant.username', $tenant->db_username);
        Config::set('database.connections.tenant.password', $tenant->db_password); // Adiciona a senha do banco

        // Recarrega a conexão do banco de dados com as novas configurações
        DB::purge('tenant');  // Limpa a conexão existente
        DB::reconnect('tenant'); // Reconnecta com as novas configurações
    }
}
