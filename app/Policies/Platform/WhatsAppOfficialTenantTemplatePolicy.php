<?php

namespace App\Policies\Platform;

use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTenantTemplate;

class WhatsAppOfficialTenantTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view');
    }

    public function view(User $user, mixed $template = null): bool
    {
        return $this->hasPermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create');
    }

    public function update(User $user, mixed $template = null): bool
    {
        return $this->hasPermission($user, 'edit');
    }

    public function syncStatus(User $user, mixed $template = null): bool
    {
        return $this->hasPermission($user, 'sync_status') || $this->hasPermission($user, 'edit');
    }

    public function submitToMeta(User $user, mixed $template = null): bool
    {
        return $this->hasPermission($user, 'send_to_meta') || $this->hasPermission($user, 'edit');
    }

    public function testSend(User $user, mixed $template = null): bool
    {
        return $this->hasPermission($user, 'send_to_meta') || $this->hasPermission($user, 'edit');
    }

    public function toggle(User $user, mixed $template = null): bool
    {
        return $this->hasPermission($user, 'edit');
    }

    public function manageBindings(User $user, mixed $template = null): bool
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

        if (in_array('whatsapp_official_tenant_templates', $modules, true)) {
            return true;
        }

        if (in_array('whatsapp_official_tenant_templates.' . $ability, $modules, true)) {
            return true;
        }

        if (in_array('whatsapp_official_templates', $modules, true)) {
            return true;
        }

        return in_array('whatsapp_official_templates.' . $ability, $modules, true);
    }
}
