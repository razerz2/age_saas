<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Detecta se é rota de tenant
            $isTenantRoute = $request->segment(1) === 'tenant';
            
            if ($isTenantRoute) {
                // NUNCA redirecionar para platform em rotas de tenant
                // Tenta obter o slug do tenant
                $tenantSlug = $request->route('tenant') 
                    ?? session('tenant_slug') 
                    ?? \App\Models\Platform\Tenant::current()?->subdomain;
                
                if ($tenantSlug) {
                    return route('tenant.login', ['tenant' => $tenantSlug]);
                }
                
                // Se não conseguir encontrar o slug, aborta com erro amigável
                abort(403, 'Acesso negado. Por favor, faça login através do link correto da sua clínica.');
            }
            
            // Para rotas da platform, redireciona para login da platform
            return route('login');
        }

        return null;
    }
}
