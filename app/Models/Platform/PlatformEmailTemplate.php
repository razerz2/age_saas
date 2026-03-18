<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Builder;

class PlatformEmailTemplate extends NotificationTemplate
{
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('platform_email_templates', function (Builder $query): void {
            $query
                ->where('scope', NotificationTemplate::SCOPE_PLATFORM)
                ->where('channel', NotificationTemplate::CHANNEL_EMAIL);
        });

        static::creating(function (self $template): void {
            $template->scope = NotificationTemplate::SCOPE_PLATFORM;
            $template->channel = NotificationTemplate::CHANNEL_EMAIL;
        });
    }
}

