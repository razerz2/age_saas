<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Platform\Tenant;

class RedirectIfTenantUnauthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('tenant')->check()) {
            return $next($request);
        }

        // Log para debug
        \Log::warning("🔒 Usuário não autenticado no guard tenant", [
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'session_slug' => session('tenant_slug'),
            'tenant_current' => Tenant::current()?->subdomain,
        ]);

        // Tenta obter o slug do tenant de várias fontes
        $slug = $this->getTenantSlug($request);

        if (!$slug) {
            // Log do problema
            \Log::error("❌ Não foi possível encontrar slug do tenant para redirecionamento", [
                'url' => $request->fullUrl(),
            ]);
            
            // NUNCA redirecionar para platform em rotas de tenant
            // Se não conseguir encontrar o slug, mostra uma página de erro amigável
            abort(403, 'Acesso negado. Por favor, faça login através do link correto da sua clínica.');
        }

        \Log::info("➡️ Redirecionando para login do tenant", ['slug' => $slug]);

        return redirect()->route('tenant.login', [
            'slug' => $slug
        ]);
    }

    /**
     * Tenta obter o slug do tenant de várias fontes
     */
    protected function getTenantSlug(Request $request): ?string
    {
        // 1. Tenta pegar da rota (para rotas como /customer/{slug}/... ou /workspace/{slug}/...)
        if ($request->route('slug')) {
            return $request->route('slug');
        }
        
        // Fallback para 'tenant' (caso ainda exista alguma rota antiga)
        if ($request->route('tenant')) {
            return $request->route('tenant');
        }

        // 2. Tenta pegar da sessão
        $sessionSlug = session('tenant_slug');
        if ($sessionSlug) {
            return $sessionSlug;
        }

        // 3. Tenta pegar do tenant atual (se já estiver ativo)
        $currentTenant = Tenant::current();
        if ($currentTenant && $currentTenant->subdomain) {
            return $currentTenant->subdomain;
        }

        // 4. Tenta pegar do usuário autenticado no guard tenant (mesmo que não passe no check)
        // Isso pode acontecer se o token expirou mas ainda está na sessão
        try {
            $user = Auth::guard('tenant')->user();
            if ($user && $user->tenant_id) {
                $tenant = Tenant::find($user->tenant_id);
                if ($tenant && $tenant->subdomain) {
                    return $tenant->subdomain;
                }
            }
        } catch (\Exception $e) {
            // Ignora erros ao tentar pegar o usuário
        }

        return null;
    }
}
