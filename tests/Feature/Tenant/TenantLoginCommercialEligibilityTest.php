<?php

use App\Http\Middleware\EnsureTenantCommercialEligibility;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Auth;

test('login tenant é bloqueado quando a tenant não é elegível comercialmente', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->from(route('tenant.login', ['slug' => $tenant->subdomain]))
        ->post(route('tenant.login.submit', ['slug' => $tenant->subdomain]), [
            'email' => 'admin@example.com',
            'password' => 'secret',
        ]);

    $response->assertRedirect(route('tenant.login', ['slug' => $tenant->subdomain]));
    $response->assertSessionHas('error', EnsureTenantCommercialEligibility::BLOCKED_ACCESS_MESSAGE);
    expect(Auth::guard('tenant')->check())->toBeFalse();
});

test('login tenant também bloqueia quando existe apenas tenants.plan_id legado', function () {
    $plan = Plan::factory()->create();
    $tenant = Tenant::factory()->create([
        'plan_id' => $plan->id,
    ]);

    $response = $this->from(route('tenant.login', ['slug' => $tenant->subdomain]))
        ->post(route('tenant.login.submit', ['slug' => $tenant->subdomain]), [
            'email' => 'admin@example.com',
            'password' => 'secret',
        ]);

    $response->assertRedirect(route('tenant.login', ['slug' => $tenant->subdomain]));
    $response->assertSessionHas('error', EnsureTenantCommercialEligibility::BLOCKED_ACCESS_MESSAGE);
    expect(Auth::guard('tenant')->check())->toBeFalse();
});
