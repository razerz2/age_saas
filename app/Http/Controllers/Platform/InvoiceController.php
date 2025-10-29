<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\Platform\Invoices;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use App\Http\Controllers\Platform\WhatsAppController;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * 📋 Lista todas as faturas
     */
    public function index()
    {
        $invoices = Invoices::with(['tenant', 'subscription'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('platform.invoices.index', compact('invoices'));
    }

    /**
     * ➕ Exibe formulário de criação
     */
    public function create()
    {
        $subscriptions = Subscription::with('tenant')->get();
        $tenants = Tenant::all();

        return view('platform.invoices.create', compact('subscriptions', 'tenants'));
    }

    /**
     * 💾 Cria nova fatura
     */
    public function store(InvoiceRequest $request)
    {
        $data = $request->validated();
        $invoice = Invoices::create($data);

        // 🔹 Tenta sincronizar com Asaas (não retorna, só executa)
        $this->syncWithAsaas($invoice, silent: true);

        return redirect()
            ->route('Platform.invoices.index')
            ->with('success', 'Fatura criada com sucesso!');
    }


    /**
     * ✏️ Edita fatura existente
     */
    public function edit(Invoices $invoice)
    {
        $invoice->load(['tenant', 'subscription.plan']);
        return view('platform.invoices.edit', compact('invoice'));
    }

    /**
     * 🔄 Atualiza fatura existente
     */
    public function update(InvoiceRequest $request, Invoices $invoice)
    {
        $data = $request->validated();
        $invoice->update($data);

        // 🔹 Sincroniza com Asaas após atualização
        $this->syncWithAsaas($invoice, silent: true);

        return redirect()
            ->route('Platform.invoices.index')
            ->with('success', 'Fatura atualizada com sucesso!');
    }


    /**
     * 👁️ Mostra detalhes da fatura
     */
    public function show(Invoices $invoice)
    {
        return view('platform.invoices.show', compact('invoice'));
    }

    /**
     * 🧹 Exclui fatura (local e Asaas)
     */
    public function destroy(Invoices $invoice)
    {
        try {
            // Guarda o ID antes de excluir
            $invoiceId = $invoice->id;

            if ($invoice->provider_id) {
                $asaas = new AsaasService();
                $asaas->deletePayment($invoice->provider_id);
            }

            $invoice->delete();

            Log::info("🗑️ Fatura {$invoiceId} excluída e sincronizada com o Asaas.", [
                'invoice_id' => $invoiceId,
                'provider_id' => $invoice->provider_id,
            ]);

            return redirect()
                ->route('Platform.invoices.index')
                ->with('success', 'Fatura removida com sucesso.');
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao excluir fatura {$invoice->id}: {$e->getMessage()}", [
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


    /**
     * 🔁 Sincroniza manualmente via botão “Tentar novamente”
     */
    public function syncManual(Invoices $invoice)
    {
        return $this->syncWithAsaas($invoice);
    }

    /**
     * ⚙️ Lógica centralizada de sincronização com o Asaas
     */
    public function syncWithAsaas(Invoices $invoice, bool $silent = false)
    {
        try {
            $asaas = new AsaasService();
            $tenant = $invoice->tenant;

            // 🔹 Define status inicial como pendente
            $invoice->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'pending',
                'asaas_last_error' => null,
                'asaas_last_sync_at' => now(),
            ]);

            /**
             * 🚫 BLOQUEIO 1 — Checkout hospedado (link /c/)
             */
            if ($invoice->payment_link && str_contains($invoice->payment_link, '/c/')) {
                Log::info("🚫 Fatura {$invoice->id} não sincronizada: checkout hospedado Asaas (/c/).");

                if (!$silent) {
                    return redirect()
                        ->back()
                        ->withErrors([
                            'general' => 'Esta fatura é um checkout hospedado do Asaas e não pode ser sincronizada diretamente. '
                                . 'Aguarde o pagamento para sincronizar automaticamente via webhook.'
                        ]);
                }

                return;
            }

            /**
             * 🚫 BLOQUEIO 2 — Fatura já paga ou finalizada
             */
            if (in_array($invoice->status, ['paid', 'received', 'confirmed', 'canceled'])) {
                Log::info("🚫 Fatura {$invoice->id} não sincronizada: status '{$invoice->status}' não permite atualização no Asaas.");

                if (!$silent) {
                    return redirect()
                        ->back()
                        ->withErrors([
                            'general' => 'Esta fatura já está finalizada (paga ou cancelada) e não pode ser sincronizada com o Asaas.'
                        ]);
                }

                return;
            }

            /**
             * 🔹 1. Garante cliente no Asaas
             */
            if (!$tenant->asaas_customer_id) {
                $searchResponse = $asaas->searchCustomer($tenant->email);

                if (!empty($searchResponse['data'][0]['id'])) {
                    $tenant->update(['asaas_customer_id' => $searchResponse['data'][0]['id']]);
                } else {
                    $createResponse = $asaas->createCustomer($tenant->toArray());

                    if (empty($createResponse) || !isset($createResponse['id'])) {
                        $invoice->update([
                            'asaas_synced' => false,
                            'asaas_sync_status' => 'pending',
                            'asaas_last_error' => 'Falha ao criar cliente no Asaas (resposta vazia ou inválida).',
                            'asaas_last_sync_at' => now(),
                        ]);

                        Log::warning("⚠️ Fatura {$invoice->id}: resposta inválida ao criar cliente no Asaas.");
                        if (!$silent) {
                            return redirect()->back()->withErrors([
                                'general' => 'Não foi possível sincronizar com o Asaas no momento. Tente novamente mais tarde.'
                            ]);
                        }
                        return;
                    }

                    $tenant->update(['asaas_customer_id' => $createResponse['id']]);
                }
            }

            /**
             * 🔹 2. Cria ou atualiza pagamento no Asaas
             */
            if ($invoice->provider_id) {
                // Atualiza pagamento existente
                $response = $asaas->updatePayment($invoice->provider_id, [
                    'value'       => $invoice->amount_cents / 100,
                    'dueDate'     => $invoice->due_date->format('Y-m-d'),
                    'description' => "Atualização da fatura {$invoice->id}",
                ]);
            } else {
                // Cria novo pagamento
                $response = $asaas->createPayment([
                    'customer'          => $tenant->asaas_customer_id,
                    'dueDate'           => $invoice->due_date->format('Y-m-d'),
                    'value'             => $invoice->amount_cents / 100,
                    'description'       => "Fatura {$invoice->id}",
                    'externalReference' => $invoice->id,
                ]);
            }

            /**
             * 🔹 3. Atualiza status conforme resposta
             */
            if (!empty($response) && isset($response['id'])) {
                $invoice->update([
                    'provider'           => 'asaas',
                    'provider_id'        => $response['id'],
                    'payment_link'       => $response['invoiceUrl'] ?? null,
                    'asaas_synced'       => true,
                    'asaas_sync_status'  => 'success',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error'   => null,
                ]);

                Log::info("✅ Fatura {$invoice->id} sincronizada com sucesso no Asaas.");
            } else {
                // 🔸 Se não houve erro de exceção mas resposta veio vazia ou com erro
                $invoice->update([
                    'asaas_synced'       => false,
                    'asaas_sync_status'  => 'pending',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error'   => 'Falha ao sincronizar: resposta vazia ou inválida do Asaas.',
                ]);

                Log::warning("⚠️ Fatura {$invoice->id}: resposta inválida do Asaas.", [
                    'response' => $response ?? 'empty',
                ]);

                if (!$silent) {
                    return redirect()->back()->withErrors([
                        'general' => $response['errors'][0]['description']
                            ?? 'Não foi possível confirmar a sincronização da fatura no Asaas.'
                    ]);
                }

                return;
            }

            /**
             * 🔹 4. Retorno final
             */
            if (!$silent) {
                return redirect()->back()->with('success', 'Fatura sincronizada com sucesso!');
            }
        } catch (\Throwable $e) {
            // 🔹 5. Erro real → marca como failed
            Log::error("❌ Erro ao sincronizar fatura {$invoice->id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);

            $invoice->update([
                'asaas_synced'       => false,
                'asaas_sync_status'  => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error'   => $e->getMessage(),
            ]);

            if (!$silent) {
                return redirect()->back()->withErrors([
                    'general' => 'Erro ao sincronizar com o Asaas. Verifique os logs para mais detalhes.'
                ]);
            }
        }
    }
}
