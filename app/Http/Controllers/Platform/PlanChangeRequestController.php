<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\PlanChangeRequest;
use App\Models\Platform\Subscription;
use App\Models\Platform\Plan;
use App\Models\Platform\Invoices;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PlanChangeRequestController extends Controller
{
    /**
     * Lista todas as solicitações de mudança de plano
     */
    public function index()
    {
        $requests = PlanChangeRequest::with([
            'tenant',
            'subscription',
            'currentPlan',
            'requestedPlan',
            'reviewer'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return view('platform.plan-change-requests.index', compact('requests'));
    }

    /**
     * Exibe detalhes de uma solicitação
     */
    public function show(PlanChangeRequest $planChangeRequest)
    {
        $planChangeRequest->load([
            'tenant',
            'subscription.plan',
            'currentPlan',
            'requestedPlan',
            'reviewer'
        ]);

        return view('platform.plan-change-requests.show', compact('planChangeRequest'));
    }

    /**
     * Aprova uma solicitação de mudança de plano
     */
    public function approve(Request $request, PlanChangeRequest $planChangeRequest)
    {
        if (!$planChangeRequest->canBeReviewed()) {
            return back()->withErrors(['error' => 'Esta solicitação não pode ser revisada.']);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Atualizar solicitação
            $planChangeRequest->update([
                'status' => 'approved',
                'admin_notes' => $validated['admin_notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Atualizar assinatura
            $subscription = $planChangeRequest->subscription->fresh();
            $newPlan = Plan::findOrFail($planChangeRequest->requested_plan_id);
            $currentPaymentMethod = $subscription->payment_method;
            $requestedPaymentMethod = $planChangeRequest->requested_payment_method;
            $paymentMethodChanged = $currentPaymentMethod !== $requestedPaymentMethod;
            
            // Atualizar plano e forma de pagamento (se mudou)
            $subscriptionUpdate = [
                'plan_id' => $planChangeRequest->requested_plan_id,
            ];
            
            if ($paymentMethodChanged) {
                $subscriptionUpdate['payment_method'] = $requestedPaymentMethod;
            }
            
            $subscription->update($subscriptionUpdate);

            // Recarregar assinatura com novo plano
            $subscription = $subscription->fresh(['plan', 'tenant']);

            // Aplicar novas regras de acesso IMEDIATAMENTE
            $subscriptionController = new SubscriptionController();
            $reflection = new \ReflectionClass($subscriptionController);
            $method = $reflection->getMethod('applyAccessRulesToTenant');
            $method->setAccessible(true);
            $method->invoke($subscriptionController, $subscription);

            // Atualizar TODAS as faturas pendentes (pending ou overdue) para o novo valor do plano
            $pendingInvoices = Invoices::where('subscription_id', $subscription->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->get();

            $asaas = new AsaasService();
            foreach ($pendingInvoices as $invoice) {
                $oldAmount = $invoice->amount_cents;
                $newAmount = $newPlan->price_cents;

                // Atualizar valor da fatura
                $invoice->update([
                    'amount_cents' => $newAmount,
                    'asaas_sync_status' => 'pending',
                    'asaas_last_sync_at' => now(),
                ]);

                Log::info('Fatura pendente atualizada com novo valor do plano', [
                    'invoice_id' => $invoice->id,
                    'old_amount' => $oldAmount,
                    'new_amount' => $newAmount,
                    'plan_name' => $newPlan->name,
                ]);

                // Se a fatura tem provider_id (Asaas), atualizar no Asaas também
                if ($invoice->provider_id && $invoice->provider === 'asaas') {
                    try {
                        // Atualizar pagamento no Asaas
                        $updateData = [
                            'value' => $newAmount / 100, // Converter centavos para reais
                        ];

                        // Se tiver due_date, manter
                        if ($invoice->due_date) {
                            $updateData['dueDate'] = $invoice->due_date->toDateString();
                        }

                        $asaasResponse = $asaas->updatePayment($invoice->provider_id, $updateData);

                        // Verificar se houve erro na resposta
                        if (isset($asaasResponse['error'])) {
                            Log::error('Erro do Asaas ao atualizar fatura', [
                                'invoice_id' => $invoice->id,
                                'error' => $asaasResponse['error'],
                            ]);
                            $invoice->update([
                                'asaas_synced' => false,
                                'asaas_sync_status' => 'failed',
                                'asaas_last_error' => $asaasResponse['error'],
                            ]);
                        } elseif (!empty($asaasResponse['id'])) {
                            // Atualizar payment_link se retornado (pode ser invoiceUrl ou bankSlipUrl)
                            $paymentLink = $asaasResponse['invoiceUrl'] ?? $asaasResponse['bankSlipUrl'] ?? null;
                            
                            $updateData = [
                                'asaas_synced' => true,
                                'asaas_sync_status' => 'success',
                                'asaas_last_error' => null,
                            ];
                            
                            if ($paymentLink) {
                                $updateData['payment_link'] = $paymentLink;
                            }
                            
                            $invoice->update($updateData);

                            Log::info('Fatura atualizada no Asaas com sucesso', [
                                'invoice_id' => $invoice->id,
                                'asaas_payment_id' => $invoice->provider_id,
                                'new_amount' => $newAmount,
                            ]);
                        } else {
                            Log::warning('Resposta inválida do Asaas ao atualizar fatura', [
                                'invoice_id' => $invoice->id,
                                'response' => $asaasResponse,
                            ]);
                            $invoice->update([
                                'asaas_synced' => false,
                                'asaas_sync_status' => 'failed',
                                'asaas_last_error' => 'Resposta inválida do Asaas',
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Erro ao atualizar fatura no Asaas', [
                            'invoice_id' => $invoice->id,
                            'error' => $e->getMessage(),
                        ]);
                        $invoice->update([
                            'asaas_synced' => false,
                            'asaas_sync_status' => 'failed',
                            'asaas_last_error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Se a forma de pagamento mudou, processar a mudança
            if ($paymentMethodChanged) {
                $tenant = $subscription->tenant;
                
                // Se mudou de PIX para Cartão de Crédito
                if ($currentPaymentMethod === 'PIX' && $requestedPaymentMethod === 'CREDIT_CARD') {
                    // Se tinha assinatura no Asaas (PIX), pode precisar cancelar
                    // Criar nova assinatura com cartão
                    if ($subscription->asaas_subscription_id) {
                        // Cancelar assinatura antiga do Asaas se existir
                        try {
                            $asaas->deleteSubscription($subscription->asaas_subscription_id);
                            Log::info('Assinatura PIX cancelada no Asaas devido à mudança para cartão', [
                                'subscription_id' => $subscription->id,
                            ]);
                        } catch (\Exception $e) {
                            Log::warning('Erro ao cancelar assinatura PIX no Asaas', [
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                    
                    // Criar nova assinatura com cartão de crédito
                    try {
                        $subscriptionResponse = $asaas->createSubscription([
                            'customer' => $tenant->asaas_customer_id,
                            'value' => $newPlan->price_cents / 100,
                            'cycle' => 'MONTHLY',
                            'nextDueDate' => now()->addMonth()->toDateString(),
                            'description' => "Assinatura do plano {$newPlan->name}",
                        ]);
                        
                        if (!empty($subscriptionResponse['subscription']['id'])) {
                            $subscription->update([
                                'asaas_subscription_id' => $subscriptionResponse['subscription']['id'],
                                'auto_renew' => true,
                            ]);
                            
                            Log::info('Nova assinatura com cartão criada no Asaas', [
                                'subscription_id' => $subscription->id,
                                'asaas_subscription_id' => $subscriptionResponse['subscription']['id'],
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Erro ao criar assinatura com cartão no Asaas', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                // Se mudou de Cartão de Crédito para PIX
                elseif ($currentPaymentMethod === 'CREDIT_CARD' && $requestedPaymentMethod === 'PIX') {
                    // Cancelar assinatura do Asaas se existir
                    if ($subscription->asaas_subscription_id) {
                        try {
                            $asaas->deleteSubscription($subscription->asaas_subscription_id);
                            $subscription->update([
                                'asaas_subscription_id' => null,
                                'auto_renew' => true,
                            ]);
                            
                            Log::info('Assinatura com cartão cancelada no Asaas devido à mudança para PIX', [
                                'subscription_id' => $subscription->id,
                            ]);
                        } catch (\Exception $e) {
                            Log::warning('Erro ao cancelar assinatura com cartão no Asaas', [
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                    
                    // Gerar link de pagamento PIX para próxima fatura
                    try {
                        // Buscar próxima fatura pendente ou criar uma nova
                        $nextInvoice = Invoices::where('subscription_id', $subscription->id)
                            ->whereIn('status', ['pending', 'overdue'])
                            ->orderBy('due_date', 'asc')
                            ->first();
                        
                        if (!$nextInvoice) {
                            // Criar nova fatura para o próximo mês
                            $dueDate = now()->addMonth();
                            $nextInvoice = Invoices::create([
                                'subscription_id' => $subscription->id,
                                'tenant_id' => $tenant->id,
                                'amount_cents' => $newPlan->price_cents,
                                'due_date' => $dueDate,
                                'status' => 'pending',
                                'payment_method' => 'PIX',
                            ]);
                        }
                        
                        // Gerar link de pagamento PIX
                        $paymentResponse = $asaas->createPayment([
                            'customer' => $tenant->asaas_customer_id,
                            'billingType' => 'PIX',
                            'dueDate' => $nextInvoice->due_date->toDateString(),
                            'value' => $newPlan->price_cents / 100,
                            'description' => "Assinatura do plano {$newPlan->name}",
                            'externalReference' => $nextInvoice->id,
                        ]);
                        
                        if (!empty($paymentResponse['id'])) {
                            $nextInvoice->update([
                                'provider' => 'asaas',
                                'provider_id' => $paymentResponse['id'],
                                'payment_link' => $paymentResponse['invoiceUrl'] ?? null,
                                'payment_method' => 'PIX',
                                'asaas_synced' => true,
                                'asaas_sync_status' => 'success',
                                'asaas_last_sync_at' => now(),
                            ]);
                            
                            Log::info('Link de pagamento PIX gerado para mudança de forma de pagamento', [
                                'invoice_id' => $nextInvoice->id,
                                'subscription_id' => $subscription->id,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Erro ao gerar link de pagamento PIX', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                // Outras mudanças (BOLETO, DEBIT_CARD, etc.)
                else {
                    // Para outras formas de pagamento, gerar link de pagamento
                    try {
                        $nextInvoice = Invoices::where('subscription_id', $subscription->id)
                            ->whereIn('status', ['pending', 'overdue'])
                            ->orderBy('due_date', 'asc')
                            ->first();
                        
                        if (!$nextInvoice) {
                            $dueDate = now()->addMonth();
                            $nextInvoice = Invoices::create([
                                'subscription_id' => $subscription->id,
                                'tenant_id' => $tenant->id,
                                'amount_cents' => $newPlan->price_cents,
                                'due_date' => $dueDate,
                                'status' => 'pending',
                                'payment_method' => $requestedPaymentMethod,
                            ]);
                        }
                        
                        $billingType = match($requestedPaymentMethod) {
                            'PIX' => 'PIX',
                            'BOLETO' => 'BOLETO',
                            'CREDIT_CARD' => 'CREDIT_CARD',
                            'DEBIT_CARD' => 'DEBIT_CARD',
                            default => 'PIX',
                        };
                        
                        $paymentResponse = $asaas->createPayment([
                            'customer' => $tenant->asaas_customer_id,
                            'billingType' => $billingType,
                            'dueDate' => $nextInvoice->due_date->toDateString(),
                            'value' => $newPlan->price_cents / 100,
                            'description' => "Assinatura do plano {$newPlan->name}",
                            'externalReference' => $nextInvoice->id,
                        ]);
                        
                        if (!empty($paymentResponse['id'])) {
                            $nextInvoice->update([
                                'provider' => 'asaas',
                                'provider_id' => $paymentResponse['id'],
                                'payment_link' => $paymentResponse['invoiceUrl'] ?? $paymentResponse['bankSlipUrl'] ?? null,
                                'payment_method' => $requestedPaymentMethod,
                                'asaas_synced' => true,
                                'asaas_sync_status' => 'success',
                                'asaas_last_sync_at' => now(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Erro ao gerar link de pagamento', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Sincronizar assinatura com Asaas (se necessário)
            if ($subscription->asaas_subscription_id) {
                try {
                    // Atualizar assinatura no Asaas com novo valor e descrição
                    // O parâmetro updatePendingPayments=true faz com que o Asaas atualize
                    // automaticamente todas as cobranças pendentes da assinatura
                    $asaasResponse = $asaas->updateSubscription($subscription->asaas_subscription_id, [
                        'value' => $newPlan->price_cents / 100,
                        'description' => "Assinatura do plano {$newPlan->name}",
                        'updatePendingPayments' => true, // Atualiza cobranças pendentes automaticamente
                    ]);

                    // Verificar se houve erro na resposta
                    if (isset($asaasResponse['error'])) {
                        Log::warning('Erro do Asaas ao atualizar assinatura', [
                            'error' => $asaasResponse['error'],
                            'subscription_id' => $subscription->id,
                        ]);
                        $subscription->update([
                            'asaas_synced' => false,
                            'asaas_sync_status' => 'failed',
                            'asaas_last_error' => $asaasResponse['error'],
                        ]);
                    } else {
                        $subscription->update([
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => null,
                        ]);

                        Log::info('Assinatura atualizada no Asaas após aprovação de mudança de plano', [
                            'request_id' => $planChangeRequest->id,
                            'subscription_id' => $subscription->id,
                            'new_plan_id' => $planChangeRequest->requested_plan_id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao sincronizar assinatura com Asaas após aprovação', [
                        'error' => $e->getMessage(),
                        'subscription_id' => $subscription->id,
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $subscription->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'failed',
                        'asaas_last_error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('Platform.plan-change-requests.index')
                ->with('success', 'Solicitação aprovada e plano alterado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao aprovar solicitação de mudança de plano', [
                'error' => $e->getMessage(),
                'request_id' => $planChangeRequest->id,
            ]);

            return back()->withErrors(['error' => 'Erro ao processar aprovação. Tente novamente.']);
        }
    }

    /**
     * Rejeita uma solicitação de mudança de plano
     */
    public function reject(Request $request, PlanChangeRequest $planChangeRequest)
    {
        if (!$planChangeRequest->canBeReviewed()) {
            return back()->withErrors(['error' => 'Esta solicitação não pode ser revisada.']);
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'É necessário informar o motivo da rejeição.',
        ]);

        try {
            $planChangeRequest->update([
                'status' => 'rejected',
                'admin_notes' => $validated['admin_notes'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            Log::info('Solicitação de mudança de plano rejeitada', [
                'request_id' => $planChangeRequest->id,
                'admin_notes' => $validated['admin_notes'],
            ]);

            return redirect()
                ->route('Platform.plan-change-requests.index')
                ->with('success', 'Solicitação rejeitada com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao rejeitar solicitação de mudança de plano', [
                'error' => $e->getMessage(),
                'request_id' => $planChangeRequest->id,
            ]);

            return back()->withErrors(['error' => 'Erro ao processar rejeição. Tente novamente.']);
        }
    }
}
