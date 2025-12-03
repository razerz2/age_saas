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

            Log::info("📩 Webhook de pré-cadastro recebido do Asaas: {$event}", [
                'payment_id' => $paymentId,
                'customer_id' => $customerId,
            ]);

            // Registrar log de auditoria
            WebhookLog::create([
                'event' => $event,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

            if (!$paymentId) {
                Log::warning("⚠️ Webhook de pré-cadastro sem payment_id", ['payload' => $payload]);
                return response()->json(['message' => 'Missing payment ID'], 400);
            }

            // Buscar pré-tenant pelo payment_id
            $preTenant = PreTenant::where('asaas_payment_id', $paymentId)->first();

            if (!$preTenant) {
                Log::warning("⚠️ Pré-tenant não encontrado para payment_id: {$paymentId}");
                return response()->json(['message' => 'Pre-tenant not found'], 404);
            }

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
     */
    private function handlePaymentConfirmed(PreTenant $preTenant, array $payload): void
    {
        if ($preTenant->isPaid()) {
            Log::info("Pré-tenant {$preTenant->id} já está marcado como pago.");
            return;
        }

        try {
            $processor = new PreTenantProcessorService();
            $processor->processPaid($preTenant);

            Log::info("✅ Pré-tenant {$preTenant->id} processado com sucesso após pagamento confirmado.");

        } catch (\Throwable $e) {
            Log::error("Erro ao processar pré-tenant pago", [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
            ]);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'processing_error',
                'payload' => ['error' => $e->getMessage()],
            ]);
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
