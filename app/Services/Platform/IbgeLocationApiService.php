<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use RuntimeException;

class IbgeLocationApiService
{
    private const IBGE_STATES_ENDPOINT = 'https://servicodados.ibge.gov.br/api/v1/localidades/estados';
    private const IBGE_CITIES_ENDPOINT = 'https://servicodados.ibge.gov.br/api/v1/localidades/municipios';

    /**
     * @return array{generated_at:string,source:string,states:array<int,array<string,mixed>>,cities:array<int,array<string,mixed>>}
     */
    public function fetchDatasetFromApi(): array
    {
        $statesResponse = Http::timeout(20)->retry(2, 400)->acceptJson()->get(self::IBGE_STATES_ENDPOINT);
        if (! $statesResponse->successful()) {
            throw new RuntimeException('Falha ao consultar estados na API do IBGE.');
        }

        $citiesResponse = Http::timeout(40)->retry(2, 400)->acceptJson()->get(self::IBGE_CITIES_ENDPOINT);
        if (! $citiesResponse->successful()) {
            throw new RuntimeException('Falha ao consultar municipios na API do IBGE.');
        }

        $rawStates = $statesResponse->json();
        $rawCities = $citiesResponse->json();

        if (! is_array($rawStates) || ! is_array($rawCities)) {
            throw new RuntimeException('Resposta invalida recebida da API do IBGE.');
        }

        $states = collect($rawStates)
            ->filter(fn ($state) => is_array($state) && isset($state['id'], $state['sigla'], $state['nome']))
            ->map(function (array $state): array {
                return [
                    'ibge_id' => (int) $state['id'],
                    'uf' => strtoupper((string) $state['sigla']),
                    'nome_estado' => trim((string) $state['nome']),
                ];
            })
            ->sortBy('uf')
            ->values()
            ->all();

        $cities = collect($rawCities)
            ->filter(function ($city): bool {
                return is_array($city)
                    && isset($city['id'], $city['nome'])
                    && isset($city['microrregiao']['mesorregiao']['UF']['id'])
                    && isset($city['microrregiao']['mesorregiao']['UF']['sigla']);
            })
            ->map(function (array $city): array {
                return [
                    'ibge_id' => (int) $city['id'],
                    'nome_cidade' => trim((string) $city['nome']),
                    'estado_ibge_id' => (int) $city['microrregiao']['mesorregiao']['UF']['id'],
                    'uf' => strtoupper((string) $city['microrregiao']['mesorregiao']['UF']['sigla']),
                ];
            })
            ->values()
            ->all();

        if (count($states) !== 27 || count($cities) < 5500) {
            Log::warning('Quantidade inesperada de registros do IBGE durante sincronizacao.', [
                'states_count' => count($states),
                'cities_count' => count($cities),
            ]);
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'source' => 'IBGE API v1 localidades',
            'states' => $states,
            'cities' => $cities,
        ];
    }

    /**
     * @return array{generated_at:string,source:string,states:array<int,array<string,mixed>>,cities:array<int,array<string,mixed>>}
     */
    public function loadDatasetFromFile(string $absolutePath): array
    {
        if (! File::exists($absolutePath)) {
            throw new RuntimeException('Arquivo local de localidades IBGE nao encontrado.');
        }

        $payload = json_decode((string) File::get($absolutePath), true);
        if (
            ! is_array($payload)
            || ! isset($payload['states'])
            || ! isset($payload['cities'])
            || ! is_array($payload['states'])
            || ! is_array($payload['cities'])
        ) {
            throw new RuntimeException('Arquivo local de localidades IBGE invalido.');
        }

        return [
            'generated_at' => (string) ($payload['generated_at'] ?? ''),
            'source' => (string) ($payload['source'] ?? 'local cache'),
            'states' => array_values($payload['states']),
            'cities' => array_values($payload['cities']),
        ];
    }

    /**
     * @param array{generated_at:string,source:string,states:array<int,array<string,mixed>>,cities:array<int,array<string,mixed>>} $dataset
     */
    public function writeDatasetToFile(array $dataset, string $absolutePath): void
    {
        $directory = dirname($absolutePath);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put(
            $absolutePath,
            json_encode($dataset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
