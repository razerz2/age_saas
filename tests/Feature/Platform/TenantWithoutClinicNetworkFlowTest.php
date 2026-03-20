<?php

use App\Models\Platform\Tenant;
use App\Models\Platform\User;
use Illuminate\Support\Str;

function createPlatformOperator(array $modules = ['tenants']): User
{
    return User::query()->create([
        'name' => 'Operador',
        'name_full' => 'Operador Platform',
        'email' => 'operator+'.Str::lower(Str::random(6)).'@example.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => $modules,
    ]);
}

test('pagina de criacao de tenant abre sem depender de ClinicNetwork', function () {
    $user = createPlatformOperator(['tenants']);

    $response = $this
        ->actingAs($user, 'web')
        ->get(route('Platform.tenants.create'));

    $response->assertOk();
    $response->assertDontSee('name="network_id"', false);
});

test('pagina de edicao de tenant nao renderiza campo de rede', function () {
    $tenant = Tenant::factory()->create();
    $user = createPlatformOperator(['tenants']);

    $response = $this
        ->actingAs($user, 'web')
        ->get(route('Platform.tenants.edit', $tenant->id));

    $response->assertOk();
    $response->assertDontSee('name="network_id"', false);
});

test('pagina de detalhes de tenant nao depende de relacionamento de rede', function () {
    $tenant = Tenant::factory()->create();
    $user = createPlatformOperator(['tenants']);

    $response = $this
        ->actingAs($user, 'web')
        ->get(route('Platform.tenants.show', $tenant->id));

    $response->assertOk();
    $response->assertDontSee('clinic-networks');
    $response->assertDontSee('Rede de Cl');
});
