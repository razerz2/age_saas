<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Platform\Tenant;

class EnsureNoTenantForLanding
{
    /**
     * Handle an incoming request.
     *
     * Garante que rotas da landing page não tenham tenant ativo
     * 
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Lista de rotas da landing page
        $landingRoutes = [
            '/',
            '/funcionalidades',
            '/planos',
            '/contato',
            '/manual',
            '/pre-cadastro',
            '/politica-de-privacidade',
            '/termos-de-servico',
        ];

        $path = $request->path();
        
        // Verifica se é uma rota da landing page
        $isLandingRoute = in_array('/' . $path, $landingRoutes) || $path === '';
        
        // Se for rota da landing page, garante que não há tenant ativo
        if ($isLandingRoute) {
            // Esquece qualquer tenant que possa estar ativo
            Tenant::forgetCurrent();
            
            // Limpa a sessão de tenant
            session()->forget('tenant_slug');
        }

        return $next($request);
    }
}

