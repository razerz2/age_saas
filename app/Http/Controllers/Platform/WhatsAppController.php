<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Invoices;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
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
            if (!$tenant || !$tenant->telefone) {
                return response()->json(['error' => 'Tenant sem telefone cadastrado.'], 400);
            }

            $msg = "💰 *Nova fatura disponível!*\n\n"
                . "Cliente: {$tenant->name}\n"
                . "Valor: R$ " . number_format($invoice->amount / 100, 2, ',', '.') . "\n"
                . "Vencimento: " . $invoice->due_date->format('d/m/Y') . "\n\n"
                . "💳 Link para pagamento:\n{$invoice->payment_url}\n\n"
                . "Agradecemos pela preferência 🙏";

            $ok = $this->whatsapp->sendMessage($tenant->telefone, $msg);

            return response()->json([
                'success' => $ok,
                'message' => $ok ? 'Notificação enviada com sucesso!' : 'Falha ao enviar notificação via WhatsApp.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar notificação WhatsApp', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro interno.'], 500);
        }
    }
}
