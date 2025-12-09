<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Platform\Tenant;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Detecta se é rota de workspace (área autenticada)
            $isWorkspaceRoute = $request->segment(1) === 'workspace';
            
            if ($isWorkspaceRoute) {
                // NUNCA redirecionar para platform em rotas de tenant
                // Tenta obter o slug do tenant de várias fontes
                $tenantSlug = $this->getTenantSlug($request);
                
                if ($tenantSlug) {
                    return route('tenant.login', ['slug' => $tenantSlug]);
                }
                
                // Se não conseguir encontrar o slug, aborta com erro amigável
                abort(403, 'Acesso negado. Por favor, faça login através do link correto da sua clínica.');
            }
            
            // Para rotas da platform, redireciona para login da platform
            return route('login');
        }

        return null;
    }

    /**
     * Tenta obter o slug do tenant de várias fontes
     * Similar à lógica do RedirectIfTenantUnauthenticated
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
                // Busca o tenant sem fazer makeCurrent para evitar problemas
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
