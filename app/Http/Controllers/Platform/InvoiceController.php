<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Invoices;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use App\Http\Requests\InvoiceRequest;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoices::with(['tenant', 'subscription'])->orderBy('created_at', 'desc')->get();
        return view('platform.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $subscriptions = Subscription::with('tenant')->get();
        $tenants = Tenant::all();
        return view('platform.invoices.create', compact('subscriptions', 'tenants'));
    }

    public function store(InvoiceRequest $request, AsaasService $asaas)
    {
        $data = $request->validated();

        try {
            $invoice = Invoices::create($data);
            $tenant = $invoice->tenant;

            // Garante cliente no Asaas
            if (!$tenant->asaas_customer_id) {
                $customerResponse = $asaas->createCustomer($tenant->toArray());
                if (!isset($customerResponse['id'])) {
                    throw new \Exception($customerResponse['errors'][0]['description'] ?? 'Falha ao criar cliente Asaas');
                }
                $tenant->update(['asaas_customer_id' => $customerResponse['id']]);
            }

            // Cria pagamento
            $paymentResponse = $asaas->createPayment([
                'customer' => $tenant->asaas_customer_id,
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'amount' => $invoice->amount_cents / 100,
                'description' => 'Fatura ' . $invoice->id,
                'external_reference' => $invoice->id,
            ]);

            if (isset($paymentResponse['id'])) {
                $invoice->update([
                    'asaas_payment_id' => $paymentResponse['id'],
                    'asaas_synced' => true,
                    'asaas_sync_status' => 'success',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error' => null,
                    'payment_link' => $paymentResponse['invoiceUrl'] ?? null,
                    'provider' => 'asaas',
                    'provider_id' => $paymentResponse['id'],
                ]);
            } else {
                $invoice->update([
                    'asaas_synced' => false,
                    'asaas_sync_status' => 'failed',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error' => $paymentResponse['errors'][0]['description'] ?? 'Erro desconhecido',
                ]);
            }

            return redirect()
                ->route('Platform.invoices.index')
                ->with('success', 'Fatura criada e sincronizada com o Asaas!');
        } catch (\Throwable $e) {
            $invoice->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Erro ao criar fatura: ' . $e->getMessage()]);
        }
    }

    public function edit(Invoices $invoice)
    {
        $invoice->load(['tenant', 'subscription.plan']);

        return view('platform.invoices.edit', compact('invoice'));
    }

    public function update(InvoiceRequest $request, Invoices $invoice, AsaasService $asaas)
    {
        $data = $request->validated();

        try {
            // 🔹 Atualiza os dados locais primeiro
            $invoice->update($data);

            // 🔹 Verifica se a fatura já tem pagamento no Asaas
            if ($invoice->asaas_payment_id) {

                // Atualiza o pagamento existente no Asaas
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'accept' => 'application/json',
                    'access_token' => config('services.asaas.api_key', env('ASAAS_API_KEY')),
                ])->put(config('services.asaas.base_url', env('ASAAS_BASE_URL')) . 'payments/' . $invoice->asaas_payment_id, [
                    'value' => $invoice->amount_cents / 100,
                    'dueDate' => $invoice->due_date->format('Y-m-d'),
                    'description' => 'Atualização da fatura ' . $invoice->id,
                ])->json();

                // 🔸 Atualiza auditoria conforme resposta
                if (isset($response['id'])) {
                    $invoice->update([
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => null,
                    ]);
                } else {
                    $invoice->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'failed',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => $response['errors'][0]['description'] ?? 'Erro desconhecido ao atualizar pagamento.',
                    ]);
                }
            } else {
                // 🔸 Se ainda não houver pagamento no Asaas, cria um novo
                $tenant = $invoice->tenant;

                if (!$tenant->asaas_customer_id) {
                    $customerResponse = $asaas->createCustomer($tenant->toArray());

                    if (!isset($customerResponse['id'])) {
                        throw new \Exception($customerResponse['errors'][0]['description'] ?? 'Falha ao criar cliente Asaas.');
                    }

                    $tenant->update([
                        'asaas_customer_id' => $customerResponse['id'],
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                    ]);
                }

                // Cria o pagamento
                $paymentResponse = $asaas->createPayment([
                    'customer' => $tenant->asaas_customer_id,
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'amount' => $invoice->amount_cents / 100,
                    'description' => 'Fatura ' . $invoice->id,
                    'external_reference' => $invoice->id,
                ]);

                if (isset($paymentResponse['id'])) {
                    $invoice->update([
                        'asaas_payment_id' => $paymentResponse['id'],
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => null,
                        'payment_link' => $paymentResponse['invoiceUrl'] ?? null,
                        'provider' => 'asaas',
                        'provider_id' => $paymentResponse['id'],
                    ]);
                } else {
                    $invoice->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'failed',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => $paymentResponse['errors'][0]['description'] ?? 'Erro desconhecido ao criar pagamento.',
                    ]);
                }
            }

            return redirect()
                ->route('Platform.invoices.index')
                ->with('success', 'Fatura atualizada e sincronizada com o Asaas!');
        } catch (\Throwable $e) {
            // 🔻 Se qualquer erro acontecer
            $invoice->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);

            Log::error('💥 Erro ao atualizar fatura Asaas', [
                'invoice_id' => $invoice->id,
                'erro' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'general' => 'Erro ao atualizar fatura: ' . $e->getMessage(),
            ]);
        }
    }


    public function show(Invoices $invoice)
    {
        return view('platform.invoices.show', compact('invoice'));
    }

    public function destroy(Invoices $invoice, AsaasService $asaas)
    {
        try {
            // 🔹 Se a fatura está vinculada a um pagamento no Asaas, tenta cancelar/excluir lá primeiro
            if ($invoice->asaas_payment_id) {
                $asaasResponse = $asaas->deletePayment($invoice->asaas_payment_id);

                if (!isset($asaasResponse['error'])) {
                    // ✅ Sincronização bem-sucedida
                    $invoice->update([
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'canceled',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => null,
                    ]);
                } else {
                    // ⚠️ Falha no Asaas (ainda assim vamos excluir localmente)
                    $invoice->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'failed',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => $asaasResponse['error'] ??
                            ($asaasResponse['errors'][0]['description'] ?? 'Erro desconhecido ao excluir pagamento no Asaas.'),
                    ]);
                }
            }

            // 🔸 Remove a fatura localmente
            $invoice->delete();

            return redirect()
                ->route('Platform.invoices.index')
                ->with('success', 'Fatura excluída e sincronizada com o Asaas!');
        } catch (\Throwable $e) {
            // 🔻 Em caso de erro inesperado
            $invoice->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);

            Log::error('💥 Erro ao excluir fatura Asaas', [
                'invoice_id' => $invoice->id,
                'erro' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'general' => 'Erro ao excluir fatura: ' . $e->getMessage(),
            ]);
        }
    }
}
