<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AsaasService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.asaas.base_url', env('ASAAS_BASE_URL'));
        $this->apiKey = config('services.asaas.api_key', env('ASAAS_API_KEY'));
    }

    /**
     * Cria um cliente (Tenant) no Asaas.
     */
    public function createCustomer(array $tenant)
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
        ])->post("{$this->baseUrl}/customers", [
            'name' => $tenant['trade_name'] ?? $tenant['legal_name'],
            'email' => $tenant['email'],
            'phone' => $tenant['phone'],
            'cpfCnpj' => $tenant['document'],
        ]);

        return $response->json();
    }

    /**
     * Cria uma cobrança (Invoice) no Asaas.
     */
    public function createPayment(array $data)
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
        ])->post("{$this->baseUrl}/payments", [
            'customer'     => $data['customer_id'],
            'billingType'  => $data['billing_type'] ?? 'PIX', // PIX, BOLETO, CREDIT_CARD
            'value'        => $data['amount'],
            'dueDate'      => $data['due_date'],
            'description'  => $data['description'] ?? 'Cobrança de plano',
            'externalReference' => $data['external_reference'] ?? null,
        ]);

        return $response->json();
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
}
