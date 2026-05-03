<?php

namespace App\Services\Billing\Providers;

use App\Services\Billing\BillingProviderInterface;
use App\Models\Tenant\Patient;
use App\Models\Tenant\FinancialCharge;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provider de billing para Asaas
 * 
 * Implementa BillingProviderInterface para integração com gateway Asaas
 */
class AsaasBillingProvider implements BillingProviderInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $environment;

    public function __construct()
    {
        // Buscar configurações do tenant
        $this->environment = tenant_setting('finance.billing.asaas.environment', 'sandbox');
        $this->apiKey = tenant_setting('finance.billing.asaas.api_key', '');

        // Definir URL base baseado no ambiente
        if ($this->environment === 'production') {
            $this->baseUrl = 'https://api.asaas.com/v3/';
        } else {
            $this->baseUrl = 'https://api-sandbox.asaas.com/v3/';
        }

        if (empty($this->apiKey)) {
            Log::warning('⚠️ AsaasBillingProvider: API Key não configurada para o tenant.');
        }
    }

    /**
     * Cria ou busca um cliente no Asaas
     */
    public function createCustomer(Patient $patient): ?string
    {
        try {
            // Se o paciente já tem customer_id, retorna
            if ($patient->asaas_customer_id ?? null) {
                return $patient->asaas_customer_id;
            }

            // Buscar cliente existente
            $searchResponse = $this->searchCustomer($patient->cpf ?? $patient->email);
            if (!empty($searchResponse['data']) && count($searchResponse['data']) > 0) {
                $customerId = $searchResponse['data'][0]['id'];
                // Atualizar paciente com customer_id
                $patient->update(['asaas_customer_id' => $customerId]);
                return $customerId;
            }

            // Criar novo cliente
            $payload = [
                'name' => $patient->full_name,
                'email' => $patient->email,
                'phone' => $patient->phone,
            ];

            if ($patient->cpf) {
                $cpf = preg_replace('/\D/', '', $patient->cpf);
                if (strlen($cpf) === 11) {
                    $payload['cpfCnpj'] = $cpf;
                }
            }

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->post($this->baseUrl . 'customers', $payload);

            $statusCode = $response->status();
            $responseData = $response->json();

            if ($statusCode >= 200 && $statusCode < 300 && isset($responseData['id'])) {
                $customerId = $responseData['id'];
                $patient->update(['asaas_customer_id' => $customerId]);
                return $customerId;
            }

            Log::error('❌ Erro ao criar cliente no Asaas', [
                'status' => $statusCode,
                'response' => $responseData,
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao criar/buscar cliente Asaas: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cria uma cobrança no Asaas
     */
    public function createCharge(FinancialCharge $charge): array
    {
        try {
            $patient = $charge->patient;
            $customerId = $this->createCustomer($patient);

            if (!$customerId) {
                return [
                    'error' => true,
                    'message' => 'Não foi possível criar/buscar cliente no Asaas',
                ];
            }

            // Determinar billingType baseado nos métodos de pagamento configurados
            $paymentMethods = json_decode(tenant_setting('finance.billing.payment_methods', '["pix"]'), true) ?? ['pix'];
            
            // Se tiver múltiplos métodos, usar UNDEFINED para permitir escolha
            $billingType = 'UNDEFINED';
            if (count($paymentMethods) === 1) {
                $billingType = $this->mapBillingType($paymentMethods[0]);
            }

            $payload = [
                'customer' => $customerId,
                'billingType' => $billingType,
                'dueDate' => $charge->due_date->format('Y-m-d'),
                'value' => number_format($charge->amount, 2, '.', ''),
                'description' => $charge->billing_type === 'reservation' 
                    ? 'Reserva de Agendamento' 
                    : 'Pagamento de Consulta',
                'externalReference' => $charge->id,
            ];

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->post($this->baseUrl . 'payments', $payload);

            $statusCode = $response->status();
            $responseData = $response->json();

            Log::info('📡 Asaas createCharge resposta:', [
                'status' => $statusCode,
                'charge_id' => $charge->id,
                'response' => $responseData,
            ]);

            if ($statusCode >= 200 && $statusCode < 300 && isset($responseData['id'])) {
                return [
                    'error' => false,
                    'data' => $responseData,
                ];
            }

            $errorMessage = $responseData['errors'][0]['description'] ?? 
                          $responseData['message'] ?? 
                          'Erro ao criar cobrança no Asaas';

            return [
                'error' => true,
                'message' => $errorMessage,
            ];
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao criar cobrança Asaas: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancela uma cobrança no Asaas
     */
    public function cancelCharge(FinancialCharge $charge): bool
    {
        try {
            if (!$charge->asaas_charge_id) {
                Log::warning('Tentativa de cancelar cobrança sem asaas_charge_id', [
                    'charge_id' => $charge->id,
                ]);
                return false;
            }

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->delete($this->baseUrl . 'payments/' . $charge->asaas_charge_id);

            return $response->status() >= 200 && $response->status() < 300;
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao cancelar cobrança: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Consulta status da cobrança no Asaas
     */
    public function getChargeStatus(FinancialCharge $charge): array
    {
        try {
            if (!$charge->asaas_charge_id) {
                return [
                    'error' => true,
                    'message' => 'Cobrança não possui asaas_charge_id',
                ];
            }

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->get($this->baseUrl . 'payments/' . $charge->asaas_charge_id);

            $statusCode = $response->status();
            $responseData = $response->json();

            if ($statusCode >= 200 && $statusCode < 300) {
                return [
                    'error' => false,
                    'data' => $responseData,
                ];
            }

            return [
                'error' => true,
                'message' => 'Erro ao consultar status da cobrança',
                'data' => $responseData,
            ];
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao consultar status da cobrança: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Gera ou recupera o link de pagamento da cobrança
     */
    public function generatePaymentLink(FinancialCharge $charge): ?string
    {
        try {
            if (!$charge->asaas_charge_id) {
                // Tentar criar cobrança primeiro
                $result = $this->createCharge($charge);
                if ($result['error'] ?? false) {
                    return null;
                }
                $charge->refresh();
            }

            // Se já tem payment_link, retorna
            if ($charge->payment_link) {
                return $charge->payment_link;
            }

            // Buscar cobrança no Asaas para obter o link
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->get($this->baseUrl . 'payments/' . $charge->asaas_charge_id);

            $statusCode = $response->status();
            $responseData = $response->json();

            if ($statusCode >= 200 && $statusCode < 300 && isset($responseData['invoiceUrl'])) {
                return $responseData['invoiceUrl'];
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao gerar link de pagamento: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca cliente no Asaas
     */
    protected function searchCustomer(string $query): array
    {
        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->get($this->baseUrl . 'customers', [
                    'name' => $query,
                    'cpfCnpj' => preg_replace('/\D/', '', $query),
                ]);

            return $response->json() ?? ['data' => []];
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao buscar cliente Asaas: ' . $e->getMessage());
            return ['data' => []];
        }
    }

    /**
     * Mapeia método de pagamento para billingType do Asaas
     */
    protected function mapBillingType(string $method): string
    {
        return match($method) {
            'pix' => 'PIX',
            'credit_card' => 'CREDIT_CARD',
            'boleto' => 'BOLETO',
            default => 'PIX',
        };
    }

    /**
     * Mapeia status do Asaas para status interno
     */
    public function mapStatus(string $asaasStatus): string
    {
        return match(strtoupper($asaasStatus)) {
            'PENDING' => 'pending',
            'RECEIVED', 'CONFIRMED' => 'paid',
            'OVERDUE' => 'expired',
            'REFUNDED', 'CANCELLED' => 'cancelled',
            default => 'pending',
        };
    }
}
