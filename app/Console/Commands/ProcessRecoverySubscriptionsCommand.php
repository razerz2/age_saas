<?php

namespace App\Console\Commands;

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Services\AsaasService;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\SystemNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRecoverySubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:process-recovery';
    protected $description = 'Processa recovery de assinaturas de cartao apos suspensao prolongada (>=5 dias)';

    public function __construct(
        protected AsaasService $asaas,
        protected WhatsAppOfficialMessageService $officialWhatsApp
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Iniciando processamento de recovery de assinaturas...');

        $recoveryDays = (int) (function_exists('sysconfig')
            ? sysconfig('billing.recovery_days_after_suspension', 5)
            : 5);

        $recoveryStarted = 0;
        $canceled = 0;
        $errors = 0;
        $skipped = 0;

        $subscriptions = Subscription::whereIn('payment_method', ['CREDIT_CARD', 'DEBIT_CARD'])
            ->whereIn('status', ['past_due', 'active'])
            ->where(function ($query) {
                $query->whereNull('is_trial')
                    ->orWhere('is_trial', false);
            })
            ->whereNotNull('asaas_subscription_id')
            ->whereNull('recovery_started_at')
            ->whereHas('plan', function ($query) {
                $query->where('plan_type', Plan::TYPE_REAL);
            })
            ->whereHas('tenant', function ($query) use ($recoveryDays) {
                $query->where('status', 'suspended')
                    ->whereNotNull('suspended_at')
                    ->where('suspended_at', '<=', Carbon::now()->subDays($recoveryDays))
                    ->whereNull('canceled_at');
            })
            ->with(['tenant', 'plan'])
            ->get();

        foreach ($subscriptions as $subscription) {
            try {
                $tenant = $subscription->tenant;
                $plan = $subscription->plan;

                if (! $tenant || ! $plan) {
                    Log::warning("Subscription {$subscription->id} sem tenant/plan associados");
                    $skipped++;
                    continue;
                }

                if ($subscription->is_trial) {
                    Log::info("Subscription {$subscription->id} ignorada: trial comercial nao participa de recovery.");
                    $skipped++;
                    continue;
                }

                if ($plan->isTest()) {
                    Log::info("Subscription {$subscription->id} ignorada: plano de teste nao participa de recovery.");
                    $skipped++;
                    continue;
                }

                $existingRecovery = Subscription::where('tenant_id', $tenant->id)
                    ->where('status', 'recovery_pending')
                    ->whereNotNull('recovery_started_at')
                    ->first();

                if ($existingRecovery) {
                    Log::info("Recovery ja existe para tenant {$tenant->trade_name} (subscription: {$existingRecovery->id})");
                    $skipped++;
                    continue;
                }

                $existingRecoveryInvoice = Invoices::where('tenant_id', $tenant->id)
                    ->where('is_recovery', true)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->first();

                if ($existingRecoveryInvoice) {
                    Log::info("Invoice de recovery ja existe para tenant {$tenant->trade_name} (invoice: {$existingRecoveryInvoice->id})");
                    $skipped++;
                    continue;
                }

                $subscription->update([
                    'recovery_started_at' => now(),
                ]);

                if ($subscription->asaas_subscription_id) {
                    $this->asaas->deleteSubscription($subscription->asaas_subscription_id);
                    Log::info("Assinatura {$subscription->asaas_subscription_id} cancelada no Asaas para recovery");
                }

                $subscription->update([
                    'status' => 'canceled',
                    'asaas_subscription_id' => null,
                    'ends_at' => now(),
                ]);

                $newSubscription = Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => null,
                    'due_day' => $subscription->due_day,
                    'status' => 'recovery_pending',
                    'auto_renew' => true,
                    'payment_method' => $subscription->payment_method,
                    'recovery_started_at' => now(),
                ]);

                $paymentLinkResponse = $this->asaas->createPaymentLink([
                    'name' => "Recuperacao de Assinatura - {$plan->name}",
                    'description' => "Pagamento para reativar sua assinatura do plano {$plan->name}",
                    'customer' => $tenant->asaas_customer_id,
                    'value' => $plan->price_cents / 100,
                    'dueDateLimitDays' => 5,
                    'externalReference' => (string) $newSubscription->id,
                ]);

                if (empty($paymentLinkResponse['id']) || empty($paymentLinkResponse['url'])) {
                    Log::error("Falha ao criar payment link para recovery do tenant {$tenant->trade_name}", [
                        'response' => $paymentLinkResponse,
                    ]);
                    $errors++;
                    continue;
                }

                $recoveryInvoice = Invoices::create([
                    'subscription_id' => $newSubscription->id,
                    'tenant_id' => $tenant->id,
                    'amount_cents' => $plan->price_cents,
                    'due_date' => Carbon::now()->addDays(5),
                    'status' => 'pending',
                    'payment_method' => $subscription->payment_method,
                    'provider' => 'asaas',
                    'provider_id' => $paymentLinkResponse['id'],
                    'payment_link' => $paymentLinkResponse['url'],
                    'is_recovery' => true,
                    'recovery_origin_subscription_id' => $subscription->id,
                    'recovery_target_subscription_id' => $newSubscription->id,
                    'asaas_payment_link_id' => $paymentLinkResponse['id'],
                ]);

                if ($tenant->phone) {
                    $this->officialWhatsApp->sendByKey(
                        'subscription.recovery_started',
                        $tenant->phone,
                        [
                            'customer_name' => $tenant->trade_name,
                            'tenant_name' => $tenant->trade_name,
                            'invoice_amount' => 'R$ ' . number_format($recoveryInvoice->amount_cents / 100, 2, ',', '.'),
                            'due_date' => Carbon::parse($recoveryInvoice->due_date)->format('d/m/Y'),
                            'payment_link' => trim((string) ($recoveryInvoice->payment_link ?: 'https://app.allsync.com.br/faturas')),
                        ],
                        [
                            'command' => static::class,
                            'event' => 'subscription.recovery_started',
                            'tenant_id' => (string) $tenant->id,
                            'subscription_id' => (string) $newSubscription->id,
                            'invoice_id' => (string) $recoveryInvoice->id,
                        ]
                    );
                }

                $recoveryStarted++;
                Log::info("Recovery iniciado para tenant {$tenant->trade_name}", [
                    'origin_subscription_id' => $subscription->id,
                    'recovery_subscription_id' => $newSubscription->id,
                    'recovery_invoice_id' => $recoveryInvoice->id,
                ]);
            } catch (\Throwable $e) {
                $errors++;
                Log::error("Erro ao processar recovery para subscription {$subscription->id}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $expiredRecoveries = Subscription::where('status', 'recovery_pending')
            ->whereNotNull('recovery_started_at')
            ->where('recovery_started_at', '<=', Carbon::now()->subDays($recoveryDays))
            ->whereHas('plan', function ($query) {
                $query->where('plan_type', Plan::TYPE_REAL);
            })
            ->with('tenant')
            ->get();

        foreach ($expiredRecoveries as $expiredSub) {
            try {
                $tenant = $expiredSub->tenant;

                if (! $tenant) {
                    $skipped++;
                    continue;
                }

                $expiredSub->update(['status' => 'canceled']);

                $tenant->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                ]);

                $expiredSub->invoices()->where('status', 'pending')->update(['status' => 'canceled']);

                $canceled++;
                Log::warning("Tenant {$tenant->trade_name} cancelado - recovery nao pago em {$recoveryDays} dias");
            } catch (\Throwable $e) {
                $errors++;
                Log::error("Erro ao cancelar recovery expirado: {$e->getMessage()}");
            }
        }

        $this->info('Processamento concluido:');
        $this->info("   - Recoveries iniciados: {$recoveryStarted}");
        $this->info("   - Recoveries cancelados (nao pagos): {$canceled}");
        $this->info("   - Ignorados (ja existe recovery): {$skipped}");
        $this->info("   - Erros: {$errors}");

        if ($recoveryStarted > 0 || $canceled > 0 || $errors > 0) {
            SystemNotificationService::notify(
                'Processamento de Recovery',
                "Foram iniciados {$recoveryStarted} recoveries, {$canceled} cancelados e {$errors} erros.",
                'subscription',
                $errors > 0 ? 'warning' : 'info'
            );
        }

        return Command::SUCCESS;
    }
}
