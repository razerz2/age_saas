<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\SystemNotification;

class SystemNotificationController extends Controller
{
    public function index()
    {
        $notifications = SystemNotification::orderBy('created_at')->get();
        return view('platform.system_notifications.index', compact('notifications'));
    }

    public function show(SystemNotification $system_notification)
    {
        if ($system_notification->status === 'new') {
            $system_notification->update(['status' => 'read']);
        }

        return view('platform.system_notifications.show', [
            'notification' => $system_notification,
        ]);
    }
}
