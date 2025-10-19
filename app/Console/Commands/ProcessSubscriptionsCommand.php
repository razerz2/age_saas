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
    protected $signature = 'subscriptions:process';
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

        // 🔎 Assinaturas ativas com renovação automática vencidas ou vencendo hoje
        $subs = Subscription::with(['tenant', 'plan'])
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('ends_at', '<=', Carbon::today())
            ->get();

        if ($subs->isEmpty()) {
            $this->info("ℹ️ Nenhuma assinatura para processar hoje.");

            // 📨 Registra notificação do sistema (execução sem assinaturas)
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

            // 🚫 Verifica se já existe fatura pendente ou vencida
            $hasInvoice = Invoices::where('subscription_id', $sub->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->exists();

            if ($hasInvoice) {
                Log::info("🛑 Assinatura {$sub->id} já possui fatura pendente ou vencida, ignorando.");
                continue;
            }

            if (!$tenant || !$plan) {
                Log::warning("⚠️ Assinatura {$sub->id} sem tenant/plan associados.");
                $errors++;
                continue;
            }

            // evita processar 2x o mesmo tenant
            if (in_array($tenant->id, $processedTenants, true)) {
                continue;
            }

            // 🔎 Garante que o cliente existe no Asaas
            if (empty($tenant->asaas_customer_id)) {
                // tenta localizar por e-mail primeiro
                $existing = $asaas->searchCustomer($tenant->email);

                if (!empty($existing['data'][0]['id'] ?? null)) {
                    $tenant->update(['asaas_customer_id' => $existing['data'][0]['id']]);
                    $tenant->refresh();
                    Log::info("ℹ️ Cliente já existia no Asaas: {$tenant->asaas_customer_id}");
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
                        $createdCustomers++;
                        Log::info("✅ Cliente criado no Asaas: {$customer['id']}");
                    } else {
                        Log::error('❌ Falha ao criar cliente Asaas', $customer ?? []);
                        $errors++;
                        $processedTenants[] = $tenant->id;
                        continue;
                    }
                }
            }


            // 👉 cria cobrança no Asaas
            $payload = [
                'customer'       => $tenant->asaas_customer_id,
                'amount'            => $plan->price_cents / 100,
                'due_date'          => Carbon::today()->addDays(5)->toDateString(),
                'description'       => 'Renovação de plano SaaS',
                'external_reference' => (string) Str::uuid(),
            ];

            $payment = $asaas->createPayment($payload);
            Log::info('📡 Asaas resposta (createPayment):', $payment ?? []);

            if (!empty($payment['id'])) {
                // grava invoice
                Invoices::create([
                    'subscription_id' => $sub->id,
                    'tenant_id'       => $tenant->id,
                    'amount_cents'    => $plan->price_cents,
                    'due_date'        => $payload['due_date'],
                    'status'          => 'pending',
                    'provider'        => 'asaas',
                    'provider_id'     => $payment['id'],
                    'payment_link'    => $payment['invoiceUrl'] ?? ($payment['bankSlipUrl'] ?? null),
                ]);
                $createdInvoices++;

                // renova assinatura
                $sub->update([
                    'starts_at' => Carbon::today(),
                    'ends_at'   => Carbon::today()->addMonths($plan->period_months),
                    'status'    => 'active',
                ]);

                $this->info("✅ Fatura gerada: {$payment['id']} | Tenant: {$tenant->trade_name}");
            } else {
                $this->error("❌ Falha ao criar cobrança no Asaas: " . json_encode($payment));
                $errors++;
            }

            $processedTenants[] = $tenant->id;
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

        // 📨 Registra notificação do sistema
        SystemNotificationService::notify(
            'Processamento de assinaturas concluído',
            "Clientes criados: {$createdCustomers}, Faturas geradas: {$createdInvoices}, Tenants suspensos: {$blockedTenants}, Falhas: {$errors}.",
            'subscription',
            $errors > 0 ? 'warning' : 'info'
        );

        // 📊 resumo
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
