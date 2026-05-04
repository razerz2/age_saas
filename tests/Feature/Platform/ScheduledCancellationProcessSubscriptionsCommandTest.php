<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Support\Carbon;

test('scheduled cancellation is not processed before end of cycle', function () {
    $tenant = Tenant::factory()->create(['status' => 'active']);
    $plan = Plan::factory()->create();

    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'auto_renew' => false,
        'ends_at' => now()->addDay(),
        'cancel_at_period_end' => true,
        'cancel_requested_at' => now()->subDay(),
        'cancellation_status' => 'pending_period_end',
        'cancellation_processed_at' => null,
    ]);

    $this->artisan('subscriptions:subscriptions-process')->assertSuccessful();

    $subscription->refresh();

    expect($subscription->status)->toBe('active')
        ->and($subscription->cancellation_status)->toBe('pending_period_end')
        ->and($subscription->cancellation_processed_at)->toBeNull()
        ->and($subscription->auto_renew)->toBeFalse();
});

test('scheduled cancellation is processed after end of cycle and remains idempotent', function () {
    $tenant = Tenant::factory()->create(['status' => 'active']);
    $plan = Plan::factory()->create();

    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'auto_renew' => false,
        'ends_at' => now()->subMinute(),
        'cancel_at_period_end' => true,
        'cancel_requested_at' => now()->subDays(2),
        'cancellation_reason' => 'Teste de cancelamento programado',
        'cancellation_status' => 'pending_period_end',
        'cancellation_processed_at' => null,
    ]);

    $this->artisan('subscriptions:subscriptions-process')->assertSuccessful();

    $subscription->refresh();
    $firstProcessedAt = $subscription->cancellation_processed_at;

    expect($subscription->status)->toBe('canceled')
        ->and($subscription->cancellation_status)->toBe('processed')
        ->and($subscription->cancellation_processed_at)->not->toBeNull()
        ->and($subscription->auto_renew)->toBeFalse();

    Carbon::setTestNow(now()->addMinute());
    $this->artisan('subscriptions:subscriptions-process')->assertSuccessful();
    Carbon::setTestNow();

    $subscription->refresh();

    expect($subscription->status)->toBe('canceled')
        ->and($subscription->cancellation_status)->toBe('processed')
        ->and($subscription->cancellation_processed_at?->toDateTimeString())
            ->toBe($firstProcessedAt?->toDateTimeString())
        ->and($subscription->auto_renew)->toBeFalse();
});
