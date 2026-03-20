<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Platform\WhatsAppController;
use App\Http\Requests\Platform\InvoiceRequest;
use App\Models\Platform\Invoices;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
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

        if ($subscription?->plan?->isTest()) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'Plano de teste nao gera fatura nem cobranca.']);
        }

        $invoice = Invoices::create($data);
        app(WhatsAppController::class)->sendInvoiceNotification($invoice);
        $this->syncWithAsaas($invoice, silent: true);

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

        $this->syncWithAsaas($invoice, silent: true);

        return redirect()
            ->route('Platform.invoices.index')
            ->with('success', 'Fatura atualizada com sucesso!');
    }

    public function show(Invoices $invoice)
    {
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
            $invoice->loadMissing(['subscription.plan', 'tenant']);

            if ($invoice->subscription?->plan?->isTest()) {
                $invoice->update([
                    'asaas_synced' => false,
                    'asaas_sync_status' => 'skipped',
                    'asaas_last_error' => null,
                    'asaas_last_sync_at' => now(),
                ]);

                Log::info("Fatura {$invoice->id} ignorada: plano de teste nao participa de cobranca.");

                if ($silent) {
                    return;
                }

                return redirect()->back()->with('warning', 'Plano de teste: esta fatura nao e sincronizada com o Asaas.');
            }

            $asaas = new AsaasService();
            $tenant = $invoice->tenant;

            $invoice->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'pending',
                'asaas_last_error' => null,
                'asaas_last_sync_at' => now(),
            ]);

            if ($invoice->payment_link && str_contains($invoice->payment_link, '/c/')) {
                Log::info("Fatura {$invoice->id} nao sincronizada: checkout hospedado Asaas (/c/).");

                if (! $silent) {
                    return redirect()
                        ->back()
                        ->withErrors([
                            'general' => 'Esta fatura e um checkout hospedado do Asaas e nao pode ser sincronizada diretamente. Aguarde o pagamento para sincronizar automaticamente via webhook.',
                        ]);
                }

                return;
            }

            if (in_array($invoice->status, ['paid', 'received', 'confirmed', 'canceled'], true)) {
                Log::info("Fatura {$invoice->id} nao sincronizada: status '{$invoice->status}' nao permite atualizacao no Asaas.");

                if (! $silent) {
                    return redirect()
                        ->back()
                        ->withErrors([
                            'general' => 'Esta fatura ja esta finalizada (paga ou cancelada) e nao pode ser sincronizada com o Asaas.',
                        ]);
                }

                return;
            }

            if (! $tenant->asaas_customer_id) {
                $searchResponse = $asaas->searchCustomer($tenant->email);

                if (! empty($searchResponse['data'][0]['id'])) {
                    $tenant->update(['asaas_customer_id' => $searchResponse['data'][0]['id']]);
                } else {
                    $createResponse = $asaas->createCustomer($tenant->toArray());

                    if (empty($createResponse) || ! isset($createResponse['id'])) {
                        $invoice->update([
                            'asaas_synced' => false,
                            'asaas_sync_status' => 'pending',
                            'asaas_last_error' => 'Falha ao criar cliente no Asaas (resposta vazia ou invalida).',
                            'asaas_last_sync_at' => now(),
                        ]);

                        Log::warning("Fatura {$invoice->id}: resposta invalida ao criar cliente no Asaas.");
                        if (! $silent) {
                            return redirect()->back()->withErrors([
                                'general' => 'Nao foi possivel sincronizar com o Asaas no momento. Tente novamente mais tarde.',
                            ]);
                        }
                        return;
                    }

                    $tenant->update(['asaas_customer_id' => $createResponse['id']]);
                }
            }

            if ($invoice->provider_id) {
                $response = $asaas->updatePayment($invoice->provider_id, [
                    'value' => $invoice->amount_cents / 100,
                    'dueDate' => $invoice->due_date->format('Y-m-d'),
                    'description' => "Atualizacao da fatura {$invoice->id}",
                ]);
            } else {
                $response = $asaas->createPayment([
                    'customer' => $tenant->asaas_customer_id,
                    'dueDate' => $invoice->due_date->format('Y-m-d'),
                    'value' => $invoice->amount_cents / 100,
                    'description' => "Fatura {$invoice->id}",
                    'externalReference' => $invoice->id,
                ]);
            }

            if (! empty($response) && isset($response['id'])) {
                $invoice->update([
                    'provider' => 'asaas',
                    'provider_id' => $response['id'],
                    'payment_link' => $response['invoiceUrl'] ?? null,
                    'asaas_synced' => true,
                    'asaas_sync_status' => 'success',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error' => null,
                ]);

                Log::info("Fatura {$invoice->id} sincronizada com sucesso no Asaas.");
            } else {
                $invoice->update([
                    'asaas_synced' => false,
                    'asaas_sync_status' => 'pending',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error' => 'Falha ao sincronizar: resposta vazia ou invalida do Asaas.',
                ]);

                Log::warning("Fatura {$invoice->id}: resposta invalida do Asaas.", [
                    'response' => $response ?? 'empty',
                ]);

                if (! $silent) {
                    return redirect()->back()->withErrors([
                        'general' => $response['errors'][0]['description']
                            ?? 'Nao foi possivel confirmar a sincronizacao da fatura no Asaas.',
                    ]);
                }

                return;
            }

            if (! $silent) {
                return redirect()->back()->with('success', 'Fatura sincronizada com sucesso!');
            }
        } catch (\Throwable $e) {
            Log::error("Erro ao sincronizar fatura {$invoice->id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);

            $invoice->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);

            if (! $silent) {
                return redirect()->back()->withErrors([
                    'general' => 'Erro ao sincronizar com o Asaas: ' . $e->getMessage(),
                ]);
            }
        }
    }
}
