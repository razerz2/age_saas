<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Platform\Subscription;
use App\Models\Platform\Invoices;
use App\Services\AsaasService;
use App\Services\WhatsAppService;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessRecoverySubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:process-recovery';
    protected $description = 'Processa recovery de assinaturas de cartão após suspensão prolongada (≥5 dias)';

    protected AsaasService $asaas;
    protected WhatsAppService $whatsapp;

    public function __construct(AsaasService $asaas, WhatsAppService $whatsapp)
    {
        parent::__construct();
        $this->asaas = $asaas;
        $this->whatsapp = $whatsapp;
    }

    public function handle()
    {
        $this->info("🔄 Iniciando processamento de recovery de assinaturas...");

        // 🔹 Obtém configuração do SystemSetting (default: 5 dias)
        $recoveryDays = (int) (function_exists('sysconfig') 
            ? sysconfig('billing.recovery_days_after_suspension', 5)
            : 5);

        $processed = 0;
        $recoveryStarted = 0;
        $canceled = 0;
        $errors = 0;
        $skipped = 0;

        // 🔹 Busca subscriptions de cartão suspensas (tenant suspenso) há ≥ X dias, sem recovery iniciado
        $subscriptions = Subscription::whereIn('payment_method', ['CREDIT_CARD', 'DEBIT_CARD'])
            ->whereIn('status', ['past_due', 'active'])
            ->whereNotNull('asaas_subscription_id')
            ->whereNull('recovery_started_at') // Ainda não iniciou recovery
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

                if (!$tenant || !$plan) {
                    Log::warning("⚠️ Subscription {$subscription->id} sem tenant/plan associados");
                    $skipped++;
                    continue;
                }

                // 🔹 IDEMPOTÊNCIA: Verifica se já existe subscription recovery_pending para este tenant
                $existingRecovery = Subscription::where('tenant_id', $tenant->id)
                    ->where('status', 'recovery_pending')
                    ->whereNotNull('recovery_started_at')
                    ->first();

                if ($existingRecovery) {
                    Log::info("ℹ️ Recovery já existe para tenant {$tenant->trade_name} (subscription: {$existingRecovery->id})");
                    $skipped++;
                    continue;
                }

                // 🔹 IDEMPOTÊNCIA: Verifica se já existe invoice recovery pendente
                $existingRecoveryInvoice = Invoices::where('tenant_id', $tenant->id)
                    ->where('is_recovery', true)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->first();

                if ($existingRecoveryInvoice) {
                    Log::info("ℹ️ Invoice de recovery já existe para tenant {$tenant->trade_name} (invoice: {$existingRecoveryInvoice->id})");
                    $skipped++;
                    continue;
                }

                // 🔹 Inicia processo de recovery (apenas na subscription)
                $subscription->update([
                    'recovery_started_at' => now(),
                ]);

                // 🔹 1. Cancela assinatura no Asaas
                if ($subscription->asaas_subscription_id) {
                    $this->asaas->deleteSubscription($subscription->asaas_subscription_id);
                    Log::info("🗑️ Assinatura {$subscription->asaas_subscription_id} cancelada no Asaas para recovery");
                }

                // 🔹 2. Encerra assinatura local
                $subscription->update([
                    'status' => 'canceled',
                    'asaas_subscription_id' => null, // Remove referência à assinatura antiga
                    'ends_at' => now(),
                ]);

                // 🔹 3. Cria nova assinatura recovery_pending
                $plan = $subscription->plan;
                $newSubscription = Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => null, // Será definido após pagamento
                    'due_day' => $subscription->due_day,
                    'status' => 'recovery_pending',
                    'auto_renew' => true,
                    'payment_method' => $subscription->payment_method,
                    'recovery_started_at' => now(),
                ]);

                // 🔹 4. Gera link de pagamento (DETACHED - não recorrente)
                // externalReference = ID da subscription recovery_pending (padronizado)
                $paymentLinkResponse = $this->asaas->createPaymentLink([
                    'name' => "Recuperação de Assinatura - {$plan->name}",
                    'description' => "Pagamento para reativar sua assinatura do plano {$plan->name}",
                    'customer' => $tenant->asaas_customer_id,
                    'value' => $plan->price_cents / 100,
                    'dueDateLimitDays' => 5,
                    'externalReference' => (string) $newSubscription->id, // Padronizado: ID da subscription recovery_pending
                ]);

                if (empty($paymentLinkResponse['id']) || empty($paymentLinkResponse['url'])) {
                    Log::error("❌ Falha ao criar payment link para recovery do tenant {$tenant->trade_name}", [
                        'response' => $paymentLinkResponse,
                    ]);
                    $errors++;
                    continue;
                }

                // 🔹 5. Cria invoice vinculada ao recovery subscription (com vínculos)
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
                    'recovery_origin_subscription_id' => $subscription->id, // Subscription original cancelada
                    'recovery_target_subscription_id' => $newSubscription->id, // Subscription recovery_pending
                    'asaas_payment_link_id' => $paymentLinkResponse['id'],
                ]);

                // 🔹 6. Envia link ao cliente via WhatsApp
                if ($tenant->phone) {
                    $message = "🔄 *Recuperação de Assinatura*\n\n"
                        . "Olá {$tenant->trade_name}!\n\n"
                        . "Sua assinatura foi suspensa por inadimplência.\n"
                        . "Para reativar seu acesso, realize o pagamento através do link abaixo:\n\n"
                        . "🔗 *Link de pagamento:*\n{$paymentLinkResponse['url']}\n\n"
                        . "⏰ *Prazo:* {$recoveryDays} " . ($recoveryDays == 1 ? 'dia' : 'dias') . "\n\n"
                        . "Após o pagamento, sua assinatura será reativada automaticamente.\n\n"
                        . "Agradecemos pela compreensão! 🙏";

                    $sent = $this->whatsapp->sendMessage($tenant->phone, $message);
                    
                    if ($sent) {
                        Log::info("✅ Link de recovery enviado via WhatsApp para tenant {$tenant->trade_name}");
                    } else {
                        Log::warning("⚠️ Falha ao enviar link de recovery via WhatsApp para tenant {$tenant->trade_name}");
                    }
                }

                $recoveryStarted++;
                Log::info("✅ Recovery iniciado para tenant {$tenant->trade_name}", [
                    'origin_subscription_id' => $subscription->id,
                    'recovery_subscription_id' => $newSubscription->id,
                    'recovery_invoice_id' => $recoveryInvoice->id,
                ]);

            } catch (\Throwable $e) {
                $errors++;
                Log::error("❌ Erro ao processar recovery para subscription {$subscription->id}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // 🔹 7. Cancela assinaturas recovery_pending que não foram pagas em X dias (usa mesmo valor de recoveryDays)
        $expiredRecoveries = Subscription::where('status', 'recovery_pending')
            ->whereNotNull('recovery_started_at')
            ->where('recovery_started_at', '<=', Carbon::now()->subDays($recoveryDays))
            ->with('tenant')
            ->get();

        foreach ($expiredRecoveries as $expiredSub) {
            try {
                $tenant = $expiredSub->tenant;
                
                // Cancela assinatura
                $expiredSub->update(['status' => 'canceled']);
                
                // Cancela tenant
                $tenant->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                ]);

                // Cancela invoice pendente
                $expiredSub->invoices()->where('status', 'pending')->update(['status' => 'canceled']);

                $canceled++;
                Log::warning("🚫 Tenant {$tenant->trade_name} cancelado - recovery não pago em {$recoveryDays} dias");

            } catch (\Throwable $e) {
                $errors++;
                Log::error("❌ Erro ao cancelar recovery expirado: {$e->getMessage()}");
            }
        }

        $this->info("✅ Processamento concluído:");
        $this->info("   - Recoveries iniciados: {$recoveryStarted}");
        $this->info("   - Recoveries cancelados (não pagos): {$canceled}");
        $this->info("   - Ignorados (já existe recovery): {$skipped}");
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
