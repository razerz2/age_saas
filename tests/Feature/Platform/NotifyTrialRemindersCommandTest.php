<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\TrialReminderDispatch;
use App\Services\Platform\WhatsAppOfficialMessageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

function createTrialSubscriptionForReminder(
    int $daysUntilEnd,
    array $subscriptionOverrides = [],
    array $planOverrides = [],
    array $tenantOverrides = []
): Subscription {
    $plan = Plan::factory()->create(array_merge([
        'plan_type' => Plan::TYPE_REAL,
        'is_active' => true,
        'trial_enabled' => true,
        'trial_days' => 14,
    ], $planOverrides));

    $tenant = Tenant::factory()->create(array_merge([
        'trade_name' => 'Clinica Trial',
        'email' => 'trial+' . uniqid() . '@example.com',
        'phone' => '5565999999999',
    ], $tenantOverrides));

    $trialEndsAt = now()->addDays($daysUntilEnd)->endOfDay();

    return Subscription::factory()->create(array_merge([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'trialing',
        'starts_at' => now()->subDays(7),
        'ends_at' => now()->addDays(max(0, $daysUntilEnd)),
        'is_trial' => true,
        'trial_ends_at' => $trialEndsAt,
        'auto_renew' => false,
    ], $subscriptionOverrides));
}

afterEach(function (): void {
    Carbon::setTestNow();
});

it('envia lembrete de trial em 7 dias uma unica vez com idempotencia', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-19 09:00:00'));
    Mail::fake();

    $subscription = createTrialSubscriptionForReminder(7);

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')
        ->once()
        ->andReturn(true);
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    Artisan::call('subscriptions:notify-trial-reminders');
    Artisan::call('subscriptions:notify-trial-reminders');

    $dispatch = TrialReminderDispatch::query()
        ->where('subscription_id', $subscription->id)
        ->first();

    expect($dispatch)->not->toBeNull()
        ->and($dispatch?->event_key)->toBe('trial.ends_in_7_days')
        ->and($dispatch?->status)->toBe('sent')
        ->and($dispatch?->attempts)->toBe(1)
        ->and($dispatch?->channels_sent)->toContain('email')
        ->and($dispatch?->channels_sent)->toContain('whatsapp')
        ->and($dispatch?->channels_sent)->toContain('internal');
});

it('envia lembrete de trial em 3 dias', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-19 09:00:00'));
    Mail::fake();

    $subscription = createTrialSubscriptionForReminder(3);

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')
        ->once()
        ->andReturn(true);
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    Artisan::call('subscriptions:notify-trial-reminders');

    $dispatch = TrialReminderDispatch::query()
        ->where('subscription_id', $subscription->id)
        ->first();

    expect($dispatch)->not->toBeNull()
        ->and($dispatch?->event_key)->toBe('trial.ends_in_3_days')
        ->and($dispatch?->status)->toBe('sent');
});

it('envia lembrete de trial que termina hoje', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-19 09:00:00'));
    Mail::fake();

    $subscription = createTrialSubscriptionForReminder(0);

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')
        ->once()
        ->andReturn(true);
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    Artisan::call('subscriptions:notify-trial-reminders');

    $dispatch = TrialReminderDispatch::query()
        ->where('subscription_id', $subscription->id)
        ->first();

    expect($dispatch)->not->toBeNull()
        ->and($dispatch?->event_key)->toBe('trial.ends_today')
        ->and($dispatch?->status)->toBe('sent');
});

it('envia lembrete de trial expirado apenas uma vez', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-19 09:00:00'));
    Mail::fake();

    $subscription = createTrialSubscriptionForReminder(-2, [
        'status' => 'canceled',
        'ends_at' => now()->subDay(),
    ]);

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')
        ->once()
        ->andReturn(true);
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    Artisan::call('subscriptions:notify-trial-reminders');
    Artisan::call('subscriptions:notify-trial-reminders');

    $dispatch = TrialReminderDispatch::query()
        ->where('subscription_id', $subscription->id)
        ->first();

    expect($dispatch)->not->toBeNull()
        ->and($dispatch?->event_key)->toBe('trial.expired')
        ->and($dispatch?->status)->toBe('sent')
        ->and($dispatch?->attempts)->toBe(1);
});

it('nao envia lembretes quando trial ja foi convertido para assinatura paga', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-19 09:00:00'));
    Mail::fake();

    $trialSubscription = createTrialSubscriptionForReminder(3);

    Subscription::factory()->create([
        'tenant_id' => $trialSubscription->tenant_id,
        'plan_id' => Plan::factory()->create([
            'plan_type' => Plan::TYPE_REAL,
            'is_active' => true,
        ])->id,
        'status' => 'pending',
        'is_trial' => false,
        'auto_renew' => true,
    ]);

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')->never();
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    Artisan::call('subscriptions:notify-trial-reminders');

    expect(TrialReminderDispatch::query()->count())->toBe(0);
});

it('nao envia lembretes para assinatura trial em plano de teste', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-19 09:00:00'));
    Mail::fake();

    createTrialSubscriptionForReminder(7, [], [
        'plan_type' => Plan::TYPE_TEST,
        'trial_enabled' => true,
    ]);

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')->never();
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    Artisan::call('subscriptions:notify-trial-reminders');

    expect(TrialReminderDispatch::query()->count())->toBe(0);
});

it('dispara apenas notificacao interna quando nao ha destinatarios de email e whatsapp', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-19 09:00:00'));
    Mail::fake();

    $subscription = createTrialSubscriptionForReminder(3, [], [], [
        'email' => null,
        'phone' => null,
    ]);

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')->never();
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    Artisan::call('subscriptions:notify-trial-reminders');

    $dispatch = TrialReminderDispatch::query()
        ->where('subscription_id', $subscription->id)
        ->first();

    expect($dispatch)->not->toBeNull()
        ->and($dispatch?->status)->toBe('sent')
        ->and($dispatch?->channels_sent)->toBe(['internal']);
});
