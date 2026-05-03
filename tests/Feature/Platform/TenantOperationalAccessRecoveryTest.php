<?php

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use App\Services\Platform\WhatsAppOfficialMessageService;
use Illuminate\Support\Facades\Artisan;

test('subscription update from real past due to active test plan restores tenant operational access', function () {
    $tenant = Tenant::factory()->create([
        'status' => 'suspended',
        'suspended_at' => now()->subDay(),
        'canceled_at' => now()->subHour(),
        'subdomain' => 'clinica-teste-' . uniqid(),
    ]);

    $realPlan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_REAL,
        'category' => Plan::CATEGORY_COMMERCIAL,
    ]);

    $testPlan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_TEST,
        'category' => Plan::CATEGORY_SANDBOX,
    ]);

    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $realPlan->id,
        'status' => 'past_due',
        'payment_method' => 'PIX',
        'auto_renew' => false,
    ]);

    Invoices::query()->create([
        'subscription_id' => $subscription->id,
        'tenant_id' => $tenant->id,
        'amount_cents' => 9990,
        'due_date' => now()->subDays(10),
        'status' => 'overdue',
        'payment_method' => 'PIX',
        'provider' => 'asaas',
        'provider_id' => 'invoice-old-' . uniqid(),
    ]);

    $response = $this->withoutMiddleware()->put(route('Platform.subscriptions.update', $subscription), [
        'tenant_id' => $tenant->id,
        'plan_id' => $testPlan->id,
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
        'auto_renew' => 0,
    ]);

    $response->assertRedirect(route('Platform.subscriptions.index'));

    $tenant->refresh();
    expect($tenant->status)->toBe('active')
        ->and($tenant->suspended_at)->toBeNull()
        ->and($tenant->canceled_at)->toBeNull();

    $loginResponse = $this->get("/customer/{$tenant->subdomain}/login");
    $loginResponse->assertStatus(200);
});

test('check overdue invoices does not suspend tenant for overdue invoice from old subscription when current test subscription is active', function () {
    $tenant = Tenant::factory()->create([
        'status' => 'active',
    ]);

    $realPlan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_REAL,
        'category' => Plan::CATEGORY_COMMERCIAL,
    ]);
    $testPlan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_TEST,
        'category' => Plan::CATEGORY_SANDBOX,
    ]);

    $oldSubscription = Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $realPlan->id,
        'status' => 'canceled',
        'ends_at' => now()->subDay(),
    ]);

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $testPlan->id,
        'status' => 'active',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addMonth(),
    ]);

    Invoices::query()->create([
        'subscription_id' => $oldSubscription->id,
        'tenant_id' => $tenant->id,
        'amount_cents' => 19990,
        'due_date' => now()->subDays(5),
        'status' => 'overdue',
        'payment_method' => 'PIX',
        'provider' => 'asaas',
        'provider_id' => 'invoice-overdue-' . uniqid(),
    ]);

    $officialMock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $officialMock->shouldReceive('sendByKey')->never();
    app()->instance(WhatsAppOfficialMessageService::class, $officialMock);

    Artisan::call('invoices:invoices-check-overdue');

    $tenant->refresh();
    expect($tenant->status)->toBe('active');
});

test('process recovery does not cancel tenant when an active eligible subscription already exists', function () {
    if (function_exists('set_sysconfig')) {
        set_sysconfig('billing.recovery_days_after_suspension', 5);
    }

    $tenant = Tenant::factory()->create([
        'status' => 'suspended',
        'suspended_at' => now()->subDays(10),
        'canceled_at' => null,
    ]);

    $realPlan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_REAL,
        'category' => Plan::CATEGORY_COMMERCIAL,
    ]);
    $testPlan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_TEST,
        'category' => Plan::CATEGORY_SANDBOX,
    ]);

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $testPlan->id,
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(20),
    ]);

    $recoveryPending = Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $realPlan->id,
        'status' => 'recovery_pending',
        'recovery_started_at' => now()->subDays(6),
        'starts_at' => now()->subDays(6),
        'ends_at' => null,
    ]);

    $asaasMock = Mockery::mock(AsaasService::class);
    $asaasMock->shouldReceive('deleteSubscription')->never();
    $asaasMock->shouldReceive('createPaymentLink')->never();
    app()->instance(AsaasService::class, $asaasMock);

    $officialMock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $officialMock->shouldReceive('sendByKey')->never();
    app()->instance(WhatsAppOfficialMessageService::class, $officialMock);

    Artisan::call('subscriptions:process-recovery');

    $tenant->refresh();
    $recoveryPending->refresh();

    expect($tenant->status)->not->toBe('canceled')
        ->and($tenant->canceled_at)->toBeNull()
        ->and($recoveryPending->status)->toBe('recovery_pending');
});
