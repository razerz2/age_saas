<?php

use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTemplate;
use Database\Seeders\WhatsAppOfficialTemplatesSeeder;

beforeEach(function (): void {
    config(['services.whatsapp.provider' => 'whatsapp_business']);

    if (function_exists('set_sysconfig')) {
        set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
    }
});

function createPlatformUserForWhatsAppOfficialIndex(): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'index+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['whatsapp_official_templates'],
        'email_verified_at' => now(),
    ]);
}

function createPlatformUserForWhatsAppOfficialTenantIndex(): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'tenant-index+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['whatsapp_official_tenant_templates'],
        'email_verified_at' => now(),
    ]);
}

function createOfficialTemplateForIndexScope(array $overrides = []): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create(array_merge([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_scope',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => "Ola {{1}}. Fatura {{2}}.",
        'variables' => [
            '1' => 'customer_name',
            '2' => 'invoice_amount',
        ],
        'sample_variables' => [
            '1' => 'Rafael',
            '2' => 'R$ 299,90',
        ],
        'version' => 1,
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ], $overrides));
}

it('shows saas event descriptions in official whatsapp templates index', function () {
    app(WhatsAppOfficialTemplatesSeeder::class)->run();
    $user = createPlatformUserForWhatsAppOfficialIndex();

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.index'))
        ->assertOk()
        ->assertSee('invoice.created')
        ->assertSee('Fatura criada')
        ->assertSee('security.2fa_code')
        ->assertSee('Codigo de verificacao (2FA)')
        ->assertSee('subscription.recovery_started')
        ->assertSee('Recovery de assinatura iniciado')
        ->assertSee('credentials.resent')
        ->assertSee('Reenvio de credenciais');
});

it('platform index hides tenant baseline templates and shows only platform event keys', function () {
    $user = createPlatformUserForWhatsAppOfficialIndex();

    createOfficialTemplateForIndexScope([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_platform_only',
    ]);
    createOfficialTemplateForIndexScope([
        'key' => 'appointment.confirmed',
        'meta_template_name' => 'tenant_appointment_confirmed_should_not_appear',
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.index'))
        ->assertOk()
        ->assertSee('saas_invoice_created_platform_only')
        ->assertDontSee('tenant_appointment_confirmed_should_not_appear');
});

it('tenant index hides platform templates and shows only tenant baseline keys', function () {
    $user = createPlatformUserForWhatsAppOfficialTenantIndex();

    createOfficialTemplateForIndexScope([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_should_not_appear_tenant_index',
    ]);
    createOfficialTemplateForIndexScope([
        'key' => 'appointment.confirmed',
        'meta_template_name' => 'tenant_appointment_confirmed_tenant_index',
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-tenant-templates.index'))
        ->assertOk()
        ->assertSee('tenant_appointment_confirmed_tenant_index')
        ->assertDontSee('saas_invoice_created_should_not_appear_tenant_index');
});
