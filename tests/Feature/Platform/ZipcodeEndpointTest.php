<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

function createBrazilLocationForZipcodeTests(): array
{
    DB::table('paises')->updateOrInsert(['id_pais' => 31], [
        'id_pais' => 31,
        'nome' => 'Brasil',
        'sigla2' => 'BR',
        'sigla3' => 'BRA',
        'codigo' => '076',
    ]);

    $stateId = DB::table('estados')->insertGetId([
        'uf' => 'SP',
        'nome_estado' => 'Sao Paulo',
        'pais_id' => 31,
        'ibge_id' => 35,
    ], 'id_estado');

    $cityId = DB::table('cidades')->insertGetId([
        'estado_id' => $stateId,
        'uf' => 'SP',
        'nome_cidade' => 'Sao Paulo',
        'ibge_id' => 3550308,
    ], 'id_cidade');

    return [
        'state_id' => $stateId,
        'city_id' => $cityId,
    ];
}

beforeEach(function () {
    Cache::flush();
});

test('consulta endpoint interno de cep e resolve cidade/estado por ibge', function () {
    $ids = createBrazilLocationForZipcodeTests();

    Http::fake([
        'https://viacep.com.br/ws/01001000/json/' => Http::response([
            'cep' => '01001-000',
            'logradouro' => 'Praca da Se',
            'complemento' => 'lado impar',
            'bairro' => 'Se',
            'localidade' => 'Sao Paulo',
            'uf' => 'SP',
            'ibge' => '3550308',
        ], 200),
    ]);

    $response = $this->getJson('/api/zipcode/01001000');

    $response->assertOk()
        ->assertJsonPath('zipcode', '01001-000')
        ->assertJsonPath('street', 'Praca da Se')
        ->assertJsonPath('neighborhood', 'Se')
        ->assertJsonPath('state.id', $ids['state_id'])
        ->assertJsonPath('state.uf', 'SP')
        ->assertJsonPath('state.ibge_id', 35)
        ->assertJsonPath('city.id', $ids['city_id'])
        ->assertJsonPath('city.ibge_id', 3550308);
});

test('retorna erro de validacao para cep invalido', function () {
    $response = $this->getJson('/api/zipcode/123');

    $response->assertStatus(422)
        ->assertJsonPath('error_code', 'invalid_zipcode');
});

test('retorna cep nao encontrado quando viacep informa erro', function () {
    Http::fake([
        'https://viacep.com.br/ws/99999999/json/' => Http::response([
            'erro' => true,
        ], 200),
    ]);

    $response = $this->getJson('/api/zipcode/99999-999');

    $response->assertStatus(404)
        ->assertJsonPath('error_code', 'zipcode_not_found');
});

test('mantem fallback textual quando viacep retorna ibge sem correspondencia interna', function () {
    createBrazilLocationForZipcodeTests();

    Http::fake([
        'https://viacep.com.br/ws/22222222/json/' => Http::response([
            'cep' => '22222-222',
            'logradouro' => 'Rua Sem Mapeamento',
            'complemento' => '',
            'bairro' => 'Centro',
            'localidade' => 'Cidade Sem Match',
            'uf' => 'SP',
            'ibge' => '9999999',
        ], 200),
    ]);

    $response = $this->getJson('/api/zipcode/22222-222');

    $response->assertOk()
        ->assertJsonPath('city', null)
        ->assertJsonPath('state', null)
        ->assertJsonPath('fallback.state_uf', 'SP')
        ->assertJsonPath('fallback.city_name', 'Cidade Sem Match')
        ->assertJsonPath('fallback.city_ibge_id', '9999999');

    $warnings = $response->json('warnings');
    expect($warnings)->toBeArray()->not->toBeEmpty();
});
