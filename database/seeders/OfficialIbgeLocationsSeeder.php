<?php

namespace Database\Seeders;

use App\Services\Platform\IbgeLocationApiService;
use App\Services\Platform\IbgeLocationSyncService;
use Illuminate\Database\Seeder;
use RuntimeException;

class OfficialIbgeLocationsSeeder extends Seeder
{
    public function __construct(
        private readonly IbgeLocationApiService $apiService,
        private readonly IbgeLocationSyncService $syncService
    ) {
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('data/ibge_localidades.json');
        $dataset = $this->apiService->loadDatasetFromFile($path);

        if (count($dataset['states']) === 0 || count($dataset['cities']) === 0) {
            throw new RuntimeException('Arquivo database/data/ibge_localidades.json esta vazio ou invalido.');
        }

        $stats = $this->syncService->sync($dataset);

        if ($this->command) {
            $this->command->info('Localidades oficiais do IBGE sincronizadas via seed local.');
            $this->command->line('Estados inseridos: ' . ($stats['states_inserted'] ?? 0));
            $this->command->line('Estados atualizados: ' . ($stats['states_updated'] ?? 0));
            $this->command->line('Cidades inseridas: ' . ($stats['cities_inserted'] ?? 0));
            $this->command->line('Cidades atualizadas: ' . ($stats['cities_updated'] ?? 0));
            $this->command->line('Cidades sem correspondencia: ' . ($stats['cities_without_match'] ?? 0));
        }
    }
}
