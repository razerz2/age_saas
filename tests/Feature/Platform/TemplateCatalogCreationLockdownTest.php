<?php

use App\Models\Platform\NotificationTemplate;
use App\Models\Platform\TenantDefaultNotificationTemplate;
use App\Models\Platform\User;
use App\Models\Platform\WhatsAppUnofficialTemplate;

function createPlatformUserForTemplateCatalogLock(): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'catalog-lock+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => [
            'platform_email_templates',
            'tenant_email_templates',
            'whatsapp_unofficial_templates',
            'tenant_default_notification_templates',
        ],
        'email_verified_at' => now(),
    ]);
}

function createPlatformEmailTemplateForCatalogLock(array $overrides = []): NotificationTemplate
{
    return NotificationTemplate::query()->create(array_merge([
        'name' => 'invoice.created',
        'display_name' => 'Fatura criada',
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'subject' => 'Assunto platform',
        'body' => 'Body platform',
        'default_subject' => 'Assunto platform',
        'default_body' => 'Body platform',
        'variables' => [],
        'enabled' => true,
    ], $overrides));
}

function createTenantEmailTemplateForCatalogLock(array $overrides = []): NotificationTemplate
{
    return NotificationTemplate::query()->create(array_merge([
        'name' => 'appointment.confirmed',
        'display_name' => 'Agendamento confirmado',
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
        'scope' => NotificationTemplate::SCOPE_TENANT,
        'subject' => 'Assunto tenant',
        'body' => 'Body tenant',
        'default_subject' => 'Assunto tenant',
        'default_body' => 'Body tenant',
        'variables' => [],
        'enabled' => true,
    ], $overrides));
}

function createWhatsAppUnofficialTemplateForCatalogLock(array $overrides = []): WhatsAppUnofficialTemplate
{
    return WhatsAppUnofficialTemplate::query()->create(array_merge([
        'key' => 'invoice.overdue',
        'title' => 'Fatura vencida',
        'category' => 'billing',
        'body' => 'Ola {{customer_name}}',
        'variables' => ['customer_name'],
        'is_active' => true,
    ], $overrides));
}

function createTenantDefaultTemplateForCatalogLock(array $overrides = []): TenantDefaultNotificationTemplate
{
    return TenantDefaultNotificationTemplate::query()->create(array_merge([
        'channel' => 'whatsapp',
        'key' => 'appointment.pending_confirmation',
        'title' => 'Agendamento pendente',
        'category' => 'appointment',
        'language' => 'pt_BR',
        'subject' => null,
        'content' => 'Ola {{patient.name}}',
        'variables' => ['patient.name'],
        'is_active' => true,
    ], $overrides));
}

it('does not show Novo Template button on the four catalog listings', function () {
    $user = createPlatformUserForTemplateCatalogLock();

    createPlatformEmailTemplateForCatalogLock();
    createTenantEmailTemplateForCatalogLock();
    createWhatsAppUnofficialTemplateForCatalogLock();
    createTenantDefaultTemplateForCatalogLock();

    $this->actingAs($user, 'web')
        ->get(route('Platform.platform-email-templates.index'))
        ->assertOk()
        ->assertDontSee('Novo Template');

    $this->actingAs($user, 'web')
        ->get(route('Platform.tenant-email-templates.index'))
        ->assertOk()
        ->assertDontSee('Novo Template');

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-unofficial-templates.index'))
        ->assertOk()
        ->assertDontSee('Novo Template');

    $this->actingAs($user, 'web')
        ->get(route('Platform.tenant-default-notification-templates.index'))
        ->assertOk()
        ->assertDontSee('Novo Template');
});

it('blocks direct access to create routes in the four catalog areas', function () {
    $user = createPlatformUserForTemplateCatalogLock();

    $this->actingAs($user, 'web')->get('/Platform/platform-email-templates/create')->assertNotFound();
    $this->actingAs($user, 'web')->get('/Platform/tenant-email-templates/create')->assertNotFound();
    $this->actingAs($user, 'web')->get('/Platform/whatsapp-unofficial-templates/create')->assertNotFound();
    $this->actingAs($user, 'web')->get('/Platform/tenant-default-notification-templates/create')->assertNotFound();
});

it('blocks manual POST to store routes in the four catalog areas', function () {
    $user = createPlatformUserForTemplateCatalogLock();

    $this->actingAs($user, 'web')
        ->post('/Platform/platform-email-templates', [
            'name' => 'invoice.new',
            'display_name' => 'Novo',
            'subject' => 'Teste',
            'body' => 'Teste',
        ])
        ->assertStatus(405);

    $this->actingAs($user, 'web')
        ->post('/Platform/tenant-email-templates', [
            'name' => 'appointment.new',
            'display_name' => 'Novo',
            'subject' => 'Teste',
            'body' => 'Teste',
        ])
        ->assertStatus(405);

    $this->actingAs($user, 'web')
        ->post('/Platform/whatsapp-unofficial-templates', [
            'key' => 'new.key',
            'title' => 'Novo',
            'category' => 'billing',
            'body' => 'Teste',
        ])
        ->assertStatus(405);

    $this->actingAs($user, 'web')
        ->post('/Platform/tenant-default-notification-templates', [
            'channel' => 'whatsapp',
            'key' => 'new.key',
            'title' => 'Novo',
            'category' => 'appointment',
            'language' => 'pt_BR',
            'content' => 'Teste',
        ])
        ->assertStatus(405);
});

it('keeps edit/update working on the four catalog modules', function () {
    $user = createPlatformUserForTemplateCatalogLock();

    $platformEmail = createPlatformEmailTemplateForCatalogLock();
    $tenantEmail = createTenantEmailTemplateForCatalogLock();
    $waUnofficial = createWhatsAppUnofficialTemplateForCatalogLock();
    $tenantDefault = createTenantDefaultTemplateForCatalogLock();

    $this->actingAs($user, 'web')
        ->get(route('Platform.platform-email-templates.edit', $platformEmail))
        ->assertOk();

    $this->actingAs($user, 'web')
        ->put(route('Platform.platform-email-templates.update', $platformEmail), [
            'name' => 'invoice.created',
            'display_name' => 'Fatura criada atualizada',
            'subject' => 'Assunto platform atualizado',
            'body' => 'Body platform atualizado',
            'enabled' => '1',
        ])
        ->assertRedirect();

    $this->actingAs($user, 'web')
        ->get(route('Platform.tenant-email-templates.edit', $tenantEmail))
        ->assertOk();

    $this->actingAs($user, 'web')
        ->put(route('Platform.tenant-email-templates.update', $tenantEmail), [
            'name' => 'appointment.confirmed',
            'display_name' => 'Agendamento confirmado atualizado',
            'subject' => 'Assunto tenant atualizado',
            'body' => 'Body tenant atualizado',
            'enabled' => '1',
        ])
        ->assertRedirect();

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-unofficial-templates.edit', $waUnofficial))
        ->assertOk();

    $this->actingAs($user, 'web')
        ->put(route('Platform.whatsapp-unofficial-templates.update', $waUnofficial), [
            'key' => 'invoice.overdue',
            'title' => 'Fatura vencida atualizada',
            'category' => 'billing',
            'body' => 'Ola {{customer_name}} atualizada',
            'variables' => ['customer_name'],
            'is_active' => '1',
        ])
        ->assertRedirect();

    $this->actingAs($user, 'web')
        ->get(route('Platform.tenant-default-notification-templates.edit', $tenantDefault))
        ->assertOk();

    $this->actingAs($user, 'web')
        ->put(route('Platform.tenant-default-notification-templates.update', $tenantDefault), [
            'channel' => 'whatsapp',
            'key' => 'appointment.pending_confirmation',
            'title' => 'Agendamento pendente atualizado',
            'category' => 'appointment',
            'language' => 'pt_BR',
            'subject' => '',
            'content' => 'Ola {{patient.name}} atualizado',
            'variables' => ['patient.name'],
            'is_active' => '1',
        ])
        ->assertRedirect();

    $platformEmail->refresh();
    $tenantEmail->refresh();
    $waUnofficial->refresh();
    $tenantDefault->refresh();

    expect((string) $platformEmail->display_name)->toBe('Fatura criada atualizada')
        ->and((string) $tenantEmail->display_name)->toBe('Agendamento confirmado atualizado')
        ->and((string) $waUnofficial->title)->toBe('Fatura vencida atualizada')
        ->and((string) $tenantDefault->title)->toBe('Agendamento pendente atualizado');
});
