<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compartilha o tenant atual com todas as views
        View::composer('*', function ($view) {
            $tenant = \Spatie\Multitenancy\Models\Tenant::current();
            if ($tenant) {
                $view->with('currentTenant', $tenant);
            }
        });

        // Helper para rotas do portal do paciente que sempre inclui o tenant
        Route::macro('patientRoute', function ($name, $parameters = []) {
            $tenant = \Spatie\Multitenancy\Models\Tenant::current();
            $slug = $tenant?->subdomain ?? request()->route('tenant');
            
            if ($slug) {
                $parameters['tenant'] = $slug;
            }
            
            return route('patient.' . $name, $parameters);
        });
    }
}
