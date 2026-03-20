<?php

namespace App\Services\Platform;

use App\Models\Platform\Cidade;
use App\Models\Platform\Estado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IbgeLocationSyncService
{
    private const BRAZIL_COUNTRY_ID = 31;

    /**
     * @param array{states:array<int,array<string,mixed>>,cities:array<int,array<string,mixed>>} $dataset
     * @return array<string,int>
     */
    public function sync(array $dataset): array
    {
        $stats = [
            'states_inserted' => 0,
            'states_updated' => 0,
            'cities_inserted' => 0,
            'cities_updated' => 0,
            'cities_ambiguous_matches' => 0,
            'cities_without_match' => 0,
            'cities_missing_state' => 0,
            'states_without_ibge' => 0,
            'states_with_duplicate_ibge' => 0,
            'cities_with_duplicate_ibge' => 0,
        ];

        DB::transaction(function () use ($dataset, &$stats): void {
            $stateContext = $this->syncStates($dataset['states'], self::BRAZIL_COUNTRY_ID, $stats);
            $this->syncCities($dataset['cities'], $stateContext, $stats);
        });

        $statesWithoutIbge = Estado::query()->where('pais_id', self::BRAZIL_COUNTRY_ID)->whereNull('ibge_id')->count();
        $unmatchedCount = Cidade::query()->whereNull('ibge_id')->count();
        $stateDuplicatesCount = Estado::query()
            ->select('ibge_id')
            ->whereNotNull('ibge_id')
            ->groupBy('ibge_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();
        $cityDuplicatesCount = Cidade::query()
            ->select('ibge_id')
            ->whereNotNull('ibge_id')
            ->groupBy('ibge_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $stats['states_without_ibge'] = (int) $statesWithoutIbge;
        $stats['cities_without_match'] = (int) $unmatchedCount;
        $stats['states_with_duplicate_ibge'] = (int) $stateDuplicatesCount;
        $stats['cities_with_duplicate_ibge'] = (int) $cityDuplicatesCount;

        if ($statesWithoutIbge > 0) {
            $sampleStates = Estado::query()
                ->where('pais_id', self::BRAZIL_COUNTRY_ID)
                ->whereNull('ibge_id')
                ->orderBy('uf')
                ->limit(20)
                ->get(['id_estado', 'uf', 'nome_estado'])
                ->toArray();

            Log::warning('Estados sem codigo IBGE apos sincronizacao.', [
                'total' => $statesWithoutIbge,
                'sample' => $sampleStates,
            ]);
        }

        if ($unmatchedCount > 0) {
            $sample = Cidade::query()
                ->whereNull('ibge_id')
                ->orderBy('estado_id')
                ->orderBy('nome_cidade')
                ->limit(50)
                ->get(['id_cidade', 'estado_id', 'nome_cidade'])
                ->toArray();

            Log::warning('Localidades sem correspondencia oficial do IBGE apos sincronizacao.', [
                'total' => $unmatchedCount,
                'sample' => $sample,
            ]);
        }

        if ($stateDuplicatesCount > 0) {
            $stateDuplicates = Estado::query()
                ->selectRaw('ibge_id, COUNT(*) as total')
                ->whereNotNull('ibge_id')
                ->groupBy('ibge_id')
                ->havingRaw('COUNT(*) > 1')
                ->limit(20)
                ->get()
                ->toArray();

            Log::warning('Estados com codigo IBGE duplicado.', [
                'total' => $stateDuplicatesCount,
                'sample' => $stateDuplicates,
            ]);
        }

        if ($cityDuplicatesCount > 0) {
            $cityDuplicates = Cidade::query()
                ->selectRaw('ibge_id, COUNT(*) as total')
                ->whereNotNull('ibge_id')
                ->groupBy('ibge_id')
                ->havingRaw('COUNT(*) > 1')
                ->limit(20)
                ->get()
                ->toArray();

            Log::warning('Cidades com codigo IBGE duplicado.', [
                'total' => $cityDuplicatesCount,
                'sample' => $cityDuplicates,
            ]);
        }

        return $stats;
    }

    /**
     * @param array<int,array<string,mixed>> $states
     * @param array<string,int> $stats
     * @return array{by_ibge:array<int,int>,by_id:array<int,array{uf:string}>}
     */
    private function syncStates(array $states, int $brazilId, array &$stats): array
    {
        $existingStates = Estado::query()->get(['id_estado', 'uf', 'nome_estado', 'pais_id', 'ibge_id']);
        $byUf = [];
        $byIbge = [];

        foreach ($existingStates as $existingState) {
            $uf = strtoupper((string) $existingState->uf);
            if ($uf !== '') {
                $byUf[$uf] = $existingState;
            }

            if (! empty($existingState->ibge_id)) {
                $byIbge[(int) $existingState->ibge_id] = $existingState;
            }
        }

        $stateIdByIbge = [];
        $stateInfoById = [];

        foreach ($states as $state) {
            $uf = strtoupper(trim((string) ($state['uf'] ?? '')));
            $name = trim((string) ($state['nome_estado'] ?? ''));
            $ibgeId = (int) ($state['ibge_id'] ?? 0);

            if ($uf === '' || $name === '' || $ibgeId <= 0) {
                continue;
            }

            $model = $byIbge[$ibgeId] ?? $byUf[$uf] ?? null;

            if ($model) {
                $model->fill([
                    'uf' => $uf,
                    'nome_estado' => $name,
                    'pais_id' => $brazilId,
                    'ibge_id' => $ibgeId,
                ]);

                if ($model->isDirty()) {
                    $model->save();
                    $stats['states_updated']++;
                }
            } else {
                $model = Estado::query()->create([
                    'uf' => $uf,
                    'nome_estado' => $name,
                    'pais_id' => $brazilId,
                    'ibge_id' => $ibgeId,
                ]);
                $stats['states_inserted']++;
            }

            $stateIdByIbge[$ibgeId] = (int) $model->id_estado;
            $stateInfoById[(int) $model->id_estado] = ['uf' => (string) $model->uf];
            $byUf[$uf] = $model;
            $byIbge[$ibgeId] = $model;
        }

        return [
            'by_ibge' => $stateIdByIbge,
            'by_id' => $stateInfoById,
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $cities
     * @param array{by_ibge:array<int,int>,by_id:array<int,array{uf:string}>} $stateContext
     * @param array<string,int> $stats
     */
    private function syncCities(array $cities, array $stateContext, array &$stats): void
    {
        $existingCities = Cidade::query()->get(['id_cidade', 'estado_id', 'uf', 'nome_cidade', 'ibge_id']);
        $byIbge = [];
        $byStateAndName = [];

        foreach ($existingCities as $existingCity) {
            if (! empty($existingCity->ibge_id)) {
                $byIbge[(int) $existingCity->ibge_id] = $existingCity;
            }

            $stateId = (int) $existingCity->estado_id;
            $normalizedName = $this->normalizeName((string) $existingCity->nome_cidade);
            if (! isset($byStateAndName[$stateId])) {
                $byStateAndName[$stateId] = [];
            }
            if (! isset($byStateAndName[$stateId][$normalizedName])) {
                $byStateAndName[$stateId][$normalizedName] = [];
            }
            $byStateAndName[$stateId][$normalizedName][] = $existingCity;
        }

        foreach ($cities as $city) {
            $ibgeId = (int) ($city['ibge_id'] ?? 0);
            $name = trim((string) ($city['nome_cidade'] ?? ''));
            $stateIbgeId = (int) ($city['estado_ibge_id'] ?? 0);
            $ufFromDataset = strtoupper(trim((string) ($city['uf'] ?? '')));

            if ($ibgeId <= 0 || $stateIbgeId <= 0 || $name === '') {
                continue;
            }

            $stateId = $stateContext['by_ibge'][$stateIbgeId] ?? null;
            if (! $stateId) {
                $stats['cities_missing_state']++;
                Log::warning('Municipio do IBGE ignorado por estado nao mapeado internamente.', [
                    'city_ibge_id' => $ibgeId,
                    'city_name' => $name,
                    'state_ibge_id' => $stateIbgeId,
                ]);
                continue;
            }

            $cityModel = $byIbge[$ibgeId] ?? null;
            if (! $cityModel) {
                $normalizedName = $this->normalizeName($name);
                $candidates = $byStateAndName[$stateId][$normalizedName] ?? [];

                if (count($candidates) > 1) {
                    $stats['cities_ambiguous_matches']++;
                    Log::warning('Multipla correspondencia de cidade detectada durante sincronizacao IBGE.', [
                        'city_ibge_id' => $ibgeId,
                        'city_name' => $name,
                        'state_id' => $stateId,
                        'candidate_ids' => collect($candidates)->pluck('id_cidade')->values()->all(),
                    ]);
                }

                if (count($candidates) > 0) {
                    $cityModel = collect($candidates)->first(
                        fn (Cidade $candidate) => empty($candidate->ibge_id) || (int) $candidate->ibge_id === $ibgeId
                    );
                    if (! $cityModel) {
                        $cityModel = $candidates[0];
                    }
                }
            }

            $resolvedUf = $stateContext['by_id'][$stateId]['uf'] ?? $ufFromDataset;
            if ($cityModel) {
                $cityModel->fill([
                    'estado_id' => $stateId,
                    'uf' => $resolvedUf,
                    'nome_cidade' => $name,
                    'ibge_id' => $ibgeId,
                ]);

                if ($cityModel->isDirty()) {
                    $cityModel->save();
                    $stats['cities_updated']++;
                }
            } else {
                $cityModel = Cidade::query()->create([
                    'estado_id' => $stateId,
                    'uf' => $resolvedUf,
                    'nome_cidade' => $name,
                    'ibge_id' => $ibgeId,
                ]);
                $stats['cities_inserted']++;
            }

            $byIbge[$ibgeId] = $cityModel;
            $normalizedName = $this->normalizeName((string) $cityModel->nome_cidade);
            if (! isset($byStateAndName[$stateId][$normalizedName])) {
                $byStateAndName[$stateId][$normalizedName] = [];
            }

            $alreadyInBucket = collect($byStateAndName[$stateId][$normalizedName])
                ->contains(fn (Cidade $bucketCity) => (int) $bucketCity->id_cidade === (int) $cityModel->id_cidade);

            if (! $alreadyInBucket) {
                $byStateAndName[$stateId][$normalizedName][] = $cityModel;
            }
        }
    }

    private function normalizeName(string $name): string
    {
        $ascii = Str::of($name)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '')->value();

        return trim($ascii);
    }
}
