<?php

use App\Models\Platform\Tenant;
use Illuminate\Support\Str;

function createTenantForPlatformUpdate(array $overrides = []): Tenant
{
    $suffix = Str::lower(Str::random(8));

    return Tenant::query()->create(array_merge([
        'legal_name' => 'Tenant Update ' . $suffix,
        'trade_name' => 'Tenant Update ' . $suffix,
        'document' => generateValidCnpj(),
        'email' => "tenant-update-{$suffix}@example.com",
        'phone' => '5565999999999',
        'subdomain' => 'tenant-update-' . $suffix,
        'db_host' => '127.0.0.1',
        'db_port' => 5432,
        'db_name' => 'tenant_update_' . $suffix,
        'db_username' => 'tenant_user',
        'db_password' => 'tenant_pass',
        'status' => 'suspended',
    ], $overrides));
}

function generateValidCnpj(): string
{
    $base = '';
    for ($i = 0; $i < 12; $i++) {
        $base .= (string) random_int(0, 9);
    }

    $calculateDigit = static function (string $numbers, array $weights): int {
        $sum = 0;
        foreach ($weights as $index => $weight) {
            $sum += ((int) $numbers[$index]) * $weight;
        }

        $rest = $sum % 11;
        return $rest < 2 ? 0 : 11 - $rest;
    };

    $digit1 = $calculateDigit($base, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
    $digit2 = $calculateDigit($base . $digit1, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

    return $base . $digit1 . $digit2;
}

function baseTenantUpdatePayload(Tenant $tenant, array $overrides = []): array
{
    return array_merge([
        'legal_name' => $tenant->legal_name,
        'trade_name' => $tenant->trade_name,
        'document' => $tenant->document,
        'email' => $tenant->email,
        'subdomain' => $tenant->subdomain,
        'phone' => $tenant->phone,
        'status' => 'active',
    ], $overrides);
}

test('tenant without location can update only status without creating tenant_localizacoes row', function () {
    $tenant = createTenantForPlatformUpdate();

    $response = $this->withoutMiddleware()->put(
        route('Platform.tenants.update', $tenant),
        baseTenantUpdatePayload($tenant, [
            'status' => 'active',
        ])
    );

    $response->assertRedirect(route('Platform.tenants.index'))
        ->assertSessionHas('success');

    $tenant->refresh();
    expect($tenant->status)->toBe('active');

    $this->assertDatabaseMissing('tenant_localizacoes', [
        'tenant_id' => $tenant->id,
    ]);
});

test('tenant without location and partial location without endereco returns friendly validation error', function () {
    $tenant = createTenantForPlatformUpdate([
        'email' => 'tenant-update-partial@example.com',
        'subdomain' => 'tenant-update-partial',
    ]);

    $response = $this->withoutMiddleware()
        ->from(route('Platform.tenants.edit', $tenant))
        ->put(route('Platform.tenants.update', $tenant), baseTenantUpdatePayload($tenant, [
            'status' => 'active',
            'estado_id' => 1,
        ]));

    $response->assertRedirect(route('Platform.tenants.edit', $tenant))
        ->assertSessionHasErrors(['endereco']);

    $tenant->refresh();
    expect($tenant->status)->toBe('suspended');

    $this->assertDatabaseMissing('tenant_localizacoes', [
        'tenant_id' => $tenant->id,
    ]);
});

test('tenant with existing location can update only status without losing location', function () {
    $tenant = createTenantForPlatformUpdate([
        'email' => 'tenant-update-existing@example.com',
        'subdomain' => 'tenant-update-existing',
    ]);

    $tenant->localizacao()->create([
        'tenant_id' => $tenant->id,
        'endereco' => 'Rua A',
        'n_endereco' => '100',
        'complemento' => 'Sala 2',
        'bairro' => 'Centro',
        'cep' => '78000-000',
        'pais_id' => 31,
        'estado_id' => 1,
        'cidade_id' => 1,
    ]);

    $response = $this->withoutMiddleware()->put(
        route('Platform.tenants.update', $tenant),
        baseTenantUpdatePayload($tenant, [
            'status' => 'active',
        ])
    );

    $response->assertRedirect(route('Platform.tenants.index'))
        ->assertSessionHas('success');

    $tenant->refresh();
    $location = $tenant->localizacao()->first();

    expect($tenant->status)->toBe('active')
        ->and($location)->not->toBeNull()
        ->and($location->endereco)->toBe('Rua A');
});

test('tenant with existing location can update location fields normally', function () {
    $tenant = createTenantForPlatformUpdate([
        'email' => 'tenant-update-location@example.com',
        'subdomain' => 'tenant-update-location',
    ]);

    $tenant->localizacao()->create([
        'tenant_id' => $tenant->id,
        'endereco' => 'Rua Inicial',
        'n_endereco' => '12',
        'complemento' => null,
        'bairro' => 'Bairro Inicial',
        'cep' => '78000-111',
        'pais_id' => 31,
        'estado_id' => 1,
        'cidade_id' => 1,
    ]);

    $response = $this->withoutMiddleware()->put(
        route('Platform.tenants.update', $tenant),
        baseTenantUpdatePayload($tenant, [
            'status' => 'active',
            'endereco' => 'Rua Atualizada',
            'n_endereco' => '456',
            'bairro' => 'Centro Novo',
            'cep' => '78000-222',
            'estado_id' => 2,
            'cidade_id' => 22,
        ])
    );

    $response->assertRedirect(route('Platform.tenants.index'))
        ->assertSessionHas('success');

    $location = $tenant->localizacao()->first();
    expect($location)->not->toBeNull()
        ->and($location->endereco)->toBe('Rua Atualizada')
        ->and($location->n_endereco)->toBe('456')
        ->and($location->bairro)->toBe('Centro Novo')
        ->and($location->cep)->toBe('78000-222')
        ->and((int) $location->estado_id)->toBe(2)
        ->and((int) $location->cidade_id)->toBe(22);
});

test('manual status edit keeps tenant operational timestamps consistent', function () {
    $tenantSuspended = createTenantForPlatformUpdate([
        'status' => 'suspended',
        'suspended_at' => now()->subDays(2),
        'canceled_at' => now()->subDay(),
        'email' => 'tenant-status-suspended@example.com',
        'subdomain' => 'tenant-status-suspended',
    ]);

    $response1 = $this->withoutMiddleware()->put(
        route('Platform.tenants.update', $tenantSuspended),
        baseTenantUpdatePayload($tenantSuspended, [
            'status' => 'active',
        ])
    );
    $response1->assertRedirect(route('Platform.tenants.index'));
    $tenantSuspended->refresh();
    expect($tenantSuspended->status)->toBe('active')
        ->and($tenantSuspended->suspended_at)->toBeNull()
        ->and($tenantSuspended->canceled_at)->toBeNull();

    $tenantActiveToSuspended = createTenantForPlatformUpdate([
        'status' => 'active',
        'suspended_at' => null,
        'canceled_at' => null,
        'email' => 'tenant-status-active-suspended@example.com',
        'subdomain' => 'tenant-status-active-suspended',
    ]);

    $response2 = $this->withoutMiddleware()->put(
        route('Platform.tenants.update', $tenantActiveToSuspended),
        baseTenantUpdatePayload($tenantActiveToSuspended, [
            'status' => 'suspended',
        ])
    );
    $response2->assertRedirect(route('Platform.tenants.index'));
    $tenantActiveToSuspended->refresh();
    expect($tenantActiveToSuspended->status)->toBe('suspended')
        ->and($tenantActiveToSuspended->suspended_at)->not->toBeNull()
        ->and($tenantActiveToSuspended->canceled_at)->toBeNull();

    $tenantActiveToCanceled = createTenantForPlatformUpdate([
        'status' => 'active',
        'suspended_at' => null,
        'canceled_at' => null,
        'email' => 'tenant-status-active-canceled@example.com',
        'subdomain' => 'tenant-status-active-canceled',
    ]);

    $response3 = $this->withoutMiddleware()->put(
        route('Platform.tenants.update', $tenantActiveToCanceled),
        baseTenantUpdatePayload($tenantActiveToCanceled, [
            'status' => 'cancelled',
        ])
    );
    $response3->assertRedirect(route('Platform.tenants.index'));
    $tenantActiveToCanceled->refresh();
    expect($tenantActiveToCanceled->status)->toBe('cancelled')
        ->and($tenantActiveToCanceled->canceled_at)->not->toBeNull();
});
