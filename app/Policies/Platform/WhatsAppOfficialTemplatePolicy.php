<?php

namespace App\Policies\Platform;

use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTemplate;

class WhatsAppOfficialTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view');
    }

    public function view(User $user, WhatsAppOfficialTemplate $template): bool
    {
        return $this->hasPermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create');
    }

    public function update(User $user, WhatsAppOfficialTemplate $template): bool
    {
        return $this->hasPermission($user, 'edit_draft');
    }

    public function duplicate(User $user, WhatsAppOfficialTemplate $template): bool
    {
        return $this->hasPermission($user, 'edit_draft');
    }

    public function submitToMeta(User $user, WhatsAppOfficialTemplate $template): bool
    {
        return $this->hasPermission($user, 'send_to_meta');
    }

    public function syncStatus(User $user, WhatsAppOfficialTemplate $template): bool
    {
        return $this->hasPermission($user, 'sync_status');
    }

    public function archive(User $user, WhatsAppOfficialTemplate $template): bool
    {
        return $this->hasPermission($user, 'archive');
    }

    public function testSend(User $user, WhatsAppOfficialTemplate $template): bool
    {
        return $this->hasPermission($user, 'send_to_meta');
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

        if (in_array('whatsapp_official_templates', $modules, true)) {
            return true;
        }

        return in_array('whatsapp_official_templates.' . $ability, $modules, true);
    }
}
