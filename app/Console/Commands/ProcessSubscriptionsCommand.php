<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Subscription;
use App\Models\Platform\Invoices;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProcessSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:subscriptions-process';
    protected $description = 'Gera faturas automáticas de assinaturas vencidas e renova os períodos.';

    public function handle()
    {
        $this->info("🚀 Iniciando processamento de assinaturas...");

        $asaas = new AsaasService();

        $processedTenants = [];
        $createdCustomers = 0;
        $createdInvoices  = 0;
        $blockedTenants   = 0;
        $errors           = 0;

        $subs = Subscription::with(['tenant', 'plan'])
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('ends_at', '<=', Carbon::today())
            ->get();

        if ($subs->isEmpty()) {
            $this->info("ℹ️ Nenhuma assinatura para processar hoje.");

            SystemNotificationService::notify(
                'Execução do processamento de assinaturas',
                'O comando subscriptions:process foi executado, porém nenhuma assinatura estava pendente de renovação.',
                'subscription',
                'info'
            );

            return Command::SUCCESS;
        }

        foreach ($subs as $sub) {
            $tenant = $sub->tenant;
            $plan   = $sub->plan;

            if (!$tenant || !$plan) {
                Log::warning("⚠️ Assinatura {$sub->id} sem tenant/plan associados.");
                $errors++;
                continue;
            }

            // 🔒 ignora assinaturas que já estão atreladas a uma assinatura automática do Asaas
            if (!empty($sub->asaas_subscription_id)) {
                Log::info("⏩ Assinatura {$sub->id} já possui assinatura automática Asaas ({$sub->asaas_subscription_id}), ignorando.");
                continue;
            }

            // 🚫 evita duplicidade de cobrança
            $hasInvoice = Invoices::where('subscription_id', $sub->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->exists();

            if ($hasInvoice) {
                Log::info("🛑 Assinatura {$sub->id} já possui fatura pendente ou vencida, ignorando.");
                continue;
            }

            // 🔹 garante cliente no Asaas
            if (empty($tenant->asaas_customer_id)) {
                $existing = $asaas->searchCustomer($tenant->email);
                if (!empty($existing['data'][0]['id'] ?? null)) {
                    $tenant->update(['asaas_customer_id' => $existing['data'][0]['id']]);
                    $tenant->refresh();
                } else {
                    $customer = $asaas->createCustomer([
                        'trade_name' => $tenant->trade_name,
                        'legal_name' => $tenant->legal_name,
                        'email'      => $tenant->email,
                        'phone'      => $tenant->phone,
                        'document'   => $tenant->document,
                        'id'         => $tenant->id,
                    ]);

                    if (!empty($customer['id'])) {
                        $tenant->update(['asaas_customer_id' => $customer['id']]);
                        $tenant->refresh();
                    } else {
                        Log::error("❌ Falha ao criar cliente Asaas para {$tenant->trade_name}");
                        $errors++;
                        continue;
                    }
                }
            }

            // 💳 Fluxo baseado no método de pagamento
            if ($sub->payment_method === 'CREDIT_CARD' && $sub->auto_renew) {
                // 🔁 Cria assinatura automática (Asaas controlará as renovações)
                $response = $asaas->createSubscription([
                    'customer'      => $tenant->asaas_customer_id,
                    'value'         => $plan->price_cents / 100,
                    'cycle'         => 'MONTHLY',
                    'nextDueDate'   => now()->toDateString(),
                    'description'   => "Assinatura automática do plano {$plan->name}",
                ]);

                if (!empty($response['id'])) {
                    $sub->update(['asaas_subscription_id' => $response['id']]);
                    Log::info("✅ Assinatura automática criada no Asaas ({$response['id']}) para tenant {$tenant->trade_name}");
                    $createdInvoices++;
                } else {
                    Log::error("❌ Falha ao criar assinatura automática Asaas: " . json_encode($response));
                    $errors++;
                }

                // não cria invoice local — o Asaas fará isso automaticamente
                continue;
            }

            if ($sub->payment_method === 'PIX' && $sub->auto_renew) {
                // 💰 Cria cobrança única via PIX
                $payload = [
                    'customer'          => $tenant->asaas_customer_id,
                    'billingType'       => 'PIX',
                    'value'             => $plan->price_cents / 100,
                    'dueDate'           => now()->addDays(5)->toDateString(),
                    'description'       => "Renovação de plano {$plan->name}",
                    'externalReference' => (string) \Illuminate\Support\Str::uuid(),
                ];

                $payment = $asaas->createPayment($payload);

                if (!empty($payment['id'])) {
                    Invoices::create([
                        'subscription_id' => $sub->id,
                        'tenant_id'       => $tenant->id,
                        'amount_cents'    => $plan->price_cents,
                        'due_date'        => $payload['dueDate'],
                        'status'          => 'pending',
                        'provider'        => 'asaas',
                        'provider_id'     => $payment['id'],
                        'payment_link'    => $payment['invoiceUrl'] ?? ($payment['bankSlipUrl'] ?? null),
                    ]);

                    $sub->update([
                        'starts_at' => now(),
                        'ends_at'   => now()->addMonths($plan->period_months),
                        'status'    => 'active',
                    ]);

                    Log::info("✅ Cobrança PIX gerada para tenant {$tenant->trade_name}");
                    $createdInvoices++;
                } else {
                    Log::error("❌ Falha ao criar cobrança PIX: " . json_encode($payment));
                    $errors++;
                }
            }

            // Ignora qualquer outro tipo (boleto, débito, etc.)
        }

        // 🔒 Suspende tenants com faturas vencidas há mais de 5 dias
        $overdues = Invoices::whereIn('status', ['pending', 'overdue'])
            ->whereDate('due_date', '<=', Carbon::today()->subDays(5))
            ->get();

        foreach ($overdues as $inv) {
            $inv->update(['status' => 'overdue']);
            Subscription::where('id', $inv->subscription_id)->update(['status' => 'past_due']);
            Tenant::where('id', $inv->tenant_id)
                ->where('status', '!=', 'suspended')
                ->update(['status' => 'suspended']);
            $blockedTenants++;
        }

        SystemNotificationService::notify(
            'Processamento de assinaturas concluído',
            "Clientes criados: {$createdCustomers}, Faturas geradas: {$createdInvoices}, Tenants suspensos: {$blockedTenants}, Falhas: {$errors}.",
            'subscription',
            $errors > 0 ? 'warning' : 'info'
        );

        $this->newLine();
        $this->info("📊 Resumo do processamento:");
        $this->line("• Clientes criados: {$createdCustomers}");
        $this->line("• Faturas geradas: {$createdInvoices}");
        $this->line("• Tenants suspensos: {$blockedTenants}");
        $this->line("• Falhas: {$errors}");
        $this->newLine();
        $this->info("✅ Processamento concluído com sucesso.");

        return Command::SUCCESS;
    }
}
