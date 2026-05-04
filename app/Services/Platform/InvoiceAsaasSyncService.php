<?php

namespace App\Services\Platform;

use App\Models\Platform\Invoices;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class InvoiceAsaasSyncService
{
    private const FINAL_INVOICE_STATUSES = ['paid', 'received', 'confirmed', 'canceled', 'refunded'];
    private const RECREATE_BLOCKED_STATUSES = ['paid', 'received', 'confirmed'];

    public function __construct(
        private readonly AsaasService $asaas
    ) {
    }

    public function syncInvoice(Invoices $invoice): Invoices
    {
        $invoice->loadMissing(['tenant', 'subscription.plan']);
        $subscription = $invoice->subscription;
        $tenant = $invoice->tenant;

        if (! $tenant || ! $subscription || ! $subscription->plan) {
            $this->markSyncFailed($invoice, 'Invoice sem tenant/assinatura/plano para sincronizar.');
            throw new RuntimeException('Invoice sem tenant/assinatura/plano para sincronizar.');
        }

        if ($subscription->plan->isTest() || (bool) $subscription->is_trial) {
            $invoice->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'skipped',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => null,
            ]);

            return $invoice->fresh();
        }

        if (in_array((string) $invoice->status, self::FINAL_INVOICE_STATUSES, true)) {
            $invoice->update([
                'asaas_synced' => true,
                'asaas_sync_status' => 'skipped',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => null,
            ]);

            return $invoice->fresh();
        }

        try {
            $customerId = $this->ensureCustomer($tenant);
            if (! $customerId) {
                throw new RuntimeException('Nao foi possivel obter/criar cliente no Asaas.');
            }

            $result = $this->createOrUpdatePayment($invoice->fresh(['tenant', 'subscription.plan']));
            if (! ($result['success'] ?? false)) {
                throw new RuntimeException((string) ($result['error'] ?? 'Falha ao sincronizar cobranca no Asaas.'));
            }

            $payment = (array) ($result['payment'] ?? []);
            $providerId = $payment['id'] ?? $invoice->provider_id;
            $paymentLink = $payment['invoiceUrl'] ?? $payment['bankSlipUrl'] ?? $invoice->payment_link;

            $invoice->update([
                'provider' => 'asaas',
                'provider_id' => $providerId,
                'asaas_payment_id' => $payment['id'] ?? $invoice->asaas_payment_id ?? $providerId,
                'payment_link' => $paymentLink,
                'asaas_synced' => true,
                'asaas_sync_status' => 'success',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => null,
            ]);

            return $invoice->fresh();
        } catch (Throwable $e) {
            $this->markSyncFailed($invoice, $e->getMessage());
            throw $e;
        }
    }

    public function ensureCustomer(Tenant $tenant): ?string
    {
        if (! empty($tenant->asaas_customer_id)) {
            return (string) $tenant->asaas_customer_id;
        }

        $search = $this->asaas->searchCustomerByEmailOrDocument($tenant->email, $tenant->document);
        $customerId = $search['data'][0]['id'] ?? null;

        if (! $customerId && ! empty($tenant->document)) {
            $documentSearch = $this->asaas->searchCustomerByDocument((string) $tenant->document);
            $customerId = $documentSearch['data'][0]['id'] ?? null;
        }

        if (! $customerId && ! empty($tenant->email)) {
            $emailSearch = $this->asaas->searchCustomer((string) $tenant->email);
            $customerId = $emailSearch['data'][0]['id'] ?? null;
        }

        if (! $customerId) {
            $create = $this->asaas->createCustomer([
                'id' => $tenant->id,
                'trade_name' => $tenant->trade_name,
                'legal_name' => $tenant->legal_name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'document' => $tenant->document,
            ]);

            $customerId = $create['id'] ?? null;
            if (! $customerId) {
                return null;
            }
        }

        $tenant->update([
            'asaas_customer_id' => $customerId,
            'asaas_synced' => true,
            'asaas_sync_status' => 'success',
            'asaas_last_sync_at' => now(),
            'asaas_last_error' => null,
        ]);

        return (string) $customerId;
    }

    public function createOrUpdatePayment(Invoices $invoice): array
    {
        $invoice->loadMissing(['tenant', 'subscription.plan']);
        $subscription = $invoice->subscription;
        $tenant = $invoice->tenant;

        if (! $tenant || ! $subscription) {
            return [
                'success' => false,
                'error' => 'Invoice sem tenant/assinatura para sincronizar.',
            ];
        }

        if (empty($tenant->asaas_customer_id)) {
            return [
                'success' => false,
                'error' => 'Tenant sem asaas_customer_id.',
            ];
        }

        $billingType = $this->resolveBillingType($invoice, $subscription);
        $payload = [
            'customer' => $tenant->asaas_customer_id,
            'billingType' => $billingType,
            'dueDate' => $invoice->due_date?->format('Y-m-d') ?? now()->addDays(5)->toDateString(),
            'value' => round(((int) $invoice->amount_cents) / 100, 2),
            'description' => "Fatura {$invoice->id}",
            'externalReference' => (string) $invoice->id,
        ];

        if (! empty($invoice->provider_id)) {
            $response = $this->asaas->updatePayment((string) $invoice->provider_id, $payload);
            if (! is_array($response) || isset($response['error'])) {
                return [
                    'success' => false,
                    'error' => $this->extractAsaasError($response),
                    'payment' => $response,
                    'action' => 'update',
                ];
            }

            if (empty($response['id'])) {
                $response['id'] = $invoice->provider_id;
            }

            return [
                'success' => true,
                'payment' => $response,
                'action' => 'update',
            ];
        }

        $response = $this->asaas->createPayment($payload);
        if (! is_array($response) || empty($response['id']) || isset($response['error'])) {
            return [
                'success' => false,
                'error' => $this->extractAsaasError($response),
                'payment' => $response,
                'action' => 'create',
            ];
        }

        return [
            'success' => true,
            'payment' => $response,
            'action' => 'create',
        ];
    }

    public function refreshStatus(Invoices $invoice): Invoices
    {
        $paymentId = $invoice->provider_id ?: $invoice->asaas_payment_id;

        if (empty($paymentId)) {
            throw new RuntimeException('Invoice sem provider_id/asaas_payment_id para consulta de status.');
        }

        $response = $this->asaas->getPaymentStatus((string) $paymentId);
        if (! is_array($response) || isset($response['error']) || empty($response['id'])) {
            $this->markSyncFailed($invoice, $this->extractAsaasError($response));
            throw new RuntimeException($this->extractAsaasError($response));
        }

        $mappedStatus = $this->mapAsaasPaymentStatus((string) ($response['status'] ?? 'PENDING'));
        $paymentLink = $response['invoiceUrl'] ?? $response['bankSlipUrl'] ?? $invoice->payment_link;

        $invoice->update([
            'status' => $mappedStatus,
            'provider' => 'asaas',
            'provider_id' => $response['id'] ?? $invoice->provider_id,
            'asaas_payment_id' => $response['id'] ?? $invoice->asaas_payment_id,
            'payment_link' => $paymentLink,
            'asaas_synced' => true,
            'asaas_sync_status' => 'success',
            'asaas_last_sync_at' => now(),
            'asaas_last_error' => null,
        ]);

        return $invoice->fresh();
    }

    public function recreatePayment(Invoices $invoice): Invoices
    {
        if (in_array((string) $invoice->status, self::RECREATE_BLOCKED_STATUSES, true)) {
            throw new RuntimeException('Nao e permitido recriar cobranca para fatura ja paga/confirmada.');
        }

        $invoice->loadMissing(['tenant', 'subscription.plan']);
        $oldProviderId = $invoice->provider_id ?: $invoice->asaas_payment_id;

        if (! empty($oldProviderId)) {
            try {
                Log::info('Tentando cancelar cobranca antiga no Asaas antes de recriar.', [
                    'invoice_id' => $invoice->id,
                    'old_provider_id' => $oldProviderId,
                ]);

                $cancelResponse = $this->asaas->deletePayment((string) $oldProviderId);

                Log::info('Resposta cancelamento cobranca antiga no Asaas.', [
                    'invoice_id' => $invoice->id,
                    'old_provider_id' => $oldProviderId,
                    'response' => $cancelResponse,
                ]);
            } catch (Throwable $e) {
                Log::warning('Falha ao cancelar cobranca antiga no Asaas. Continuando recriacao.', [
                    'invoice_id' => $invoice->id,
                    'old_provider_id' => $oldProviderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $invoice->update([
            'provider' => 'asaas',
            'provider_id' => null,
            'asaas_payment_id' => null,
            'payment_link' => null,
            'status' => 'pending',
            'asaas_synced' => false,
            'asaas_sync_status' => 'pending',
            'asaas_last_sync_at' => now(),
            'asaas_last_error' => null,
        ]);

        Log::info('Recriando cobranca Asaas da fatura.', [
            'invoice_id' => $invoice->id,
            'external_reference' => (string) $invoice->id,
        ]);

        return $this->syncInvoice($invoice->fresh(['tenant', 'subscription.plan']));
    }

    private function resolveBillingType(Invoices $invoice, Subscription $subscription): string
    {
        $method = (string) ($invoice->payment_method ?: $subscription->payment_method ?: 'PIX');

        return match ($method) {
            'PIX' => 'PIX',
            'PIX_RECURRENT' => 'PIX',
            'PIX_AUTOMATIC' => 'PIX',
            'BOLETO' => 'BOLETO',
            'CREDIT_CARD' => 'CREDIT_CARD',
            default => 'PIX',
        };
    }

    private function extractAsaasError($response): string
    {
        if (! is_array($response)) {
            return 'Resposta invalida do Asaas.';
        }

        return (string) (
            $response['errors'][0]['description']
            ?? $response['message']
            ?? $response['error']
            ?? 'Falha na integracao com Asaas.'
        );
    }

    private function mapAsaasPaymentStatus(string $asaasStatus): string
    {
        return match (strtoupper($asaasStatus)) {
            'PENDING', 'AWAITING_RISK_ANALYSIS' => 'pending',
            'OVERDUE' => 'overdue',
            'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH' => 'paid',
            'REFUNDED', 'REFUND_REQUESTED', 'CHARGEBACK_REQUESTED', 'CHARGEBACK_DISPUTE',
            'AWAITING_CHARGEBACK_REVERSAL', 'DUNNING_REQUESTED', 'DUNNING_RECEIVED', 'DELETED' => 'canceled',
            default => 'pending',
        };
    }

    private function markSyncFailed(Invoices $invoice, string $error): void
    {
        $invoice->update([
            'asaas_synced' => false,
            'asaas_sync_status' => 'failed',
            'asaas_last_sync_at' => now(),
            'asaas_last_error' => $error,
        ]);
    }
}
