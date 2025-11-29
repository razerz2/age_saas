<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\Tenant\User;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/Platform/dashboard';

    public function boot(): void
    {
        /**
         * ================================================================
         * 🔥 Route Model Binding para "user"
         * Garantir que o ID do User seja resolvido corretamente
         * ================================================================
         */
        Route::bind('user', function ($value) {
            return User::findOrFail($value);  // Faz o binding de 'user' usando o ID
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

            // Plataforma
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Tenants
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
}
