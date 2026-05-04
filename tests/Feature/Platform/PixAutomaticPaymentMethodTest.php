<?php

use App\Http\Controllers\Platform\SubscriptionController;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;

function createTenantForPixAutomatic(string $suffix, array $overrides = []): Tenant
{
    return Tenant::query()->create(array_merge([
        'legal_name' => 'Tenant Pix Auto ' . $suffix,
        'trade_name' => 'Tenant Pix Auto ' . $suffix,
        'document' => null,
        'email' => "tenant-pix-auto-{$suffix}@example.com",
        'phone' => '5565999999999',
        'subdomain' => 'tenant-pix-auto-' . $suffix,
        'db_host' => '127.0.0.1',
        'db_port' => 5432,
        'db_name' => 'tenant_pix_auto_' . $suffix,
        'db_username' => 'tenant_user',
        'db_password' => 'tenant_pass',
        'status' => 'active',
    ], $overrides));
}

function createPlanForPixAutomatic(): Plan
{
    return Plan::query()->create([
        'name' => 'Plano Pix Automatico',
        'description' => 'Plano para testes de Pix Automatico',
        'periodicity' => 'monthly',
        'period_months' => 1,
        'price_cents' => 29990,
        'category' => 'commercial',
        'features' => [],
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
    ]);
}

test('subscription request accepts pix automatic when enabled', function () {
    set_sysconfig('billing.payment_methods.pix_automatic_enabled', '1');
    set_sysconfig('billing.payment_methods.pix_enabled', '0');
    set_sysconfig('billing.payment_methods.pix_recurrent_enabled', '0');
    set_sysconfig('billing.payment_methods.boleto_enabled', '0');
    set_sysconfig('billing.payment_methods.credit_card_enabled', '0');
    set_sysconfig('billing.payment_methods.debit_card_enabled', '0');

    $tenant = createTenantForPixAutomatic('enabled', [
        'asaas_customer_id' => 'cus_pix_auto_enabled',
    ]);
    $plan = createPlanForPixAutomatic();

    $asaas = \Mockery::mock('overload:App\\Services\\AsaasService');
    $asaas->shouldReceive('createPixAutomaticAuthorization')
        ->once()
        ->andReturn([
            'id' => 'auth_enabled_1',
            'status' => 'PENDING',
        ]);

    $response = $this->withoutMiddleware()->post(route('Platform.subscriptions.store'), [
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->format('Y-m-d'),
        'ends_at' => now()->addMonth()->format('Y-m-d'),
        'due_day' => 10,
        'status' => 'pending',
        'auto_renew' => 1,
        'payment_method' => 'PIX_AUTOMATIC',
    ]);

    $response->assertRedirect(route('Platform.subscriptions.index'));
    $response->assertSessionHasNoErrors();

    $subscription = Subscription::query()->latest('created_at')->first();
    expect($subscription)->not->toBeNull();
    expect($subscription->payment_method)->toBe('PIX_AUTOMATIC');
    expect($subscription->asaas_pix_automatic_authorization_id)->toBe('auth_enabled_1');
});

test('subscription request rejects pix automatic when disabled', function () {
    set_sysconfig('billing.payment_methods.pix_automatic_enabled', '0');
    set_sysconfig('billing.payment_methods.pix_enabled', '1');

    $tenant = createTenantForPixAutomatic('disabled', [
        'asaas_customer_id' => 'cus_pix_auto_disabled',
    ]);
    $plan = createPlanForPixAutomatic();

    $response = $this->withoutMiddleware()
        ->from(route('Platform.subscriptions.create'))
        ->post(route('Platform.subscriptions.store'), [
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'starts_at' => now()->format('Y-m-d'),
            'ends_at' => now()->addMonth()->format('Y-m-d'),
            'due_day' => 10,
            'status' => 'pending',
            'auto_renew' => 1,
            'payment_method' => 'PIX_AUTOMATIC',
        ]);

    $response->assertRedirect(route('Platform.subscriptions.create'))
        ->assertSessionHasErrors(['payment_method']);
});

test('syncWithAsaas for pix automatic calls only pix automatic authorization endpoint', function () {
    set_sysconfig('billing.payment_methods.pix_automatic_enabled', '1');

    $tenant = createTenantForPixAutomatic('sync-pix-auto', [
        'asaas_customer_id' => 'cus_sync_pix_auto',
    ]);
    $plan = createPlanForPixAutomatic();

    $subscription = Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'due_day' => 10,
        'status' => 'pending',
        'auto_renew' => true,
        'payment_method' => 'PIX_AUTOMATIC',
    ]);

    $asaas = \Mockery::mock('overload:App\\Services\\AsaasService');
    $asaas->shouldReceive('createPixAutomaticAuthorization')
        ->once()
        ->andReturn([
            'id' => 'auth_sync_pix_auto',
            'status' => 'CREATED',
        ]);
    $asaas->shouldReceive('createSubscription')->never();

    $result = app(SubscriptionController::class)->syncWithAsaas($subscription->fresh(), true);

    expect($result)->toBeTrue();
    expect($subscription->fresh()->asaas_pix_automatic_authorization_id)->toBe('auth_sync_pix_auto');
});

test('syncWithAsaas keeps pix recurrent flow untouched', function () {
    set_sysconfig('billing.payment_methods.pix_recurrent_enabled', '1');

    $tenant = createTenantForPixAutomatic('sync-pix-recurrent', [
        'asaas_customer_id' => 'cus_sync_pix_recurrent',
    ]);
    $plan = createPlanForPixAutomatic();

    $subscription = Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'due_day' => 10,
        'status' => 'pending',
        'auto_renew' => true,
        'payment_method' => 'PIX_RECURRENT',
    ]);

    $asaas = \Mockery::mock('overload:App\\Services\\AsaasService');
    $asaas->shouldReceive('createSubscription')
        ->once()
        ->andReturn([
            'subscription' => ['id' => 'sub_pix_recurrent_1'],
        ]);
    $asaas->shouldReceive('createPixAutomaticAuthorization')->never();

    $result = app(SubscriptionController::class)->syncWithAsaas($subscription->fresh(), true);

    expect($result)->toBeTrue();
    expect($subscription->fresh()->asaas_subscription_id)->toBe('sub_pix_recurrent_1');
});
