<?php

namespace App\Console\Commands;

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\SystemNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessSubscriptionsCommand extends Command
{
    public function __construct(
        private readonly WhatsAppOfficialMessageService $officialWhatsApp
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

            if (! empty($sub->asaas_subscription_id)) {
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

            if ($sub->payment_method === 'CREDIT_CARD' && $sub->auto_renew) {
                $response = $asaas->createSubscription([
                    'customer' => $tenant->asaas_customer_id,
                    'value' => $plan->price_cents / 100,
                    'cycle' => 'MONTHLY',
                    'nextDueDate' => now()->toDateString(),
                    'description' => "Assinatura automatica do plano {$plan->name}",
                ]);

                if (! empty($response['id'])) {
                    $sub->update(['asaas_subscription_id' => $response['id']]);
                    Log::info("Assinatura automatica criada no Asaas ({$response['id']}) para tenant {$tenant->trade_name}");
                    $createdInvoices++;
                } else {
                    Log::error('Falha ao criar assinatura automatica Asaas: ' . json_encode($response));
                    $errors++;
                }

                continue;
            }

            if ($sub->payment_method === 'PIX' && $sub->auto_renew) {
                $payload = [
                    'customer' => $tenant->asaas_customer_id,
                    'billingType' => 'PIX',
                    'value' => $plan->price_cents / 100,
                    'dueDate' => now()->addDays(5)->toDateString(),
                    'description' => "Renovacao de plano {$plan->name}",
                    'externalReference' => (string) Str::uuid(),
                ];

                $payment = $asaas->createPayment($payload);

                if (! empty($payment['id'])) {
                    $invoice = Invoices::create([
                        'subscription_id' => $sub->id,
                        'tenant_id' => $tenant->id,
                        'amount_cents' => $plan->price_cents,
                        'due_date' => $payload['dueDate'],
                        'status' => 'pending',
                        'provider' => 'asaas',
                        'provider_id' => $payment['id'],
                        'payment_link' => $payment['invoiceUrl'] ?? ($payment['bankSlipUrl'] ?? null),
                    ]);

                    $this->officialWhatsApp->sendByKey(
                        'invoice.created',
                        $tenant->phone,
                        [
                            'customer_name' => $tenant->trade_name,
                            'tenant_name' => $tenant->trade_name,
                            'invoice_amount' => 'R$ ' . number_format($invoice->amount_cents / 100, 2, ',', '.'),
                            'due_date' => Carbon::parse($invoice->due_date)->format('d/m/Y'),
                            'payment_link' => trim((string) ($invoice->payment_link ?: 'https://app.allsync.com.br/faturas')),
                        ],
                        [
                            'command' => static::class,
                            'invoice_id' => (string) $invoice->id,
                            'tenant_id' => (string) $tenant->id,
                            'event' => 'invoice.created',
                        ]
                    );

                    $sub->update([
                        'starts_at' => now(),
                        'ends_at' => now()->addMonths($plan->period_months),
                        'status' => 'active',
                    ]);

                    Log::info("Cobranca PIX gerada para tenant {$tenant->trade_name}");
                    $createdInvoices++;
                } else {
                    Log::error('Falha ao criar cobranca PIX: ' . json_encode($payment));
                    $errors++;
                }
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

            $tenant = Tenant::query()->find($inv->tenant_id);
            if ($tenant && $tenant->phone) {
                $this->officialWhatsApp->sendByKey(
                    'invoice.overdue',
                    $tenant->phone,
                    [
                        'customer_name' => $tenant->trade_name,
                        'tenant_name' => $tenant->trade_name,
                        'invoice_amount' => 'R$ ' . number_format($inv->amount_cents / 100, 2, ',', '.'),
                        'due_date' => Carbon::parse($inv->due_date)->format('d/m/Y'),
                        'payment_link' => trim((string) ($inv->payment_link ?: 'https://app.allsync.com.br/faturas')),
                    ],
                    [
                        'command' => static::class,
                        'invoice_id' => (string) $inv->id,
                        'tenant_id' => (string) $tenant->id,
                        'event' => 'invoice.overdue',
                    ]
                );
            }
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
