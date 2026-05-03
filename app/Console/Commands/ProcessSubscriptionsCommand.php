<?php

namespace App\Console\Commands;

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use App\Services\Platform\InvoiceAsaasSyncService;
use App\Services\Platform\InvoicePaymentNotificationService;
use App\Services\SystemNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionsCommand extends Command
{
    public function __construct(
        private readonly InvoiceAsaasSyncService $invoiceAsaasSyncService,
        private readonly InvoicePaymentNotificationService $invoicePaymentNotificationService
    ) {
        parent::__construct();
    }

    protected $signature = 'subscriptions:subscriptions-process';
    protected $description = 'Gera faturas automaticas de assinaturas vencidas e renova os periodos.';

    public function handle()
    {
        $this->info('Iniciando processamento de assinaturas...');

        $asaas = new AsaasService();

        $createdCustomers = 0;
        $createdInvoices = 0;
        $blockedTenants = 0;
        $errors = 0;

        $expiredTrials = Subscription::with(['tenant', 'plan'])
            ->where('is_trial', true)
            ->whereIn('status', ['active', 'trialing'])
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->get();

        foreach ($expiredTrials as $trialSubscription) {
            $trialSubscription->update([
                'status' => 'canceled',
                'ends_at' => $trialSubscription->trial_ends_at,
                'auto_renew' => false,
                'asaas_synced' => false,
                'asaas_sync_status' => 'skipped',
                'asaas_last_error' => null,
                'asaas_last_sync_at' => now(),
            ]);

            Log::info("Assinatura de trial {$trialSubscription->id} expirou e foi encerrada.");
        }

        $subs = Subscription::with(['tenant', 'plan'])
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->where(function ($query) {
                $query->whereNull('is_trial')
                    ->orWhere('is_trial', false);
            })
            ->whereDate('ends_at', '<=', Carbon::today())
            ->get();

        if ($subs->isEmpty()) {
            $this->info('Nenhuma assinatura para processar hoje.');

            SystemNotificationService::notify(
                'Execucao do processamento de assinaturas',
                'O comando subscriptions:subscriptions-process foi executado, porem nenhuma assinatura estava pendente de renovacao.',
                'subscription',
                'info'
            );

            return Command::SUCCESS;
        }

        foreach ($subs as $sub) {
            $tenant = $sub->tenant;
            $plan = $sub->plan;

            if (! $tenant || ! $plan) {
                Log::warning("Assinatura {$sub->id} sem tenant/plan associados.");
                $errors++;
                continue;
            }

            if ($sub->is_trial) {
                Log::info("Assinatura {$sub->id} ignorada: trial comercial nao participa de cobranca.");
                continue;
            }

            if ($plan->isTest()) {
                $renewalBase = $sub->ends_at ? Carbon::parse($sub->ends_at) : Carbon::today();
                $renewalStart = $renewalBase->isFuture() ? $renewalBase : Carbon::today();

                $sub->update([
                    'starts_at' => $renewalStart,
                    'ends_at' => $renewalStart->copy()->addMonths($plan->period_months ?: 1),
                    'status' => 'active',
                ]);

                Log::info("Assinatura {$sub->id} renovada sem cobranca: plano de teste.");
                continue;
            }

            $usesAsaasSubscriptionFlow = $sub->auto_renew
                && in_array($sub->payment_method, ['CREDIT_CARD', 'DEBIT_CARD', 'PIX_RECURRENT'], true);

            if ($usesAsaasSubscriptionFlow && ! empty($sub->asaas_subscription_id)) {
                Log::info("Assinatura {$sub->id} ja possui assinatura automatica Asaas ({$sub->asaas_subscription_id}), ignorando.");
                continue;
            }

            $hasInvoice = Invoices::where('subscription_id', $sub->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->exists();

            if ($hasInvoice) {
                Log::info("Assinatura {$sub->id} ja possui fatura pendente ou vencida, ignorando.");
                continue;
            }

            if (empty($tenant->asaas_customer_id)) {
                $existing = $asaas->searchCustomer($tenant->email);
                if (! empty($existing['data'][0]['id'] ?? null)) {
                    $tenant->update(['asaas_customer_id' => $existing['data'][0]['id']]);
                    $tenant->refresh();
                } else {
                    $customer = $asaas->createCustomer([
                        'trade_name' => $tenant->trade_name,
                        'legal_name' => $tenant->legal_name,
                        'email' => $tenant->email,
                        'phone' => $tenant->phone,
                        'document' => $tenant->document,
                        'id' => $tenant->id,
                    ]);

                    if (! empty($customer['id'])) {
                        $tenant->update(['asaas_customer_id' => $customer['id']]);
                        $tenant->refresh();
                        $createdCustomers++;
                    } else {
                        Log::error("Falha ao criar cliente Asaas para {$tenant->trade_name}");
                        $errors++;
                        continue;
                    }
                }
            }

            if ($usesAsaasSubscriptionFlow) {
                $billingType = $sub->payment_method === 'PIX_RECURRENT'
                    ? 'PIX'
                    : 'CREDIT_CARD';

                $response = $asaas->createSubscription([
                    'customer' => $tenant->asaas_customer_id,
                    'billingType' => $billingType,
                    'value' => $plan->price_cents / 100,
                    'cycle' => 'MONTHLY',
                    'nextDueDate' => now()->toDateString(),
                    'description' => "Assinatura automatica do plano {$plan->name}",
                    'externalReference' => (string) $sub->id,
                ]);

                $asaasSubscriptionId = $response['subscription']['id'] ?? null;
                $paymentLink = $response['payment_link'] ?? ($response['payment']['url'] ?? null);

                if (! empty($asaasSubscriptionId)) {
                    $sub->update([
                        'asaas_subscription_id' => $asaasSubscriptionId,
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => null,
                        'status' => 'pending',
                    ]);

                    Log::info("Assinatura automatica criada no Asaas ({$asaasSubscriptionId}) para tenant {$tenant->trade_name}", [
                        'payment_method' => $sub->payment_method,
                        'billing_type' => $billingType,
                        'payment_link' => $paymentLink,
                    ]);
                    $createdInvoices++;
                } else {
                    $errorMessage = $response['errors'][0]['description']
                        ?? $response['message']
                        ?? $response['error']
                        ?? json_encode($response);

                    $sub->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'failed',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => $errorMessage,
                    ]);

                    Log::error('Falha ao criar assinatura automatica Asaas: ' . json_encode($response));
                    $errors++;
                }

                continue;
            }

            if ($sub->payment_method === 'PIX' && $sub->auto_renew) {
                $invoice = Invoices::create([
                    'subscription_id' => $sub->id,
                    'tenant_id' => $tenant->id,
                    'amount_cents' => $plan->price_cents,
                    'due_date' => now()->addDays(5)->toDateString(),
                    'status' => 'pending',
                    'provider' => 'asaas',
                    'payment_method' => 'PIX',
                ]);

                try {
                    $invoice = $this->invoiceAsaasSyncService->syncInvoice($invoice);
                    $invoice->refresh();
                } catch (\Throwable $syncError) {
                    $invoice->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'failed',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => $syncError->getMessage(),
                    ]);

                    Log::error('Falha ao criar/sincronizar cobranca PIX: ' . $syncError->getMessage());
                    $errors++;
                    continue;
                }

                $this->invoicePaymentNotificationService->notifyInvoiceCreated($invoice);

                // IMPORTANTE: nao renova starts_at/ends_at/status aqui.
                // Renovacao ocorre apenas no webhook PAYMENT_RECEIVED/PAYMENT_CONFIRMED
                // (ou por rotina de conciliacao equivalente).
                Log::info("Cobranca PIX gerada para tenant {$tenant->trade_name} sem renovacao antecipada da assinatura.");
                $createdInvoices++;
                continue;
            }

            if ($sub->payment_method === 'BOLETO' && $sub->auto_renew) {
                $invoice = Invoices::create([
                    'subscription_id' => $sub->id,
                    'tenant_id' => $tenant->id,
                    'amount_cents' => $plan->price_cents,
                    'due_date' => now()->addDays(5)->toDateString(),
                    'status' => 'pending',
                    'provider' => 'asaas',
                    'payment_method' => 'BOLETO',
                ]);

                try {
                    $invoice = $this->invoiceAsaasSyncService->syncInvoice($invoice);
                    $invoice->refresh();
                } catch (\Throwable $syncError) {
                    $invoice->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'failed',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => $syncError->getMessage(),
                    ]);

                    Log::error('Falha ao criar/sincronizar cobranca BOLETO: ' . $syncError->getMessage());
                    $errors++;
                    continue;
                }

                $this->invoicePaymentNotificationService->notifyInvoiceCreated($invoice);

                Log::info("Cobranca BOLETO gerada para tenant {$tenant->trade_name} sem renovacao antecipada da assinatura.");
                $createdInvoices++;
            }
        }

        $overdues = Invoices::where('status', 'pending')
            ->whereDate('due_date', '<', Carbon::today())
            ->whereHas('subscription.plan', function ($query) {
                $query->where('plan_type', Plan::TYPE_REAL);
            })
            ->get();

        foreach ($overdues as $inv) {
            $inv->update(['status' => 'overdue']);
            Subscription::where('id', $inv->subscription_id)->update(['status' => 'past_due']);
            $blockedTenants++;

            $this->invoicePaymentNotificationService->notifyInvoiceOverdue($inv);
        }

        SystemNotificationService::notify(
            'Processamento de assinaturas concluido',
            "Clientes criados: {$createdCustomers}, Faturas geradas: {$createdInvoices}, Tenants suspensos: {$blockedTenants}, Falhas: {$errors}.",
            'subscription',
            $errors > 0 ? 'warning' : 'info'
        );

        $this->newLine();
        $this->info('Resumo do processamento:');
        $this->line("- Clientes criados: {$createdCustomers}");
        $this->line("- Faturas geradas: {$createdInvoices}");
        $this->line("- Tenants suspensos: {$blockedTenants}");
        $this->line("- Falhas: {$errors}");
        $this->newLine();
        $this->info('Processamento concluido com sucesso.');

        return Command::SUCCESS;
    }

}
