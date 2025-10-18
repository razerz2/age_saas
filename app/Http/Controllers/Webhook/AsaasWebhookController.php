<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Platform\Invoices;
use App\Models\Platform\WebhookLog;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;

class AsaasWebhookController extends Controller
{
    public function handle(Request $request)
    {

        // 🔹 Salva o log do webhook no banco
        WebhookLog::create([
            'provider' => 'asaas',
            'event' => $request->input('event'),
            'invoice_id' => optional(
             Invoices::where('provider_id', $request->input('payment.id'))->first()
            )->id,
            'payment_id' => $request->input('payment.id'),
            'payload' => $request->all(),
        ]);

        // Loga tudo para análise
        Log::info('📩 Webhook Asaas recebido', $request->all());

        $event = $request->input('event');
        $payment = $request->input('payment');

        if (!$payment || empty($payment['id'])) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $invoice = Invoices::where('provider_id', $payment['id'])->first();
        if (!$invoice) {
            Log::warning("🔍 Fatura não encontrada para payment_id {$payment['id']}");
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $subscription = Subscription::find($invoice->subscription_id);
        $tenant = Tenant::find($invoice->tenant_id);

        switch ($event) {
            // 💰 Cobrança criada
            case 'PAYMENT_CREATED':
                $invoice->update(['status' => 'pending']);
                break;

            // ✅ Pagamento confirmado
            case 'PAYMENT_CONFIRMED':
            case 'PAYMENT_RECEIVED':
                $invoice->update(['status' => 'paid']);
                if ($subscription) $subscription->update(['status' => 'active']);
                if ($tenant) $tenant->update(['status' => 'active']);
                break;

            // ⚠️ Pagamento atrasado
            case 'PAYMENT_OVERDUE':
                $invoice->update(['status' => 'overdue']);
                if ($subscription) $subscription->update(['status' => 'past_due']);
                if ($tenant) $tenant->update(['status' => 'suspended']);
                break;

            // ❌ Cobrança removida manualmente
            case 'PAYMENT_DELETED':
                $invoice->update(['status' => 'canceled']);
                if ($subscription) $subscription->update(['status' => 'canceled']);
                if ($tenant) $tenant->update(['status' => 'suspended']);
                break;

            // 🔁 Estorno total ou parcial
            case 'PAYMENT_REFUNDED':
            case 'PAYMENT_PARTIALLY_REFUNDED':
                $invoice->update(['status' => 'canceled']);
                if ($subscription) $subscription->update(['status' => 'past_due']);
                if ($tenant) $tenant->update(['status' => 'suspended']);
                break;

            // ♻️ Cobrança restaurada após erro
            case 'PAYMENT_RESTORED':
                $invoice->update(['status' => 'pending']);
                if ($subscription && $subscription->status === 'past_due') {
                    $subscription->update(['status' => 'active']);
                }
                if ($tenant && $tenant->status === 'suspended') {
                    $tenant->update(['status' => 'active']);
                }
                break;

            // ⚔️ Chargeback ou disputa
            case 'PAYMENT_CHARGEBACK_REQUESTED':
            case 'PAYMENT_CHARGEBACK_DISPUTE':
                $invoice->update(['status' => 'overdue']);
                if ($subscription) $subscription->update(['status' => 'past_due']);
                if ($tenant) $tenant->update(['status' => 'suspended']);
                break;

            default:
                Log::info("ℹ️ Evento Asaas não tratado: {$event}");
        }

        return response()->json(['message' => 'Webhook processado com sucesso']);
    }
}
