<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Services\Platform\PreTenantProcessorService;
use Illuminate\Support\Facades\DB;

function commercialTrialPayload(array $overrides = []): array
{
    DB::table('paises')->updateOrInsert(['id_pais' => 31], [
        'id_pais' => 31,
        'nome' => 'Brasil',
        'sigla2' => 'BR',
        'sigla3' => 'BRA',
        'codigo' => 76,
    ]);

    $stateId = DB::table('estados')->insertGetId([
        'uf' => 'SP',
        'nome_estado' => 'Sao Paulo',
        'pais_id' => 31,
    ], 'id_estado');

    $cityId = DB::table('cidades')->insertGetId([
        'estado_id' => $stateId,
        'uf' => 'SP',
        'nome_cidade' => 'Sao Paulo',
    ], 'id_cidade');

    return array_merge([
        'name' => 'Clinica Trial',
        'fantasy_name' => 'Clinica Trial LTDA',
        'email' => 'trial-landing@example.com',
        'phone' => '11999999999',
        'document' => '12345678901',
        'accept_terms' => true,
        'trial' => true,
        'address' => 'Rua A',
        'address_number' => '123',
        'complement' => 'Sala 1',
        'neighborhood' => 'Centro',
        'zipcode' => '01001000',
        'state_id' => $stateId,
        'city_id' => $cityId,
    ], $overrides);
}

test('landing trial cria assinatura trial sem fluxo financeiro', function () {
    $plan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
        'trial_enabled' => true,
        'trial_days' => 14,
    ]);

    $tenant = Tenant::factory()->create([
        'subdomain' => 'trial-comercial',
        'email' => 'tenant-existing@example.com',
    ]);

    $processor = Mockery::mock(PreTenantProcessorService::class);
    $processor->shouldReceive('createTenantFromPreTenant')
        ->once()
        ->andReturn($tenant);
    $processor->shouldReceive('sendWelcomeEmail')
        ->once()
        ->andReturnNull();
    $this->app->instance(PreTenantProcessorService::class, $processor);

    $payload = commercialTrialPayload([
        'plan_id' => $plan->id,
        'email' => 'novo-trial@example.com',
    ]);

    $response = $this->postJson('/pre-cadastro', $payload);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'trial' => true,
            'tenant_id' => $tenant->id,
        ]);

    $this->assertDatabaseHas('subscriptions', [
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'is_trial' => true,
        'status' => 'trialing',
        'auto_renew' => false,
        'asaas_sync_status' => 'skipped',
    ]);

    $this->assertDatabaseHas('pre_tenants', [
        'email' => 'novo-trial@example.com',
        'country_id' => 31,
    ]);

    expect(DB::table('invoices')->count())->toBe(0);
});

test('landing trial bloqueia plano sem trial habilitado', function () {
    $plan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
        'trial_enabled' => false,
        'trial_days' => null,
    ]);

    $payload = commercialTrialPayload([
        'plan_id' => $plan->id,
        'email' => 'trial-bloqueado@example.com',
    ]);

    $response = $this->postJson('/pre-cadastro', $payload);

    $response->assertStatus(422)
        ->assertJson([
            'error' => 'Este plano nao esta disponivel para teste gratuito no momento.',
        ]);
});
