<?php

namespace App\Policies\Platform;

use App\Models\Platform\TenantDefaultNotificationTemplate;
use App\Models\Platform\User;

class TenantDefaultNotificationTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view');
    }

    public function view(User $user, TenantDefaultNotificationTemplate $template): bool
    {
        return $this->hasPermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, TenantDefaultNotificationTemplate $template): bool
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

        if (in_array('tenant_default_notification_templates', $modules, true)) {
            return true;
        }

        return in_array('tenant_default_notification_templates.' . $ability, $modules, true);
    }
}
