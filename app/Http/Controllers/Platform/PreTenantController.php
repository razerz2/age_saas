<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\PreTenant;
use App\Models\Platform\PreTenantLog;
use App\Models\Platform\Invoices;
use App\Services\Platform\PreTenantProcessorService;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PreTenantController extends Controller
{
    /**
     * Lista pré-cadastros com filtros
     */
    public function index(Request $request)
    {
        $query = PreTenant::with(['plan', 'pais', 'estado', 'cidade'])
            ->orderBy('created_at', 'desc');

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por email
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $preTenants = $query->get();

        return view('platform.pre_tenants.index', compact('preTenants'));
    }

    /**
     * Visualiza detalhes do pré-cadastro
     */
    public function show(PreTenant $preTenant)
    {
        $preTenant->load(['plan', 'pais', 'estado', 'cidade', 'logs']);

        return view('platform.pre_tenants.show', compact('preTenant'));
    }

    /**
     * Aprova manualmente (força criação do tenant)
     */
    public function approve(PreTenant $preTenant)
    {
        try {
            if ($preTenant->isPaid()) {
                return redirect()
                    ->route('Platform.pre_tenants.show', $preTenant->id)
                    ->with('warning', 'Este pré-cadastro já foi processado.');
            }

            $processor = new PreTenantProcessorService();
            $processor->processPaid($preTenant);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'manual_approval',
                'payload' => ['message' => 'Aprovado manualmente pelo administrador'],
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->with('success', 'Pré-cadastro aprovado e tenant criado com sucesso!');

        } catch (\Throwable $e) {
            Log::error('Erro ao aprovar pré-cadastro manualmente', [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->withErrors(['error' => 'Erro ao processar pré-cadastro: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancela pré-cadastro
     */
    public function cancel(PreTenant $preTenant)
    {
        try {
            $preTenant->markAsCanceled();

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'manual_cancellation',
                'payload' => ['message' => 'Cancelado manualmente pelo administrador'],
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->with('success', 'Pré-cadastro cancelado com sucesso!');

        } catch (\Throwable $e) {
            Log::error('Erro ao cancelar pré-cadastro', [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->withErrors(['error' => 'Erro ao cancelar pré-cadastro: ' . $e->getMessage()]);
        }
    }

    /**
     * Confirma pagamento manualmente (fallback caso webhook não chegue)
     * Segue a mesma rotina do webhook: cria database da tenant, cria assinatura vinculando tenant e plano
     */
    public function confirmPayment(PreTenant $preTenant)
    {
        try {
            // Verifica se já está pago e processado
            if ($preTenant->isPaid()) {
                $tenantCreatedLog = $preTenant->logs()->where('event', 'tenant_created')->first();
                if ($tenantCreatedLog) {
                    return redirect()
                        ->route('Platform.pre_tenants.show', $preTenant->id)
                        ->with('warning', 'Este pré-cadastro já foi processado e o tenant já foi criado.');
                }
            }

            // Verifica se tem plano associado
            if (!$preTenant->plan_id) {
                return redirect()
                    ->route('Platform.pre_tenants.show', $preTenant->id)
                    ->withErrors(['error' => 'Este pré-cadastro não possui um plano associado. Não é possível confirmar o pagamento.']);
            }

            // Processa o pagamento seguindo a mesma rotina do webhook
            $processor = new PreTenantProcessorService();
            
            // Simula payload do webhook para manter compatibilidade
            // O formato de data deve ser compatível com o que o Asaas envia (ISO 8601)
            $now = now();
            $webhookPayload = [
                'payment' => [
                    'id' => $preTenant->asaas_payment_id,
                    'confirmedDate' => $now->toIso8601String(),
                    'paymentDate' => $now->toIso8601String(),
                    'value' => ($preTenant->plan->price_cents ?? 0) / 100,
                    'billingType' => 'PIX', // Default, pode ser ajustado se necessário
                ],
                'customer' => [
                    'id' => $preTenant->asaas_customer_id,
                ],
            ];

            $processor->processPaid($preTenant, $webhookPayload);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'manual_payment_confirmation',
                'payload' => [
                    'message' => 'Pagamento confirmado manualmente pelo administrador',
                    'reason' => 'Webhook não recebido ou falha na comunicação com Asaas',
                ],
            ]);

            Log::info("✅ Pagamento confirmado manualmente para pré-tenant {$preTenant->id}", [
                'pre_tenant_id' => $preTenant->id,
                'admin_action' => true,
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->with('success', 'Pagamento confirmado com sucesso! Tenant criado e assinatura vinculada ao plano escolhido.');

        } catch (\Throwable $e) {
            Log::error('Erro ao confirmar pagamento manualmente', [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'manual_payment_confirmation_error',
                'payload' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->withErrors(['error' => 'Erro ao confirmar pagamento: ' . $e->getMessage()]);
        }
    }

    /**
     * Exclui pré-cadastro
     */
    public function destroy(PreTenant $preTenant)
    {
        try {
            // Verificar se pode ser excluído
            if (!$preTenant->canBeDeleted()) {
                return redirect()
                    ->route('Platform.pre_tenants.show', $preTenant->id)
                    ->with('error', 'Não é possível excluir este pré-cadastro pois ele já foi processado e um tenant foi criado. Se necessário, exclua o tenant primeiro.');
            }

            $asaas = new AsaasService();

            // 1. Buscar e excluir faturas relacionadas ao pré-cadastro
            // Faturas podem estar relacionadas através do asaas_payment_id (Payment Link ID)
            // ou através de pagamentos gerados pelo Payment Link
            $relatedInvoices = Invoices::where(function ($query) use ($preTenant) {
                if ($preTenant->asaas_payment_id) {
                    $query->where('provider_id', $preTenant->asaas_payment_id)
                          ->orWhere('asaas_payment_id', $preTenant->asaas_payment_id);
                }
            })->get();

            $deletedInvoicesCount = 0;
            foreach ($relatedInvoices as $invoice) {
                try {
                    // Excluir no Asaas se tiver provider_id ou asaas_payment_id
                    $paymentIdToDelete = $invoice->provider_id ?? $invoice->asaas_payment_id;
                    if ($paymentIdToDelete && $paymentIdToDelete !== $preTenant->asaas_payment_id) {
                        // Só exclui se for um payment diferente do Payment Link
                        $asaas->deletePayment($paymentIdToDelete);
                    }

                    // Excluir do sistema
                    $invoice->delete();
                    $deletedInvoicesCount++;

                    Log::info("✅ Fatura {$invoice->id} excluída (relacionada ao pré-cadastro {$preTenant->id})");
                } catch (\Throwable $e) {
                    Log::warning("⚠️ Erro ao excluir fatura {$invoice->id}: " . $e->getMessage());
                    // Continua mesmo se houver erro em uma fatura
                }
            }

            // 2. Buscar e excluir pagamentos (payments) relacionados ao pré-cadastro no Asaas
            // Pagamentos podem ter sido gerados pelo Payment Link e ter externalReference = pre_tenant_id
            $deletedPaymentsCount = 0;
            if ($preTenant->asaas_customer_id) {
                try {
                    // Listar pagamentos do cliente no Asaas (filtrado por customer)
                    $payments = $asaas->listPayments(1, 100, $preTenant->asaas_customer_id);
                    if (isset($payments['data']) && is_array($payments['data'])) {
                        foreach ($payments['data'] as $payment) {
                            // Verificar se o pagamento está relacionado ao pré-cadastro
                            // através do externalReference (que é o pre_tenant_id)
                            $isRelated = false;
                            
                            // Verifica se o externalReference é o ID do pré-cadastro
                            if (isset($payment['externalReference']) && $payment['externalReference'] === $preTenant->id) {
                                $isRelated = true;
                            }
                            
                            if ($isRelated && isset($payment['id'])) {
                                try {
                                    $asaas->deletePayment($payment['id']);
                                    $deletedPaymentsCount++;
                                    Log::info("✅ Pagamento {$payment['id']} excluído no Asaas (relacionado ao pré-cadastro {$preTenant->id})");
                                } catch (\Throwable $e) {
                                    Log::warning("⚠️ Erro ao excluir pagamento {$payment['id']}: " . $e->getMessage());
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("⚠️ Erro ao buscar pagamentos relacionados: " . $e->getMessage());
                    // Continua mesmo se houver erro ao buscar pagamentos
                }
            }

            // 3. Excluir Payment Link no Asaas (se existir)
            if ($preTenant->asaas_payment_id) {
                try {
                    $asaas->deletePaymentLink($preTenant->asaas_payment_id);
                    Log::info("✅ Payment Link {$preTenant->asaas_payment_id} excluído no Asaas");
                } catch (\Throwable $e) {
                    Log::warning("⚠️ Erro ao excluir Payment Link {$preTenant->asaas_payment_id}: " . $e->getMessage());
                    // Continua mesmo se houver erro ao excluir o Payment Link
                }
            }

            // 4. Excluir cliente no Asaas (se não houver tenant criado)
            if ($preTenant->asaas_customer_id) {
                try {
                    $asaas->deleteCustomer($preTenant->asaas_customer_id);
                    Log::info("✅ Cliente {$preTenant->asaas_customer_id} excluído no Asaas");
                } catch (\Throwable $e) {
                    Log::warning("⚠️ Erro ao excluir cliente {$preTenant->asaas_customer_id}: " . $e->getMessage());
                    // Continua mesmo se houver erro ao excluir o cliente
                }
            }

            // 5. Criar log antes de excluir
            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'deleted',
                'payload' => [
                    'message' => 'Pré-cadastro excluído pelo administrador',
                    'invoices_deleted' => $deletedInvoicesCount,
                    'payments_deleted' => $deletedPaymentsCount,
                    'payment_link_deleted' => !empty($preTenant->asaas_payment_id),
                    'customer_deleted' => !empty($preTenant->asaas_customer_id),
                ],
            ]);

            // 6. Excluir logs relacionados primeiro (devido à foreign key)
            PreTenantLog::where('pre_tenant_id', $preTenant->id)->delete();

            // 7. Excluir o pré-cadastro
            $preTenant->delete();

            $successMessage = 'Pré-cadastro excluído com sucesso!';
            $details = [];
            if ($deletedInvoicesCount > 0) {
                $details[] = "{$deletedInvoicesCount} fatura(s)";
            }
            if ($deletedPaymentsCount > 0) {
                $details[] = "{$deletedPaymentsCount} pagamento(s)";
            }
            if (!empty($details)) {
                $successMessage .= " " . implode(', ', $details) . " relacionado(s) também foram excluído(s).";
            }

            return redirect()
                ->route('Platform.pre_tenants.index')
                ->with('success', $successMessage);

        } catch (\Throwable $e) {
            Log::error('Erro ao excluir pré-cadastro', [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->withErrors(['error' => 'Erro ao excluir pré-cadastro: ' . $e->getMessage()]);
        }
    }
}
