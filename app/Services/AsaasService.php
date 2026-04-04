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
        $resolved = function_exists('asaas_config')
            ? asaas_config()
            : [
                'api_url' => (string) config('services.asaas.api_url', config('services.asaas.url', env('ASAAS_BASE_URL', env('ASAAS_API_URL', '')))),
                'api_key' => (string) config('services.asaas.api_key', env('ASAAS_API_KEY', '')),
                'webhook_secret' => (string) config('services.asaas.webhook_secret', env('ASAAS_WEBHOOK_SECRET', '')),
            ];

        // Usa sysconfig se disponível
        

        // fallback: usa config() e env()
        // OBS: em config/services.php a chave é "services.asaas.url" (não "base_url")
        $rawBaseUrl = (string) ($resolved['api_url'] ?? '');

        $this->baseUrl = rtrim((string) $rawBaseUrl, '/') . '/';

        $rawApiKey = (string) ($resolved['api_key'] ?? '');

        // Garante string para não quebrar com typed properties (PHP 8+)
        $this->apiKey = (string) $rawApiKey;

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
            
            // Validação básica
            if (empty($data['email'])) {
                return ['error' => 'Email é obrigatório para criar cliente no Asaas'];
            }
            
            $name = $data['trade_name'] ?? $data['legal_name'] ?? '';
            if (empty($name)) {
                return ['error' => 'Nome é obrigatório para criar cliente no Asaas'];
            }

            $payload = [
                'name'       => $name,
                'email'      => $data['email'],
                'externalReference' => $data['id'] ?? null,
            ];
            
            // Adiciona telefone se fornecido
            if (!empty($data['phone'])) {
                $phone = preg_replace('/\D/', '', $data['phone']);
                if (!empty($phone)) {
                    $payload['phone'] = $phone;
                }
            }
            
            // Adiciona documento se fornecido
            if (!empty($document)) {
                $payload['cpfCnpj'] = $document;
                $payload['personType'] = strlen($document) > 11 ? 'JURIDICA' : 'FISICA';
            }

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey, // ✅ header correto!
            ])
                ->post($this->baseUrl . 'customers', $payload);

            $statusCode = $response->status();
            $responseData = $response->json();

            Log::info('📡 Asaas createCustomer resposta:', [
                'status' => $statusCode,
                'response' => $responseData,
            ]);

            // Se não foi sucesso (2xx), trata como erro
            if ($statusCode < 200 || $statusCode >= 300) {
                $errorMessage = $responseData['errors'][0]['description'] ?? 
                               $responseData['message'] ?? 
                               'Erro ao criar cliente no Asaas';
                
                Log::error('❌ Erro ao criar cliente Asaas', [
                    'status' => $statusCode,
                    'response' => $responseData,
                    'error_message' => $errorMessage,
                ]);
                
                return [
                    'error' => $errorMessage,
                    'errors' => $responseData['errors'] ?? [],
                ];
            }

            return $responseData;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao criar cliente Asaas: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
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
            /**
             * 1️⃣ Cria a assinatura no Asaas
             */
            $subscriptionPayload = [
                'customer'     => $data['customer'],
                'billingType'  => 'CREDIT_CARD',
                'value'        => $data['value'],
                'cycle'        => $data['cycle'] ?? 'MONTHLY',
                'nextDueDate'  => $data['nextDueDate'] ?? now()->addDay()->toDateString(),
                'description'  => $data['description'] ?? 'Assinatura SaaS',
            ];

            $subscriptionResponse = Http::withHeaders([
                'accept'       => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])->post($this->baseUrl . 'subscriptions', $subscriptionPayload)->json();

            Log::info('📡 Asaas createSubscription resposta:', is_array($subscriptionResponse) ? $subscriptionResponse : ['response' => $subscriptionResponse]);

            if (empty($subscriptionResponse['id'])) {
                return [
                    'error'    => true,
                    'message'  => 'Falha ao criar assinatura no Asaas.',
                    'response' => $subscriptionResponse,
                ];
            }

            $subscriptionId = $subscriptionResponse['id'];

            /**
             * 2️⃣ Cria um Payment Link (checkout hospedado no Asaas)
             */
            $paymentLinkPayload = [
                'name'           => 'Assinatura SaaS - ' . ($data['description'] ?? 'Plano'),
                'description'    => 'Pagamento inicial da assinatura SaaS.',
                'billingType'    => 'CREDIT_CARD',
                'chargeType'     => 'RECURRENT', // 👈 recorrente
                'endDate'        => now()->addYears(1)->toDateString(),
                'value'          => $data['value'],
                'subscription'   => $subscriptionId,
                'dueDateLimitDays' => 5,
            ];

            $paymentLinkResponse = Http::withHeaders([
                'accept'       => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])->post($this->baseUrl . 'paymentLinks', $paymentLinkPayload)->json();

            Log::info('💳 Asaas createPaymentLink resposta:', is_array($paymentLinkResponse) ? $paymentLinkResponse : ['response' => $paymentLinkResponse]);

            /**
             * 3️⃣ Retorna os dados estruturados
             */
            return [
                'subscription' => $subscriptionResponse,
                'payment'      => $paymentLinkResponse ?? [],
                'payment_link' => $paymentLinkResponse['url'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao criar assinatura Asaas: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error'   => true,
                'message' => $e->getMessage(),
            ];
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
     * Cria um Payment Link (Link de Pagamento) no Asaas com múltiplas formas de pagamento.
     * Permite que o cliente escolha entre PIX, Boleto e Cartão de Crédito.
     */
    public function createPaymentLink(array $data)
    {
        try {
            $payload = [
                'name'              => $data['name'] ?? 'Pagamento de Plano',
                'description'      => $data['description'] ?? 'Pagamento de plano SaaS',
                'billingType'       => 'UNDEFINED', // Permite múltiplas formas de pagamento (PIX, Boleto, Cartão)
                'chargeType'        => 'DETACHED', // Pagamento único (não recorrente)
                'value'             => $data['value'] ?? 0,
                'dueDateLimitDays'  => $data['dueDateLimitDays'] ?? 5,
            ];

            // Se tiver customer, vincula ao cliente
            if (!empty($data['customer'])) {
                $payload['customer'] = $data['customer'];
            }

            // Se tiver externalReference, adiciona
            if (!empty($data['externalReference'])) {
                $payload['externalReference'] = $data['externalReference'];
            }

            $response = Http::withHeaders([
                'accept'       => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->post($this->baseUrl . 'paymentLinks', $payload);

            $statusCode = $response->status();
            $responseData = $response->json();

            Log::info('📡 Asaas createPaymentLink resposta:', [
                'status' => $statusCode,
                'response' => $responseData,
            ]);

            // Se não foi sucesso (2xx), trata como erro
            if ($statusCode < 200 || $statusCode >= 300) {
                $errorMessage = $responseData['errors'][0]['description'] ?? 
                               $responseData['message'] ?? 
                               'Erro ao criar link de pagamento no Asaas';
                
                Log::error('❌ Erro ao criar Payment Link Asaas', [
                    'status' => $statusCode,
                    'response' => $responseData,
                    'error_message' => $errorMessage,
                ]);
                
                return [
                    'error' => $errorMessage,
                    'errors' => $responseData['errors'] ?? [],
                ];
            }

            return $responseData;
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao criar Payment Link Asaas: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
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
                'billingType'       => $data['billingType'] ?? 'PIX',
                'dueDate'           => $data['dueDate'] ?? now()->addDays(5)->toDateString(),
                'value'             => $data['value'] ?? 0,
                'description'       => $data['description'] ?? 'Cobrança SaaS',
                'externalReference' => $data['externalReference'] ?? null,
            ];

            $response = Http::withHeaders([
                'accept'       => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->post($this->baseUrl . 'payments', $payload);

            $statusCode = $response->status();
            $responseData = $response->json();

            Log::info('📡 Asaas createPayment resposta:', [
                'status' => $statusCode,
                'response' => $responseData,
            ]);

            // Se não foi sucesso (2xx), trata como erro
            if ($statusCode < 200 || $statusCode >= 300) {
                $errorMessage = $responseData['errors'][0]['description'] ?? 
                               $responseData['message'] ?? 
                               'Erro ao criar pagamento no Asaas';
                
                Log::error('❌ Erro ao criar pagamento Asaas', [
                    'status' => $statusCode,
                    'response' => $responseData,
                    'error_message' => $errorMessage,
                ]);
                
                return [
                    'error' => $errorMessage,
                    'errors' => $responseData['errors'] ?? [],
                ];
            }

            return $responseData;
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao criar pagamento Asaas: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Atualiza uma cobrança existente (fatura).
     */
    public function updatePayment(string $paymentId, array $data)
    {
        try {
            $response = Http::withHeaders([
                'accept'       => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])
                ->put($this->baseUrl . 'payments/' . $paymentId, $data)
                ->json();

            Log::info('🔄 Asaas updatePayment resposta:', is_array($response) ? $response : ['response' => $response]);
            return $response;
            
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao atualizar pagamento Asaas: ' . $e->getMessage(), [
                'paymentId' => $paymentId,
                'trace'     => $e->getTraceAsString(),
            ]);
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
    public function listPayments(int $page = 1, int $limit = 100, ?string $customerId = null)
    {
        try {
            $params = [
                'limit' => $limit,
                'offset' => ($page - 1) * $limit,
            ];

            // Se fornecido, filtra por cliente
            if ($customerId) {
                $params['customer'] = $customerId;
            }

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->get($this->baseUrl . 'payments', $params)->json();

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
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])->delete($this->baseUrl . "payments/{$paymentId}")
                ->json();

            Log::info('🗑️ Pagamento excluído no Asaas.', [
                'payment_id' => $paymentId,
                'response'   => $response ?? [],
            ]);

            return $response;
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao excluir pagamento no Asaas: {$e->getMessage()}", [
                'payment_id' => $paymentId,
                'trace'      => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Exclui um Payment Link específico.
     */
    public function deletePaymentLink(string $paymentLinkId)
    {
        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->apiKey,
            ])->delete($this->baseUrl . "paymentLinks/{$paymentLinkId}")
                ->json();

            Log::info('🗑️ Payment Link excluído no Asaas.', [
                'payment_link_id' => $paymentLinkId,
                'response'        => $response ?? [],
            ]);

            return $response;
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao excluir Payment Link no Asaas: {$e->getMessage()}", [
                'payment_link_id' => $paymentLinkId,
                'trace'           => $e->getTraceAsString(),
            ]);

            return ['error' => $e->getMessage()];
        }
    }
}
