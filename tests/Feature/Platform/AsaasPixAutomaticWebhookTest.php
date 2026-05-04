<?php

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;

function createTenantForPixAutomaticWebhook(string $suffix, array $overrides = []): Tenant
{
    return Tenant::query()->create(array_merge([
        'legal_name' => 'Tenant Webhook Pix Auto ' . $suffix,
        'trade_name' => 'Tenant Webhook Pix Auto ' . $suffix,
        'document' => null,
        'email' => "tenant-webhook-pix-auto-{$suffix}@example.com",
        'phone' => '5565999999999',
        'subdomain' => 'tenant-webhook-pix-auto-' . $suffix,
        'db_host' => '127.0.0.1',
        'db_port' => 5432,
        'db_name' => 'tenant_webhook_pix_auto_' . $suffix,
        'db_username' => 'tenant_user',
        'db_password' => 'tenant_pass',
        'status' => 'active',
    ], $overrides));
}

function createPlanForPixAutomaticWebhook(): Plan
{
    return Plan::query()->create([
        'name' => 'Plano Webhook Pix Automatico',
        'description' => 'Plano para testes de webhook Pix Automatico',
        'periodicity' => 'monthly',
        'period_months' => 1,
        'price_cents' => 25990,
        'category' => 'commercial',
        'features' => [],
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
    ]);
}

test('webhook processes PIX_AUTOMATIC authorization activated', function () {
    $tenant = createTenantForPixAutomaticWebhook('auth-activated', [
        'asaas_customer_id' => 'cus_auth_activated',
    ]);
    $plan = createPlanForPixAutomaticWebhook();

    $subscription = Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'due_day' => 10,
        'status' => 'pending',
        'auto_renew' => true,
        'payment_method' => 'PIX_AUTOMATIC',
        'asaas_pix_automatic_authorization_id' => 'auth_prev_1',
        'asaas_pix_automatic_authorization_status' => 'created',
    ]);

    $response = $this->withoutMiddleware()->postJson('/webhook/asaas', [
        'event' => 'PIX_AUTOMATIC_RECURRING_AUTHORIZATION_ACTIVATED',
        'customer' => ['id' => 'cus_auth_activated'],
        'authorization' => [
            'id' => 'auth_new_activated',
            'status' => 'ACTIVE',
            'externalReference' => $subscription->id,
        ],
    ]);

    $response->assertOk();

    $subscription->refresh();
    expect($subscription->asaas_pix_automatic_authorization_id)->toBe('auth_new_activated');
    expect($subscription->asaas_pix_automatic_authorization_status)->toBe('active');
    expect($subscription->status)->toBe('pending');
});

test('webhook processes PIX_AUTOMATIC authorization cancelled', function () {
    $tenant = createTenantForPixAutomaticWebhook('auth-cancelled', [
        'asaas_customer_id' => 'cus_auth_cancelled',
    ]);
    $plan = createPlanForPixAutomaticWebhook();

    $subscription = Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'due_day' => 10,
        'status' => 'pending',
        'auto_renew' => true,
        'payment_method' => 'PIX_AUTOMATIC',
        'asaas_pix_automatic_authorization_id' => 'auth_prev_cancel',
        'asaas_pix_automatic_authorization_status' => 'active',
    ]);

    $response = $this->withoutMiddleware()->postJson('/webhook/asaas', [
        'event' => 'PIX_AUTOMATIC_RECURRING_AUTHORIZATION_CANCELLED',
        'customer' => ['id' => 'cus_auth_cancelled'],
        'authorization' => [
            'id' => 'auth_cancelled',
            'status' => 'CANCELLED',
            'externalReference' => $subscription->id,
        ],
    ]);

    $response->assertOk();

    $subscription->refresh();
    expect($subscription->asaas_pix_automatic_authorization_id)->toBe('auth_cancelled');
    expect($subscription->asaas_pix_automatic_authorization_status)->toBe('cancelled');
    expect($subscription->status)->toBe('canceled');
});

test('payment confirmed remains final source for paid invoice and subscription activation', function () {
    $tenant = createTenantForPixAutomaticWebhook('payment-confirmed', [
        'asaas_customer_id' => 'cus_payment_confirmed',
    ]);
    $plan = createPlanForPixAutomaticWebhook();

    $subscription = Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'due_day' => 10,
        'status' => 'pending',
        'auto_renew' => true,
        'payment_method' => 'PIX_AUTOMATIC',
        'asaas_pix_automatic_authorization_id' => 'auth_payment_confirmed',
        'asaas_pix_automatic_authorization_status' => 'active',
    ]);

    $invoice = Invoices::query()->create([
        'subscription_id' => $subscription->id,
        'tenant_id' => $tenant->id,
        'amount_cents' => 25990,
        'due_date' => now()->toDateString(),
        'status' => 'pending',
        'payment_method' => 'PIX_AUTOMATIC',
        'provider' => 'asaas',
        'provider_id' => 'pay_confirmed_1',
        'asaas_payment_id' => 'pay_confirmed_1',
    ]);

    $response = $this->withoutMiddleware()->postJson('/webhook/asaas', [
        'event' => 'PAYMENT_CONFIRMED',
        'customer' => ['id' => 'cus_payment_confirmed'],
        'payment' => [
            'id' => 'pay_confirmed_1',
            'externalReference' => $subscription->id,
            'status' => 'RECEIVED',
            'value' => 259.90,
            'dueDate' => now()->toDateString(),
            'paymentDate' => now()->toDateString(),
        ],
    ]);

    $response->assertOk();

    $invoice->refresh();
    $subscription->refresh();

    expect($invoice->status)->toBe('paid');
    expect($invoice->paid_at)->not->toBeNull();
    expect($subscription->status)->toBe('active');
});
