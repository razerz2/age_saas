<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\RecurringAppointment;
use App\Observers\AppointmentObserver;
use App\Observers\RecurringAppointmentObserver;

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
        // Registra os Observers para sincronização automática com Google Calendar
        Appointment::observe(AppointmentObserver::class);
        RecurringAppointment::observe(RecurringAppointmentObserver::class);

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
            $slug = $tenant?->subdomain;
            
            // Tenta obter do route apenas se houver uma requisição HTTP disponível
            if (!$slug && app()->runningInConsole() === false && request() !== null) {
                $slug = request()->route('tenant');
            }
            
            if ($slug) {
                $parameters['tenant'] = $slug;
            }
            
            return route('patient.' . $name, $parameters);
        });
    }
}
