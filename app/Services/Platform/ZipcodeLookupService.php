<?php

namespace App\Services\Platform;

use App\Models\Platform\Cidade;
use App\Services\Platform\Exceptions\ZipcodeLookupException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZipcodeLookupService
{
    private const CACHE_TTL_SECONDS = 21600;

    /**
     * @return array<string,mixed>
     */
    public function lookup(string $zipcode): array
    {
        $digits = preg_replace('/\D+/', '', $zipcode);
        if (strlen((string) $digits) !== 8) {
            throw new ZipcodeLookupException('CEP invalido. Informe 8 digitos.', 422, 'invalid_zipcode');
        }

        $cacheKey = 'zipcode_lookup:v1:' . $digits;

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($digits): array {
            $viacep = $this->fetchFromViaCep($digits);
            $cityIbgeId = preg_replace('/\D+/', '', (string) ($viacep['ibge'] ?? ''));

            $city = null;
            $state = null;
            $warnings = [];

            if (! empty($cityIbgeId)) {
                $city = Cidade::query()
                    ->with('estado')
                    ->where('ibge_id', (int) $cityIbgeId)
                    ->first();

                if ($city) {
                    $state = $city->estado;
                } else {
                    $warnings[] = 'Municipio retornado pelo ViaCEP nao foi encontrado na base interna por codigo IBGE.';
                    Log::warning('CEP retornou municipio sem correspondencia local por IBGE.', [
                        'zipcode' => $digits,
                        'city_ibge_id' => $cityIbgeId,
                        'uf' => $viacep['uf'] ?? null,
                        'localidade' => $viacep['localidade'] ?? null,
                    ]);
                }
            } else {
                $warnings[] = 'ViaCEP nao retornou codigo IBGE do municipio.';
                Log::warning('ViaCEP retornou CEP sem codigo IBGE do municipio.', [
                    'zipcode' => $digits,
                    'response' => $viacep,
                ]);
            }

            return [
                'zipcode' => $this->formatZipcode($digits),
                'street' => (string) ($viacep['logradouro'] ?? ''),
                'neighborhood' => (string) ($viacep['bairro'] ?? ''),
                'complement' => (string) ($viacep['complemento'] ?? ''),
                'state' => $state ? [
                    'id' => (int) $state->id_estado,
                    'uf' => (string) $state->uf,
                    'name' => (string) $state->nome_estado,
                    'ibge_id' => $state->ibge_id !== null ? (int) $state->ibge_id : null,
                ] : null,
                'city' => $city ? [
                    'id' => (int) $city->id_cidade,
                    'name' => (string) $city->nome_cidade,
                    'ibge_id' => $city->ibge_id !== null ? (int) $city->ibge_id : null,
                ] : null,
                'fallback' => [
                    'state_uf' => (string) ($viacep['uf'] ?? ''),
                    'city_name' => (string) ($viacep['localidade'] ?? ''),
                    'city_ibge_id' => $cityIbgeId !== '' ? $cityIbgeId : null,
                ],
                'warnings' => $warnings,
            ];
        });
    }

    /**
     * @return array<string,mixed>
     */
    private function fetchFromViaCep(string $digits): array
    {
        try {
            $response = Http::timeout(8)
                ->retry(1, 200)
                ->acceptJson()
                ->get(sprintf('https://viacep.com.br/ws/%s/json/', $digits));
        } catch (\Throwable $exception) {
            throw new ZipcodeLookupException('Falha ao consultar o servico de CEP externo.', 502, 'zipcode_service_unavailable');
        }

        if (! $response->successful()) {
            throw new ZipcodeLookupException('Servico de CEP externo indisponivel.', 502, 'zipcode_service_unavailable');
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new ZipcodeLookupException('Resposta invalida recebida do servico de CEP.', 502, 'invalid_zipcode_response');
        }

        if (! empty($payload['erro'])) {
            throw new ZipcodeLookupException('CEP nao encontrado.', 404, 'zipcode_not_found');
        }

        return $payload;
    }

    private function formatZipcode(string $digits): string
    {
        return substr($digits, 0, 5) . '-' . substr($digits, 5, 3);
    }
}
