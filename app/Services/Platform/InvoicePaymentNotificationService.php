<?php

namespace App\Services\Platform;

use App\Models\Platform\Invoices;
use App\Models\Platform\NotificationTemplate;
use App\Services\SystemNotificationService;
use App\Services\TemplateRenderer;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoicePaymentNotificationService
{
    public function __construct(
        private readonly WhatsAppOfficialMessageService $officialWhatsApp,
        private readonly TemplateRenderer $templateRenderer
    ) {
    }

    public function notifyInvoiceCreated(Invoices $invoice): void
    {
        $this->notifyByEventKey($invoice, 'invoice.created');
    }

    public function notifyInvoiceOverdue(Invoices $invoice): void
    {
        $this->notifyByEventKey($invoice, 'invoice.overdue');
    }

    private function notifyByEventKey(Invoices $invoice, string $eventKey): void
    {
        $invoice->loadMissing(['tenant', 'subscription.plan']);

        $tenant = $invoice->tenant;
        $subscription = $invoice->subscription;
        $plan = $subscription?->plan;

        $payload = [
            'customer_name' => (string) ($tenant?->trade_name ?? ''),
            'tenant_name' => (string) ($tenant?->trade_name ?? ''),
            'plan_name' => (string) ($plan?->name ?? ''),
            'invoice_amount' => 'R$ ' . number_format(((int) $invoice->amount_cents) / 100, 2, ',', '.'),
            'due_date' => $this->formatDate($invoice->due_date),
            'payment_link' => trim((string) $invoice->payment_link),
        ];

        $meta = [
            'invoice_id' => (string) $invoice->id,
            'tenant_id' => (string) ($tenant?->id ?? ''),
            'event_key' => $eventKey,
        ];

        $hasValidPaymentLink = $this->hasValidUrl($payload['payment_link']);

        if ($hasValidPaymentLink && $tenant?->phone) {
            try {
                $this->officialWhatsApp->sendByKey(
                    $eventKey,
                    $tenant->phone,
                    $payload,
                    array_merge($meta, [
                        'service' => static::class,
                        'channel' => 'whatsapp',
                    ])
                );
            } catch (\Throwable $e) {
                $this->logChannelError('whatsapp', $meta, $e);
            }
        }

        if ($hasValidPaymentLink && $tenant?->email) {
            try {
                $this->sendEmail($tenant->email, $eventKey, $payload);
            } catch (\Throwable $e) {
                $this->logChannelError('email', $meta, $e);
            }
        }

        try {
            SystemNotificationService::notify(
                $eventKey === 'invoice.created'
                    ? 'Cobranca de assinatura gerada'
                    : 'Cobranca de assinatura vencida',
                sprintf(
                    'Fatura %s para tenant %s (%s).',
                    (string) $invoice->id,
                    (string) ($tenant?->trade_name ?? 'N/A'),
                    $eventKey
                ),
                'invoice',
                'info'
            );
        } catch (\Throwable $e) {
            $this->logChannelError('internal_notification', $meta, $e);
        }
    }

    private function sendEmail(string $destinationEmail, string $eventKey, array $payload): void
    {
        $templateName = 'platform.' . $eventKey;
        $subject = 'Cobranca de assinatura';
        $htmlBody = $this->buildFallbackEmailBody($payload);

        $template = NotificationTemplate::query()
            ->where('name', $templateName)
            ->where('scope', NotificationTemplate::SCOPE_PLATFORM)
            ->where('channel', NotificationTemplate::CHANNEL_EMAIL)
            ->first();

        if ($template) {
            try {
                $rendered = $this->templateRenderer->render($templateName, $payload, NotificationTemplate::SCOPE_PLATFORM);
                $subject = trim((string) $rendered->subject) !== '' ? (string) $rendered->subject : $subject;
                $htmlBody = trim((string) $rendered->body) !== '' ? (string) $rendered->body : $htmlBody;
            } catch (\Throwable $e) {
                Log::warning('Falha ao renderizar template de email da platform. Usando fallback.', [
                    'template_name' => $templateName,
                    'event_key' => $eventKey,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Mail::send([], [], function ($message) use ($destinationEmail, $subject, $htmlBody): void {
            $message
                ->to($destinationEmail)
                ->subject($subject)
                ->html($htmlBody);
        });
    }

    private function buildFallbackEmailBody(array $payload): string
    {
        $customer = e((string) ($payload['customer_name'] ?? 'Cliente'));
        $plan = e((string) ($payload['plan_name'] ?? 'Plano'));
        $amount = e((string) ($payload['invoice_amount'] ?? ''));
        $dueDate = e((string) ($payload['due_date'] ?? ''));
        $paymentLink = e((string) ($payload['payment_link'] ?? ''));

        return <<<HTML
<p>Ola, {$customer}.</p>
<p>Sua cobranca do plano <strong>{$plan}</strong> esta disponivel.</p>
<p>Valor: <strong>{$amount}</strong><br>Vencimento: <strong>{$dueDate}</strong></p>
<p>Link de pagamento: <a href="{$paymentLink}">{$paymentLink}</a></p>
HTML;
    }

    private function hasValidUrl(?string $link): bool
    {
        $value = trim((string) $link);

        if ($value === '') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function formatDate(mixed $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format('d/m/Y');
        }

        if (! empty($date)) {
            try {
                return Carbon::parse((string) $date)->format('d/m/Y');
            } catch (\Throwable) {
            }
        }

        return now()->format('d/m/Y');
    }

    private function logChannelError(string $channel, array $meta, \Throwable $e): void
    {
        Log::error('Falha ao enviar notificacao de fatura.', array_merge($meta, [
            'channel' => $channel,
            'error' => $e->getMessage(),
        ]));
    }
}
