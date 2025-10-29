<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Platform\Invoices;
use App\Models\Platform\WebhookLog;
use App\Models\Platform\Tenant;
use App\Models\Platform\Subscription;
use App\Services\SystemNotificationService;
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

            // 🔹 2. Buscar entidades locais
            $invoice = null;
            if ($paymentId) {
                $invoice = Invoices::where('asaas_payment_id', $paymentId)
                    ->orWhere('provider_id', $paymentId)
                    ->first();
            }

            $tenant       = $invoice?->tenant ?? Tenant::where('asaas_customer_id', $customerId)->first();
            $subscription = $subscriptionId ? Subscription::where('asaas_subscription_id', $subscriptionId)->first() : null;

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

                    $invoice->update([
                        'status'             => 'paid',
                        'asaas_synced'       => true,
                        'asaas_sync_status'  => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error'   => null,
                    ]);

                    if ($invoice->subscription && $invoice->subscription->status === 'pending') {
                        $months = $invoice->subscription->plan->period_months ?? 1;
                        $invoice->subscription->update([
                            'status'              => 'active',
                            'starts_at'           => now(),
                            'ends_at'             => now()->addMonths($months),
                            'asaas_last_sync_at'  => now(),
                            'asaas_synced'        => true,
                            'asaas_sync_status'   => 'success',
                            'asaas_last_error'    => null,
                        ]);
                    }

                    if ($tenant && $tenant->status === 'suspended') {
                        $tenant->update(['status' => 'active']);
                        Log::info("✅ Tenant {$tenant->trade_name} reativado após pagamento da fatura {$paymentId}.");
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

                    if ($tenant) {
                        $diffDays = now()->diffInDays($invoice->due_date);
                        if ($diffDays >= 5 && $tenant->status !== 'suspended') {
                            $tenant->update(['status' => 'suspended']);
                            Log::warning("⛔ Tenant {$tenant->trade_name} suspenso (atraso de {$diffDays} dias).");
                        }

                        SystemNotificationService::notify(
                            'Fatura vencida',
                            "Fatura #{$invoice->id} do tenant {$tenant->trade_name} está vencida há {$diffDays} dias.",
                            'invoice',
                            'warning'
                        );
                    }
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
