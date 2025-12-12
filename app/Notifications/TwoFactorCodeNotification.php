<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Services\WhatsAppService;
use App\Helpers\EmailLayoutHelper;

class TwoFactorCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $code;
    protected string $method;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code, string $method = 'email')
    {
        $this->code = $code;
        $this->method = $method;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($this->method === 'whatsapp') {
            return ['whatsapp'];
        }
        
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'Sistema');
        
        // Renderiza a view e aplica o layout
        $html = EmailLayoutHelper::renderViewContent('emails.two-factor-code', [
            'user' => $notifiable,
            'code' => $this->code,
            'app_name' => $appName,
        ]);

        return (new MailMessage)
            ->subject("Código de Verificação - {$appName}")
            ->view('emails.two-factor-code-wrapper', [
                'htmlContent' => $html,
            ]);
    }

    /**
     * Envia código via WhatsApp
     */
    public function toWhatsApp(object $notifiable): void
    {
        $appName = config('app.name', 'Sistema');
        
        // Para Platform users, não há telefone, então não envia WhatsApp
        // Apenas Tenant users têm telefone
        $phone = null;
        if (isset($notifiable->telefone)) {
            $phone = $notifiable->telefone;
        } elseif (isset($notifiable->phone)) {
            $phone = $notifiable->phone;
        }
        
        if (!$phone) {
            \Log::warning('Tentativa de enviar código 2FA via WhatsApp sem telefone', [
                'user_id' => $notifiable->id,
                'user_type' => get_class($notifiable)
            ]);
            // Se não tem telefone, tenta enviar por email como fallback
            $this->toMail($notifiable);
            return;
        }

        $message = "🔐 *Código de Verificação*\n\n";
        $message .= "Olá, {$notifiable->name}!\n\n";
        $message .= "Seu código de verificação é:\n";
        $message .= "*{$this->code}*\n\n";
        $message .= "Este código expira em 10 minutos.\n\n";
        $message .= "Se você não solicitou este código, ignore esta mensagem.";

        $whatsappService = app(WhatsAppService::class);
        $whatsappService->sendMessage($phone, $message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
            'method' => $this->method,
        ];
    }
}
