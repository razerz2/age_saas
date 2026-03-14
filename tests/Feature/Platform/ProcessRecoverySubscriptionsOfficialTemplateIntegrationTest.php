<?php

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use App\Services\Platform\WhatsAppOfficialMessageService;
use Illuminate\Support\Facades\Artisan;

function createRecoveryEligibleSubscription(): Subscription
{
    $suffix = uniqid();

    $plan = Plan::query()->create([
        'name' => 'Plano Recovery ' . $suffix,
        'description' => 'Plano para teste de recovery',
        'periodicity' => 'monthly',
        'period_months' => 1,
        'price_cents' => 29990,
        'category' => 'commercial',
        'features' => [],
        'is_active' => true,
    ]);

    $tenant = Tenant::query()->create([
        'legal_name' => 'Tenant Recovery ' . $suffix,
        'trade_name' => 'Tenant Recovery ' . $suffix,
        'document' => null,
        'email' => "tenant-recovery+{$suffix}@example.com",
        'phone' => '5565999999999',
        'subdomain' => 'tenant-recovery-' . $suffix,
        'db_host' => '127.0.0.1',
        'db_port' => 5432,
        'db_name' => 'tenant_recovery_' . $suffix,
        'db_username' => 'tenant_user',
        'db_password' => 'tenant_pass',
        'status' => 'suspended',
        'suspended_at' => now()->subDays(6),
        'plan_id' => $plan->id,
        'asaas_customer_id' => 'cus_' . $suffix,
    ]);

    return Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subMonths(2),
        'ends_at' => now()->subDays(6),
        'due_day' => 1,
        'status' => 'past_due',
        'auto_renew' => true,
        'payment_method' => 'CREDIT_CARD',
        'asaas_subscription_id' => 'sub_old_' . $suffix,
    ]);
}

it('migrates recovery whatsapp send to official template catalog', function () {
    if (function_exists('set_sysconfig')) {
        set_sysconfig('billing.recovery_days_after_suspension', 5);
    }

    $subscription = createRecoveryEligibleSubscription();

    $asaasMock = Mockery::mock(AsaasService::class);
    $asaasMock->shouldReceive('deleteSubscription')
        ->once()
        ->andReturn(true);
    $asaasMock->shouldReceive('createPaymentLink')
        ->once()
        ->andReturn([
            'id' => 'plink_test_123',
            'url' => 'https://app.allsync.com.br/faturas/recovery-test',
        ]);
    app()->instance(AsaasService::class, $asaasMock);

    $officialMock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $officialMock->shouldReceive('sendByKey')
        ->once()
        ->withArgs(function (string $key, ?string $phone, array $variables, array $context): bool {
            return $key === 'subscription.recovery_started'
                && $phone === '5565999999999'
                && ($variables['customer_name'] ?? null) !== null
                && ($variables['tenant_name'] ?? null) !== null
                && ($variables['invoice_amount'] ?? null) !== null
                && ($variables['due_date'] ?? null) !== null
                && ($variables['payment_link'] ?? null) !== null
                && ($context['event'] ?? null) === 'subscription.recovery_started';
        })
        ->andReturn(true);
    app()->instance(WhatsAppOfficialMessageService::class, $officialMock);

    Artisan::call('subscriptions:process-recovery');

    $subscription->refresh();
    expect($subscription->status)->toBe('canceled')
        ->and($subscription->asaas_subscription_id)->toBeNull();

    $recoverySubscription = Subscription::query()
        ->where('tenant_id', $subscription->tenant_id)
        ->where('status', 'recovery_pending')
        ->first();

    expect($recoverySubscription)->not->toBeNull();

    $recoveryInvoice = Invoices::query()
        ->where('subscription_id', $recoverySubscription?->id)
        ->where('is_recovery', true)
        ->first();

    expect($recoveryInvoice)->not->toBeNull();
});
