<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $module)
    {
        // Detecta qual guard usar baseado na rota
        $isTenantRoute = $request->segment(1) === 'tenant';
        $isPlatformRoute = $request->segment(1) === 'platform' || $request->segment(1) === 'admin';
        
        // Determina o guard e rotas de redirecionamento
        if ($isTenantRoute) {
            $guard = 'tenant';
            $loginRoute = 'tenant.login';
            $dashboardRoute = 'tenant.dashboard';
            $moduleClass = \App\Models\Tenant\Module::class;
            $tenantSlug = session('tenant_slug');
            
            // Se não estiver autenticado no guard tenant
            if (!Auth::guard($guard)->check()) {
                Log::warning("🔒 Usuário não autenticado no guard tenant", [
                    'url' => $request->fullUrl(),
                    'module' => $module
                ]);
                
                // Tenta redirecionar para o login do tenant
                if ($tenantSlug) {
                    return redirect()->route($loginRoute, ['tenant' => $tenantSlug])
                        ->with('error', 'Você precisa estar autenticado para acessar o sistema.');
                }
                
                // NUNCA redirecionar para platform em rotas de tenant
                // Se não tiver slug, mostra erro amigável
                abort(403, 'Acesso negado. Por favor, faça login através do link correto da sua clínica.');
            }
            
            $user = Auth::guard($guard)->user();
        } else {
            // Platform
            $guard = 'web';
            $loginRoute = 'login';
            $dashboardRoute = 'Platform.dashboard';
            $moduleClass = \App\Models\Platform\Module::class;
            
            if (!Auth::guard($guard)->check()) {
                return redirect()->route($loginRoute)
                    ->with('error', 'Você precisa estar autenticado para acessar o sistema.');
            }
            
            $user = Auth::guard($guard)->user();
        }

        // Verifica se o usuário não tem acesso ao módulo solicitado
        if (!in_array($module, $user->modules ?? [])) {
            // Busca o nome do módulo
            $moduleName = $moduleClass::getName($module) ?? ucfirst($module);

            // Mensagem personalizada e amigável
            $message = "Você não tem permissão para acessar o módulo \"{$moduleName}\". Entre em contato com o administrador do sistema para solicitar acesso a este módulo.";

            Log::warning("🚫 Acesso negado ao módulo", [
                'user_id' => $user->id,
                'user_name' => $user->name ?? $user->name_full,
                'module' => $module,
                'module_name' => $moduleName,
                'user_modules' => $user->modules ?? [],
                'url' => $request->fullUrl()
            ]);

            // Redireciona de volta ao dashboard apropriado
            return redirect()
                ->route($dashboardRoute)
                ->with('error', $message);
        }

        return $next($request);
    }
}
