<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\InvoiceRequest;
use App\Models\Platform\Invoices;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use App\Services\Platform\InvoiceAsaasSyncService;
use App\Services\Platform\WhatsAppOfficialMessageService;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceAsaasSyncService $invoiceAsaasSyncService,
        private readonly WhatsAppOfficialMessageService $officialWhatsApp
    ) {
    }

    public function index()
    {
        $invoices = Invoices::with(['tenant', 'subscription'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('platform.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $subscriptions = Subscription::with('tenant')->get();
        $tenants = Tenant::all();

        return view('platform.invoices.create', compact('subscriptions', 'tenants'));
    }

    public function store(InvoiceRequest $request)
    {
        $data = $request->validated();

        $subscription = null;
        if (! empty($data['subscription_id'])) {
            $subscription = Subscription::with('plan')->find($data['subscription_id']);
        }

        if ($subscription?->plan?->isTest() || (bool) $subscription?->is_trial) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'Plano de teste/trial nao gera fatura nem cobranca.']);
        }

        $invoice = Invoices::create($data);

        try {
            $invoice = $this->invoiceAsaasSyncService->syncInvoice($invoice);
        } catch (\Throwable $e) {
            Log::error("Erro ao sincronizar fatura {$invoice->id} no store: {$e->getMessage()}");
        }

        $this->sendInvoiceNotificationIfPossible($invoice->fresh(['tenant']));

        return redirect()
            ->route('Platform.invoices.index')
            ->with('success', 'Fatura criada com sucesso!');
    }

    public function edit(Invoices $invoice)
    {
        $invoice->load(['tenant', 'subscription.plan']);
        return view('platform.invoices.edit', compact('invoice'));
    }

    public function update(InvoiceRequest $request, Invoices $invoice)
    {
        $data = $request->validated();
        $invoice->update($data);

        try {
            $invoice = $this->invoiceAsaasSyncService->syncInvoice($invoice->fresh(['tenant', 'subscription.plan']));
        } catch (\Throwable $e) {
            Log::error("Erro ao sincronizar fatura {$invoice->id} no update: {$e->getMessage()}");
        }

        return redirect()
            ->route('Platform.invoices.index')
            ->with('success', 'Fatura atualizada com sucesso!');
    }

    public function show(Invoices $invoice)
    {
        $invoice->load(['tenant', 'subscription.plan']);
        return view('platform.invoices.show', compact('invoice'));
    }

    public function destroy(Invoices $invoice)
    {
        try {
            $invoiceId = $invoice->id;

            if ($invoice->provider_id) {
                $asaas = new AsaasService();
                $asaas->deletePayment($invoice->provider_id);
            }

            $invoice->delete();

            Log::info("Fatura {$invoiceId} excluida e sincronizada com o Asaas.", [
                'invoice_id' => $invoiceId,
                'provider_id' => $invoice->provider_id,
            ]);

            return redirect()
                ->route('Platform.invoices.index')
                ->with('success', 'Fatura removida com sucesso.');
        } catch (\Throwable $e) {
            Log::error("Erro ao excluir fatura {$invoice->id}: {$e->getMessage()}", [
                'invoice_id' => $invoice->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            $invoice->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'general' => 'Erro ao excluir fatura: ' . $e->getMessage(),
            ]);
        }
    }

    public function syncManual(Invoices $invoice)
    {
        return $this->syncWithAsaas($invoice);
    }

    public function syncWithAsaas(Invoices $invoice, bool $silent = false)
    {
        try {
            $invoice = $this->invoiceAsaasSyncService->syncInvoice($invoice);

            if ($silent) {
                return $invoice;
            }

            return redirect()->back()->with('success', 'Fatura sincronizada com sucesso no Asaas.');
        } catch (\Throwable $e) {
            if ($silent) {
                return $invoice;
            }

            return redirect()->back()->withErrors([
                'general' => 'Erro na sincronizacao com Asaas: ' . $e->getMessage(),
            ]);
        }
    }

    public function refreshAsaasStatus(Invoices $invoice)
    {
        try {
            $this->invoiceAsaasSyncService->refreshStatus($invoice);

            return redirect()->back()->with('success', 'Status da fatura atualizado no Asaas.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors([
                'general' => 'Falha ao consultar status no Asaas: ' . $e->getMessage(),
            ]);
        }
    }

    public function recreateAsaasPayment(Invoices $invoice)
    {
        try {
            $invoice = $this->invoiceAsaasSyncService->recreatePayment($invoice);

            return redirect()->back()->with('success', 'Cobranca recriada com sucesso no Asaas.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors([
                'general' => 'Falha ao recriar cobranca no Asaas: ' . $e->getMessage(),
            ]);
        }
    }

    public function resendPaymentLink(Invoices $invoice)
    {
        $invoice->loadMissing(['tenant']);

        if (! $this->hasRealPaymentLink($invoice->payment_link)) {
            return redirect()->back()->withErrors([
                'general' => 'Fatura sem link de pagamento valido para reenvio.',
            ]);
        }

        $sent = $this->sendInvoiceNotificationIfPossible($invoice);

        if (! $sent) {
            return redirect()->back()->withErrors([
                'general' => 'Falha ao reenviar link de pagamento (template ausente/inapto ou telefone invalido).',
            ]);
        }

        return redirect()->back()->with('success', 'Link de pagamento reenviado com sucesso.');
    }

    private function sendInvoiceNotificationIfPossible(Invoices $invoice): bool
    {
        $tenant = $invoice->tenant;

        if (! $tenant || empty($tenant->phone) || ! $this->hasRealPaymentLink($invoice->payment_link)) {
            return false;
        }

        return (bool) $this->officialWhatsApp->sendByKey(
            'invoice.created',
            $tenant->phone,
            [
                'customer_name' => $tenant->trade_name,
                'tenant_name' => $tenant->trade_name,
                'invoice_amount' => 'R$ ' . number_format($invoice->amount_cents / 100, 2, ',', '.'),
                'due_date' => $invoice->due_date?->format('d/m/Y') ?? now()->format('d/m/Y'),
                'payment_link' => trim((string) $invoice->payment_link),
            ],
            [
                'controller' => static::class,
                'invoice_id' => (string) $invoice->id,
                'tenant_id' => (string) $tenant->id,
                'event' => 'invoice.created',
            ]
        );
    }

    private function hasRealPaymentLink(?string $link): bool
    {
        $value = trim((string) $link);

        if ($value === '') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}

