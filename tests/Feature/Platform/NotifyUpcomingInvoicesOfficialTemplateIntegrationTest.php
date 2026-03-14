<?php

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\Platform\WhatsAppOfficialMessageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

function createUpcomingInvoiceForPlatformNotification(): Invoices
{
    $plan = Plan::query()->create([
        'name' => 'Plano SaaS',
        'description' => 'Plano para teste',
        'periodicity' => 'monthly',
        'period_months' => 1,
        'price_cents' => 29990,
        'category' => 'commercial',
        'features' => [],
        'is_active' => true,
    ]);

    $suffix = uniqid();

    $tenant = Tenant::query()->create([
        'legal_name' => 'Clinica Teste ' . $suffix,
        'trade_name' => 'Clinica Teste ' . $suffix,
        'document' => null,
        'email' => "tenant+{$suffix}@example.com",
        'phone' => '5565999999999',
        'subdomain' => 'tenant-' . $suffix,
        'db_host' => '127.0.0.1',
        'db_port' => 5432,
        'db_name' => 'tenant_db_' . $suffix,
        'db_username' => 'tenant_user',
        'db_password' => 'tenant_pass',
        'status' => 'active',
        'plan_id' => $plan->id,
    ]);

    $subscription = Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->addDays(20),
        'due_day' => 1,
        'status' => 'active',
        'auto_renew' => true,
        'payment_method' => 'PIX',
    ]);

    return Invoices::query()->create([
        'subscription_id' => $subscription->id,
        'tenant_id' => $tenant->id,
        'amount_cents' => 29990,
        'due_date' => Carbon::today()->addDays(5)->toDateString(),
        'status' => 'pending',
        'payment_method' => 'PIX',
        'payment_link' => 'https://app.allsync.com.br/faturas/abc',
        'provider' => 'asaas',
    ]);
}

it('marks notified_upcoming_at when official template send succeeds', function () {
    $invoice = createUpcomingInvoiceForPlatformNotification();

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')
        ->once()
        ->andReturn(true);

    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    Artisan::call('invoices:notify-upcoming');

    $invoice->refresh();

    expect($invoice->notified_upcoming_at)->not->toBeNull();
});

it('does not mark notified_upcoming_at when official template send fails', function () {
    $invoice = createUpcomingInvoiceForPlatformNotification();

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')
        ->once()
        ->andReturn(false);

    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    Artisan::call('invoices:notify-upcoming');

    $invoice->refresh();

    expect($invoice->notified_upcoming_at)->toBeNull();
});
