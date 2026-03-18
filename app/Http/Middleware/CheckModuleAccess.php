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
        // Detecta qual guard usar baseado na rota (case-insensitive)
        $segment1 = strtolower($request->segment(1) ?? '');
        $isWorkspaceRoute = $segment1 === 'workspace';
        $isPlatformRoute = $segment1 === 'platform' || $segment1 === 'admin';
        
        // Determina o guard e rotas de redirecionamento
        if ($isWorkspaceRoute) {
            $guard = 'tenant';
            $loginRoute = 'tenant.login';
            $dashboardRoute = 'tenant.dashboard';
            $moduleClass = \App\Models\Tenant\Module::class;
            $tenantSlug = session('tenant_slug') ?? $request->route('slug');
            
            // Se não estiver autenticado no guard tenant
            if (!Auth::guard($guard)->check()) {
                Log::warning("🔒 Usuário não autenticado no guard tenant", [
                    'url' => $request->fullUrl(),
                    'module' => $module
                ]);
                
                // Tenta redirecionar para o login do tenant
                if ($tenantSlug) {
                    return redirect()->route($loginRoute, ['slug' => $tenantSlug])
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

        // Para rotas workspace, verifica se é admin (admin tem acesso a todos os módulos)
        if ($isWorkspaceRoute && isset($user->role) && $user->role === 'admin') {
            return $next($request);
        }

        // Garantir que modules seja sempre um array
        $userModules = [];
        if ($user->modules) {
            if (is_array($user->modules)) {
                $userModules = $user->modules;
            } elseif (is_string($user->modules)) {
                $decoded = json_decode($user->modules, true);
                $userModules = is_array($decoded) ? $decoded : [];
            }
        }

        // Verifica se o usuário não tem acesso ao módulo solicitado
        // Garantir que $userModules seja sempre um array válido
        if (!is_array($userModules)) {
            $userModules = [];
        }
        
        $moduleFallbacks = [
            'whatsapp_official_tenant_templates' => ['whatsapp_official_templates'],
        ];

        $allowedModules = [$module];
        if (array_key_exists($module, $moduleFallbacks)) {
            $allowedModules = array_values(array_unique(array_merge($allowedModules, $moduleFallbacks[$module])));
        }

        $hasModuleAccess = false;
        foreach ($allowedModules as $allowedModule) {
            if (in_array($allowedModule, $userModules, true)) {
                $hasModuleAccess = true;
                break;
            }
        }

        if (!$hasModuleAccess) {
            // Busca o nome do módulo com tratamento de erro
            try {
                $moduleName = $moduleClass::getName($module) ?? ucfirst($module);
            } catch (\Exception $e) {
                Log::warning("Erro ao buscar nome do módulo", [
                    'module' => $module,
                    'error' => $e->getMessage()
                ]);
                $moduleName = ucfirst($module);
            }

            // Mensagem personalizada e amigável
            $message = "Você não tem permissão para acessar o módulo \"{$moduleName}\". Entre em contato com o administrador do sistema para solicitar acesso a este módulo.";

            Log::warning("🚫 Acesso negado ao módulo", [
                'user_id' => $user->id,
                'user_name' => $user->name ?? $user->name_full,
                'module' => $module,
                'allowed_modules' => $allowedModules,
                'module_name' => $moduleName,
                'user_modules' => $userModules,
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
