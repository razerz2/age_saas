<?php

use App\Http\Middleware\EnsureTenantCommercialEligibility;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'tenant.commercial.eligibility'])
        ->get('/__tests/workspace/{slug}/eligibility', function () {
            return response('eligible-ok', 200);
        });
});

test('tenant sem plano e sem assinatura fica bloqueada ao acessar rota protegida', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->get("/__tests/workspace/{$tenant->subdomain}/eligibility");

    $response->assertRedirect(route('tenant.login', ['slug' => $tenant->subdomain]));
    $response->assertSessionHas('error', EnsureTenantCommercialEligibility::BLOCKED_ACCESS_MESSAGE);
});

test('tenant com plan_id legado mas sem assinatura ativa continua bloqueada', function () {
    $plan = Plan::factory()->create();
    $tenant = Tenant::factory()->create([
        'plan_id' => $plan->id,
    ]);

    $response = $this->get("/__tests/workspace/{$tenant->subdomain}/eligibility");

    $response->assertRedirect(route('tenant.login', ['slug' => $tenant->subdomain]));
    $response->assertSessionHas('error', EnsureTenantCommercialEligibility::BLOCKED_ACCESS_MESSAGE);
});

test('tenant com assinatura ativa e plano válido acessa rota protegida', function () {
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    $response = $this->get("/__tests/workspace/{$tenant->subdomain}/eligibility");

    $response->assertOk();
    $response->assertSee('eligible-ok');
});

test('acesso direto por URL protegida continua bloqueado sem elegibilidade', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->get("/__tests/workspace/{$tenant->subdomain}/eligibility");

    $response->assertRedirect(route('tenant.login', ['slug' => $tenant->subdomain]));
});

test('tenant com trial expirado mantem sessao e recebe bloqueio controlado', function () {
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_REAL,
        'is_active' => true,
    ]);

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'canceled',
        'is_trial' => true,
        'starts_at' => now()->subDays(15),
        'trial_ends_at' => now()->subDay(),
        'ends_at' => now()->subDay(),
        'auto_renew' => false,
    ]);

    $response = $this->get("/__tests/workspace/{$tenant->subdomain}/eligibility");

    $response->assertRedirect(route('tenant.dashboard', ['slug' => $tenant->subdomain]));
    $response->assertSessionHas('warning');
});

test('rota tenant dashboard inclui middleware de elegibilidade comercial', function () {
    $middlewareList = app('router')->getRoutes()->getByName('tenant.dashboard')->gatherMiddleware();

    expect($middlewareList)->toContain('tenant.commercial.eligibility');
});

test('platform segue acessível normalmente', function () {
    $response = $this->get('/Platform/login');

    $response->assertStatus(200);
});
