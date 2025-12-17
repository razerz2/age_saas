<?php

namespace App\Services\Finance\Reconciliation;

use App\Models\Tenant\AsaasWebhookEvent;
use App\Models\Tenant\FinancialCharge;
use App\Services\Finance\Reconciliation\ChargeReconciliationService;
use App\Services\Finance\Reconciliation\TransactionReconciliationService;
use App\Services\Finance\Reconciliation\CommissionReconciliationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AsaasWebhookProcessor
{
    protected ChargeReconciliationService $chargeService;
    protected TransactionReconciliationService $transactionService;
    protected CommissionReconciliationService $commissionService;

    public function __construct(
        ChargeReconciliationService $chargeService,
        TransactionReconciliationService $transactionService,
        CommissionReconciliationService $commissionService
    ) {
        $this->chargeService = $chargeService;
        $this->transactionService = $transactionService;
        $this->commissionService = $commissionService;
    }

    /**
     * Processa webhook do Asaas
     */
    public function handle(array $payload): array
    {
        // Verificação obrigatória
        if (tenant_setting('finance.enabled') !== 'true') {
            Log::channel('finance')->info('Webhook ignorado: módulo financeiro desabilitado', [
                'tenant' => tenant()->subdomain ?? 'unknown',
            ]);
            return ['status' => 'skipped', 'message' => 'Módulo financeiro desabilitado'];
        }

        // Verificar feature flag
        if (tenant_setting('finance.webhook_enabled') === 'false') {
            Log::channel('finance')->info('Webhook ignorado: feature flag desabilitada', [
                'tenant' => tenant()->subdomain ?? 'unknown',
            ]);
            return ['status' => 'skipped', 'message' => 'Webhook desabilitado por feature flag'];
        }

        $eventId = $payload['event'] ?? $payload['id'] ?? $payload['payment']['id'] ?? null;
        $eventType = $this->normalizeEventType($payload);

        if (!$eventId) {
            Log::warning('Webhook sem event ID', ['payload' => $payload]);
            return ['status' => 'error', 'message' => 'Event ID não encontrado'];
        }

        // Verificar idempotência
        $webhookEvent = $this->getOrCreateEvent($eventId, $eventType, $payload);

        if ($webhookEvent->isProcessed()) {
            Log::info('Webhook já processado', ['event_id' => $eventId]);
            return ['status' => 'skipped', 'message' => 'Evento já processado'];
        }

        try {
            DB::beginTransaction();

            // Buscar charge
            $paymentId = $payload['payment']['id'] ?? $payload['id'] ?? null;
            if (!$paymentId) {
                throw new \Exception('Payment ID não encontrado no payload');
            }

            $charge = FinancialCharge::where('asaas_charge_id', $paymentId)->first();

            if (!$charge) {
                Log::warning('Charge não encontrada para webhook', [
                    'payment_id' => $paymentId,
                    'event_id' => $eventId,
                ]);
                $webhookEvent->markAsSkipped('Charge não encontrada');
                DB::commit();
                return ['status' => 'skipped', 'message' => 'Charge não encontrada'];
            }

            // Processar evento
            $result = $this->processEvent($eventType, $charge, $payload);

            // Marcar como processado
            $webhookEvent->markAsProcessed();

            DB::commit();

            Log::channel('finance')->info('Webhook processado com sucesso', [
                'tenant' => tenant()->subdomain ?? 'unknown',
                'event_id' => $eventId,
                'event_type' => $eventType,
                'charge_id' => $charge->id,
                'payment_id' => $paymentId,
                'appointment_id' => $charge->appointment_id,
            ]);

            return ['status' => 'success', 'message' => 'Evento processado', 'result' => $result];
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erro ao processar webhook', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $webhookEvent->markAsError($e->getMessage());

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Normaliza o tipo de evento do Asaas
     */
    protected function normalizeEventType(array $payload): string
    {
        $event = $payload['event'] ?? null;
        $status = $payload['payment']['status'] ?? $payload['status'] ?? null;

        if ($event) {
            return strtoupper($event);
        }

        // Mapear status para evento
        return match(strtoupper($status ?? '')) {
            'RECEIVED', 'CONFIRMED' => 'PAYMENT_CONFIRMED',
            'OVERDUE' => 'PAYMENT_OVERDUE',
            'CANCELLED' => 'PAYMENT_CANCELED',
            'REFUNDED' => 'PAYMENT_REFUNDED',
            default => 'UNKNOWN',
        };
    }

    /**
     * Processa evento específico
     */
    protected function processEvent(string $eventType, FinancialCharge $charge, array $payload): array
    {
        return match($eventType) {
            'PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED' => $this->onPaid($charge, $payload),
            'PAYMENT_OVERDUE' => $this->onOverdue($charge),
            'PAYMENT_CANCELED' => $this->onCanceled($charge),
            'PAYMENT_REFUNDED' => $this->onRefunded($charge, $payload),
            default => ['status' => 'skipped', 'message' => 'Evento não tratado'],
        };
    }

    /**
     * Evento: Pagamento recebido/confirmado
     */
    protected function onPaid(FinancialCharge $charge, array $payload): array
    {
        // Atualizar status da cobrança
        $paymentMethod = $payload['payment']['billingType'] ?? $payload['billingType'] ?? null;
        
        // Extrair valores brutos e taxas do payload do Asaas
        $paymentData = $payload['payment'] ?? $payload;
        $grossAmount = isset($paymentData['value']) ? (float) $paymentData['value'] : $charge->amount;
        
        // Taxa do gateway (Asaas pode retornar em 'fee' ou calcular baseado no método)
        $gatewayFee = $this->extractGatewayFee($paymentData, $paymentMethod);
        
        $charge->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $this->mapPaymentMethod($paymentMethod),
        ]);

        // Disparar evento PaymentConfirmed com valores brutos e taxas
        $eventId = $payload['event'] ?? $payload['id'] ?? $payload['payment']['id'] ?? null;
        \Illuminate\Support\Facades\Event::dispatch(
            new \App\Events\Finance\PaymentConfirmed(
                $charge, 
                $paymentMethod ?? 'unknown', 
                $eventId,
                $grossAmount,
                $gatewayFee
            )
        );

        return [
            'charge' => ['status' => 'updated'],
            'event' => 'PaymentConfirmed dispatched',
            'gross_amount' => $grossAmount,
            'gateway_fee' => $gatewayFee,
        ];
    }
    
    /**
     * Extrai taxa do gateway do payload do Asaas
     * 
     * @param array $paymentData
     * @param string|null $paymentMethod
     * @return float
     */
    protected function extractGatewayFee(array $paymentData, ?string $paymentMethod): float
    {
        // Se o Asaas retornar a taxa diretamente
        if (isset($paymentData['fee'])) {
            return (float) $paymentData['fee'];
        }
        
        // Taxas aproximadas do Asaas (podem variar)
        // PIX: geralmente sem taxa ou taxa mínima
        // Boleto: ~R$ 2,00
        // Cartão de crédito: ~3,99% + R$ 0,40
        // Cartão de débito: ~2,99% + R$ 0,40
        
        if (!$paymentMethod) {
            return 0;
        }
        
        $value = isset($paymentData['value']) ? (float) $paymentData['value'] : 0;
        
        return match(strtoupper($paymentMethod)) {
            'PIX' => 0, // PIX geralmente sem taxa
            'BOLETO' => 2.00, // Taxa fixa aproximada
            'CREDIT_CARD' => ($value * 0.0399) + 0.40, // 3,99% + R$ 0,40
            'DEBIT_CARD' => ($value * 0.0299) + 0.40, // 2,99% + R$ 0,40
            default => 0,
        };
    }

    /**
     * Mapeia método de pagamento do Asaas
     */
    protected function mapPaymentMethod(?string $billingType): ?string
    {
        if (!$billingType) {
            return null;
        }

        return match(strtoupper($billingType)) {
            'PIX' => 'pix',
            'CREDIT_CARD' => 'credit_card',
            'BOLETO' => 'boleto',
            'DEBIT_CARD' => 'debit_card',
            default => null,
        };
    }

    /**
     * Evento: Pagamento vencido
     */
    protected function onOverdue(FinancialCharge $charge): array
    {
        return $this->chargeService->reconcileOverdue($charge);
    }

    /**
     * Evento: Pagamento cancelado
     */
    protected function onCanceled(FinancialCharge $charge): array
    {
        return $this->chargeService->reconcileCanceled($charge);
    }

    /**
     * Evento: Pagamento estornado
     */
    protected function onRefunded(FinancialCharge $charge, array $payload): array
    {
        // Conciliação da cobrança
        $chargeResult = $this->chargeService->reconcileRefunded($charge);

        // Reversão da transação
        $transactionResult = $this->transactionService->reconcileRefund($charge);

        // Reversão da comissão
        $commissionResult = $this->commissionService->reconcileRefund($charge);

        return [
            'charge' => $chargeResult,
            'transaction' => $transactionResult,
            'commission' => $commissionResult,
        ];
    }

    /**
     * Obtém ou cria registro de evento
     */
    protected function getOrCreateEvent(string $eventId, string $eventType, array $payload): AsaasWebhookEvent
    {
        $event = AsaasWebhookEvent::where('asaas_event_id', $eventId)->first();

        if (!$event) {
            $event = AsaasWebhookEvent::create([
                'asaas_event_id' => $eventId,
                'type' => $eventType,
                'payload' => $payload,
                'status' => 'pending',
            ]);
        }

        return $event;
    }
}

