<?php

use App\Mail\TenantAdminCredentialsMail;
use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantAdmin;
use App\Services\Platform\TenantCreatorService;
use App\Services\Platform\WhatsAppOfficialMessageService;
use Illuminate\Support\Facades\Mail;

test('cadastro manual com plano de teste envia email de credenciais automaticamente', function () {
    Mail::fake();

    $plan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_TEST,
        'is_active' => true,
    ]);

    \Mockery::mock('alias:App\Services\TenantProvisioner')
        ->shouldReceive('prepareDatabaseConfig')
        ->once()
        ->andReturn([
            'db_host' => '127.0.0.1',
            'db_port' => 5432,
            'db_name' => 'tenant_test_db',
            'db_username' => 'postgres',
            'db_password' => 'secret',
        ]);

    \Mockery::mock('alias:App\Services\TenantProvisioner')
        ->shouldReceive('createDatabase')
        ->once()
        ->andReturn('Admin@123');

    \Mockery::mock('overload:App\Services\SystemSettingsService')
        ->shouldReceive('emailIsConfigured')
        ->andReturn(true);

    $officialWhatsApp = \Mockery::mock(WhatsAppOfficialMessageService::class);
    $officialWhatsApp->shouldReceive('sendByKey')->atLeast()->once()->andReturnNull();

    $service = app()->make(TenantCreatorService::class, [
        'officialWhatsApp' => $officialWhatsApp,
    ]);

    $tenant = $service->create([
        'legal_name' => 'Clinica Teste',
        'trade_name' => 'Clinica Teste',
        'document' => '52998224725',
        'email' => 'tenant-teste@example.com',
        'subdomain' => 'tenant-teste',
        'status' => 'active',
        'plan_id' => $plan->id,
    ]);

    Mail::assertSent(TenantAdminCredentialsMail::class, 1);
    expect($tenant->id)->not()->toBeNull();
});

test('cadastro manual com plano comercial real nao envia email de credenciais automaticamente', function () {
    Mail::fake();

    $plan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_REAL,
        'is_active' => true,
    ]);

    \Mockery::mock('alias:App\Services\TenantProvisioner')
        ->shouldReceive('prepareDatabaseConfig')
        ->once()
        ->andReturn([
            'db_host' => '127.0.0.1',
            'db_port' => 5432,
            'db_name' => 'tenant_real_db',
            'db_username' => 'postgres',
            'db_password' => 'secret',
        ]);

    \Mockery::mock('alias:App\Services\TenantProvisioner')
        ->shouldReceive('createDatabase')
        ->once()
        ->andReturn('Admin@123');

    \Mockery::mock('overload:App\Services\SystemSettingsService')
        ->shouldReceive('emailIsConfigured')
        ->andReturn(true);

    $officialWhatsApp = \Mockery::mock(WhatsAppOfficialMessageService::class);
    $officialWhatsApp->shouldReceive('sendByKey')->atLeast()->once()->andReturnNull();

    $service = app()->make(TenantCreatorService::class, [
        'officialWhatsApp' => $officialWhatsApp,
    ]);

    $tenant = $service->create([
        'legal_name' => 'Clinica Real',
        'trade_name' => 'Clinica Real',
        'document' => '11144477735',
        'email' => 'tenant-real@example.com',
        'subdomain' => 'tenant-real',
        'status' => 'active',
        'plan_id' => $plan->id,
    ]);

    Mail::assertNothingSent();
    expect($tenant->admin)->not()->toBeNull();
});

test('reenvio manual bloqueia tenant comercial real sem assinatura ativa paga', function () {
    Mail::fake();

    $tenant = Tenant::factory()->create([
        'email' => 'bloqueado@example.com',
    ]);

    $plan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_REAL,
        'is_active' => true,
    ]);

    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'is_trial' => false,
    ]);

    expect($subscription->id)->not()->toBeNull();

    TenantAdmin::query()->create([
        'tenant_id' => $tenant->id,
        'email' => 'admin@bloqueado.com',
        'password' => 'Admin@123',
        'login_url' => url("/t/{$tenant->subdomain}/login"),
        'name' => 'Administrador',
        'password_visible' => true,
    ]);

    $response = $this
        ->withoutMiddleware()
        ->post(route('Platform.tenants.send-credentials', $tenant));

    $response->assertSessionHasErrors('general');
    Mail::assertNothingSent();

    Invoices::query()->create([
        'tenant_id' => $tenant->id,
        'subscription_id' => $subscription->id,
        'status' => 'paid',
        'amount_cents' => 1000,
        'due_date' => now()->addDay(),
    ]);

    $response = $this
        ->withoutMiddleware()
        ->post(route('Platform.tenants.send-credentials', $tenant));

    $response->assertSessionHasNoErrors();
    Mail::assertSent(TenantAdminCredentialsMail::class, 1);
});
