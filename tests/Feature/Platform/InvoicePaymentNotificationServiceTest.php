<?php

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\Platform\InvoicePaymentNotificationService;
use App\Services\Platform\WhatsAppOfficialMessageService;
use Illuminate\Support\Facades\Mail;

function createInvoiceForPaymentNotificationService(array $overrides = []): Invoices
{
    $suffix = uniqid();

    $plan = Plan::query()->create([
        'name' => 'Plano Service ' . $suffix,
        'description' => 'Plano para teste do service',
        'periodicity' => 'monthly',
        'period_months' => 1,
        'price_cents' => 19990,
        'category' => 'commercial',
        'features' => [],
        'is_active' => true,
    ]);

    $tenant = Tenant::query()->create([
        'legal_name' => 'Tenant Legal ' . $suffix,
        'trade_name' => 'Tenant Trade ' . $suffix,
        'document' => null,
        'email' => "tenant-service+{$suffix}@example.com",
        'phone' => '5565999999999',
        'subdomain' => 'tenant-service-' . $suffix,
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
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
        'due_day' => 10,
        'status' => 'active',
        'auto_renew' => true,
        'payment_method' => 'PIX',
    ]);

    return Invoices::query()->create(array_merge([
        'subscription_id' => $subscription->id,
        'tenant_id' => $tenant->id,
        'amount_cents' => 19990,
        'due_date' => now()->addDays(5)->toDateString(),
        'status' => 'pending',
        'payment_link' => 'https://example.com/pay/123',
        'payment_method' => 'PIX',
        'provider' => 'asaas',
    ], $overrides));
}

it('dispatches multichannel when payment link is valid and tenant has email and phone', function () {
    Mail::fake();
    $invoice = createInvoiceForPaymentNotificationService();

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')->once()->withArgs(function (string $key): bool {
        return $key === 'invoice.created';
    })->andReturn(true);
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    app(InvoicePaymentNotificationService::class)->notifyInvoiceCreated($invoice);

    Mail::assertSent(function ($mail): bool {
        return count($mail->to) > 0;
    });
});

it('sends email when tenant has no phone', function () {
    Mail::fake();
    $invoice = createInvoiceForPaymentNotificationService();
    $invoice->tenant->update(['phone' => null]);
    $invoice->refresh();

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')->never();
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    app(InvoicePaymentNotificationService::class)->notifyInvoiceCreated($invoice);

    Mail::assertSent(function ($mail): bool {
        return count($mail->to) > 0;
    });
});

it('sends whatsapp when tenant has no email', function () {
    Mail::fake();
    $invoice = createInvoiceForPaymentNotificationService();
    $invoice->tenant->update(['email' => null]);
    $invoice->refresh();

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')->once()->withArgs(function (string $key): bool {
        return $key === 'invoice.created';
    })->andReturn(true);
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    app(InvoicePaymentNotificationService::class)->notifyInvoiceCreated($invoice);

    Mail::assertNothingSent();
});

it('does not dispatch external channels when payment link is invalid', function () {
    Mail::fake();
    $invoice = createInvoiceForPaymentNotificationService([
        'payment_link' => 'link-invalido',
    ]);

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')->never();
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    app(InvoicePaymentNotificationService::class)->notifyInvoiceCreated($invoice);

    Mail::assertNothingSent();
});

it('keeps flow when one channel fails', function () {
    Mail::fake();
    $invoice = createInvoiceForPaymentNotificationService();

    $mock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $mock->shouldReceive('sendByKey')->once()->andThrow(new RuntimeException('erro whatsapp'));
    app()->instance(WhatsAppOfficialMessageService::class, $mock);

    app(InvoicePaymentNotificationService::class)->notifyInvoiceCreated($invoice);

    Mail::assertSent(function ($mail): bool {
        return count($mail->to) > 0;
    });
});

