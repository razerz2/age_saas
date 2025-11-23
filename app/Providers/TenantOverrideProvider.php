<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use App\Models\Platform\Tenant;

class TenantOverrideProvider extends ServiceProvider
{
    public function register()
    {
        // Sempre que o Spatie tentar criar ou restaurar Tenant
        // ele deve usar o SEU model e não o base
        $this->app->bind(SpatieTenant::class, Tenant::class);
    }
}
