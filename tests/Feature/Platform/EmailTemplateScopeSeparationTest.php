<?php

use App\Models\Platform\NotificationTemplate;
use App\Models\Platform\User;

beforeEach(function (): void {
    config(['services.whatsapp.provider' => 'whatsapp_business']);
});

function createPlatformUserForEmailTemplates(array $modules): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'email-templates+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => $modules,
        'email_verified_at' => now(),
    ]);
}

function createNotificationTemplateRecord(array $overrides = []): NotificationTemplate
{
    return NotificationTemplate::query()->create(array_merge([
        'name' => 'invoice.created',
        'display_name' => 'Fatura criada',
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'subject' => 'Assunto teste',
        'body' => 'Body teste',
        'default_subject' => 'Assunto teste',
        'default_body' => 'Body teste',
        'variables' => [],
        'enabled' => true,
    ], $overrides));
}

it('lists only platform email templates in platform scope page', function () {
    $user = createPlatformUserForEmailTemplates(['platform_email_templates']);

    createNotificationTemplateRecord([
        'name' => 'platform.invoice.created',
        'display_name' => 'Template Platform',
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
    ]);
    createNotificationTemplateRecord([
        'name' => 'tenant.appointment.confirmed',
        'display_name' => 'Template Tenant',
        'scope' => NotificationTemplate::SCOPE_TENANT,
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
    ]);
    createNotificationTemplateRecord([
        'name' => 'platform.whatsapp.legacy',
        'display_name' => 'Template WhatsApp Legado',
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'channel' => NotificationTemplate::CHANNEL_WHATSAPP,
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.platform-email-templates.index'))
        ->assertOk()
        ->assertSee('Template Platform')
        ->assertDontSee('Template Tenant')
        ->assertDontSee('Template WhatsApp Legado');
});

it('lists only tenant email templates in tenant scope page', function () {
    $user = createPlatformUserForEmailTemplates(['tenant_email_templates']);

    createNotificationTemplateRecord([
        'name' => 'platform.invoice.created',
        'display_name' => 'Template Platform',
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
    ]);
    createNotificationTemplateRecord([
        'name' => 'tenant.appointment.confirmed',
        'display_name' => 'Template Tenant',
        'scope' => NotificationTemplate::SCOPE_TENANT,
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.tenant-email-templates.index'))
        ->assertOk()
        ->assertSee('Template Tenant')
        ->assertDontSee('Template Platform');
});

it('blocks manual create/store routes for platform email templates', function () {
    $user = createPlatformUserForEmailTemplates(['platform_email_templates']);

    $this->actingAs($user, 'web')
        ->get('/Platform/platform-email-templates/create')
        ->assertNotFound();

    $this->actingAs($user, 'web')
        ->post('/Platform/platform-email-templates', [
            'name' => 'invoice.overdue',
            'display_name' => 'Fatura vencida',
            'subject' => 'Fatura vencida',
            'body' => 'Seu pagamento esta pendente.',
            'enabled' => '1',
        ])
        ->assertStatus(405);
});

it('blocks manual create/store routes for tenant email templates', function () {
    $user = createPlatformUserForEmailTemplates(['tenant_email_templates']);

    $this->actingAs($user, 'web')
        ->get('/Platform/tenant-email-templates/create')
        ->assertNotFound();

    $this->actingAs($user, 'web')
        ->post('/Platform/tenant-email-templates', [
            'name' => 'appointment.confirmed',
            'display_name' => 'Agendamento confirmado',
            'subject' => 'Agendamento confirmado',
            'body' => 'Seu agendamento foi confirmado.',
            'enabled' => '1',
        ])
        ->assertStatus(405);
});

it('updates platform and tenant email templates in their own scopes', function () {
    $platformUser = createPlatformUserForEmailTemplates(['platform_email_templates']);
    $tenantUser = createPlatformUserForEmailTemplates(['tenant_email_templates']);

    $platformTemplate = createNotificationTemplateRecord([
        'name' => 'invoice.created',
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
        'display_name' => 'Template Platform Original',
    ]);
    $tenantTemplate = createNotificationTemplateRecord([
        'name' => 'appointment.pending_confirmation',
        'scope' => NotificationTemplate::SCOPE_TENANT,
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
        'display_name' => 'Template Tenant Original',
    ]);

    $this->actingAs($platformUser, 'web')
        ->put(route('Platform.platform-email-templates.update', $platformTemplate->id), [
            'name' => 'invoice.created',
            'display_name' => 'Template Platform Atualizado',
            'subject' => 'Assunto Platform',
            'body' => 'Body Platform',
            'enabled' => '1',
        ])
        ->assertRedirect();

    $this->actingAs($tenantUser, 'web')
        ->put(route('Platform.tenant-email-templates.update', $tenantTemplate->id), [
            'name' => 'appointment.pending_confirmation',
            'display_name' => 'Template Tenant Atualizado',
            'subject' => 'Assunto Tenant',
            'body' => 'Body Tenant',
            'enabled' => '1',
        ])
        ->assertRedirect();

    $platformTemplate->refresh();
    $tenantTemplate->refresh();

    expect((string) $platformTemplate->display_name)->toBe('Template Platform Atualizado')
        ->and((string) $tenantTemplate->display_name)->toBe('Template Tenant Atualizado');
});

it('blocks cross-scope edit access between platform and tenant email templates', function () {
    $user = createPlatformUserForEmailTemplates(['platform_email_templates', 'tenant_email_templates']);

    $platformTemplate = createNotificationTemplateRecord([
        'name' => 'platform.invoice.created',
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
    ]);
    $tenantTemplate = createNotificationTemplateRecord([
        'name' => 'tenant.appointment.confirmed',
        'scope' => NotificationTemplate::SCOPE_TENANT,
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.platform-email-templates.edit', $tenantTemplate->id))
        ->assertNotFound();

    $this->actingAs($user, 'web')
        ->get(route('Platform.tenant-email-templates.edit', $platformTemplate->id))
        ->assertNotFound();
});

it('renders separated email menu entries without showing email layouts and keeps email layouts route working', function () {
    $user = createPlatformUserForEmailTemplates([
        'platform_email_templates',
        'tenant_email_templates',
        'notification_templates',
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.platform-email-templates.index'))
        ->assertOk()
        ->assertSee('Templates de Email Platform')
        ->assertSee('Templates de Email Tenant')
        ->assertDontSee('Layouts de Email');

    $this->actingAs($user, 'web')
        ->get(route('Platform.email-layouts.index'))
        ->assertOk();
});
