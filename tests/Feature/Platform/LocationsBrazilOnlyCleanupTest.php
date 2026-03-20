<?php

use App\Models\Platform\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

function createLocationOperator(): User
{
    return User::query()->create([
        'name' => 'Operador Local',
        'name_full' => 'Operador Localizacao',
        'email' => 'locations+'.Str::lower(Str::random(6)).'@example.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['locations'],
    ]);
}

test('rotas funcionais de paises nao existem na platform', function () {
    $user = createLocationOperator();

    expect(Route::has('Platform.paises.index'))->toBeFalse();
    expect(Route::has('Platform.paises.create'))->toBeFalse();

    $this->actingAs($user, 'web')
        ->get('/Platform/paises')
        ->assertNotFound();
});

test('menu administrativo de locais nao exibe item de paises', function () {
    $user = createLocationOperator();

    $this->actingAs($user, 'web')
        ->get(route('Platform.dashboard'))
        ->assertOk()
        ->assertSee('Locais')
        ->assertDontSee('Países')
        ->assertDontSee('Paises');
});

test('endpoint publico de estados nao expoe pais_id no payload', function () {
    DB::table('paises')->updateOrInsert(['id_pais' => 31], [
        'id_pais' => 31,
        'nome' => 'Brasil',
        'sigla2' => 'BR',
        'sigla3' => 'BRA',
        'codigo' => '076',
    ]);

    DB::table('estados')->insert([
        'uf' => 'SP',
        'nome_estado' => 'Sao Paulo',
        'pais_id' => 31,
        'ibge_id' => 35,
    ]);

    $response = $this->getJson('/api/location/estados');

    $response->assertOk();

    $states = $response->json();
    expect($states)->toBeArray()->not->toBeEmpty();
    expect(array_key_exists('pais_id', $states[0]))->toBeFalse();
});
