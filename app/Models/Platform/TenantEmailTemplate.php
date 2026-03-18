<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Builder;

class TenantEmailTemplate extends NotificationTemplate
{
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('tenant_email_templates', function (Builder $query): void {
            $query
                ->where('scope', NotificationTemplate::SCOPE_TENANT)
                ->where('channel', NotificationTemplate::CHANNEL_EMAIL);
        });

        static::creating(function (self $template): void {
            $template->scope = NotificationTemplate::SCOPE_TENANT;
            $template->channel = NotificationTemplate::CHANNEL_EMAIL;
        });
    }
}

