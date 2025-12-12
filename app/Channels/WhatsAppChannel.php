<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Services\WhatsAppService;

class WhatsAppChannel
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        if (method_exists($notification, 'toWhatsApp')) {
            $notification->toWhatsApp($notifiable);
        }
    }
}

