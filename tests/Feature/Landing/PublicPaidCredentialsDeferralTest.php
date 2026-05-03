<?php

use App\Mail\TenantAdminCredentialsMail;
use App\Models\Platform\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

function paidLandingPayload(array $overrides = []): array
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
        'name' => 'Clinica Pago',
        'fantasy_name' => 'Clinica Pago LTDA',
        'email' => 'pago-landing@example.com',
        'phone' => '11999999999',
        'document' => '12345678901',
        'accept_terms' => true,
        'trial' => false,
        'address' => 'Rua B',
        'address_number' => '123',
        'complement' => 'Sala 1',
        'neighborhood' => 'Centro',
        'zipcode' => '01001000',
        'state_id' => $stateId,
        'city_id' => $cityId,
    ], $overrides);
}

test('pre-cadastro comercial pago nao envia credenciais antes do processPaid', function () {
    Mail::fake();

    $plan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
        'trial_enabled' => false,
        'trial_days' => null,
        'price_cents' => 9900,
    ]);

    $asaas = \Mockery::mock('overload:App\\Services\\AsaasService');
    $asaas->shouldReceive('createCustomer')->once()->andReturn(['id' => 'cus_test_123']);
    $asaas->shouldReceive('createPaymentLink')->once()->andReturn([
        'id' => 'plink_test_123',
        'url' => 'https://pay.example.com/plink_test_123',
    ]);

    $response = $this->postJson('/pre-cadastro', paidLandingPayload([
        'plan_id' => $plan->id,
        'email' => 'pago-sem-processar@example.com',
    ]));

    $response->assertOk()->assertJson([
        'success' => true,
    ]);

    Mail::assertNothingSent();
    expect(DB::table('tenant_admins')->count())->toBe(0);
});

