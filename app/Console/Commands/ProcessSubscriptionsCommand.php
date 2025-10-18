<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Subscription;
use App\Models\Platform\Invoices;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Services\AsaasService;
use Carbon\Carbon;

class ProcessSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:process';
    protected $description = 'Gera faturas automáticas e bloqueia tenants inadimplentes.';

    public function handle()
    {
        $today = Carbon::today();
        $asaas = new AsaasService();

        // 1️⃣ Gera novas faturas para assinaturas que terminam hoje
        $subscriptions = Subscription::whereDate('ends_at', $today)
            ->where('auto_renew', true)
            ->where('status', 'active')
            ->get();

        foreach ($subscriptions as $sub) {
            $plan = Plan::find($sub->plan_id);

            // Cria nova fatura
            $invoice = Invoices::create([
                'subscription_id' => $sub->id,
                'tenant_id' => $sub->tenant_id,
                'amount_cents' => $plan->price_cents,
                'due_date' => $today->copy()->addDays(5),
                'status' => 'pending',
            ]);

            // Cria cobrança no Asaas
            $tenant = Tenant::find($sub->tenant_id);

            if (!$tenant->asaas_customer_id) {
                $customer = $asaas->createCustomer($tenant->toArray());
                $tenant->update(['asaas_customer_id' => $customer['id'] ?? null]);
            }

            $payment = $asaas->createPayment([
                'customer_id' => $tenant->asaas_customer_id,
                'amount' => $invoice->amount_cents / 100,
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'description' => 'Renovação de plano SaaS',
                'external_reference' => $invoice->id,
            ]);

            $invoice->update([
                'provider' => 'asaas',
                'provider_id' => $payment['id'] ?? null,
                'payment_link' => $payment['invoiceUrl'] ?? null,
            ]);

            // Renova assinatura
            $sub->update([
                'starts_at' => $today,
                'ends_at' => $today->copy()->addMonths($plan->period_months),
                'status' => 'active',
            ]);

            $this->info("Fatura gerada para assinatura {$sub->id}");
        }

        // 2️⃣ Bloqueia tenants com faturas vencidas há mais de 5 dias
        $overdueInvoices = Invoices::whereIn('status', ['pending', 'overdue'])
            ->whereDate('due_date', '<=', $today->copy()->subDays(5))
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $subscription = Subscription::find($invoice->subscription_id);
            if ($subscription) {
                $subscription->update(['status' => 'past_due']);
            }

            $tenant = Tenant::find($invoice->tenant_id);
            if ($tenant) {
                $tenant->update(['status' => 'suspended']); // precisa desse campo no tenants
            }

            $invoice->update(['status' => 'overdue']);
            $this->warn("Tenant {$invoice->tenant_id} bloqueado por atraso de pagamento.");
        }

        $this->info('Processamento de assinaturas concluído.');
    }
}