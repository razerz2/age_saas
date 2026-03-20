<?php

namespace App\Jobs\Platform;

use App\Models\Platform\ImportLog;
use App\Models\Platform\ClinicNetwork;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Estado;
use App\Models\Platform\Cidade;
use App\Services\Platform\TenantCreatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;

class ImportTenantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importLog;
    protected $tenantCreator;

    /**
     * O Job pode demorar bastante, então aumentamos o timeout
     */
    public $timeout = 900; // 15 minutos

    public function __construct(ImportLog $importLog)
    {
        $this->importLog = $importLog;
    }

    public function handle(TenantCreatorService $tenantCreator)
    {
        $this->tenantCreator = $tenantCreator;
        $this->importLog->update(['status' => 'processing']);

        $config = $this->importLog->config;
        $networkId = $config['network_id'];
        $planId = $config['plan_id'];
        $allowDuplicateDoc = $config['allow_duplicate_document'] ?? false;

        $network = ClinicNetwork::find($networkId);
        $plan = Plan::find($planId);

        if (!$network || !$plan) {
            $this->failImport("Rede ou Plano não encontrados.");
            return;
        }

        $filePath = storage_path('app/' . $this->importLog->temp_path);
        if (!file_exists($filePath)) {
            $this->failImport("Arquivo temporário não encontrado.");
            return;
        }

        $data = $this->parseCsv($filePath);
        $this->importLog->update(['total_rows' => count($data)]);

        $results = [
            'success' => [],
            'errors' => [],
            'skipped' => [],
        ];

        foreach ($data as $index => $row) {
            $lineNumber = $index + 2;

            // Validações básicas por linha
            if (empty($row['legal_name']) || empty($row['subdomain']) || empty($row['endereco'])) {
                $this->addError($results, "Linha {$lineNumber}: Campos obrigatórios ausentes.");
                continue;
            }

            // 🔍 IDENTIFICAR JÁ CADASTRADOS (IDEMPOTÊNCIA)
            // Se o subdomínio já existir, ignoramos silenciosamente como solicitado
            if (Tenant::where('subdomain', $row['subdomain'])->exists()) {
                $results['skipped'][] = "Linha {$lineNumber}: Clínica com subdomínio '{$row['subdomain']}' já cadastrada. Ignorando...";
                $this->importLog->increment('processed_rows');
                $this->importLog->increment('skipped_count');
                continue;
            }

            // Validar unicidade de email (se informado e diferente do subdomínio)
            if (!empty($row['email']) && Tenant::where('email', $row['email'])->exists()) {
                $this->addError($results, "Linha {$lineNumber}: E-mail '{$row['email']}' já está em uso por outra clínica.");
                continue;
            }

            // Validar documento
            if (!$allowDuplicateDoc && !empty($row['document'])) {
                $cleanDoc = preg_replace('/\D/', '', $row['document']);
                if (Tenant::whereRaw("regexp_replace(document, '\D', '', 'g') = ?", [$cleanDoc])->exists()) {
                    $this->addError($results, "Linha {$lineNumber}: O documento '{$row['document']}' já está cadastrado.");
                    continue;
                }
            }

            try {
                // Resolver IDs de localização
                $estadoId = null;
                $cidadeId = null;

                if (!empty($row['estado'])) {
                    $estado = Estado::where('pais_id', 31)
                        ->where(function($q) use ($row) {
                            $q->where('uf', strtoupper(trim($row['estado'])))
                              ->orWhere('nome_estado', 'ilike', trim($row['estado']));
                        })->first();
                    
                    if ($estado) {
                        $estadoId = $estado->id_estado;
                        if (!empty($row['cidade'])) {
                            $cidade = Cidade::where('estado_id', $estadoId)
                                ->where('nome_cidade', 'ilike', trim($row['cidade']))
                                ->first();
                            if ($cidade) {
                                $cidadeId = $cidade->id_cidade;
                            }
                        }
                    }
                }

                $tenantData = array_merge($row, [
                    'network_id' => $network->id,
                    // Mantido para compatibilidade legada; a elegibilidade comercial
                    // continua dependente da assinatura criada pelo TenantCreatorService.
                    'plan_id'    => $plan->id,
                    'pais_id'    => 31,
                    'estado_id'  => $estadoId,
                    'cidade_id'  => $cidadeId,
                ]);

                unset($tenantData['estado'], $tenantData['cidade']);

                $tenant = $this->tenantCreator->create($tenantData);
                SpatieTenant::forgetCurrent();

                $results['success'][] = "Linha {$lineNumber}: Clínica '{$tenant->legal_name}' importada.";
                $this->importLog->increment('processed_rows');

            } catch (\Throwable $e) {
                Log::error("Erro na importação (Job) linha {$lineNumber}", ['error' => $e->getMessage()]);
                $this->addError($results, "Linha {$lineNumber}: Erro interno: " . $e->getMessage());
            }

            // Atualizar sumário em tempo real a cada 5 linhas para não sobrecarregar
            if ($index % 5 === 0) {
                $this->importLog->update(['summary' => $results]);
            }
        }

        // Finalizar
        $this->importLog->update([
            'status' => 'completed',
            'summary' => $results,
        ]);

        // Remover arquivo temporário
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    protected function addError(&$results, $message)
    {
        $results['errors'][] = $message;
        $this->importLog->increment('processed_rows');
        $this->importLog->increment('error_count');
    }

    protected function failImport($message)
    {
        $this->importLog->update([
            'status' => 'failed',
            'summary' => ['errors' => [$message]],
        ]);
    }

    private function parseCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            if ($header) {
                $header = array_map(fn($h) => strtolower(trim($h)), $header);
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($header) === count($data)) {
                        $rows[] = array_combine($header, $data);
                    }
                }
            }
            fclose($handle);
        }
        return $rows;
    }
}
