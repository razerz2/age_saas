<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;

function createTenantForPaymentMethodAvailability(string $suffix): Tenant
{
    return Tenant::query()->create([
        'legal_name' => 'Tenant PM ' . $suffix,
        'trade_name' => 'Tenant PM ' . $suffix,
        'document' => null,
        'email' => "tenant-pm-{$suffix}@example.com",
        'phone' => '5565999999999',
        'subdomain' => 'tenant-pm-' . $suffix,
        'db_host' => '127.0.0.1',
        'db_port' => 5432,
        'db_name' => 'tenant_pm_' . $suffix,
        'db_username' => 'tenant_user',
        'db_password' => 'tenant_pass',
        'status' => 'active',
    ]);
}

function createPlanForPaymentMethodAvailability(): Plan
{
    return Plan::query()->create([
        'name' => 'Plano PM',
        'description' => 'Plano para testes de metodo de pagamento',
        'periodicity' => 'monthly',
        'period_months' => 1,
        'price_cents' => 19990,
        'category' => 'commercial',
        'features' => [],
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
    ]);
}

test('active payment methods appear on subscription create form', function () {
    set_sysconfig('billing.payment_methods.pix_enabled', '1');
    set_sysconfig('billing.payment_methods.boleto_enabled', '1');
    set_sysconfig('billing.payment_methods.credit_card_enabled', '0');
    set_sysconfig('billing.payment_methods.pix_recurrent_enabled', '0');
    set_sysconfig('billing.payment_methods.debit_card_enabled', '0');

    $response = $this->withoutMiddleware()->get(route('Platform.subscriptions.create'));

    $response->assertOk()
        ->assertSee('PIX manual')
        ->assertSee('Boleto')
        ->assertDontSee('Cartao de credito recorrente')
        ->assertDontSee('PIX recorrente')
        ->assertDontSee('Cartao de debito');
});

test('disabled payment method does not appear for new subscription and is blocked by request', function () {
    set_sysconfig('billing.payment_methods.credit_card_enabled', '0');
    set_sysconfig('billing.payment_methods.pix_enabled', '1');

    $tenant = createTenantForPaymentMethodAvailability('new-disabled');
    $plan = createPlanForPaymentMethodAvailability();

    $createResponse = $this->withoutMiddleware()->get(route('Platform.subscriptions.create'));
    $createResponse->assertOk()->assertDontSee('Cartao de credito recorrente');

    $postResponse = $this->withoutMiddleware()->from(route('Platform.subscriptions.create'))->post(route('Platform.subscriptions.store'), [
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->format('Y-m-d'),
        'ends_at' => now()->addMonth()->format('Y-m-d'),
        'due_day' => 10,
        'status' => 'pending',
        'auto_renew' => 1,
        'payment_method' => 'CREDIT_CARD',
    ]);

    $postResponse->assertRedirect(route('Platform.subscriptions.create'))
        ->assertSessionHasErrors(['payment_method']);
});

test('edit form preserves current disabled payment method with warning', function () {
    set_sysconfig('billing.payment_methods.debit_card_enabled', '0');
    set_sysconfig('billing.payment_methods.pix_enabled', '1');

    $tenant = createTenantForPaymentMethodAvailability('edit-disabled');
    $plan = createPlanForPaymentMethodAvailability();

    $subscription = Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'due_day' => 10,
        'status' => 'active',
        'auto_renew' => true,
        'payment_method' => 'DEBIT_CARD',
    ]);

    $response = $this->withoutMiddleware()->get(route('Platform.subscriptions.edit', $subscription));

    $response->assertOk()
        ->assertSee('Metodo atualmente desativado para novas assinaturas.')
        ->assertSee('Cartao de debito');
});

