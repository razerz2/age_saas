<?php

namespace App\Console\Commands;

use App\Models\Platform\NotificationTemplate;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\TrialReminderDispatch;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\SystemNotificationService;
use App\Services\Tenant\TemplateRenderer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotifyTrialRemindersCommand extends Command
{
    protected $signature = 'subscriptions:notify-trial-reminders';
    protected $description = 'Envia lembretes automaticos de trial (7 dias, 3 dias, hoje e expirado) com idempotencia.';

    private const EVENT_ENDS_IN_7_DAYS = 'trial.ends_in_7_days';
    private const EVENT_ENDS_IN_3_DAYS = 'trial.ends_in_3_days';
    private const EVENT_ENDS_TODAY = 'trial.ends_today';
    private const EVENT_EXPIRED = 'trial.expired';

    public function __construct(
        private readonly WhatsAppOfficialMessageService $officialWhatsApp,
        private readonly TemplateRenderer $templateRenderer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $timezone = $this->resolveTimezone();
        $today = Carbon::now($timezone)->startOfDay();

        $this->info(sprintf(
            'Iniciando lembretes de trial em %s (%s)...',
            $today->format('Y-m-d'),
            $timezone
        ));

        $stats = [
            'candidates' => 0,
            'scheduled' => 0,
            'sent' => 0,
            'partial' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        $candidates = $this->resolveTrialCandidates();

        foreach ($candidates as $subscription) {
            $stats['candidates']++;

            $tenant = $subscription->tenant;
            $plan = $subscription->plan;

            if (! $tenant || ! $plan) {
                $stats['skipped']++;
                continue;
            }

            if ($plan->isTest() || ! $plan->hasCommercialTrial()) {
                $stats['skipped']++;
                continue;
            }

            $trialEndsAt = $subscription->trial_ends_at?->copy()->timezone($timezone)->startOfDay();
            if (! $trialEndsAt) {
                $stats['skipped']++;
                continue;
            }

            if ($this->trialWasClosedManually($subscription, $trialEndsAt)) {
                $stats['skipped']++;
                continue;
            }

            if ($this->tenantHasConvertedTrialToPaid($subscription)) {
                $stats['skipped']++;
                continue;
            }

            $event = $this->resolveReminderEvent($subscription, $today, $trialEndsAt);
            if (! $event) {
                $stats['skipped']++;
                continue;
            }

            $stats['scheduled']++;

            $dispatch = TrialReminderDispatch::query()->firstOrCreate(
                [
                    'subscription_id' => $subscription->id,
                    'event_key' => $event['event_key'],
                    'reference_date' => $trialEndsAt->toDateString(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'status' => 'pending',
                    'channels_sent' => [],
                    'attempts' => 0,
                    'meta' => [
                        'trial_ends_at' => $trialEndsAt->toDateString(),
                        'plan_id' => (string) $plan->id,
                    ],
                ]
            );

            if ($dispatch->status === 'sent') {
                $stats['skipped']++;
                continue;
            }

            $context = $this->buildTemplateContext($subscription, $tenant, $plan, $trialEndsAt, $event['days_remaining']);
            $intendedChannels = $this->resolveIntendedChannels($tenant);

            $channelsSent = collect($dispatch->channels_sent ?? [])
                ->filter(fn ($channel) => is_string($channel) && $channel !== '')
                ->values()
                ->all();

            $errors = [];

            if (in_array('email', $intendedChannels, true) && ! in_array('email', $channelsSent, true)) {
                [$emailSent, $emailError] = $this->sendEmailReminder($event['event_key'], $tenant, $context, [
                    'command' => static::class,
                    'subscription_id' => (string) $subscription->id,
                    'tenant_id' => (string) $tenant->id,
                    'event' => $event['event_key'],
                ]);

                if ($emailSent) {
                    $channelsSent[] = 'email';
                } elseif ($emailError !== null) {
                    $errors[] = $emailError;
                }
            }

            if (in_array('whatsapp', $intendedChannels, true) && ! in_array('whatsapp', $channelsSent, true)) {
                $whatsAppSent = $this->officialWhatsApp->sendByKey(
                    $event['event_key'],
                    $tenant->phone,
                    $context,
                    [
                        'command' => static::class,
                        'subscription_id' => (string) $subscription->id,
                        'tenant_id' => (string) $tenant->id,
                        'event' => $event['event_key'],
                    ]
                );

                if ($whatsAppSent) {
                    $channelsSent[] = 'whatsapp';
                } else {
                    $errors[] = 'Falha no envio WhatsApp oficial.';
                }
            }

            if (in_array('internal', $intendedChannels, true) && ! in_array('internal', $channelsSent, true)) {
                SystemNotificationService::notify(
                    $this->internalNotificationTitle($event['event_key'], $tenant),
                    $this->internalNotificationMessage($event['event_key'], $context),
                    'subscription',
                    $event['event_key'] === self::EVENT_EXPIRED ? 'warning' : 'info'
                );

                $channelsSent[] = 'internal';
            }

            $channelsSent = array_values(array_unique($channelsSent));
            $pendingChannels = array_values(array_diff($intendedChannels, $channelsSent));

            $dispatch->tenant_id = $tenant->id;
            $dispatch->channels_sent = $channelsSent;
            $dispatch->attempts = ((int) $dispatch->attempts) + 1;
            $dispatch->meta = array_merge((array) $dispatch->meta, [
                'days_remaining' => $event['days_remaining'],
                'upgrade_url' => $context['upgrade_url'],
            ]);

            if ($pendingChannels === []) {
                $dispatch->status = 'sent';
                $dispatch->dispatched_at = now();
                $dispatch->last_error = null;
                $stats['sent']++;
            } elseif ($channelsSent !== []) {
                $dispatch->status = 'partial';
                $dispatch->last_error = $errors !== [] ? implode(' | ', array_unique($errors)) : null;
                $stats['partial']++;
            } else {
                $dispatch->status = 'failed';
                $dispatch->last_error = $errors !== [] ? implode(' | ', array_unique($errors)) : 'Nenhum canal disponivel para envio.';
                $stats['failed']++;
            }

            $dispatch->save();
        }

        $summary = sprintf(
            'Candidatas: %d | Janela: %d | Enviadas: %d | Parciais: %d | Falhas: %d | Ignoradas: %d',
            $stats['candidates'],
            $stats['scheduled'],
            $stats['sent'],
            $stats['partial'],
            $stats['failed'],
            $stats['skipped']
        );

        $this->info($summary);

        SystemNotificationService::notify(
            'Lembretes automaticos de trial',
            $summary,
            'subscription',
            $stats['failed'] > 0 ? 'warning' : 'info'
        );

        return Command::SUCCESS;
    }

    /**
     * @return Collection<int, Subscription>
     */
    private function resolveTrialCandidates(): Collection
    {
        return Subscription::query()
            ->with(['tenant.admin', 'plan'])
            ->where('is_trial', true)
            ->whereNotNull('trial_ends_at')
            ->whereIn('status', ['active', 'trialing', 'canceled'])
            ->whereHas('plan', function ($query): void {
                $query->where('plan_type', Plan::TYPE_REAL);
            })
            ->orderByDesc('trial_ends_at')
            ->get()
            ->groupBy('tenant_id')
            ->map(static function (Collection $items): Subscription {
                return $items->sortByDesc('trial_ends_at')->first();
            })
            ->values();
    }

    private function tenantHasConvertedTrialToPaid(Subscription $trialSubscription): bool
    {
        return Subscription::query()
            ->where('tenant_id', $trialSubscription->tenant_id)
            ->where('id', '!=', $trialSubscription->id)
            ->where(function ($query): void {
                $query->whereNull('is_trial')
                    ->orWhere('is_trial', false);
            })
            ->where('status', '!=', 'canceled')
            ->whereHas('plan', function ($query): void {
                $query->where('plan_type', Plan::TYPE_REAL);
            })
            ->exists();
    }

    private function trialWasClosedManually(Subscription $subscription, Carbon $trialEndsAt): bool
    {
        if ($subscription->status !== 'canceled') {
            return false;
        }

        if ($subscription->ends_at && $subscription->ends_at->copy()->lt($trialEndsAt)) {
            return true;
        }

        return $trialEndsAt->isFuture();
    }

    /**
     * @return array{event_key: string, days_remaining: int}|null
     */
    private function resolveReminderEvent(Subscription $subscription, Carbon $today, Carbon $trialEndsAt): ?array
    {
        $daysRemaining = $today->diffInDays($trialEndsAt, false);

        $isOpenTrial = in_array($subscription->status, ['active', 'trialing'], true);

        if ($isOpenTrial && $daysRemaining === 7) {
            return ['event_key' => self::EVENT_ENDS_IN_7_DAYS, 'days_remaining' => 7];
        }

        if ($isOpenTrial && $daysRemaining === 3) {
            return ['event_key' => self::EVENT_ENDS_IN_3_DAYS, 'days_remaining' => 3];
        }

        if ($isOpenTrial && $daysRemaining === 0) {
            return ['event_key' => self::EVENT_ENDS_TODAY, 'days_remaining' => 0];
        }

        if ($daysRemaining < 0) {
            return ['event_key' => self::EVENT_EXPIRED, 'days_remaining' => 0];
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTemplateContext(
        Subscription $subscription,
        Tenant $tenant,
        Plan $plan,
        Carbon $trialEndsAt,
        int $daysRemaining
    ): array {
        $tenantName = trim((string) ($tenant->trade_name ?: $tenant->legal_name ?: 'Cliente'));

        return [
            'clinic_name' => $tenantName,
            'tenant_name' => $tenantName,
            'customer_name' => $tenantName,
            'plan_name' => (string) $plan->name,
            'trial_ends_at' => $trialEndsAt->format('d/m/Y'),
            'days_remaining' => $daysRemaining,
            'upgrade_url' => $this->resolveUpgradeUrl($tenant),
            'login_url' => $this->resolveLoginUrl($tenant),
            'platform_name' => (string) config('app.name', 'Platform'),
            'subscription_id' => (string) $subscription->id,
        ];
    }

    /**
     * @return array{0: bool, 1: string|null}
     */
    private function sendEmailReminder(string $eventKey, Tenant $tenant, array $context, array $meta): array
    {
        $template = NotificationTemplate::query()
            ->where('scope', NotificationTemplate::SCOPE_PLATFORM)
            ->where('channel', NotificationTemplate::CHANNEL_EMAIL)
            ->where('name', $eventKey)
            ->where('enabled', true)
            ->first();

        $subjectTemplate = $template?->subject ?: $this->fallbackEmailSubject($eventKey);
        $bodyTemplate = $template?->body ?: $this->fallbackEmailBody($eventKey);

        $subject = trim($this->templateRenderer->render((string) $subjectTemplate, $context));
        $body = $this->templateRenderer->render((string) $bodyTemplate, $context);

        if ($subject === '') {
            $subject = $this->fallbackEmailSubject($eventKey);
        }

        $subject = Str::limit($subject, 255, '');
        $htmlBody = $this->toHtmlBody($body);

        $recipients = $this->resolveEmailRecipients($tenant);
        if ($recipients === []) {
            return [false, 'Sem destinatario de email valido.'];
        }

        $sent = false;
        $errors = [];

        foreach ($recipients as $email) {
            try {
                Mail::send([], [], function ($message) use ($email, $subject, $htmlBody): void {
                    $message
                        ->to($email)
                        ->subject($subject)
                        ->html($htmlBody);
                });

                $sent = true;
            } catch (\Throwable $e) {
                $errors[] = "Falha ao enviar email para {$email}: {$e->getMessage()}";
                Log::warning('trial_reminder_email_send_failed', array_merge($meta, [
                    'event_key' => $eventKey,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]));
            }
        }

        return [$sent, $errors !== [] ? implode(' | ', $errors) : null];
    }

    private function toHtmlBody(string $body): string
    {
        if (preg_match('/<[^>]+>/', $body) === 1) {
            return $body;
        }

        return nl2br(e($body));
    }

    /**
     * @return array<int, string>
     */
    private function resolveIntendedChannels(Tenant $tenant): array
    {
        $channels = ['internal'];

        if ($this->resolveEmailRecipients($tenant) !== []) {
            $channels[] = 'email';
        }

        if (trim((string) $tenant->phone) !== '') {
            $channels[] = 'whatsapp';
        }

        return $channels;
    }

    /**
     * @return array<int, string>
     */
    private function resolveEmailRecipients(Tenant $tenant): array
    {
        $tenant->loadMissing('admin');

        $candidates = [
            $tenant->email,
            $tenant->admin?->email,
        ];

        $emails = collect($candidates)
            ->filter(static fn ($value): bool => is_string($value) && trim($value) !== '')
            ->map(static fn (string $email): string => strtolower(trim($email)))
            ->filter(static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();

        return $emails;
    }

    private function resolveUpgradeUrl(Tenant $tenant): string
    {
        try {
            return route('tenant.plan-change-request.create', ['slug' => $tenant->subdomain]);
        } catch (\Throwable) {
            return $this->resolveLoginUrl($tenant);
        }
    }

    private function resolveLoginUrl(Tenant $tenant): string
    {
        try {
            return route('tenant.login', ['slug' => $tenant->subdomain]);
        } catch (\Throwable) {
            return url('/customer/' . $tenant->subdomain . '/login');
        }
    }

    private function fallbackEmailSubject(string $eventKey): string
    {
        return match ($eventKey) {
            self::EVENT_ENDS_IN_7_DAYS => 'Seu periodo de teste termina em 7 dias',
            self::EVENT_ENDS_IN_3_DAYS => 'Seu periodo de teste termina em 3 dias',
            self::EVENT_ENDS_TODAY => 'Seu periodo de teste termina hoje',
            self::EVENT_EXPIRED => 'Seu periodo de teste expirou',
            default => 'Atualizacao sobre seu periodo de teste',
        };
    }

    private function fallbackEmailBody(string $eventKey): string
    {
        return match ($eventKey) {
            self::EVENT_ENDS_IN_7_DAYS => 'Seu periodo de teste termina em 7 dias. Escolha um plano para continuar com acesso completo.\n\nAcesse: {{upgrade_url}}',
            self::EVENT_ENDS_IN_3_DAYS => 'Seu periodo de teste termina em 3 dias. Evite interrupcoes escolhendo seu plano agora.\n\nAcesse: {{upgrade_url}}',
            self::EVENT_ENDS_TODAY => 'Seu periodo de teste termina hoje. Escolha um plano para continuar usando o sistema sem interrupcoes.\n\nAcesse: {{upgrade_url}}',
            self::EVENT_EXPIRED => 'Seu periodo de teste expirou. Seu acesso foi pausado. Escolha um plano para reativar sua conta.\n\nAcesse: {{upgrade_url}}',
            default => 'Atualize seu plano para continuar usando o sistema.\n\nAcesse: {{upgrade_url}}',
        };
    }

    private function internalNotificationTitle(string $eventKey, Tenant $tenant): string
    {
        $tenantName = (string) ($tenant->trade_name ?: $tenant->legal_name ?: $tenant->id);

        return match ($eventKey) {
            self::EVENT_ENDS_IN_7_DAYS => "Trial: faltam 7 dias ({$tenantName})",
            self::EVENT_ENDS_IN_3_DAYS => "Trial: faltam 3 dias ({$tenantName})",
            self::EVENT_ENDS_TODAY => "Trial termina hoje ({$tenantName})",
            self::EVENT_EXPIRED => "Trial expirado ({$tenantName})",
            default => "Lembrete de trial ({$tenantName})",
        };
    }

    private function internalNotificationMessage(string $eventKey, array $context): string
    {
        $base = match ($eventKey) {
            self::EVENT_ENDS_IN_7_DAYS => 'O trial comercial termina em 7 dias.',
            self::EVENT_ENDS_IN_3_DAYS => 'O trial comercial termina em 3 dias.',
            self::EVENT_ENDS_TODAY => 'O trial comercial termina hoje.',
            self::EVENT_EXPIRED => 'O trial comercial expirou e o acesso foi pausado.',
            default => 'Atualizacao de trial comercial.',
        };

        return sprintf(
            "%s Tenant: %s | Plano: %s | Fim do trial: %s | Upgrade: %s",
            $base,
            (string) ($context['tenant_name'] ?? '-'),
            (string) ($context['plan_name'] ?? '-'),
            (string) ($context['trial_ends_at'] ?? '-'),
            (string) ($context['upgrade_url'] ?? '-')
        );
    }

    private function resolveTimezone(): string
    {
        $timezone = (string) (function_exists('sysconfig')
            ? sysconfig('timezone', config('app.timezone', 'America/Sao_Paulo'))
            : config('app.timezone', 'America/Sao_Paulo'));

        return $timezone !== '' ? $timezone : 'America/Sao_Paulo';
    }
}
