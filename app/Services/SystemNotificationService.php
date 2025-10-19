<?php

namespace App\Services;

use App\Models\Platform\SystemNotification;
use Illuminate\Support\Facades\Log;

class SystemNotificationService
{
    public static function notify(string $title, ?string $message = null, ?string $context = null, string $level = 'info'): void
    {
        SystemNotification::create([
            'title'   => $title,
            'message' => $message,
            'context' => $context,
            'level'   => $level,
        ]);

        Log::info("📢 System Notification: {$title}");
    }
}