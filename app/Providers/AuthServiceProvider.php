<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Tenant\Doctor::class => \App\Policies\Tenant\DoctorPolicy::class,
        \App\Models\Tenant\Appointment::class => \App\Policies\Tenant\AppointmentPolicy::class,
        \App\Models\Tenant\RecurringAppointment::class => \App\Policies\Tenant\RecurringAppointmentPolicy::class,
        \App\Models\Tenant\Calendar::class => \App\Policies\Tenant\CalendarPolicy::class,
        \App\Models\Tenant\BusinessHour::class => \App\Policies\Tenant\BusinessHourPolicy::class,
        \App\Models\Tenant\AppointmentType::class => \App\Policies\Tenant\AppointmentTypePolicy::class,
        \App\Models\Tenant\Form::class => \App\Policies\Tenant\FormPolicy::class,
        \App\Models\Tenant\FormResponse::class => \App\Policies\Tenant\FormResponsePolicy::class,
        \App\Models\Platform\PreTenant::class => \App\Policies\Platform\PreTenantPolicy::class,
        \App\Models\Platform\ApiTenantToken::class => \App\Policies\Platform\ApiTenantTokenPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('patients.update', function ($user = null) {
            if (!$user) {
                return false;
            }

            if (isset($user->role) && $user->role === 'admin') {
                return true;
            }

            $modules = $user->modules;
            if (is_string($modules)) {
                $decoded = json_decode($modules, true);
                $modules = is_array($decoded) ? $decoded : [];
            }

            if (!is_array($modules)) {
                $modules = [];
            }

            return in_array('patients', $modules, true);
        });
    }
}
