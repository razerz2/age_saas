<?php

namespace App\Policies\Platform;

use App\Models\Platform\PreTenant;
use App\Models\Platform\User;

class PreTenantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasModule('pre_tenants');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PreTenant $preTenant): bool
    {
        return $user->hasModule('pre_tenants');
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PreTenant $preTenant): bool
    {
        return $user->hasModule('pre_tenants');
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, PreTenant $preTenant): bool
    {
        return $user->hasModule('pre_tenants');
    }
}
