<?php

use App\Services\Platform\IbgeLocationSyncService;
use Illuminate\Support\Facades\DB;

function createBrazilForIbgeSyncTests(): int
{
    DB::table('paises')->updateOrInsert(['id_pais' => 31], [
        'id_pais' => 31,
        'nome' => 'Brasil',
        'sigla2' => 'BR',
        'sigla3' => 'BRA',
        'codigo' => '076',
    ]);

    return 31;
}

test('sincroniza estados do ibge preservando ids internos quando casar por uf', function () {
    $brazilId = createBrazilForIbgeSyncTests();

    $spId = DB::table('estados')->insertGetId([
        'uf' => 'SP',
        'nome_estado' => 'Sao Paulo antigo',
        'pais_id' => $brazilId,
        'ibge_id' => null,
    ], 'id_estado');

    $dataset = [
        'states' => [
            ['ibge_id' => 35, 'uf' => 'SP', 'nome_estado' => 'Sao Paulo'],
            ['ibge_id' => 33, 'uf' => 'RJ', 'nome_estado' => 'Rio de Janeiro'],
        ],
        'cities' => [],
    ];

    $stats = app(IbgeLocationSyncService::class)->sync($dataset);

    expect($stats['states_updated'])->toBe(1)
        ->and($stats['states_inserted'])->toBe(1);

    $spRow = DB::table('estados')->where('id_estado', $spId)->first();
    expect($spRow)->not->toBeNull()
        ->and((int) $spRow->ibge_id)->toBe(35)
        ->and($spRow->nome_estado)->toBe('Sao Paulo');

    $rjRow = DB::table('estados')->where('uf', 'RJ')->first();
    expect($rjRow)->not->toBeNull()
        ->and((int) $rjRow->ibge_id)->toBe(33);
});

test('sincroniza cidades do ibge por estado e nome normalizado preenchendo ibge_id', function () {
    $brazilId = createBrazilForIbgeSyncTests();

    $spId = DB::table('estados')->insertGetId([
        'uf' => 'SP',
        'nome_estado' => 'Sao Paulo',
        'pais_id' => $brazilId,
        'ibge_id' => 35,
    ], 'id_estado');

    $cityId = DB::table('cidades')->insertGetId([
        'estado_id' => $spId,
        'uf' => 'SP',
        'nome_cidade' => 'Sao Paulo',
        'ibge_id' => null,
    ], 'id_cidade');

    $dataset = [
        'states' => [
            ['ibge_id' => 35, 'uf' => 'SP', 'nome_estado' => 'Sao Paulo'],
        ],
        'cities' => [
            [
                'ibge_id' => 3550308,
                'nome_cidade' => 'São Paulo',
                'estado_ibge_id' => 35,
                'uf' => 'SP',
            ],
        ],
    ];

    $stats = app(IbgeLocationSyncService::class)->sync($dataset);

    expect($stats['cities_updated'])->toBe(1)
        ->and($stats['cities_inserted'])->toBe(0)
        ->and($stats['cities_without_match'])->toBe(0);

    $cityRow = DB::table('cidades')->where('id_cidade', $cityId)->first();
    expect($cityRow)->not->toBeNull()
        ->and((int) $cityRow->ibge_id)->toBe(3550308)
        ->and($cityRow->nome_cidade)->toBe('São Paulo')
        ->and((int) $cityRow->estado_id)->toBe($spId);
});
