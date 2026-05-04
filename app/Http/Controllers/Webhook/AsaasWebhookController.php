<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Platform\Invoices;
use App\Models\Platform\WebhookLog;
use App\Models\Platform\Tenant;
use App\Models\Platform\Subscription;
use App\Models\Platform\PreTenant;
use App\Services\SystemNotificationService;
use App\Services\Platform\PreTenantProcessorService;
use App\Services\AsaasService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AsaasWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $startedAt = microtime(true);
        $invoice = null;
        $event = 'UNKNOWN';
        $paymentId = null;

        try {
            $payload = $request->all();
            $event = $payload['event'] ?? 'UNKNOWN';

            $paymentId      = $payload['payment']['id'] ?? null;
            $customerId     = $payload['customer']['id'] ?? null;
            $subscriptionId = $payload['subscription']['id'] ?? ($payload['payment']['subscription'] ?? null);
            $pixAutomaticAuthorizationId = $this->extractPixAutomaticAuthorizationId($payload);
            $pixAutomaticInstructionId = $this->extractPixAutomaticInstructionId($payload);
            $referenceId    = $paymentId ?? $subscriptionId ?? $customerId;
            $externalReference = $payload['payment']['externalReference'] ?? null;
            if (empty($externalReference)) {
                $externalReference = $this->extractPixAutomaticExternalReference($payload);
            }
            $paymentLinkId = $payload['payment']['paymentLink'] ?? null;

            Log::info("Webhook recebido do Asaas: {$event}", [
                'reference' => $this->maskIdentifier($referenceId),
                'payment_id' => $this->maskIdentifier($paymentId),
                'customer_id' => $this->maskIdentifier($customerId),
                'subscription_id' => $this->maskIdentifier($subscriptionId),
                'external_reference' => $this->maskIdentifier($externalReference),
                'payment_link_id' => $this->maskIdentifier($paymentLinkId),
                'pix_automatic_authorization_id' => $this->maskIdentifier($pixAutomaticAuthorizationId),
                'pix_automatic_instruction_id' => $this->maskIdentifier($pixAutomaticInstructionId),
            ]);

            // 🔹 1. Registrar log de auditoria
            WebhookLog::create([
                'event' => $event,
                'payment_id' => $paymentId,
                'payload' => json_encode([
                    'event' => $event,
                    'payment' => [
                        'id' => $paymentId,
                        'externalReference' => $externalReference,
                        'subscription' => $subscriptionId,
                    ],
                    'customer' => [
                        'id' => $customerId,
                    ],
                ], JSON_UNESCAPED_UNICODE),
            ]);

            if (!$paymentId && !$customerId && !$subscriptionId && !$pixAutomaticAuthorizationId && !$pixAutomaticInstructionId) {
                Log::warning('Webhook sem ID relevante');
                return $this->finalizeWebhookResponse(
                    $event,
                    $paymentId,
                    $invoice?->id,
                    $startedAt,
                    200,
                    ['received' => true],
                    'ignored_missing_resource_id',
                    'warning'
                );
            }

            // 🔹 2. VERIFICAR SE É PRÉ-CADASTRO ANTES DE PROCESSAR COMO FATURA NORMAL
            // O Asaas pode enviar webhooks de pré-cadastro para /webhook/asaas
            if ($externalReference || $paymentLinkId) {
                $preTenant = null;

                if ($externalReference && !Str::isUuid((string) $externalReference)) {
                    Log::debug('ExternalReference nao e UUID de pre-tenant. Seguindo fluxo normal.', [
                        'external_reference' => $this->maskIdentifier($externalReference),
                    ]);
                    $externalReference = null;
                }
                
                // Buscar pré-tenant pelo externalReference (ID do pré-tenant)
                if ($externalReference) {
                    $preTenant = PreTenant::find($externalReference);
                    if ($preTenant) {
                        Log::info("🔍 Pré-tenant encontrado pelo externalReference no webhook principal", [
                            'pre_tenant_id' => $preTenant->id,
                            'external_reference' => $this->maskIdentifier($externalReference),
                        ]);
                    } else {
                        Log::debug("🔍 Pré-tenant não encontrado pelo externalReference", [
                            'external_reference' => $this->maskIdentifier($externalReference),
                        ]);
                    }
                }
                
                // Se não encontrou, tentar pelo paymentLink
                if (!$preTenant && $paymentLinkId) {
                    $preTenant = PreTenant::where('asaas_payment_id', $paymentLinkId)->first();
                    if ($preTenant) {
                        Log::info("🔍 Pré-tenant encontrado pelo paymentLink no webhook principal", [
                            'pre_tenant_id' => $preTenant->id,
                            'payment_link_id' => $paymentLinkId,
                        ]);
                    } else {
                        Log::debug("🔍 Pré-tenant não encontrado pelo paymentLink", [
                            'payment_link_id' => $paymentLinkId,
                        ]);
                    }
                }
                
                // Se encontrou pré-tenant, processar como pré-cadastro
                if ($preTenant) {
                    Log::info("🔄 Processando webhook como pré-cadastro no webhook principal", [
                        'pre_tenant_id' => $preTenant->id,
                        'event' => $event,
                        'payment_id' => $paymentId,
                    ]);
                    
                    try {
                        $processor = app(PreTenantProcessorService::class);
                        
                        // Verificar se já foi processado
                        $tenantCreatedLog = $preTenant->logs()->where('event', 'tenant_created')->first();
                        if ($tenantCreatedLog) {
                            $payloadData = is_string($tenantCreatedLog->payload) 
                                ? json_decode($tenantCreatedLog->payload, true) 
                                : $tenantCreatedLog->payload;
                            $tenantId = $payloadData['tenant_id'] ?? null;
                            
                            if ($tenantId) {
                                $existingTenant = Tenant::find($tenantId);
                                if ($existingTenant) {
                                    Log::info("✅ Pré-tenant já processado. Verificando assinatura...", [
                                        'pre_tenant_id' => $preTenant->id,
                                        'tenant_id' => $tenantId,
                                    ]);
                                    
                                    $subscription = $existingTenant->subscriptions()->latest()->first();
                                    if (!$subscription) {
                                        Log::warning("⚠️ Tenant existe mas não tem assinatura. Criando...", [
                                            'pre_tenant_id' => $preTenant->id,
                                            'tenant_id' => $tenantId,
                                        ]);
                                        $processor->createSubscription($preTenant, $existingTenant, $payload);
                                    } else {
                                        Log::info("✅ Tenant e assinatura já existem. Webhook ignorado (idempotência).", [
                                            'pre_tenant_id' => $preTenant->id,
                                            'tenant_id' => $tenantId,
                                            'subscription_id' => $subscription->id,
                                        ]);
                                    }
                                    return $this->finalizeWebhookResponse(
                                        $event,
                                        $paymentId,
                                        $invoice?->id,
                                        $startedAt,
                                        200,
                                        ['received' => true],
                                        'pre_tenant_idempotent'
                                    );
                                }
                            }
                        }
                        
                        // Processar pagamento confirmado
                        if (in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'])) {
                            $processor->processPaid($preTenant, $payload);
                            Log::info("✅ Pré-tenant processado com sucesso via webhook principal", [
                                'pre_tenant_id' => $preTenant->id,
                            ]);
                        }
                        
                        return $this->finalizeWebhookResponse(
                            $event,
                            $paymentId,
                            $invoice?->id,
                            $startedAt,
                            200,
                            ['received' => true],
                            'pre_tenant_processed'
                        );
                    } catch (\Throwable $e) {
                        Log::error("❌ Erro ao processar pré-cadastro via webhook principal", [
                            'pre_tenant_id' => $preTenant->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        return $this->finalizeWebhookResponse(
                            $event,
                            $paymentId,
                            $invoice?->id,
                            $startedAt,
                            500,
                            ['error' => 'Internal Server Error'],
                            'pre_tenant_failed',
                            'error'
                        );
                    }
                } else {
                    // Não é pré-cadastro ou pré-tenant não encontrado - continua fluxo normal
                    Log::debug("ℹ️ Não é pré-cadastro ou pré-tenant não encontrado. Continuando fluxo normal...", [
                        'external_reference' => $this->maskIdentifier($externalReference),
                        'payment_link_id' => $paymentLinkId,
                    ]);
                }
            }

            // 🔹 3. Buscar entidades locais (para tenants já existentes - fluxo normal)
            $invoice = null;
            if ($paymentId) {
                $invoice = Invoices::where('asaas_payment_id', $paymentId)
                    ->orWhere('provider_id', $paymentId)
                    ->first();
            }

            if (!$invoice && $externalReference && Str::isUuid((string) $externalReference)) {
                $invoice = Invoices::find($externalReference);

                if ($invoice) {
                    Log::info("🔍 Invoice encontrada por externalReference (invoice UUID)", [
                        'external_reference' => $this->maskIdentifier($externalReference),
                        'invoice_id' => $invoice->id,
                    ]);
                }
            }

            // 🔹 Busca invoice por externalReference (para recovery)
            // externalReference deve ser o ID da subscription recovery_pending
            if (!$invoice && $externalReference && Str::isUuid((string) $externalReference)) {
                // Busca invoice recovery vinculada à subscription pelo externalReference
                $invoice = Invoices::where('recovery_target_subscription_id', $externalReference)
                    ->where(function ($query) use ($paymentId) {
                        $query->where('provider_id', $paymentId)
                              ->orWhere('asaas_payment_id', $paymentId)
                              ->orWhere('asaas_payment_link_id', $paymentId);
                    })
                    ->where('is_recovery', true)
                    ->first();
                
                if ($invoice) {
                    Log::info("🔍 Invoice de recovery encontrada por externalReference", [
                        'external_reference' => $this->maskIdentifier($externalReference),
                        'invoice_id' => $invoice->id,
                        'payment_id' => $paymentId,
                    ]);
                }
            }

            $tenant       = $invoice?->tenant ?? Tenant::where('asaas_customer_id', $customerId)->first();
            
            // 🔹 Busca subscription: primeiro por asaas_subscription_id, depois por invoice recovery
            $subscription = null;
            if ($subscriptionId) {
                $subscription = Subscription::where('asaas_subscription_id', $subscriptionId)->first();
            }
            // Se não encontrou e é invoice recovery, busca pela subscription recovery_target
            if (!$subscription && $invoice && $invoice->is_recovery && $invoice->recovery_target_subscription_id) {
                $subscription = Subscription::find($invoice->recovery_target_subscription_id);
            }
            // Fallback: subscription da invoice
            if (!$subscription && $invoice) {
                $subscription = $invoice->subscription;
            }
            if (!$subscription && $externalReference && Str::isUuid((string) $externalReference)) {
                $subscription = Subscription::find($externalReference);
            }
            if (!$subscription && $pixAutomaticAuthorizationId) {
                $subscription = Subscription::query()
                    ->where('asaas_pix_automatic_authorization_id', $pixAutomaticAuthorizationId)
                    ->first();
            }
            if (!$subscription && $pixAutomaticInstructionId) {
                $subscription = Subscription::query()
                    ->where('asaas_pix_automatic_last_instruction_id', $pixAutomaticInstructionId)
                    ->first();
            }
            if (!$subscription && $customerId) {
                $subscription = Subscription::query()
                    ->whereHas('tenant', function ($query) use ($customerId) {
                        $query->where('asaas_customer_id', $customerId);
                    })
                    ->latest('created_at')
                    ->first();
            }

            if (in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED', 'PAYMENT_OVERDUE'], true) && !$invoice) {
                if ($subscription && $paymentId) {
                    $invoice = Invoices::query()
                        ->where('subscription_id', $subscription->id)
                        ->where(function ($query) use ($paymentId) {
                            $query->where('asaas_payment_id', $paymentId)
                                ->orWhere('provider_id', $paymentId);
                        })
                        ->latest('due_date')
                        ->first();
                }

                if (!$invoice) {
                    if ($subscription) {
                        Log::warning('Webhook de pagamento sem invoice local, mas com subscription vinculada. Seguindo para fallback no switch.', [
                            'event' => $event,
                            'payment_id' => $this->maskIdentifier($paymentId),
                            'subscription_id' => $this->maskIdentifier($subscription->asaas_subscription_id ?? $subscription->id),
                        ]);
                        // Continua para permitir criacao/vinculo tardio no switch.
                    } else {
                        Log::warning('Webhook de pagamento sem invoice local', [
                            'event' => $event,
                            'payment_id' => $this->maskIdentifier($paymentId),
                            'external_reference' => $this->maskIdentifier($externalReference),
                        ]);

                        return $this->finalizeWebhookResponse(
                            $event,
                            $paymentId,
                            null,
                            $startedAt,
                            200,
                            ['received' => true],
                            'ignored_invoice_not_found',
                            'warning'
                        );
                    }
                }
            }

            if ($invoice && !$subscription) {
                $subscription = $invoice->subscription;
            }

            if ($invoice && !$tenant) {
                $tenant = $invoice->tenant;
            }

            if (in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'], true) && $invoice && $invoice->status === 'paid' && $invoice->paid_at) {
                Log::info("Evento {$event} ignorado: fatura {$invoice->id} ja estava paga (idempotencia).");

                return $this->finalizeWebhookResponse(
                    $event,
                    $paymentId,
                    $invoice->id,
                    $startedAt,
                    200,
                    ['received' => true],
                    'idempotent_already_paid'
                );
            }

            // 🔹 Marca entidades como "em sincronização"
            foreach ([$tenant, $subscription, $invoice] as $entity) {
                if ($entity) {
                    $entity->update([
                        'asaas_sync_status' => 'pending',
                        'asaas_last_sync_at' => now(),
                    ]);
                }
            }

            // 🔹 3. Processar eventos
            // Para PIX_AUTOMATIC, habilitar no painel do Asaas todos os eventos:
            // PIX_AUTOMATIC_RECURRING_AUTHORIZATION_CREATED
            // PIX_AUTOMATIC_RECURRING_AUTHORIZATION_ACTIVATED
            // PIX_AUTOMATIC_RECURRING_AUTHORIZATION_REFUSED
            // PIX_AUTOMATIC_RECURRING_AUTHORIZATION_CANCELLED
            // PIX_AUTOMATIC_RECURRING_AUTHORIZATION_EXPIRED
            // PIX_AUTOMATIC_RECURRING_PAYMENT_INSTRUCTION_CREATED
            // PIX_AUTOMATIC_RECURRING_PAYMENT_INSTRUCTION_SCHEDULED
            // PIX_AUTOMATIC_RECURRING_PAYMENT_INSTRUCTION_REFUSED
            // PIX_AUTOMATIC_RECURRING_PAYMENT_INSTRUCTION_CANCELLED
            // PIX_AUTOMATIC_RECURRING_ELIGIBILITY_UPDATED
            switch ($event) {

                /**
                 * 🔄 ASSINATURAS
                 */
                case 'SUBSCRIPTION_CREATED':
                    Log::info("🧾 Assinatura criada no Asaas: {$subscriptionId}");

                    if ($tenant && !$subscription) {
                        $subscription = $tenant->subscriptions()->latest()->first();
                        if ($subscription && empty($subscription->asaas_subscription_id)) {
                            $subscription->update([
                                'asaas_subscription_id' => $subscriptionId,
                                'status' => 'pending',
                                'asaas_synced' => true,
                                'asaas_sync_status' => 'success',
                                'asaas_last_sync_at' => now(),
                                'asaas_last_error' => null,
                            ]);

                            SystemNotificationService::notify(
                                'Nova assinatura automática criada',
                                "Assinatura #{$subscription->id} vinculada ao Asaas ({$subscriptionId}) para o tenant {$tenant->trade_name}.",
                                'subscription',
                                'info'
                            );
                        }
                    }
                    break;

                case 'SUBSCRIPTION_UPDATED':
                    if ($subscription) {
                        $subscription->update([
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);

                        Log::info("🔄 Assinatura {$subscriptionId} atualizada no Asaas.");
                        SystemNotificationService::notify(
                            'Assinatura atualizada',
                            "A assinatura #{$subscription->id} vinculada ao tenant {$tenant?->trade_name} foi atualizada no Asaas.",
                            'subscription',
                            'info'
                        );
                    }
                    break;

                case 'SUBSCRIPTION_INACTIVATED':
                    if ($subscription) {
                        $subscription->update([
                            'status' => 'pending',
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);
                        Log::warning("⏸️ Assinatura {$subscription->id} inativada no Asaas.");
                        SystemNotificationService::notify(
                            'Assinatura inativada',
                            "A assinatura #{$subscription->id} do tenant {$tenant?->trade_name} foi marcada como pendente no Asaas.",
                            'subscription',
                            'warning'
                        );
                    }
                    break;

                case 'SUBSCRIPTION_DELETED':
                    $subscription = Subscription::where('asaas_subscription_id', $subscriptionId)->first();

                    if ($subscription) {
                        $subscription->update([
                            'asaas_sync_status' => 'deleted',
                            'asaas_last_sync_at' => now(),
                        ]);

                        $invoicesDeleted = 0;
                        if ($subscription->invoices()->exists()) {
                            $invoicesDeleted = $subscription->invoices()->count();
                            $subscription->invoices()->delete();
                        }

                        $subId = $subscription->id;
                        $tenantName = $subscription->tenant?->trade_name ?? 'Desconhecido';
                        $subscription->delete();

                        Log::warning("🚫 Assinatura {$subId} (Asaas ID {$subscriptionId}) e {$invoicesDeleted} faturas vinculadas removidas após exclusão no Asaas.", [
                            'asaas_subscription_id' => $subscriptionId,
                            'invoices_deleted' => $invoicesDeleted,
                            'tenant' => $tenantName,
                        ]);

                        SystemNotificationService::notify(
                            'Assinatura excluída',
                            "A assinatura #{$subId} ({$subscriptionId}) do tenant {$tenantName} foi removida automaticamente do sistema após exclusão no Asaas (junto com {$invoicesDeleted} faturas).",
                            'subscription',
                            'warning'
                        );
                    }
                    break;


                /**
                     * 💳 PAGAMENTOS
                     */
                case 'PIX_AUTOMATIC_RECURRING_AUTHORIZATION_CREATED':
                    if (!$subscription) {
                        Log::warning('PIX_AUTOMATIC_RECURRING_AUTHORIZATION_CREATED sem assinatura vinculada.', [
                            'authorization_id' => $this->maskIdentifier($pixAutomaticAuthorizationId),
                            'external_reference' => $this->maskIdentifier($externalReference),
                        ]);
                        break;
                    }

                    $subscription->update([
                        'asaas_pix_automatic_authorization_id' => $pixAutomaticAuthorizationId ?: $subscription->asaas_pix_automatic_authorization_id,
                        'asaas_pix_automatic_authorization_status' => $this->extractPixAutomaticAuthorizationStatus($payload, 'created'),
                        'asaas_pix_automatic_payload' => $payload,
                        'asaas_pix_automatic_last_event_at' => now(),
                        'status' => 'pending',
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => null,
                    ]);
                    break;

                case 'PIX_AUTOMATIC_RECURRING_AUTHORIZATION_ACTIVATED':
                    if (!$subscription) {
                        Log::warning('PIX_AUTOMATIC_RECURRING_AUTHORIZATION_ACTIVATED sem assinatura vinculada.', [
                            'authorization_id' => $this->maskIdentifier($pixAutomaticAuthorizationId),
                            'external_reference' => $this->maskIdentifier($externalReference),
                        ]);
                        break;
                    }

                    $subscription->update([
                        'asaas_pix_automatic_authorization_id' => $pixAutomaticAuthorizationId ?: $subscription->asaas_pix_automatic_authorization_id,
                        'asaas_pix_automatic_authorization_status' => $this->extractPixAutomaticAuthorizationStatus($payload, 'active'),
                        'asaas_pix_automatic_payload' => $payload,
                        'asaas_pix_automatic_last_event_at' => now(),
                        'status' => $this->isPixAutomaticPaymentConfirmed($payload) ? 'active' : 'pending',
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => null,
                    ]);
                    break;

                case 'PIX_AUTOMATIC_RECURRING_AUTHORIZATION_REFUSED':
                case 'PIX_AUTOMATIC_RECURRING_AUTHORIZATION_CANCELLED':
                case 'PIX_AUTOMATIC_RECURRING_AUTHORIZATION_EXPIRED':
                    if (!$subscription) {
                        Log::warning("{$event} sem assinatura vinculada.", [
                            'authorization_id' => $this->maskIdentifier($pixAutomaticAuthorizationId),
                            'external_reference' => $this->maskIdentifier($externalReference),
                        ]);
                        break;
                    }

                    $authorizationStatus = match ($event) {
                        'PIX_AUTOMATIC_RECURRING_AUTHORIZATION_CANCELLED' => 'cancelled',
                        'PIX_AUTOMATIC_RECURRING_AUTHORIZATION_EXPIRED' => 'expired',
                        default => 'refused',
                    };

                    $subscription->update([
                        'asaas_pix_automatic_authorization_id' => $pixAutomaticAuthorizationId ?: $subscription->asaas_pix_automatic_authorization_id,
                        'asaas_pix_automatic_authorization_status' => $authorizationStatus,
                        'asaas_pix_automatic_payload' => $payload,
                        'asaas_pix_automatic_last_event_at' => now(),
                        'status' => $authorizationStatus === 'cancelled' ? 'canceled' : 'pending',
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => null,
                    ]);

                    SystemNotificationService::notify(
                        'Pix Automatico: autorizacao atualizada',
                        "Assinatura #{$subscription->id} teve autorizacao Pix Automatico marcada como {$authorizationStatus}.",
                        'subscription',
                        'warning'
                    );
                    break;

                case 'PIX_AUTOMATIC_RECURRING_PAYMENT_INSTRUCTION_CREATED':
                case 'PIX_AUTOMATIC_RECURRING_PAYMENT_INSTRUCTION_SCHEDULED':
                    if (!$subscription) {
                        Log::warning("{$event} sem assinatura vinculada.", [
                            'instruction_id' => $this->maskIdentifier($pixAutomaticInstructionId),
                            'authorization_id' => $this->maskIdentifier($pixAutomaticAuthorizationId),
                        ]);
                        break;
                    }

                    if (!empty($pixAutomaticInstructionId)) {
                        $subscription->update([
                            'asaas_pix_automatic_last_instruction_id' => $pixAutomaticInstructionId,
                            'asaas_pix_automatic_payload' => $payload,
                            'asaas_pix_automatic_last_event_at' => now(),
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);
                    }

                    $instructionAmountCents = $this->extractPixAutomaticAmountCents($payload);
                    $instructionDueDate = $this->extractPixAutomaticDueDate($payload);

                    if (!empty($pixAutomaticInstructionId) && $instructionAmountCents > 0 && !empty($instructionDueDate)) {
                        $invoice = Invoices::query()
                            ->where(function ($query) use ($pixAutomaticInstructionId) {
                                $query->where('provider_id', $pixAutomaticInstructionId)
                                    ->orWhere('asaas_payment_id', $pixAutomaticInstructionId);
                            })
                            ->first();

                        $invoicePayload = [
                            'subscription_id' => $subscription->id,
                            'tenant_id' => $subscription->tenant_id,
                            'amount_cents' => $instructionAmountCents,
                            'due_date' => $instructionDueDate,
                            'status' => 'pending',
                            'payment_method' => 'PIX_AUTOMATIC',
                            'provider' => 'asaas',
                            'provider_id' => $pixAutomaticInstructionId,
                            'asaas_payment_id' => $pixAutomaticInstructionId,
                            'payment_link' => $this->extractPixAutomaticLink($payload),
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ];

                        if ($invoice) {
                            $invoice->update($invoicePayload);
                        } else {
                            $invoice = Invoices::create($invoicePayload);
                        }
                    }
                    break;

                case 'PIX_AUTOMATIC_RECURRING_PAYMENT_INSTRUCTION_REFUSED':
                case 'PIX_AUTOMATIC_RECURRING_PAYMENT_INSTRUCTION_CANCELLED':
                    if ($subscription) {
                        $subscription->update([
                            'asaas_pix_automatic_last_instruction_id' => $pixAutomaticInstructionId ?: $subscription->asaas_pix_automatic_last_instruction_id,
                            'asaas_pix_automatic_payload' => $payload,
                            'asaas_pix_automatic_last_event_at' => now(),
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);
                    }

                    $instructionInvoice = null;
                    if (!empty($pixAutomaticInstructionId)) {
                        $instructionInvoice = Invoices::query()
                            ->where(function ($query) use ($pixAutomaticInstructionId) {
                                $query->where('provider_id', $pixAutomaticInstructionId)
                                    ->orWhere('asaas_payment_id', $pixAutomaticInstructionId);
                            })
                            ->first();
                    }

                    if (!empty($instructionInvoice)) {
                        $instructionInvoice->update([
                            'status' => 'canceled',
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);
                    }

                    SystemNotificationService::notify(
                        'Pix Automatico: instrucao recusada/cancelada',
                        "Instrucao Pix Automatico {$this->maskIdentifier($pixAutomaticInstructionId)} foi recusada ou cancelada.",
                        'invoice',
                        'warning'
                    );
                    break;

                case 'PIX_AUTOMATIC_RECURRING_ELIGIBILITY_UPDATED':
                    if ($subscription) {
                        $subscription->update([
                            'asaas_pix_automatic_payload' => $payload,
                            'asaas_pix_automatic_last_event_at' => now(),
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);
                    }
                    break;

                case 'PAYMENT_CREATED':
                    $subscriptionIdFromAsaas = $payload['payment']['subscription'] ?? null;
                    Log::info("🧾 Pagamento criado no Asaas: {$paymentId}");

                    if (empty($paymentId)) {
                        Log::warning('PAYMENT_CREATED sem payment.id. Evento ignorado.');
                        break;
                    }

                    if ($paymentId && Invoices::query()
                        ->where('asaas_payment_id', $paymentId)
                        ->orWhere('provider_id', $paymentId)
                        ->exists()) {
                        Log::info("Evento PAYMENT_CREATED idempotente para payment {$paymentId}.");
                        break;
                    }

                    if ($externalReference && Str::isUuid((string) $externalReference)) {
                        $invoice = Invoices::find($externalReference);
                    }

                    if (!$subscription && $subscriptionIdFromAsaas) {
                        $subscription = Subscription::where('asaas_subscription_id', $subscriptionIdFromAsaas)->first();
                    }
                    if (!$subscription && $externalReference && Str::isUuid((string) $externalReference)) {
                        $subscription = Subscription::find($externalReference);
                    }

                    if (!$subscription) {
                        Log::warning('PAYMENT_CREATED sem assinatura local vinculada.', [
                            'payment_id' => $this->maskIdentifier($paymentId),
                            'subscription_id' => $this->maskIdentifier($subscriptionIdFromAsaas),
                            'external_reference' => $this->maskIdentifier($externalReference),
                        ]);
                        break;
                    }

                    $paymentStatus = strtoupper((string) ($payload['payment']['status'] ?? 'PENDING'));
                    $localStatus = $paymentStatus === 'OVERDUE' ? 'overdue' : 'pending';
                    $paymentLink = $payload['payment']['invoiceUrl']
                        ?? $payload['payment']['bankSlipUrl']
                        ?? null;

                    if ($invoice) {
                        $invoice->update([
                            'subscription_id'   => $subscription->id,
                            'tenant_id'         => $subscription->tenant_id,
                            'amount_cents'      => (int) round(((float) ($payload['payment']['value'] ?? 0)) * 100),
                            'due_date'          => $payload['payment']['dueDate'] ?? now(),
                            'status'            => $localStatus,
                            'provider'          => 'asaas',
                            'provider_id'       => $paymentId,
                            'asaas_payment_id'  => $paymentId,
                            'payment_method'    => $subscription->payment_method,
                            'payment_link'      => $paymentLink,
                            'asaas_synced'      => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error'  => null,
                        ]);

                        Log::info("✅ Invoice {$invoice->id} vinculada ao pagamento {$paymentId} via PAYMENT_CREATED.");
                        break;
                    }

                    $invoice = Invoices::create([
                        'subscription_id'   => $subscription->id,
                        'tenant_id'         => $subscription->tenant_id,
                        'amount_cents'      => (int) round(((float) ($payload['payment']['value'] ?? 0)) * 100),
                        'due_date'          => $payload['payment']['dueDate'] ?? now(),
                        'status'            => $localStatus,
                        'provider'          => 'asaas',
                        'provider_id'       => $paymentId,
                        'asaas_payment_id'  => $paymentId,
                        'payment_method'    => $subscription->payment_method,
                        'payment_link'      => $paymentLink,
                        'asaas_synced'      => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error'  => null,
                    ]);

                    Log::info("✅ Fatura local criada para pagamento {$paymentId} (assinatura {$subscription->id})");
                    SystemNotificationService::notify(
                        'Fatura automática criada',
                        "Nova fatura gerada automaticamente pela assinatura #{$subscription->id} do tenant {$subscription->tenant?->trade_name}.",
                        'invoice',
                        'info'
                    );
                    break;

                case 'PAYMENT_RECEIVED':
                case 'PAYMENT_CONFIRMED':
                    if (!$invoice && $subscription && $paymentId) {
                        $invoice = Invoices::create([
                            'subscription_id' => $subscription->id,
                            'tenant_id' => $subscription->tenant_id,
                            'amount_cents' => (int) round(((float) ($payload['payment']['value'] ?? 0)) * 100),
                            'due_date' => $payload['payment']['dueDate'] ?? now(),
                            'status' => 'pending',
                            'provider' => 'asaas',
                            'provider_id' => $paymentId,
                            'asaas_payment_id' => $paymentId,
                            'payment_method' => $subscription->payment_method,
                            'payment_link' => $payload['payment']['invoiceUrl'] ?? ($payload['payment']['bankSlipUrl'] ?? null),
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);

                        Log::info("🧾 Invoice criada tardiamente no {$event} para pagamento {$paymentId}.");
                    }

                    if (!$invoice) {
                        Log::warning("⚠️ Fatura {$paymentId} não encontrada para evento {$event}");
                        break;
                    }

                    // 🔹 Obtém data de pagamento do payload (se disponível) ou usa now()
                    $paidAt = isset($payload['payment']['paymentDate']) 
                        ? Carbon::parse($payload['payment']['paymentDate'])
                        : now();

                    $invoice->update([
                        'status'             => 'paid',
                        'paid_at'            => $paidAt,
                        'provider'           => 'asaas',
                        'provider_id'        => $paymentId ?? $invoice->provider_id,
                        'asaas_payment_id'   => $paymentId ?? $invoice->asaas_payment_id ?? $invoice->provider_id,
                        'payment_link'       => $payload['payment']['invoiceUrl'] ?? ($payload['payment']['bankSlipUrl'] ?? $invoice->payment_link),
                        'asaas_synced'       => true,
                        'asaas_sync_status'  => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error'   => null,
                    ]);

                    // 🔹 Ativa assinatura se estava pendente ou recovery_pending
                    $subscription = $invoice->subscription;
                    $tenant = $tenant ?? $invoice->tenant ?? $subscription?->tenant;
                    if ($subscription && in_array($subscription->status, ['pending', 'recovery_pending'])) {
                        $months = $subscription->plan->period_months ?? 1;
                        
                        // 🔹 Se é recovery, cria nova assinatura recorrente no Asaas
                        if ($subscription->status === 'recovery_pending' && $subscription->payment_method === 'CREDIT_CARD') {
                            $plan = $subscription->plan;
                            $tenant = $subscription->tenant;
                            
                            // 🔹 Calcula nextDueDate baseado na data do pagamento (paymentDate)
                            $paidAtDate = $paidAt->copy()->startOfDay();
                            $nextDueDate = $paidAtDate->copy()->addMonths(1)->toDateString();
                            
                            // Cria nova assinatura recorrente no Asaas
                            $asaas = new AsaasService();
                            $asaasResponse = $asaas->createSubscription([
                                'customer' => $tenant->asaas_customer_id,
                                'billingType' => 'CREDIT_CARD',
                                'value' => $plan->price_cents / 100,
                                'cycle' => 'MONTHLY',
                                'nextDueDate' => $nextDueDate, // Baseado na data do pagamento
                                'description' => "Assinatura do plano {$plan->name}",
                                'externalReference' => (string) $subscription->id,
                            ]);
                            
                            if (!empty($asaasResponse['subscription']['id'])) {
                                $months = $plan->period_months ?? 1;
                                
                                $subscription->update([
                                    'asaas_subscription_id' => $asaasResponse['subscription']['id'],
                                    'status' => 'active',
                                    'starts_at' => $paidAtDate,
                                    'ends_at' => $paidAtDate->copy()->addMonths($months),
                                    'recovery_started_at' => null, // Limpa recovery
                                    'asaas_synced' => true,
                                    'asaas_sync_status' => 'success',
                                    'asaas_last_sync_at' => now(),
                                    'asaas_last_error' => null,
                                ]);
                                
                                // 🔹 Atualiza invoice com ID da nova assinatura criada
                                $invoice->update([
                                    'asaas_recovery_subscription_id' => $asaasResponse['subscription']['id'],
                                ]);
                                
                                Log::info("✅ Recovery concluído - nova assinatura criada no Asaas", [
                                    'subscription_id' => $subscription->id,
                                    'new_asaas_subscription_id' => $asaasResponse['subscription']['id'],
                                    'next_due_date' => $nextDueDate,
                                    'paid_at' => $paidAtDate->toDateString(),
                                ]);
                            } else {
                                Log::error("❌ Falha ao criar assinatura no Asaas durante recovery", [
                                    'subscription_id' => $subscription->id,
                                    'response' => $asaasResponse,
                                ]);
                            }
                        } else {
                            // Assinatura normal pendente
                            $paidAtDate = $paidAt->copy()->startOfDay();
                            $subscription->update([
                                'status'              => 'active',
                                'starts_at'           => $paidAtDate,
                                'ends_at'             => $paidAtDate->copy()->addMonths($months),
                                'billing_anchor_date' => $paidAtDate->toDateString(),
                                'asaas_last_sync_at'  => now(),
                                'asaas_synced'        => true,
                                'asaas_sync_status'   => 'success',
                                'asaas_last_error'    => null,
                            ]);
                        }
                    }

                    // 🔹 REGRA CRÍTICA: PIX_RECURRENT reativa e garante cobertura do ciclo pago.
                    // PIX/BOLETO mantém comportamento atual (só recalcula se pago após vencimento).
                    // Cartão: Asaas é autoridade total (não recalcula ciclo localmente).
                    $subscription = $invoice->subscription;
                    $paymentMethod = $invoice->payment_method ?? $subscription?->payment_method;
                    
                    if ($subscription && $paymentMethod === 'PIX_RECURRENT') {
                        $months = $subscription->plan->period_months ?? 1;
                        $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
                        $currentEndsAt = $subscription->ends_at ? Carbon::parse($subscription->ends_at)->startOfDay() : null;
                        $targetEndsAt = $dueDate->copy()->addMonths($months);
                        $shouldAdvanceEndsAt = !$currentEndsAt || $currentEndsAt->lt($targetEndsAt);

                        $previousStatus = $subscription->status;
                        $previousEndsAt = $subscription->ends_at ? Carbon::parse($subscription->ends_at)->toDateString() : null;

                        $subscription->update([
                            'status' => 'active',
                            'recovery_started_at' => null,
                            'ends_at' => $shouldAdvanceEndsAt ? $targetEndsAt : $subscription->ends_at,
                            'billing_anchor_date' => $dueDate->toDateString(),
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);

                        Log::info('PIX_RECURRENT reativado apos pagamento confirmado', [
                            'subscription_id' => $this->maskIdentifier($subscription->id),
                            'invoice_id' => $this->maskIdentifier($invoice->id),
                            'payment_id' => $this->maskIdentifier($paymentId),
                            'status_from' => $previousStatus,
                            'status_to' => 'active',
                            'ends_at_from' => $previousEndsAt,
                            'ends_at_to' => $subscription->fresh()->ends_at?->toDateString(),
                        ]);
                    } elseif ($subscription && in_array($paymentMethod, ['PIX', 'BOLETO'], true)) {
                        $dueDate = Carbon::parse($invoice->due_date);
                        
                        // Só recalcula se pagamento foi após o vencimento
                        if ($paidAt->isAfter($dueDate)) {
                            $months = $subscription->plan->period_months ?? 1;
                            
                            // 🔹 Atualiza billing_anchor_date = paid_at->toDateString()
                            $anchorDate = $paidAt->copy();
                            
                            // Calcula próximo vencimento baseado no anchor date
                            $nextDueDate = $anchorDate->copy()->addMonths($months);
                            
                            $subscription->update([
                                'ends_at' => $nextDueDate,
                                'billing_anchor_date' => $anchorDate->toDateString(),
                                'status' => 'active',
                                'asaas_last_sync_at' => now(),
                            ]);
                            
                            Log::info("🔄 Ciclo recalculado para assinatura {$subscription->id} (pagamento após vencimento)", [
                                'paid_at' => $paidAt->toDateString(),
                                'due_date' => $dueDate->toDateString(),
                                'billing_anchor_date' => $anchorDate->toDateString(),
                                'new_ends_at' => $nextDueDate->toDateString(),
                                'payment_method' => $paymentMethod,
                            ]);
                        } else {
                            Log::info("ℹ️ Pagamento antes ou no vencimento - ciclo não recalculado", [
                                'paid_at' => $paidAt->toDateString(),
                                'due_date' => $dueDate->toDateString(),
                                'payment_method' => $paymentMethod,
                            ]);
                        }
                    } else {
                        // Para cartão: Asaas é autoridade total, não recalcula
                        Log::info("ℹ️ Pagamento de cartão - Asaas controla o ciclo, não recalcula localmente", [
                            'payment_method' => $paymentMethod,
                            'subscription_id' => $subscription?->id,
                        ]);
                    }

                    // 🔹 Reativação automática (apenas após recovery ou pagamento normal)
                    // Se é recovery, já foi reativado acima. Se não, reativa normalmente.
                    if ($tenant && in_array($tenant->status, ['suspended', 'past_due'], true) && $subscription?->status !== 'recovery_pending') {
                        $tenant->update([
                            'status' => 'active',
                            'suspended_at' => null, // Limpa suspended_at
                        ]);
                        Log::info("✅ Tenant {$tenant->trade_name} reativado automaticamente após confirmação de pagamento da fatura {$paymentId}.");
                    } elseif ($tenant && $tenant->status === 'suspended' && $subscription?->status === 'recovery_pending') {
                        // Reativa tenant após recovery ser concluído
                        $tenant->update([
                            'status' => 'active',
                            'suspended_at' => null,
                        ]);
                        Log::info("✅ Tenant {$tenant->trade_name} reativado após conclusão do recovery.");
                    }

                    SystemNotificationService::notify(
                        'Pagamento confirmado',
                        "Fatura #{$invoice->id} do tenant {$tenant?->trade_name} foi marcada como paga.",
                        'invoice',
                        'info'
                    );
                    break;

                case 'PAYMENT_OVERDUE':
                    if (!$invoice && $subscription && $paymentId) {
                        $invoice = Invoices::create([
                            'subscription_id' => $subscription->id,
                            'tenant_id' => $subscription->tenant_id,
                            'amount_cents' => (int) round(((float) ($payload['payment']['value'] ?? 0)) * 100),
                            'due_date' => $payload['payment']['dueDate'] ?? now(),
                            'status' => 'overdue',
                            'provider' => 'asaas',
                            'provider_id' => $paymentId,
                            'asaas_payment_id' => $paymentId,
                            'payment_method' => $subscription->payment_method,
                            'payment_link' => $payload['payment']['invoiceUrl'] ?? ($payload['payment']['bankSlipUrl'] ?? null),
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);
                    }

                    if (!$invoice) break;
                    $tenant = $tenant ?? $invoice->tenant;
                    $subscription = $subscription ?? $invoice->subscription;

                    $invoice->update([
                        'status'             => 'overdue',
                        'asaas_synced'       => true,
                        'asaas_sync_status'  => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error'   => null,
                    ]);

                    Log::warning("⚠️ Fatura {$invoice->id} marcada como vencida.");

                    if ($subscription) {
                        $subscription->update([
                            'status' => 'past_due',
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);
                    }

                    // 🔹 Suspensão imediata (sem período de carência), preservando suspended_at em reenvio
                    if ($tenant) {
                        $alreadySuspended = $tenant->status === 'suspended';
                        $suspendedAt = $tenant->suspended_at ?: now();

                        $tenant->update([
                            'status' => 'suspended',
                            'suspended_at' => $suspendedAt,
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);

                        if (!$alreadySuspended) {
                            Log::warning("⛔ Tenant {$tenant->trade_name} suspenso imediatamente por fatura vencida (sem período de carência).");
                        }
                    }

                    Log::info('PAYMENT_OVERDUE sincronizado com sucesso para invoice/subscription/tenant', [
                        'payment_id' => $this->maskIdentifier($paymentId),
                        'invoice_id' => $this->maskIdentifier($invoice->id),
                        'subscription_id' => $this->maskIdentifier($subscription?->id),
                        'tenant_id' => $this->maskIdentifier($tenant?->id),
                    ]);

                    SystemNotificationService::notify(
                        'Fatura vencida',
                        "Fatura #{$invoice->id} do tenant {$tenant?->trade_name} está vencida. Tenant suspenso imediatamente.",
                        'invoice',
                        'warning'
                    );
                    break;

                case 'PAYMENT_REFUNDED':
                    if ($invoice) {
                        $invoice->update([
                            'status'             => 'canceled',
                            'asaas_synced'       => true,
                            'asaas_sync_status'  => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error'   => null,
                        ]);

                        Log::warning("🚫 Fatura {$invoice->id} estornada no Asaas.");
                        SystemNotificationService::notify(
                            'Pagamento estornado',
                            "Fatura #{$invoice->id} do tenant {$tenant?->trade_name} foi estornada.",
                            'invoice',
                            'warning'
                        );
                    }
                    break;

                case 'PAYMENT_DELETED':
                    if ($invoice) {
                        $invoice->update([
                            'asaas_sync_status' => 'deleted',
                            'asaas_last_sync_at' => now(),
                        ]);
                        $invoice->delete();
                        Log::info("🗑️ Fatura {$invoice->id} removida pois foi excluída no Asaas.");

                        SystemNotificationService::notify(
                            'Fatura removida',
                            "Fatura #{$invoice->id} foi excluída no Asaas e removida do sistema.",
                            'invoice',
                            'warning'
                        );
                    }
                    break;

                case 'CUSTOMER_DELETED':
                    if ($tenant) {
                        $tenant->update([
                            'asaas_customer_id' => null,
                            'asaas_synced' => false,
                            'asaas_sync_status' => 'deleted',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => 'Cliente excluído via webhook Asaas',
                        ]);

                        Log::info("👤 Cliente {$customerId} excluído no Asaas — removido do Tenant {$tenant->trade_name}");
                        SystemNotificationService::notify(
                            'Cliente removido no Asaas',
                            "O cliente vinculado ao tenant {$tenant->trade_name} foi excluído no Asaas.",
                            'customer',
                            'warning'
                        );
                    }
                    break;

                default:
                    Log::info("ℹ️ Evento {$event} recebido, sem ação específica.");
                    SystemNotificationService::notify(
                        'Evento Asaas recebido',
                        "O evento {$event} foi recebido do Asaas e registrado no log.",
                        'webhook',
                        'info'
                    );
                    break;
            }

            return $this->finalizeWebhookResponse(
                $event,
                $paymentId,
                $invoice?->id,
                $startedAt,
                200,
                ['received' => true],
                'processed'
            );
        } catch (\Throwable $e) {
            Log::error("❌ Erro no Webhook Asaas: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'event' => $event,
                'payment_id' => $this->maskIdentifier($paymentId),
            ]);

            // 🔹 Marca as entidades com erro de sincronização
            foreach (['invoice', 'subscription', 'tenant'] as $var) {
                if (isset($$var) && $$var) {
                    $$var->update([
                        'asaas_sync_status' => 'failed',
                        'asaas_last_error' => $e->getMessage(),
                        'asaas_last_sync_at' => now(),
                    ]);
                }
            }

            return $this->finalizeWebhookResponse(
                $event,
                $paymentId,
                $invoice?->id,
                $startedAt,
                500,
                ['error' => 'Internal Server Error'],
                'failed',
                'error'
            );
        }
    }

    private function extractPixAutomaticAuthorizationId(array $payload): ?string
    {
        return $this->firstNonEmptyString([
            data_get($payload, 'pixAutomaticRecurringAuthorization.id'),
            data_get($payload, 'authorization.id'),
            data_get($payload, 'pixAutomatic.authorization.id'),
            data_get($payload, 'data.authorization.id'),
        ]);
    }

    private function extractPixAutomaticInstructionId(array $payload): ?string
    {
        return $this->firstNonEmptyString([
            data_get($payload, 'pixAutomaticRecurringPaymentInstruction.id'),
            data_get($payload, 'paymentInstruction.id'),
            data_get($payload, 'instruction.id'),
            data_get($payload, 'data.paymentInstruction.id'),
        ]);
    }

    private function extractPixAutomaticExternalReference(array $payload): ?string
    {
        return $this->firstNonEmptyString([
            data_get($payload, 'pixAutomaticRecurringAuthorization.externalReference'),
            data_get($payload, 'authorization.externalReference'),
            data_get($payload, 'pixAutomaticRecurringPaymentInstruction.externalReference'),
            data_get($payload, 'paymentInstruction.externalReference'),
            data_get($payload, 'data.authorization.externalReference'),
            data_get($payload, 'data.paymentInstruction.externalReference'),
        ]);
    }

    private function extractPixAutomaticAuthorizationStatus(array $payload, string $fallback): string
    {
        $status = $this->firstNonEmptyString([
            data_get($payload, 'pixAutomaticRecurringAuthorization.status'),
            data_get($payload, 'authorization.status'),
            data_get($payload, 'status'),
        ]);

        return strtolower($status ?: $fallback);
    }

    private function isPixAutomaticPaymentConfirmed(array $payload): bool
    {
        $paymentStatus = strtoupper((string) (
            data_get($payload, 'payment.status')
            ?? data_get($payload, 'pixAutomaticRecurringPaymentInstruction.paymentStatus')
            ?? data_get($payload, 'paymentInstruction.paymentStatus')
            ?? ''
        ));

        return in_array($paymentStatus, ['RECEIVED', 'CONFIRMED', 'PAID'], true);
    }

    private function extractPixAutomaticAmountCents(array $payload): int
    {
        $value = data_get($payload, 'pixAutomaticRecurringPaymentInstruction.value')
            ?? data_get($payload, 'paymentInstruction.value')
            ?? data_get($payload, 'payment.value');

        if ($value === null || $value === '') {
            return 0;
        }

        return (int) round(((float) $value) * 100);
    }

    private function extractPixAutomaticDueDate(array $payload): ?string
    {
        $dueDate = $this->firstNonEmptyString([
            data_get($payload, 'pixAutomaticRecurringPaymentInstruction.dueDate'),
            data_get($payload, 'paymentInstruction.dueDate'),
            data_get($payload, 'payment.dueDate'),
            data_get($payload, 'pixAutomaticRecurringPaymentInstruction.scheduledDate'),
            data_get($payload, 'paymentInstruction.scheduledDate'),
        ]);

        if (!$dueDate) {
            return null;
        }

        try {
            return Carbon::parse($dueDate)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function extractPixAutomaticLink(array $payload): ?string
    {
        return $this->firstNonEmptyString([
            data_get($payload, 'pixAutomaticRecurringAuthorization.authorizationUrl'),
            data_get($payload, 'authorization.authorizationUrl'),
            data_get($payload, 'pixAutomaticRecurringPaymentInstruction.invoiceUrl'),
            data_get($payload, 'paymentInstruction.invoiceUrl'),
            data_get($payload, 'payment.invoiceUrl'),
        ]);
    }

    private function firstNonEmptyString(array $values): ?string
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $text = trim((string) $value);
            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    private function finalizeWebhookResponse(
        string $event,
        ?string $paymentId,
        ?string $invoiceId,
        float $startedAt,
        int $statusCode,
        array $body,
        string $result,
        string $level = 'info'
    ) {
        Log::log($level, 'Asaas webhook finalizado', [
            'event' => $event,
            'payment_id' => $this->maskIdentifier($paymentId),
            'invoice_id' => $this->maskIdentifier($invoiceId),
            'duration_ms' => $this->durationMs($startedAt),
            'result' => $result,
            'status_code' => $statusCode,
        ]);

        return response()->json($body, $statusCode);
    }

    private function durationMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    private function maskIdentifier($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = (string) $value;
        $length = strlen($text);

        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($text, 0, 4) . '***' . substr($text, -4);
    }

    /**
     * 🕐 Suspende tenants com faturas vencidas há mais de 5 dias.
     */
    public static function suspendOverdueTenants()
    {
        $limitDate = Carbon::now()->subDays(5);

        $overdueInvoices = Invoices::where('status', 'overdue')
            ->where('due_date', '<=', $limitDate)
            ->with('tenant')
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $tenant = $invoice->tenant;
            if ($tenant && $tenant->status !== 'suspended') {
                $tenant->update(['status' => 'suspended']);
                Log::warning("⛔ Tenant {$tenant->trade_name} suspenso por fatura atrasada ({$invoice->id}).");
            }
        }

        Log::info('🕐 Verificação de tenants com atraso > 5 dias concluída.');
    }
}
