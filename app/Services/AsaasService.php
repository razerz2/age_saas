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
        $this->baseUrl = rtrim(config('services.asaas.base_url', env('ASAAS_API_URL')), '/') . '/';
        $this->apiKey  = config('services.asaas.api_key', env('ASAAS_API_KEY'));

        if (empty($this->apiKey)) {
            Log::error('❌ Chave API do Asaas não configurada.');
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
     * Cria uma cobrança (fatura) no Asaas.
     */
    public function createPayment(array $data)
    {
        try {
            $payload = [
                'customer'          => $data['customer'],
                'billingType'       => $data['billingType'] ?? 'PIX',
                'dueDate'           => $data['due_date'],
                'value'             => $data['amount'],
                'description'       => $data['description'] ?? 'Cobrança SaaS',
                'externalReference' => $data['external_reference'] ?? null,
            ];

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $this->apiKey, // ✅ header correto
            ])
                ->post($this->baseUrl . 'payments', $payload)
                ->json();

            Log::info('📡 Asaas createPayment resposta:', $response);
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
}
