<?php

use App\Http\Controllers\Platform\TenantController;
use App\Models\Platform\Tenant;
use App\Models\Platform\User;
use App\Services\Platform\TenantCreatorService;
use Illuminate\Support\Str;

function validTenantCreationPayload(array $overrides = []): array
{
    $suffix = Str::lower(Str::random(8));

    return array_merge([
        'legal_name' => 'Clinica Teste ' . $suffix,
        'trade_name' => 'Clinica Teste ' . $suffix,
        'document' => '52998224725',
        'email' => "tenant-{$suffix}@example.com",
        'subdomain' => "tenant-{$suffix}",
        'status' => 'active',
    ], $overrides);
}

function fakeCreatedTenant(bool $eligible): Tenant
{
    $tenant = $eligible
        ? new class extends Tenant {
            public function isEligibleForAccess(): bool
            {
                return true;
            }
        }
        : new class extends Tenant {
            public function isEligibleForAccess(): bool
            {
                return false;
            }
        };

    $tenant->setAttribute('id', (string) Str::uuid());
    $tenant->setAttribute('trade_name', 'Tenant Operacional');
    $tenant->setAttribute('subdomain', 'tenant-operacional');
    $tenant->setAttribute('network_id', (string) Str::uuid());

    return $tenant;
}

function createPlatformUserWithModules(array $modules): User
{
    return User::query()->create([
        'name' => 'Operador',
        'name_full' => 'Operador Platform',
        'email' => 'operador+' . Str::lower(Str::random(6)) . '@example.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => $modules,
    ]);
}

test('store de tenant sem elegibilidade comercial redireciona para detalhe com alerta de pendencia', function () {
    $createdTenant = fakeCreatedTenant(false);

    $serviceMock = Mockery::mock(TenantCreatorService::class);
    $serviceMock->shouldReceive('create')->once()->andReturn($createdTenant);
    app()->instance(TenantCreatorService::class, $serviceMock);

    $response = $this
        ->withoutMiddleware()
        ->post(route('Platform.tenants.store'), validTenantCreationPayload());

    $response->assertRedirect(route('Platform.tenants.show', $createdTenant->id));
    $response->assertSessionHas('warning', TenantController::CREATED_PENDING_COMMERCIAL_MESSAGE);
    $response->assertSessionHas('tenant_needs_commercial_regularization', true);
});

test('store de tenant elegivel mantem fluxo de sucesso sem flag de pendencia comercial', function () {
    $createdTenant = fakeCreatedTenant(true);

    $serviceMock = Mockery::mock(TenantCreatorService::class);
    $serviceMock->shouldReceive('create')->once()->andReturn($createdTenant);
    app()->instance(TenantCreatorService::class, $serviceMock);

    $response = $this
        ->withoutMiddleware()
        ->post(route('Platform.tenants.store'), validTenantCreationPayload([
            'document' => '11144477735',
        ]));

    $response->assertRedirect(route('Platform.tenants.show', $createdTenant->id));
    $response->assertSessionHas('success', TenantController::CREATED_ELIGIBLE_MESSAGE);
    $response->assertSessionMissing('tenant_needs_commercial_regularization');
});

test('tenant bloqueada exibe acao rapida de regularizacao comercial no detalhe', function () {
    $tenant = Tenant::factory()->create([
        'plan_id' => null,
    ]);

    $user = createPlatformUserWithModules(['tenants', 'subscriptions']);

    $response = $this
        ->actingAs($user, 'web')
        ->withSession([
            'tenant_needs_commercial_regularization' => true,
            'warning' => TenantController::CREATED_PENDING_COMMERCIAL_MESSAGE,
        ])
        ->get(route('Platform.tenants.show', $tenant->id));

    $response->assertOk();
    $response->assertSee('Tenant criada com pendencia comercial');
    $response->assertSee('Criar Assinatura');
    $response->assertSee(route('Platform.subscriptions.create', ['tenant_id' => $tenant->id]), false);
});
