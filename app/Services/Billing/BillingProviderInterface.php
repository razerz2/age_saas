<?php

namespace App\Services\Billing;

use App\Models\Tenant\Patient;
use App\Models\Tenant\FinancialCharge;

/**
 * Interface para providers de billing (gateways de pagamento)
 * 
 * Permite que o sistema suporte múltiplos gateways de forma plugável
 */
interface BillingProviderInterface
{
    /**
     * Cria ou busca um cliente no provider
     * 
     * @param Patient $patient
     * @return string|null ID do cliente no provider
     */
    public function createCustomer(Patient $patient): ?string;

    /**
     * Cria uma cobrança no provider
     * 
     * @param FinancialCharge $charge
     * @return array ['error' => bool, 'data' => array|null, 'message' => string|null]
     */
    public function createCharge(FinancialCharge $charge): array;

    /**
     * Cancela uma cobrança no provider
     * 
     * @param FinancialCharge $charge
     * @return bool Sucesso da operação
     */
    public function cancelCharge(FinancialCharge $charge): bool;

    /**
     * Consulta o status atual de uma cobrança no provider
     * 
     * @param FinancialCharge $charge
     * @return array Dados da cobrança no provider
     */
    public function getChargeStatus(FinancialCharge $charge): array;

    /**
     * Gera ou recupera o link de pagamento da cobrança
     * 
     * @param FinancialCharge $charge
     * @return string|null URL do link de pagamento
     */
    public function generatePaymentLink(FinancialCharge $charge): ?string;
}

