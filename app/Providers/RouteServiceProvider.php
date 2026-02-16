<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\User as TenantUser;
use App\Models\Platform\User as PlatformUser;
use App\Models\Tenant\Appointment;
use App\Models\Platform\Tenant;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/Platform/dashboard';

    public function boot(): void
    {
        /**
         * ================================================================
         * 🔥 Route Model Binding para "user"
         * Usa o modelo correto baseado no contexto (Platform ou Tenant)
         * ================================================================
         */
        Route::bind('user', function ($value, $route) {
            // Verificar se é uma rota da Platform verificando o path ou o prefixo da rota
            $isPlatformRoute = false;
            
            if ($route) {
                // Verificar pelo prefixo da rota
                $prefix = $route->getPrefix();
                if ($prefix && str_contains($prefix, 'Platform')) {
                    $isPlatformRoute = true;
                }
                // Verificar pelo nome da rota
                elseif ($route->getName() && str_starts_with($route->getName(), 'Platform.')) {
                    $isPlatformRoute = true;
                }
            }
            
            // Se não conseguiu determinar pela rota, verificar pelo path da requisição
            if (!$isPlatformRoute && request()) {
                $path = request()->path();
                if (str_starts_with($path, 'Platform')) {
                    $isPlatformRoute = true;
                }
            }
            
            // Se é rota da Platform, usar Platform\User
            if ($isPlatformRoute) {
                return PlatformUser::findOrFail($value);
            }
            
            // Caso contrário, é uma rota de tenant - garantir conexão e usar Tenant\User
            $this->ensureTenantConnection();
            return TenantUser::findOrFail($value);
        });

        /**
         * ================================================================
         * 🔥 Route Model Binding para "appointment"
         * Garantir que o tenant esteja ativo antes de buscar o Appointment
         * ================================================================
         */
        Route::bind('appointment', function ($value) {
            // Garantir que o tenant está ativo e a conexão configurada
            $this->ensureTenantConnection();
            
            return Appointment::findOrFail($value);
        });

        /**
         * ================================================================
         * Rotas
         * ================================================================
         */
        $this->routes(function () {
            // API
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Bot API
            Route::middleware(['api', 'platform.bot.token'])
                ->prefix('bot')
                ->group(base_path('routes/platform_bot_api.php'));

            // Plataforma (Landing Page)
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Tenants (Baseados em Path)
            Route::middleware(['web'])
                ->group(base_path('routes/tenant.php'));

            // Portal do Paciente
            Route::middleware(['web'])
                ->group(base_path('routes/patient_portal.php'));
        });

        /**
         * ================================================================
         * Rate Limiter (padrão do Laravel)
         * ================================================================
         */
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Garante que a conexão do tenant está configurada
     */
    private function ensureTenantConnection()
    {
        // Tentar ativar o tenant a partir do usuário autenticado primeiro
        $user = Auth::guard('tenant')->user();
        $tenant = null;
        
        if ($user && $user->tenant) {
            $tenant = $user->tenant;
        } else {
            // Tentar ativar a partir da sessão
            $slug = session('tenant_slug');
            if ($slug) {
                $tenant = Tenant::where('subdomain', $slug)->first();
            }
        }
        
        // Se encontrou um tenant, garantir que está ativo
        if ($tenant) {
            $currentTenant = Tenant::current();
            
            // Se o tenant atual é diferente ou não existe, ativar o correto
            if (!$currentTenant || $currentTenant->id !== $tenant->id) {
                $tenant->makeCurrent();
                $currentTenant = $tenant;
            }
            
            // Sempre garantir que a conexão está configurada corretamente
            if ($currentTenant) {
                Config::set('database.connections.tenant.host', $currentTenant->db_host ?? env('DB_TENANT_HOST', '127.0.0.1'));
                Config::set('database.connections.tenant.port', $currentTenant->db_port ?? env('DB_TENANT_PORT', '5432'));
                Config::set('database.connections.tenant.database', $currentTenant->db_name);
                Config::set('database.connections.tenant.username', $currentTenant->db_username);
                Config::set('database.connections.tenant.password', $currentTenant->db_password ?? '');
                
                DB::purge('tenant');
                DB::reconnect('tenant');
            }
        }
    }
}
