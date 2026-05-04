<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Tenant\User as TenantUser;
use Illuminate\Support\Str;

function makeTenantGuardUser(Tenant $tenant, string $role = 'admin'): TenantUser
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

function setCurrentTenant(Tenant $tenant): void
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

test('rotas de assinatura estao no grupo protegido do tenant', function () {
    $show = app('router')->getRoutes()->getByName('tenant.subscription.show');
    $refresh = app('router')->getRoutes()->getByName('tenant.subscription.invoices.refresh-status');

    expect($show)->not->toBeNull();
    expect($refresh)->not->toBeNull();

    $showMiddleware = $show->gatherMiddleware();
    $refreshMiddleware = $refresh->gatherMiddleware();

    expect($showMiddleware)->toContain('tenant.auth');
    expect($showMiddleware)->toContain('tenant.commercial.eligibility');
    expect($refreshMiddleware)->toContain('tenant.auth');
    expect($refreshMiddleware)->toContain('tenant.commercial.eligibility');
});

test('admin tenant acessa minha assinatura', function () {
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
    ]);

    setCurrentTenant($tenant);
    auth('tenant')->setUser(makeTenantGuardUser($tenant, 'admin'));

    $response = $this->withoutMiddleware()->get(route('tenant.subscription.show', ['slug' => $tenant->subdomain]));

    $response->assertOk();
    $response->assertSee('Minha assinatura');
});

test('usuario tenant nao admin nao acessa minha assinatura', function () {
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
    ]);

    setCurrentTenant($tenant);
    auth('tenant')->setUser(makeTenantGuardUser($tenant, 'user'));

    $response = $this->withoutMiddleware()->get(route('tenant.subscription.show', ['slug' => $tenant->subdomain]));

    $response->assertForbidden();
});