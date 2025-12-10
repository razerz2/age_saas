<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Notification;
use App\Services\TenantNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Lista todas as notificações
     */
    public function index()
    {
        $notifications = Notification::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('tenant.notifications.index', compact('notifications'));
    }

    /**
     * Exibe uma notificação específica
     */
    public function show($slug, $id)
    {
        $notification = Notification::findOrFail($id);
        
        // Marca como lida ao visualizar
        if ($notification->status === 'new') {
            $notification->markAsRead();
        }

        return view('tenant.notifications.show', compact('notification'));
    }

    /**
     * Marca uma notificação como lida
     */
    public function markAsRead($slug, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Marca todas as notificações como lidas
     */
    public function markAllAsRead()
    {
        $count = TenantNotificationService::markAllAsRead();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Retorna notificações em JSON (para AJAX)
     */
    public function json()
    {
        $notifications = Notification::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $unreadCount = TenantNotificationService::unreadCount();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}

