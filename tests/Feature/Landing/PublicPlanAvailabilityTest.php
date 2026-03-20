<?php

use App\Models\Platform\Plan;
use Illuminate\Support\Facades\DB;

function landingPayload(array $overrides = []): array
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
        'name' => 'Clinica Publica',
        'fantasy_name' => 'Clinica Publica LTDA',
        'email' => 'clinica-publica@example.com',
        'phone' => '11999999999',
        'document' => '12345678901',
        'accept_terms' => true,
        'address' => 'Rua A',
        'address_number' => '123',
        'complement' => 'Sala 1',
        'neighborhood' => 'Centro',
        'zipcode' => '01001000',
        'state_id' => $stateId,
        'city_id' => $cityId,
    ], $overrides);
}

test('landing lista somente planos real + visivel + ativo para comercializacao publica', function () {
    $eligible = Plan::factory()->create([
        'name' => 'Plano Publico Elegivel',
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
    ]);

    $testPlan = Plan::factory()->create([
        'name' => 'Plano Teste Publico',
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_TEST,
        'show_on_landing_page' => true,
    ]);

    $hiddenPlan = Plan::factory()->create([
        'name' => 'Plano Oculto Publico',
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => false,
    ]);

    $inactivePlan = Plan::factory()->create([
        'name' => 'Plano Inativo Publico',
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => false,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
    ]);

    $response = $this->get('/planos');
    $response->assertOk();

    $plans = collect($response->viewData('plans'));
    expect($plans->pluck('id')->all())->toBe([$eligible->id]);

    expect($plans->contains('id', $testPlan->id))->toBeFalse();
    expect($plans->contains('id', $hiddenPlan->id))->toBeFalse();
    expect($plans->contains('id', $inactivePlan->id))->toBeFalse();
});

test('endpoint publico de plano bloqueia acesso a plano de teste oculto ou inativo', function () {
    $testPlan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_TEST,
        'show_on_landing_page' => true,
    ]);

    $hiddenPlan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => false,
    ]);

    $inactivePlan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => false,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
    ]);

    $this->get("/planos/json/{$testPlan->id}")->assertNotFound();
    $this->get("/planos/json/{$hiddenPlan->id}")->assertNotFound();
    $this->get("/planos/json/{$inactivePlan->id}")->assertNotFound();
});

test('pre-cadastro publico bloqueia plano de teste', function () {
    $testPlan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_TEST,
        'show_on_landing_page' => true,
    ]);

    $payload = landingPayload([
        'plan_id' => $testPlan->id,
        'email' => 'teste-plan@example.com',
    ]);

    $response = $this->postJson('/pre-cadastro', $payload);

    $response->assertStatus(422)
        ->assertJson([
            'error' => 'O plano selecionado não está disponível para contratação pública.',
        ]);
});

test('pre-cadastro publico bloqueia plano oculto', function () {
    $hiddenPlan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => false,
    ]);

    $payload = landingPayload([
        'plan_id' => $hiddenPlan->id,
        'email' => 'hidden-plan@example.com',
    ]);

    $response = $this->postJson('/pre-cadastro', $payload);

    $response->assertStatus(422)
        ->assertJson([
            'error' => 'O plano selecionado não está disponível para contratação pública.',
        ]);
});

test('pre-cadastro publico bloqueia plano inativo', function () {
    $inactivePlan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => false,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
    ]);

    $payload = landingPayload([
        'plan_id' => $inactivePlan->id,
        'email' => 'inactive-plan@example.com',
    ]);

    $response = $this->postJson('/pre-cadastro', $payload);

    $response->assertStatus(422)
        ->assertJson([
            'error' => 'O plano selecionado não está disponível para contratação pública.',
        ]);
});

test('platform continua com acesso administrativo a todos os tipos de plano', function () {
    $realVisible = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
    ]);

    $testPlan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_TEST,
        'show_on_landing_page' => true,
    ]);

    $hiddenPlan = Plan::factory()->create([
        'category' => Plan::CATEGORY_COMMERCIAL,
        'is_active' => true,
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => false,
    ]);

    $allPlans = Plan::query()->pluck('id')->all();

    expect($allPlans)->toContain($realVisible->id);
    expect($allPlans)->toContain($testPlan->id);
    expect($allPlans)->toContain($hiddenPlan->id);
});
