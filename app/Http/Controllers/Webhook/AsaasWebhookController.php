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

class AsaasWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            $event = $payload['event'] ?? 'UNKNOWN';

            $paymentId      = $payload['payment']['id'] ?? null;
            $customerId     = $payload['customer']['id'] ?? null;
            $subscriptionId = $payload['subscription']['id'] ?? ($payload['payment']['subscription'] ?? null);
            $referenceId    = $paymentId ?? $subscriptionId ?? $customerId;

            Log::info("📩 Webhook recebido do Asaas: {$event} ({$referenceId})", [
                'payload' => $payload,
            ]);

            // 🔹 1. Registrar log de auditoria
            WebhookLog::create([
                'event' => $event,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

            if (!$paymentId && !$customerId && !$subscriptionId) {
                Log::warning("⚠️ Webhook sem ID relevante", ['payload' => $payload]);
                return response()->json(['message' => 'Missing resource ID'], 400);
            }

            // 🔹 2. VERIFICAR SE É PRÉ-CADASTRO ANTES DE PROCESSAR COMO FATURA NORMAL
            // O Asaas pode enviar webhooks de pré-cadastro para /webhook/asaas
            $externalReference = $payload['payment']['externalReference'] ?? null;
            $paymentLinkId = $payload['payment']['paymentLink'] ?? null;
            
            if ($externalReference || $paymentLinkId) {
                $preTenant = null;
                
                // Buscar pré-tenant pelo externalReference (ID do pré-tenant)
                if ($externalReference) {
                    $preTenant = PreTenant::find($externalReference);
                    if ($preTenant) {
                        Log::info("🔍 Pré-tenant encontrado pelo externalReference no webhook principal", [
                            'pre_tenant_id' => $preTenant->id,
                            'external_reference' => $externalReference,
                        ]);
                    } else {
                        Log::debug("🔍 Pré-tenant não encontrado pelo externalReference", [
                            'external_reference' => $externalReference,
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
                                    return response()->json(['message' => 'OK'], 200);
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
                        
                        return response()->json(['message' => 'OK'], 200);
                    } catch (\Throwable $e) {
                        Log::error("❌ Erro ao processar pré-cadastro via webhook principal", [
                            'pre_tenant_id' => $preTenant->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        return response()->json(['error' => 'Internal Server Error'], 500);
                    }
                } else {
                    // Não é pré-cadastro ou pré-tenant não encontrado - continua fluxo normal
                    Log::debug("ℹ️ Não é pré-cadastro ou pré-tenant não encontrado. Continuando fluxo normal...", [
                        'external_reference' => $externalReference,
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

            // 🔹 Busca invoice por externalReference (para recovery)
            // externalReference deve ser o ID da subscription recovery_pending
            if (!$invoice && $externalReference) {
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
                        'external_reference' => $externalReference,
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
                case 'PAYMENT_CREATED':
                    $subscriptionIdFromAsaas = $payload['payment']['subscription'] ?? null;
                    Log::info("🧾 Pagamento criado no Asaas: {$paymentId}");

                    if ($subscriptionIdFromAsaas) {
                        $subscription = Subscription::where('asaas_subscription_id', $subscriptionIdFromAsaas)->first();

                        if ($subscription && !Invoices::where('asaas_payment_id', $paymentId)->exists()) {
                            Invoices::create([
                                'subscription_id'   => $subscription->id,
                                'tenant_id'         => $subscription->tenant_id,
                                'amount_cents'      => (int) (($payload['payment']['value'] ?? 0) * 100),
                                'due_date'          => $payload['payment']['dueDate'] ?? now(),
                                'status'            => 'pending',
                                'provider'          => 'asaas',
                                'provider_id'       => $subscriptionIdFromAsaas,
                                'asaas_payment_id'  => $paymentId,
                                'payment_link'      => $payload['payment']['invoiceUrl'] ?? null,
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
                        }
                    }
                    break;

                case 'PAYMENT_RECEIVED':
                case 'PAYMENT_CONFIRMED':
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
                        'asaas_synced'       => true,
                        'asaas_sync_status'  => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error'   => null,
                    ]);

                    // 🔹 Ativa assinatura se estava pendente ou recovery_pending
                    $subscription = $invoice->subscription;
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
                                'value' => $plan->price_cents / 100,
                                'cycle' => 'MONTHLY',
                                'nextDueDate' => $nextDueDate, // Baseado na data do pagamento
                                'description' => "Assinatura do plano {$plan->name}",
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
                            $subscription->update([
                                'status'              => 'active',
                                'starts_at'           => now(),
                                'ends_at'             => now()->addMonths($months),
                                'asaas_last_sync_at'  => now(),
                                'asaas_synced'        => true,
                                'asaas_sync_status'   => 'success',
                                'asaas_last_error'    => null,
                            ]);
                        }
                    }

                    // 🔹 REGRA CRÍTICA: Só recalcula ciclo se paid_at > due_date E apenas para PIX/Boleto
                    // Cartão: Asaas é autoridade total (não recalcula ciclo localmente)
                    $subscription = $invoice->subscription;
                    $paymentMethod = $invoice->payment_method ?? $subscription?->payment_method;
                    
                    if ($subscription && in_array($paymentMethod, ['PIX', 'BOLETO'])) {
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
                    if ($tenant && $tenant->status === 'suspended' && $subscription?->status !== 'recovery_pending') {
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
                    if (!$invoice) break;

                    $invoice->update([
                        'status'             => 'overdue',
                        'asaas_synced'       => true,
                        'asaas_sync_status'  => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error'   => null,
                    ]);

                    Log::warning("⚠️ Fatura {$invoice->id} marcada como vencida.");

                    // 🔹 Suspensão imediata (sem período de carência)
                    if ($tenant && $tenant->status !== 'suspended') {
                        $tenant->update(['status' => 'suspended']);
                        Log::warning("⛔ Tenant {$tenant->trade_name} suspenso imediatamente por fatura vencida (sem período de carência).");
                    }

                    // Atualiza status da assinatura
                    if ($invoice->subscription) {
                        $invoice->subscription->update(['status' => 'past_due']);
                    }

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

            return response()->json(['message' => 'OK'], 200);
        } catch (\Throwable $e) {
            Log::error("❌ Erro no Webhook Asaas: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
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

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
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
