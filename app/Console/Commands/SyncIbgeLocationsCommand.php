<?php

namespace App\Console\Commands;

use App\Services\Platform\IbgeLocationApiService;
use App\Services\Platform\IbgeLocationSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncIbgeLocationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:sync-ibge
        {--from-cache : Sincroniza usando apenas o arquivo local}
        {--write-cache : Atualiza o arquivo local com o payload oficial}
        {--cache-path=database/data/ibge_localidades.json : Caminho do arquivo local de cache JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza estados e municipios oficiais do IBGE preservando os IDs internos existentes.';

    public function __construct(
        private readonly IbgeLocationApiService $apiService,
        private readonly IbgeLocationSyncService $syncService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cachePath = base_path((string) $this->option('cache-path'));
        $fromCache = (bool) $this->option('from-cache');
        $writeCache = (bool) $this->option('write-cache');

        try {
            if ($fromCache) {
                $this->info('Carregando dataset IBGE do arquivo local...');
                $dataset = $this->apiService->loadDatasetFromFile($cachePath);
            } else {
                $this->info('Consultando estados e municipios na API oficial do IBGE...');
                $dataset = $this->apiService->fetchDatasetFromApi();

                if ($writeCache) {
                    $this->apiService->writeDatasetToFile($dataset, $cachePath);
                    $this->info('Arquivo local atualizado: ' . $cachePath);
                }
            }

            $this->line('Fonte: ' . ($dataset['source'] ?? 'n/a'));
            if (! empty($dataset['generated_at'])) {
                $this->line('Gerado em: ' . $dataset['generated_at']);
            }
            $this->line('Estados recebidos: ' . count($dataset['states'] ?? []));
            $this->line('Cidades recebidas: ' . count($dataset['cities'] ?? []));

            $this->info('Aplicando sincronizacao incremental em estados e cidades...');
            $stats = $this->syncService->sync($dataset);

            $this->newLine();
            $this->info('Sincronizacao finalizada com sucesso.');
            $this->table(
                ['Indicador', 'Total'],
                [
                    ['Estados inseridos', $stats['states_inserted'] ?? 0],
                    ['Estados atualizados', $stats['states_updated'] ?? 0],
                    ['Estados sem codigo IBGE', $stats['states_without_ibge'] ?? 0],
                    ['Cidades inseridas', $stats['cities_inserted'] ?? 0],
                    ['Cidades atualizadas', $stats['cities_updated'] ?? 0],
                    ['Cidades sem correspondencia oficial', $stats['cities_without_match'] ?? 0],
                    ['Cidades com correspondencia ambigua', $stats['cities_ambiguous_matches'] ?? 0],
                    ['Cidades ignoradas por estado ausente', $stats['cities_missing_state'] ?? 0],
                    ['Estados com IBGE duplicado', $stats['states_with_duplicate_ibge'] ?? 0],
                    ['Cidades com IBGE duplicado', $stats['cities_with_duplicate_ibge'] ?? 0],
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Erro ao sincronizar base IBGE: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }
}
