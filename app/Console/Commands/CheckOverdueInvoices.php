<?php

namespace App\Console\Commands;

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\SystemNotificationService;
use Carbon\Carbon;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:invoices-check-overdue';
    protected $description = 'Marca faturas vencidas e suspende tenants imediatamente (sem período de carência).';

    public function __construct(
        private readonly WhatsAppOfficialMessageService $officialWhatsApp
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("🔎 Verificando faturas vencidas (suspensão imediata, sem carência)...");

        $suspended = 0;
        $markedOverdue = 0;

        // 🔹 1. Marca como overdue todas as faturas pending com due_date no passado
        $pendingOverdue = Invoices::where('status', 'pending')
            ->whereDate('due_date', '<', Carbon::today())
            ->get();

        foreach ($pendingOverdue as $invoice) {
            $invoice->update(['status' => 'overdue']);
            $markedOverdue++;
            Log::info("📅 Fatura {$invoice->id} marcada como overdue (vencida em {$invoice->due_date->format('d/m/Y')})");

            $tenant = $invoice->tenant;
            if (!$tenant || !$tenant->phone) {
                continue;
            }

            $this->officialWhatsApp->sendByKey(
                'invoice.overdue',
                $tenant->phone,
                [
                    'customer_name' => $tenant->trade_name,
                    'tenant_name' => $tenant->trade_name,
                    'invoice_amount' => 'R$ ' . number_format($invoice->amount_cents / 100, 2, ',', '.'),
                    'due_date' => $invoice->due_date?->format('d/m/Y') ?? Carbon::today()->format('d/m/Y'),
                    'payment_link' => trim((string) ($invoice->payment_link ?: 'https://app.allsync.com.br/faturas')),
                ],
                [
                    'command' => static::class,
                    'invoice_id' => (string) $invoice->id,
                    'tenant_id' => (string) $tenant->id,
                    'event' => 'invoice.overdue',
                ]
            );
        }

        // 🔹 2. Suspende imediatamente todos os tenants com faturas overdue
        $overdueInvoices = Invoices::where('status', 'overdue')
            ->with(['tenant', 'subscription.plan'])
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $tenant = $invoice->tenant;

            if (! $tenant) {
                continue;
            }

            $currentSubscription = $tenant->activeSubscription();

            if (
                $currentSubscription
                && $currentSubscription->id !== $invoice->subscription_id
            ) {
                Log::info('Suspensao ignorada: invoice overdue de assinatura nao vigente.', [
                    'tenant_id' => $tenant->id,
                    'invoice_id' => $invoice->id,
                    'invoice_subscription_id' => $invoice->subscription_id,
                    'current_subscription_id' => $currentSubscription->id,
                ]);
                continue;
            }

            $invoicePlan = $invoice->subscription?->plan;
            if ($invoicePlan && ($invoicePlan->isTest() || $invoicePlan->category === Plan::CATEGORY_SANDBOX)) {
                Log::info('Suspensao ignorada: invoice overdue associada a plano de teste/sandbox.', [
                    'tenant_id' => $tenant->id,
                    'invoice_id' => $invoice->id,
                    'plan_id' => $invoicePlan->id,
                ]);
                continue;
            }

            if ($currentSubscription) {
                $currentSubscription->loadMissing('plan');
                $currentPlan = $currentSubscription->plan;

                if ($currentPlan && ($currentPlan->isTest() || $currentPlan->category === Plan::CATEGORY_SANDBOX)) {
                    Log::info('Suspensao ignorada: tenant com assinatura atual ativa em plano de teste/sandbox.', [
                        'tenant_id' => $tenant->id,
                        'invoice_id' => $invoice->id,
                        'current_subscription_id' => $currentSubscription->id,
                        'current_plan_id' => $currentPlan->id,
                    ]);
                    continue;
                }
            }

            if ($tenant && $tenant->status !== 'suspended') {
                $tenant->update([
                    'status' => 'suspended',
                    'suspended_at' => now(),
                ]);
                $suspended++;
                
                // Atualiza status da assinatura
                if ($invoice->subscription) {
                    $invoice->subscription->update(['status' => 'past_due']);
                }

                $this->officialWhatsApp->sendByKey(
                    'tenant.suspended_due_to_overdue',
                    $tenant->phone,
                    [
                        'customer_name' => $tenant->trade_name,
                        'tenant_name' => $tenant->trade_name,
                        'invoice_amount' => 'R$ ' . number_format($invoice->amount_cents / 100, 2, ',', '.'),
                        'due_date' => $invoice->due_date?->format('d/m/Y') ?? Carbon::today()->format('d/m/Y'),
                        'payment_link' => trim((string) ($invoice->payment_link ?: 'https://app.allsync.com.br/faturas')),
                    ],
                    [
                        'command' => static::class,
                        'invoice_id' => (string) $invoice->id,
                        'tenant_id' => (string) $tenant->id,
                        'event' => 'tenant.suspended_due_to_overdue',
                    ]
                );
                
                Log::warning("⛔ Tenant {$tenant->trade_name} suspenso imediatamente por fatura vencida (ID: {$invoice->id}, vencimento: {$invoice->due_date->format('d/m/Y')})");
            }
        }

        if ($markedOverdue > 0 || $suspended > 0) {
            SystemNotificationService::notify(
                'Verificação de faturas vencidas',
                "Foram marcadas {$markedOverdue} faturas como vencidas e {$suspended} tenants foram suspensos imediatamente (sem período de carência).",
                'invoice',
                'warning'
            );
        } else {
            SystemNotificationService::notify(
                'Verificação de faturas vencidas',
                'Nenhuma fatura vencida encontrada.',
                'invoice',
                'info'
            );
        }

        $this->info("✅ Verificação concluída: {$markedOverdue} faturas marcadas como overdue, {$suspended} tenants suspensos.");
        return Command::SUCCESS;
    }
}
