<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Invoices;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsapp;
    protected WhatsAppOfficialMessageService $officialWhatsApp;

    public function __construct(WhatsAppService $whatsapp, WhatsAppOfficialMessageService $officialWhatsApp)
    {
        $this->whatsapp = $whatsapp;
        $this->officialWhatsApp = $officialWhatsApp;
    }

    /**
     * Envia mensagem simples
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $success = $this->whatsapp->sendMessage($request->phone, $request->message);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Mensagem enviada com sucesso!' : 'Falha ao enviar mensagem.'
        ], $success ? 200 : 500);
    }

    /**
     * Envia notificação de nova fatura
     */
    public function sendInvoiceNotification(Invoices $invoice)
    {
        try {
            $tenant = $invoice->tenant;
            if (!$tenant || !$tenant->phone) {
                return response()->json(['error' => 'Tenant sem telefone cadastrado.'], 400);
            }

            $ok = $this->officialWhatsApp->sendByKey(
                'invoice.created',
                $tenant->phone,
                [
                    'customer_name' => $tenant->trade_name,
                    'tenant_name' => $tenant->trade_name,
                    'invoice_amount' => 'R$ ' . number_format($invoice->amount_cents / 100, 2, ',', '.'),
                    'due_date' => $invoice->due_date?->format('d/m/Y') ?? now()->format('d/m/Y'),
                    'payment_link' => trim((string) ($invoice->payment_link ?: 'https://app.allsync.com.br/faturas')),
                ],
                [
                    'controller' => static::class,
                    'invoice_id' => (string) $invoice->id,
                    'tenant_id' => (string) $tenant->id,
                    'event' => 'invoice.created',
                ]
            );

            return response()->json([
                'success' => $ok,
                'message' => $ok
                    ? 'Notificação enviada com sucesso!'
                    : 'Falha ao enviar notificação oficial (template ausente/inapto ou erro no provider).'
            ], $ok ? 200 : 422);
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar notificação WhatsApp', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro interno.'], 500);
        }
    }
}
