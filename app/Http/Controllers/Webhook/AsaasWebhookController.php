<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Platform\Invoices;
use App\Models\Platform\WebhookLog;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\SystemNotificationService;
use Carbon\Carbon;

class AsaasWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            $event = $payload['event'] ?? 'UNKNOWN';

            Log::info("📩 Webhook recebido do Asaas: {$event}", $payload);

            // 🔹 1. Registrar log para auditoria
            WebhookLog::create([
                'event' => $event,
                'payload' => json_encode($payload),
            ]);

            // 🔹 2. Pegar ID da fatura (payment)
            $paymentId = $payload['payment']['id'] ?? null;
            if (!$paymentId) {
                Log::warning("⚠️ Webhook sem ID de pagamento recebido");
                return response()->json(['message' => 'Missing payment ID'], 400);
            }

            // 🔹 3. Localizar fatura correspondente
            $invoice = Invoices::where('provider_id', $paymentId)->first();
            if (!$invoice) {
                Log::warning("⚠️ Fatura {$paymentId} não encontrada no sistema");
                return response()->json(['message' => 'Invoice not found'], 404);
            }

            // 🔹 4. Atualizar status da fatura e tenant
            switch ($event) {
                case 'PAYMENT_RECEIVED':
                    $paymentId = $payload['payment']['id'] ?? null;
                    if (!$paymentId) break;

                    $invoice = Invoices::where('provider_id', $paymentId)->first();
                    if ($invoice) {
                        $invoice->update(['status' => 'paid']);
                        $tenant = $invoice->tenant;

                        if ($tenant && $tenant->status === 'suspended') {
                            $tenant->update(['status' => 'active']);
                            Log::info("✅ Tenant {$tenant->trade_name} reativado após pagamento da fatura {$paymentId}.");
                        }

                        // 🔔 Notificação Platform
                        SystemNotificationService::notify(
                            'Pagamento recebido',
                            "Fatura #{$invoice->id} do tenant {$tenant->trade_name} foi paga com sucesso.",
                            'invoice',
                            'info'
                        );
                    }
                    break;

                case 'PAYMENT_CONFIRMED':
                    $invoice->update([
                        'status' => 'paid',
                        'paid_at' => Carbon::now(),
                    ]);
                    $invoice->tenant->update(['status' => 'active']);
                    Log::info("✅ Fatura {$invoice->id} marcada como PAGA.");

                    // 🔔 Notificação Platform
                    SystemNotificationService::notify(
                        'Pagamento confirmado',
                        "Fatura #{$invoice->id} do tenant {$invoice->tenant->trade_name} foi confirmada como paga.",
                        'invoice',
                        'info'
                    );
                    break;

                case 'PAYMENT_OVERDUE':
                    $paymentId = $payload['payment']['id'] ?? null;
                    if (!$paymentId) break;

                    $invoice = Invoices::where('provider_id', $paymentId)->first();
                    if ($invoice) {
                        $invoice->update(['status' => 'overdue']);
                        Log::info("⚠️ Fatura {$paymentId} marcada como vencida.");

                        // Suspender tenant após 5 dias
                        $tenant = $invoice->tenant;
                        if ($tenant) {
                            $diffDays = now()->diffInDays($invoice->due_date);
                            if ($diffDays >= 5 && $tenant->status !== 'suspended') {
                                $tenant->update(['status' => 'suspended']);
                                Log::warning("⛔ Tenant {$tenant->trade_name} suspenso (atraso de {$diffDays} dias).");
                            }
                        }

                        // 🔔 Notificação Platform
                        SystemNotificationService::notify(
                            'Fatura vencida',
                            "Fatura #{$invoice->id} do tenant {$tenant->trade_name} está vencida há {$diffDays} dias.",
                            'invoice',
                            'warning'
                        );
                    }
                    break;

                case 'PAYMENT_REFUNDED':
                    $invoice->update(['status' => 'cancelled']);
                    Log::warning("🚫 Fatura {$invoice->id} cancelada.");

                    // 🔔 Notificação Platform
                    SystemNotificationService::notify(
                        'Pagamento estornado',
                        "Fatura #{$invoice->id} do tenant {$invoice->tenant->trade_name} foi estornada.",
                        'invoice',
                        'warning'
                    );
                    break;

                case 'PAYMENT_DELETED':
                    $paymentId = $payload['payment']['id'] ?? null;
                    if ($paymentId) {
                        $invoice = Invoices::where('provider_id', $paymentId)->first();
                        if ($invoice) {
                            $invoice->delete();
                            Log::info("🗑️ Fatura {$paymentId} removida pois foi excluída no Asaas.");

                            // 🔔 Notificação Platform
                            SystemNotificationService::notify(
                                'Fatura removida',
                                "Fatura #{$paymentId} foi excluída no Asaas e removida do sistema.",
                                'invoice',
                                'warning'
                            );
                        }
                    }
                    break;

                case 'CUSTOMER_DELETED':
                    $customerId = $payload['customer']['id'] ?? null;
                    if ($customerId) {
                        $tenant = Tenant::where('asaas_customer_id', $customerId)->first();
                        if ($tenant) {
                            $tenant->update(['asaas_customer_id' => null]);
                            Log::info("👤 Cliente {$customerId} excluído no Asaas — campo asaas_customer_id resetado no Tenant {$tenant->trade_name}");

                            // 🔔 Notificação Platform
                            SystemNotificationService::notify(
                                'Cliente removido no Asaas',
                                "O cliente vinculado ao tenant {$tenant->trade_name} foi excluído no Asaas.",
                                'customer',
                                'warning'
                            );
                        }
                    }
                    break;

                default:
                    Log::info("ℹ️ Evento {$event} recebido, sem ação direta.");
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
            Log::error("❌ Erro no Webhook Asaas: {$e->getMessage()}");
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * 🕐 Comando auxiliar para verificar e suspender tenants com faturas atrasadas > 5 dias.
     * Pode ser chamado via cron ou scheduler diário.
     */
    public static function suspendOverdueTenants()
    {
        $limitDate = Carbon::now()->subDays(5);

        $overdueInvoices = Invoices::where('status', 'overdue')
            ->where('due_date', '<=', $limitDate)
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $tenant = $invoice->tenant;
            if ($tenant && $tenant->status !== 'suspended') {
                $tenant->update(['status' => 'suspended']);
                Log::warning("⛔ Tenant {$tenant->trade_name} suspenso por fatura em atraso há mais de 5 dias.");
            }
        }

        Log::info('🕐 Verificação de tenants com atraso > 5 dias concluída.');
    }
}
