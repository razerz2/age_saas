<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Platform\PreTenant;
use App\Models\Platform\PreTenantLog;
use App\Models\Platform\WebhookLog;
use App\Services\Platform\PreTenantProcessorService;

class PreRegistrationWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            $event = $payload['event'] ?? 'UNKNOWN';

            $paymentId = $payload['payment']['id'] ?? null;
            $customerId = $payload['customer']['id'] ?? null;
            $externalReference = $payload['payment']['externalReference'] ?? null;
            $paymentLinkId = $payload['payment']['paymentLink'] ?? null;
            $referenceId = $paymentId ?? $externalReference ?? $paymentLinkId ?? $customerId;

            Log::info("📩 Webhook de pré-cadastro recebido do Asaas: {$event} ({$referenceId})", [
                'payment_id' => $paymentId,
                'customer_id' => $customerId,
                'external_reference' => $externalReference,
                'payment_link_id' => $paymentLinkId,
                'payload' => $payload,
            ]);

            // 🔹 1. Registrar log de auditoria
            WebhookLog::create([
                'event' => $event,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

            if (!$paymentId && !$externalReference && !$paymentLinkId && !$customerId) {
                Log::warning("⚠️ Webhook de pré-cadastro sem ID relevante", ['payload' => $payload]);
                return response()->json(['message' => 'Missing resource ID'], 400);
            }

            // 🔹 2. Buscar pré-tenant (prioridade: externalReference > paymentLink > asaas_customer_id)
            $preTenant = null;
            
            // Primeiro tenta pelo externalReference (ID do pré-tenant) - mais confiável
            if ($externalReference) {
                $preTenant = PreTenant::find($externalReference);
                if ($preTenant) {
                    Log::info("✅ Pré-tenant encontrado pelo externalReference: {$externalReference}", [
                        'pre_tenant_id' => $preTenant->id,
                        'status' => $preTenant->status,
                    ]);
                } else {
                    Log::debug("🔍 Pré-tenant não encontrado pelo externalReference", [
                        'external_reference' => $externalReference,
                        'tentando_buscar' => 'PreTenant::find()',
                    ]);
                }
            }
            
            // Se não encontrou, tenta pelo paymentLink (ID do Payment Link salvo em asaas_payment_id)
            if (!$preTenant && $paymentLinkId) {
                $preTenant = PreTenant::where('asaas_payment_id', $paymentLinkId)->first();
                if ($preTenant) {
                    Log::info("✅ Pré-tenant encontrado pelo paymentLink: {$paymentLinkId}", [
                        'pre_tenant_id' => $preTenant->id,
                        'status' => $preTenant->status,
                    ]);
                } else {
                    Log::debug("🔍 Pré-tenant não encontrado pelo paymentLink", [
                        'payment_link_id' => $paymentLinkId,
                        'tentando_buscar' => "PreTenant::where('asaas_payment_id', '{$paymentLinkId}')",
                    ]);
                }
            }
            
            // Se ainda não encontrou, tenta pelo asaas_customer_id (do payload payment.customer)
            if (!$preTenant && $customerId) {
                $preTenant = PreTenant::where('asaas_customer_id', $customerId)
                    ->where('status', '!=', 'canceled')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($preTenant) {
                    Log::info("✅ Pré-tenant encontrado pelo asaas_customer_id: {$customerId}", [
                        'pre_tenant_id' => $preTenant->id,
                        'status' => $preTenant->status,
                    ]);
                } else {
                    Log::debug("🔍 Pré-tenant não encontrado pelo asaas_customer_id", [
                        'customer_id' => $customerId,
                        'tentando_buscar' => "PreTenant::where('asaas_customer_id', '{$customerId}')",
                    ]);
                }
            }
            
            // Se ainda não encontrou e tem customer no payload, tentar buscar pelo customer do payment
            if (!$preTenant && isset($payload['payment']['customer'])) {
                $paymentCustomerId = $payload['payment']['customer'];
                if ($paymentCustomerId && $paymentCustomerId !== $customerId) {
                    $preTenant = PreTenant::where('asaas_customer_id', $paymentCustomerId)
                        ->where('status', '!=', 'canceled')
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($preTenant) {
                        Log::info("✅ Pré-tenant encontrado pelo customer do payment: {$paymentCustomerId}", [
                            'pre_tenant_id' => $preTenant->id,
                            'status' => $preTenant->status,
                        ]);
                    }
                }
            }

            if (!$preTenant) {
                Log::warning("⚠️ Pré-tenant não encontrado", [
                    'payment_id' => $paymentId,
                    'external_reference' => $externalReference,
                    'payment_link_id' => $paymentLinkId,
                    'customer_id' => $customerId,
                ]);
                return response()->json(['message' => 'Pre-tenant not found'], 404);
            }
            
            // Atualizar payment_id se necessário (para rastreamento)
            // Nota: o asaas_payment_id armazena o paymentLink, mas podemos querer rastrear o payment também
            if ($paymentId && $preTenant->asaas_payment_id !== $paymentLinkId) {
                // Se o paymentLink mudou ou não estava salvo, atualiza
                if (!$preTenant->asaas_payment_id && $paymentLinkId) {
                    $preTenant->update(['asaas_payment_id' => $paymentLinkId]);
                    Log::info("📝 Atualizado asaas_payment_id do pré-tenant {$preTenant->id} com paymentLink: {$paymentLinkId}");
                }
            }

            // 🔹 Marca pré-tenant como "em sincronização" (similar ao webhook principal)
            // Nota: PreTenant não tem campos de sincronização, mas podemos atualizar o status se necessário

            // Processar eventos
            switch ($event) {
                case 'PAYMENT_CONFIRMED':
                case 'PAYMENT_RECEIVED':
                    $this->handlePaymentConfirmed($preTenant, $payload);
                    break;

                case 'PAYMENT_REFUNDED':
                case 'PAYMENT_CANCELED':
                    $this->handlePaymentCanceled($preTenant, $payload);
                    break;

                default:
                    Log::info("Evento não processado: {$event}", [
                        'pre_tenant_id' => $preTenant->id,
                    ]);
            }

            return response()->json(['message' => 'Webhook processed'], 200);

        } catch (\Throwable $e) {
            Log::error('Erro ao processar webhook de pré-cadastro', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Processa pagamento confirmado
     * 🔒 Com verificação de idempotência para evitar processamento duplicado
     */
    private function handlePaymentConfirmed(PreTenant $preTenant, array $payload): void
    {
        // 🔒 Verificação de idempotência: verifica se já foi processado completamente
        $tenantCreatedLog = $preTenant->logs()->where('event', 'tenant_created')->first();
        if ($tenantCreatedLog) {
            $payloadData = is_string($tenantCreatedLog->payload) 
                ? json_decode($tenantCreatedLog->payload, true) 
                : $tenantCreatedLog->payload;
            $tenantId = $payloadData['tenant_id'] ?? null;
            
            if ($tenantId) {
                $tenant = \App\Models\Platform\Tenant::find($tenantId);
                if ($tenant) {
                    Log::info("✅ Pré-tenant {$preTenant->id} já processado - tenant {$tenantId} existe. Verificando assinatura...", [
                        'pre_tenant_id' => $preTenant->id,
                        'tenant_id' => $tenantId,
                    ]);
                    
                    $subscription = $tenant->subscriptions()->latest()->first();
                    
                    if (!$subscription) {
                        // Tenant existe mas não tem assinatura - precisa criar
                        Log::warning("⚠️ Tenant {$tenant->id} existe mas não tem assinatura. Criando assinatura...", [
                            'pre_tenant_id' => $preTenant->id,
                            'tenant_id' => $tenantId,
                        ]);
                        $processor = new PreTenantProcessorService();
                        $processor->createSubscription($preTenant, $tenant, $payload);
                    } else {
                        // Assinatura existe, verifica se precisa sincronizar com Asaas
                        $paymentData = $payload['payment'] ?? [];
                        $paymentDate = null;
                        if (!empty($paymentData['confirmedDate'])) {
                            $paymentDate = \Carbon\Carbon::parse($paymentData['confirmedDate']);
                        } elseif (!empty($paymentData['paymentDate'])) {
                            $paymentDate = \Carbon\Carbon::parse($paymentData['paymentDate']);
                        } else {
                            $paymentDate = now();
                        }
                        
                        if (!$subscription->asaas_synced || $subscription->asaas_sync_status !== 'success') {
                            Log::info("🔄 Assinatura {$subscription->id} existe mas não está sincronizada com Asaas. Sincronizando...", [
                                'pre_tenant_id' => $preTenant->id,
                                'subscription_id' => $subscription->id,
                            ]);
                            $processor = new PreTenantProcessorService();
                            $processor->syncSubscriptionWithAsaas($subscription, $paymentDate);
                        } else {
                            Log::info("✅ Assinatura {$subscription->id} já está completa com sincronização no Asaas. Webhook ignorado (idempotência).", [
                                'pre_tenant_id' => $preTenant->id,
                                'subscription_id' => $subscription->id,
                            ]);
                        }
                    }
                    
                    return; // Já foi processado, não precisa continuar
                }
            }
        }
        
        // Se chegou aqui, ainda não foi processado ou o tenant não foi encontrado
        if ($preTenant->isPaid()) {
            Log::info("ℹ️ Pré-tenant {$preTenant->id} já está marcado como pago, mas tenant não foi encontrado. Tentando criar...", [
                'pre_tenant_id' => $preTenant->id,
            ]);
        }

        try {
            $processor = new PreTenantProcessorService();
            $processor->processPaid($preTenant, $payload);

            Log::info("✅ Pré-tenant {$preTenant->id} processado com sucesso após pagamento confirmado.");

        } catch (\Throwable $e) {
            Log::error("Erro ao processar pré-tenant pago", [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            try {
            // Garantir que usa conexão da plataforma (não do tenant)
            try {
                \Illuminate\Support\Facades\DB::connection()->table('pre_tenant_logs')->insert([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'processing_error',
                    'payload' => json_encode(['error' => $e->getMessage()]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $logError) {
                Log::warning('Erro ao criar log de erro do processamento no webhook', [
                    'error' => $logError->getMessage(),
                ]);
            }
            } catch (\Throwable $logError) {
                Log::warning('Erro ao criar log de erro do processamento', [
                    'error' => $logError->getMessage(),
            ]);
            }
        }
    }

    /**
     * Processa pagamento cancelado/estornado
     */
    private function handlePaymentCanceled(PreTenant $preTenant, array $payload): void
    {
        if ($preTenant->status === 'canceled') {
            Log::info("Pré-tenant {$preTenant->id} já está cancelado.");
            return;
        }

        $preTenant->markAsCanceled();

        PreTenantLog::create([
            'pre_tenant_id' => $preTenant->id,
            'event' => 'payment_canceled',
            'payload' => [
                'reason' => $payload['payment']['status'] ?? 'canceled',
            ],
        ]);

        Log::info("❌ Pré-tenant {$preTenant->id} cancelado após estorno/cancelamento de pagamento.");
    }
}
