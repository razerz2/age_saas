<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Platform\Tenant;

class EnsureTenantFromGuard
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info("📌 EnsureTenantFromGuard → início", [
            'tenant_current' => Tenant::current()?->id,
            'auth_check'     => Auth::guard('tenant')->check(),
            'session_slug'   => session('tenant_slug')
        ]);

        // 1) Tenant já está ativo → segue
        if (Tenant::current()) {
            \Log::info("➡️ Tenant já ativo, continuando...");
            return $next($request);
        }

        // 2) Se o usuário estiver logado pelo guard tenant
        if (Auth::guard('tenant')->check()) {

            $user = Auth::guard('tenant')->user();

            \Log::info("🔐 Usuário logado detectado", [
                'user_id'   => $user->id,
                'tenant_fk' => $user->tenant_id,
                'tenant_relacionado_existe' => (bool) $user->tenant
            ]);

            // 3) Usuário realmente pertence a um tenant
            if ($user->tenant) {

                $tenant = $user->tenant;

                // Proteção extra: tenant_id precisa ser UUID
                if (!is_string($tenant->id) || strlen($tenant->id) < 10) {

                    \Log::error("❌ ERRO GRAVE — tenant_id inválido vindo do User", [
                        'user_id'       => $user->id,
                        'tenant_id_raw' => $tenant->id,
                        'tipo'          => gettype($tenant->id)
                    ]);

                    // Evita ativar tenant inválido
                    return $next($request);
                }

                \Log::info("🔁 Ativando tenant via usuário autenticado", [
                    'tenant_id' => $tenant->id,
                    'slug'      => $tenant->subdomain
                ]);

                // 4) Finalmente, ativa o tenant correto
                $tenant->makeCurrent();

                // 5) Armazena Slug limpo para persistência
                session(['tenant_slug' => $tenant->subdomain]);
            }
        }

        return $next($request);
    }
}
