<?php

namespace App\Notifications\Platform;

use App\Helpers\EmailLayoutHelper;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Support\PlatformTwoFactorPhoneResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TwoFactorCodeOfficialNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly string $method = 'email'
    ) {
    }

    public function via(object $notifiable): array
    {
        if ($this->method === 'whatsapp') {
            return ['whatsapp'];
        }

        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'Sistema');

        $html = EmailLayoutHelper::renderViewContent('emails.two-factor-code', [
            'user' => $notifiable,
            'code' => $this->code,
            'app_name' => $appName,
        ]);

        return (new MailMessage)
            ->subject("Codigo de Verificacao - {$appName}")
            ->view('emails.two-factor-code-wrapper', [
                'htmlContent' => $html,
            ]);
    }

    public function toWhatsApp(object $notifiable): void
    {
        $resolved = app(PlatformTwoFactorPhoneResolver::class)->resolveWithReason($notifiable);
        $phone = $resolved['phone'];
        if ($phone === null) {
            Log::warning('platform_2fa_whatsapp_skipped_missing_phone', [
                'user_id' => $notifiable->id ?? null,
                'user_type' => get_class($notifiable),
                'reason' => $resolved['reason'],
            ]);
            return;
        }

        app(WhatsAppOfficialMessageService::class)->sendByKey(
            'security.2fa_code',
            $phone,
            [
                'customer_name' => (string) ($notifiable->name ?? 'Usuario'),
                'code' => $this->code,
                'expires_in_minutes' => '10',
            ],
            [
                'notification' => static::class,
                'user_id' => (string) ($notifiable->id ?? ''),
                'event' => 'security.2fa_code',
            ]
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
            'method' => $this->method,
        ];
    }
}
