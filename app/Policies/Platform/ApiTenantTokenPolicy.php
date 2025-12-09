<?php

namespace App\Policies\Platform;

use App\Models\Platform\ApiTenantToken;
use App\Models\Platform\User;

class ApiTenantTokenPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $modules = $user->modules ?? [];
        return is_array($modules) && in_array('api_tokens', $modules);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApiTenantToken $apiTenantToken): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApiTenantToken $apiTenantToken): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApiTenantToken $apiTenantToken): bool
    {
        return $this->viewAny($user);
    }
}
