<?php

namespace App\Services;

use App\Models\Platform\Subscription;
use App\Models\Platform\Invoices;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{

    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $baseUrl = null;
        $apiKey = null;

        // Usa sysconfig se disponível
        if (function_exists('sysconfig')) {
            $baseUrl = sysconfig('ASAAS_API_URL');
            $apiKey = sysconfig('ASAAS_API_KEY');
        }

        // fallback: usa config() e env()
        $this->baseUrl = rtrim(
            $baseUrl ?: config('services.asaas.base_url', env('ASAAS_BASE_URL', env('ASAAS_API_URL'))),
            '/'
        ) . '/';

        $this->apiKey = $apiKey ?: config('services.asaas.api_key', env('ASAAS_API_KEY'));

        if (empty($this->apiKey) || $this->baseUrl === '/') {
            Log::warning('⚠️ AsaasService: configuração vazia. Verifique sysconfig e .env.');
        }
    }



    /**
     * Cria um cliente no Asaas.
     */
    public function createCustomer(array $data)
    {
        try {
            // Sanitiza CNPJ/CPF
            $document = preg_replace('/\D/', '', $data['document'] ?? '');

            $payload = [
                'name'       => $data['trade_name'] ?? $data['legal_name'],
                'email'      => $data['email'],
                'phone'      => preg_replace('/\D/', '', $data['phone'] ?? ''),
                'cpfCnpj'    => $document,
                'personType' => strlen($document) > 11 ? 'JURIDICA' : 'FISICA',
                'externalReference' => $data['id'],
            ];

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey, // ✅ header correto!
            ])
                ->post($this->baseUrl . 'customers', $payload)
                ->json();

            Log::info('📡 Asaas createCustomer resposta:', $response);
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao criar cliente Asaas: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Busca cliente existente por e-mail.
     */
    public function searchCustomer(string $email)
    {
        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->get($this->baseUrl . 'customers', ['email' => $email])
                ->json();

            Log::info('📡 Asaas searchCustomer resposta:', $response);
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar cliente Asaas: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Lista clientes (paginação simples).
     */
    public function listCustomers(int $page = 1, int $limit = 100)
    {
        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->get($this->baseUrl . 'customers', [
                'limit' => $limit,
                'offset' => ($page - 1) * $limit,
            ])->json();

            Log::info('📡 Asaas listCustomers resposta:', $response);
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao listar clientes Asaas: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    /**
     * Atualiza um clientes.
     */
    public function updateCustomer(string $customerId, array $data)
    {
        try {
            $document = preg_replace('/\D/', '', $data['document'] ?? '');

            $payload = [
                'name'       => $data['trade_name'] ?? $data['legal_name'],
                'email'      => $data['email'],
                'phone'      => preg_replace('/\D/', '', $data['phone'] ?? ''),
                'cpfCnpj'    => $document,
                'personType' => strlen($document) > 11 ? 'JURIDICA' : 'FISICA',
            ];

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->put($this->baseUrl . 'customers/' . $customerId, $payload)
                ->json();

            Log::info("🔄 Asaas updateCustomer resposta:", $response);
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao atualizar cliente Asaas: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * Exclui um cliente específico.
     */
    public function deleteCustomer(string $customerId)
    {
        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->delete($this->baseUrl . 'customers/' . $customerId)
                ->json();

            Log::info("🗑️ Cliente {$customerId} excluído do Asaas:", $response);
            return $response;
        } catch (\Exception $e) {
            Log::error("❌ Erro ao excluir cliente {$customerId}: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Cria uma assinatura recorrente no Asaas (para cartão de crédito).
     */
    public function createSubscription(array $data)
    {
        try {
            $payload = [
                'customer'      => $data['customer'],
                'billingType'   => 'CREDIT_CARD',
                'value'         => $data['value'],
                'cycle'         => $data['cycle'] ?? 'MONTHLY',
                'nextDueDate'   => $data['nextDueDate'] ?? now()->addDay()->toDateString(),
                'description'   => $data['description'] ?? 'Assinatura recorrente SaaS',
            ];

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->post($this->baseUrl . 'subscriptions', $payload)
                ->json();

            Log::info('📡 Asaas createSubscription resposta:', $response);
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao criar assinatura Asaas: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Atualiza assinatura existente.
     */
    public function updateSubscription(string $subscriptionId, array $data)
    {
        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->put("{$this->baseUrl}subscriptions/{$subscriptionId}", $data)
                ->json();

            Log::info("🔄 Asaas updateSubscription resposta:", $response);
            return $response;
        } catch (\Exception $e) {
            Log::error("❌ Erro ao atualizar assinatura Asaas: {$e->getMessage()}");
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Exclui assinatura no Asaas.
     */
    public function deleteSubscription(string $subscriptionId)
    {
        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->delete("{$this->baseUrl}subscriptions/{$subscriptionId}")
                ->json();

            Log::info("🗑️ Assinatura {$subscriptionId} excluída no Asaas:", $response);
            return $response;
        } catch (\Exception $e) {
            Log::error("❌ Erro ao excluir assinatura Asaas: {$e->getMessage()}");
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Cria uma cobrança (fatura) no Asaas.
     */
    public function createPayment(array $data)
    {
        try {
            $payload = [
                'customer'          => $data['customer'],
                // define billingType de forma segura (padrão PIX)
                'billingType'       => $data['billingType'] ?? 'PIX',
                // aceita tanto dueDate quanto due_date
                'dueDate'           => $data['dueDate'] ?? ($data['due_date'] ?? now()->addDays(5)->toDateString()),
                // aceita tanto value quanto amount
                'value'             => $data['value'] ?? ($data['amount'] ?? 0),
                'description'       => $data['description'] ?? 'Cobrança SaaS',
                'externalReference' => $data['externalReference'] ?? ($data['external_reference'] ?? null),
            ];

            $response = Http::withHeaders([
                'accept'        => 'application/json',
                'content-type'  => 'application/json',
                'access_token'  => $this->apiKey,
            ])
                ->post($this->baseUrl . 'payments', $payload)
                ->json();

            Log::info("📡 Asaas createPayment ({$payload['billingType']}) resposta:", $response);
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao criar pagamento Asaas: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Busca o status de uma cobrança.
     */
    public function getPaymentStatus(string $paymentId)
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
        ])->get("{$this->baseUrl}/payments/{$paymentId}");

        return $response->json();
    }

    public function createInvoiceForSubscription(Subscription $subscription)
    {
        try {
            $tenant = $subscription->tenant;
            $plan   = $subscription->plan;

            if (!$tenant->asaas_customer_id) {
                // 1️⃣ Garante que o cliente existe no Asaas
                $customerResponse = $this->createCustomer($tenant);
                if (!isset($customerResponse['id'])) {
                    Log::error("❌ Falha ao criar cliente Asaas para {$tenant->trade_name}");
                    return null;
                }

                $tenant->update(['asaas_customer_id' => $customerResponse['id']]);
            }

            // 2️⃣ Monta os dados da cobrança
            $payload = [
                'customer'        => $tenant->asaas_customer_id,
                'billingType'     => 'PIX',
                'dueDate'         => now()->addDays(5)->toDateString(),
                'value'           => $plan->price_cents / 100,
                'description'     => "Renovação de plano {$plan->name}",
                'externalReference' => $subscription->id,
            ];

            // 3️⃣ Cria a cobrança no Asaas
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->post("{$this->baseUrl}/payments", $payload);

            $data = $response->json();

            if (isset($data['id'])) {
                // 4️⃣ Cria a fatura localmente
                $invoice = Invoices::create([
                    'subscription_id' => $subscription->id,
                    'tenant_id'       => $tenant->id,
                    'amount_cents'    => $plan->price_cents,
                    'due_date'        => $payload['dueDate'],
                    'status'          => 'pending',
                    'payment_link'    => $data['invoiceUrl'] ?? null,
                    'provider'        => 'asaas',
                    'provider_id'     => $data['id'],
                ]);

                Log::info("✅ Fatura {$data['id']} criada para assinatura {$subscription->id}");
                return $invoice;
            }

            Log::error("❌ Falha ao criar fatura Asaas: " . json_encode($data));
            return null;
        } catch (\Exception $e) {
            Log::error("💥 Erro ao criar fatura Asaas: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Lista pagamentos (paginação simples).
     */
    public function listPayments(int $page = 1, int $limit = 100)
    {
        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->get($this->baseUrl . 'payments', [
                'limit' => $limit,
                'offset' => ($page - 1) * $limit,
            ])->json();

            Log::info('📡 Asaas listPayments resposta:', $response);
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao listar pagamentos Asaas: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Exclui um pagamento específico.
     */
    public function deletePayment(string $paymentId)
    {
        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->delete($this->baseUrl . 'payments/' . $paymentId)
                ->json();

            Log::info("🗑️ Pagamento {$paymentId} excluído do Asaas:", $response);
            return $response;
        } catch (\Exception $e) {
            Log::error("❌ Erro ao excluir pagamento {$paymentId}: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

}
