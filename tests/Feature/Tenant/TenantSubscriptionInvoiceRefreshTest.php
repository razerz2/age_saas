<?php

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Tenant\User as TenantUser;
use App\Services\AsaasService;
use Illuminate\Support\Str;

function makeTenantGuardUserForRefresh(Tenant $tenant, string $role = 'admin'): TenantUser
{
    $user = new TenantUser();
    $user->id = (string) Str::uuid();
    $user->tenant_id = $tenant->id;
    $user->name = 'Teste';
    $user->name_full = 'Usuário Teste';
    $user->email = 'tenant+'.Str::lower(Str::random(8)).'@example.com';
    $user->role = $role;

    return $user;
}

function setCurrentTenantForRefresh(Tenant $tenant): void
{
    app()->instance(config('multitenancy.current_tenant_container_key', 'currentTenant'), $tenant);
}

beforeEach(function () {
    app()->forgetInstance(config('multitenancy.current_tenant_container_key', 'currentTenant'));
});

afterEach(function () {
    app()->forgetInstance(config('multitenancy.current_tenant_container_key', 'currentTenant'));
    auth('tenant')->logout();
});

function createTenantSubscriptionInvoice(Tenant $tenant, array $invoiceOverrides = []): array
{
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
    ]);

    $invoice = Invoices::create(array_merge([
        'tenant_id' => $tenant->id,
        'subscription_id' => $subscription->id,
        'amount_cents' => 10000,
        'due_date' => now()->addDays(3),
        'status' => 'pending',
        'payment_method' => 'PIX',
        'provider' => 'asaas',
        'provider_id' => null,
        'asaas_payment_id' => null,
    ], $invoiceOverrides));

    return [$subscription, $invoice];
}

test('admin nao consegue atualizar invoice de outro tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    [, $invoiceB] = createTenantSubscriptionInvoice($tenantB, [
        'provider_id' => 'pay_other',
    ]);

    setCurrentTenantForRefresh($tenantA);
    auth('tenant')->setUser(makeTenantGuardUserForRefresh($tenantA, 'admin'));

    $response = $this->withoutMiddleware()->post(route('tenant.subscription.invoices.refresh-status', [
        'slug' => $tenantA->subdomain,
        'invoice' => $invoiceB->id,
    ]));

    $response->assertNotFound();
});

test('invoice sem provider_id e asaas_payment_id retorna warning', function () {
    $tenant = Tenant::factory()->create();
    [, $invoice] = createTenantSubscriptionInvoice($tenant, [
        'provider_id' => null,
        'asaas_payment_id' => null,
    ]);

    setCurrentTenantForRefresh($tenant);
    auth('tenant')->setUser(makeTenantGuardUserForRefresh($tenant, 'admin'));

    $response = $this->withoutMiddleware()->post(route('tenant.subscription.invoices.refresh-status', [
        'slug' => $tenant->subdomain,
        'invoice' => $invoice->id,
    ]));

    $response->assertRedirect(route('tenant.subscription.show', ['slug' => $tenant->subdomain]));
    $response->assertSessionHas('warning');
});

test('refresh paid atualiza invoice para paid e define paid_at', function () {
    $tenant = Tenant::factory()->create();
    [, $invoice] = createTenantSubscriptionInvoice($tenant, [
        'provider_id' => 'pay_paid_1',
        'status' => 'pending',
        'paid_at' => null,
    ]);

    $mock = Mockery::mock(AsaasService::class);
    $mock->shouldReceive('getPaymentStatus')->once()->andReturn(['status' => 'RECEIVED']);
    app()->instance(AsaasService::class, $mock);

    setCurrentTenantForRefresh($tenant);
    auth('tenant')->setUser(makeTenantGuardUserForRefresh($tenant, 'admin'));

    $response = $this->withoutMiddleware()->post(route('tenant.subscription.invoices.refresh-status', [
        'slug' => $tenant->subdomain,
        'invoice' => $invoice->id,
    ]));

    $response->assertRedirect(route('tenant.subscription.show', ['slug' => $tenant->subdomain]));
    $response->assertSessionHas('success');

    $invoice->refresh();
    expect($invoice->status)->toBe('paid');
    expect($invoice->paid_at)->not->toBeNull();
});

test('refresh pending mantem pending', function () {
    $tenant = Tenant::factory()->create();
    [, $invoice] = createTenantSubscriptionInvoice($tenant, [
        'provider_id' => 'pay_pending_1',
        'status' => 'pending',
    ]);

    $mock = Mockery::mock(AsaasService::class);
    $mock->shouldReceive('getPaymentStatus')->once()->andReturn(['status' => 'PENDING']);
    app()->instance(AsaasService::class, $mock);

    setCurrentTenantForRefresh($tenant);
    auth('tenant')->setUser(makeTenantGuardUserForRefresh($tenant, 'admin'));

    $response = $this->withoutMiddleware()->post(route('tenant.subscription.invoices.refresh-status', [
        'slug' => $tenant->subdomain,
        'invoice' => $invoice->id,
    ]));

    $response->assertRedirect(route('tenant.subscription.show', ['slug' => $tenant->subdomain]));
    $response->assertSessionHas('info');

    $invoice->refresh();
    expect($invoice->status)->toBe('pending');
});

test('refresh overdue atualiza para overdue', function () {
    $tenant = Tenant::factory()->create();
    [, $invoice] = createTenantSubscriptionInvoice($tenant, [
        'provider_id' => 'pay_overdue_1',
        'status' => 'pending',
    ]);

    $mock = Mockery::mock(AsaasService::class);
    $mock->shouldReceive('getPaymentStatus')->once()->andReturn(['status' => 'OVERDUE']);
    app()->instance(AsaasService::class, $mock);

    setCurrentTenantForRefresh($tenant);
    auth('tenant')->setUser(makeTenantGuardUserForRefresh($tenant, 'admin'));

    $response = $this->withoutMiddleware()->post(route('tenant.subscription.invoices.refresh-status', [
        'slug' => $tenant->subdomain,
        'invoice' => $invoice->id,
    ]));

    $response->assertRedirect(route('tenant.subscription.show', ['slug' => $tenant->subdomain]));
    $response->assertSessionHas('success');

    $invoice->refresh();
    expect($invoice->status)->toBe('overdue');
});

test('usuario nao admin recebe 403 no refresh', function () {
    $tenant = Tenant::factory()->create();
    [, $invoice] = createTenantSubscriptionInvoice($tenant, [
        'provider_id' => 'pay_forbidden_1',
    ]);

    setCurrentTenantForRefresh($tenant);
    auth('tenant')->setUser(makeTenantGuardUserForRefresh($tenant, 'user'));

    $response = $this->withoutMiddleware()->post(route('tenant.subscription.invoices.refresh-status', [
        'slug' => $tenant->subdomain,
        'invoice' => $invoice->id,
    ]));

    $response->assertForbidden();
});