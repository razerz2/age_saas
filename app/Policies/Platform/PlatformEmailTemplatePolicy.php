<?php

namespace App\Policies\Platform;

use App\Models\Platform\PlatformEmailTemplate;
use App\Models\Platform\User;

class PlatformEmailTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view');
    }

    public function view(User $user, PlatformEmailTemplate $template): bool
    {
        return $this->hasPermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PlatformEmailTemplate $template): bool
    {
        return $this->hasPermission($user, 'edit');
    }

    public function restore(User $user, PlatformEmailTemplate $template): bool
    {
        return $this->hasPermission($user, 'edit');
    }

    public function toggle(User $user, PlatformEmailTemplate $template): bool
    {
        return $this->hasPermission($user, 'edit');
    }

    private function hasPermission(User $user, string $ability): bool
    {
        $modules = $user->modules ?? [];
        if (is_string($modules)) {
            $decoded = json_decode($modules, true);
            $modules = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($modules)) {
            return false;
        }

        if (in_array('platform_email_templates', $modules, true)) {
            return true;
        }

        return in_array('platform_email_templates.' . $ability, $modules, true);
    }
}
