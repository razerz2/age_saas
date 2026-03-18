<?php

use App\Models\Platform\NotificationTemplate;
use App\Models\Platform\User;
use App\Services\Platform\EmailTemplateTestSendService;

function createPlatformUserForEmailTemplateTest(array $modules): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'email-template-test+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => $modules,
        'email_verified_at' => now(),
    ]);
}

function createScopedEmailTemplate(array $overrides = []): NotificationTemplate
{
    return NotificationTemplate::query()->create(array_merge([
        'name' => 'invoice.created',
        'display_name' => 'Fatura criada',
        'channel' => NotificationTemplate::CHANNEL_EMAIL,
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'subject' => 'Assunto {{customer_name}}',
        'body' => 'Body {{customer_name}} {{payment_link}}',
        'default_subject' => 'Assunto {{customer_name}}',
        'default_body' => 'Body {{customer_name}} {{payment_link}}',
        'variables' => [],
        'enabled' => true,
    ], $overrides));
}

it('shows Testar envio button on platform show page', function () {
    $user = createPlatformUserForEmailTemplateTest(['platform_email_templates']);
    $template = createScopedEmailTemplate([
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'name' => 'platform.invoice.created',
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.platform-email-templates.show', $template))
        ->assertOk()
        ->assertSee('Testar envio')
        ->assertSee('Email de destino');
});

it('shows Testar envio button on tenant show page', function () {
    $user = createPlatformUserForEmailTemplateTest(['tenant_email_templates']);
    $template = createScopedEmailTemplate([
        'scope' => NotificationTemplate::SCOPE_TENANT,
        'name' => 'tenant.appointment.confirmed',
        'display_name' => 'Agendamento confirmado',
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.tenant-email-templates.show', $template))
        ->assertOk()
        ->assertSee('Testar envio')
        ->assertSee('Email de destino');
});

it('submits modal flow with valid email on platform template', function () {
    $user = createPlatformUserForEmailTemplateTest(['platform_email_templates']);
    $template = createScopedEmailTemplate([
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'name' => 'platform.invoice.created',
    ]);

    $this->actingAs($user, 'web')
        ->post(route('Platform.platform-email-templates.test-send', $template), [
            'destination_email' => 'destino@example.com',
            'test_send_modal' => '1',
        ])
        ->assertRedirect(route('Platform.platform-email-templates.show', $template))
        ->assertSessionHas('success');
});

it('sends platform test email successfully', function () {
    $user = createPlatformUserForEmailTemplateTest(['platform_email_templates']);
    $template = createScopedEmailTemplate([
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'name' => 'platform.subscription.created',
        'subject' => 'Assunto {{tenant_name}}',
        'body' => 'Conteudo {{tenant_name}} {{login_url}}',
        'default_subject' => 'Assunto {{tenant_name}}',
        'default_body' => 'Conteudo {{tenant_name}} {{login_url}}',
    ]);

    $this->actingAs($user, 'web')
        ->post(route('Platform.platform-email-templates.test-send', $template), [
            'destination_email' => 'platform-test@example.com',
            'test_send_modal' => '1',
        ])
        ->assertRedirect(route('Platform.platform-email-templates.show', $template))
        ->assertSessionHas('success', 'Email de teste enviado com sucesso.');
});

it('sends tenant test email successfully', function () {
    $user = createPlatformUserForEmailTemplateTest(['tenant_email_templates']);
    $template = createScopedEmailTemplate([
        'scope' => NotificationTemplate::SCOPE_TENANT,
        'name' => 'tenant.waitlist.offered',
        'display_name' => 'Waitlist offered',
        'subject' => 'Assunto {{patient.name}}',
        'body' => 'Conteudo {{patient.name}} {{links.waitlist_offer}}',
        'default_subject' => 'Assunto {{patient.name}}',
        'default_body' => 'Conteudo {{patient.name}} {{links.waitlist_offer}}',
    ]);

    $this->actingAs($user, 'web')
        ->post(route('Platform.tenant-email-templates.test-send', $template), [
            'destination_email' => 'tenant-test@example.com',
            'test_send_modal' => '1',
        ])
        ->assertRedirect(route('Platform.tenant-email-templates.show', $template))
        ->assertSessionHas('success', 'Email de teste enviado com sucesso.');
});

it('blocks invalid destination email', function () {
    $user = createPlatformUserForEmailTemplateTest(['platform_email_templates']);
    $template = createScopedEmailTemplate([
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'name' => 'platform.invoice.overdue',
    ]);

    $this->actingAs($user, 'web')
        ->from(route('Platform.platform-email-templates.show', $template))
        ->post(route('Platform.platform-email-templates.test-send', $template), [
            'destination_email' => 'email-invalido',
            'test_send_modal' => '1',
        ])
        ->assertRedirect(route('Platform.platform-email-templates.show', $template))
        ->assertSessionHasErrors(['destination_email']);
});

it('blocks cross-scope access on show and test-send routes', function () {
    $user = createPlatformUserForEmailTemplateTest(['platform_email_templates', 'tenant_email_templates']);

    $platformTemplate = createScopedEmailTemplate([
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'name' => 'platform.invoice.created',
    ]);

    $tenantTemplate = createScopedEmailTemplate([
        'scope' => NotificationTemplate::SCOPE_TENANT,
        'name' => 'tenant.appointment.confirmed',
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.platform-email-templates.show', $tenantTemplate->id))
        ->assertNotFound();

    $this->actingAs($user, 'web')
        ->post(route('Platform.platform-email-templates.test-send', $tenantTemplate->id), [
            'destination_email' => 'cross@example.com',
        ])
        ->assertNotFound();

    $this->actingAs($user, 'web')
        ->get(route('Platform.tenant-email-templates.show', $platformTemplate->id))
        ->assertNotFound();

    $this->actingAs($user, 'web')
        ->post(route('Platform.tenant-email-templates.test-send', $platformTemplate->id), [
            'destination_email' => 'cross@example.com',
        ])
        ->assertNotFound();
});

it('renders subject and body with controlled dummy fallback for unresolved placeholders', function () {
    $template = createScopedEmailTemplate([
        'scope' => NotificationTemplate::SCOPE_PLATFORM,
        'name' => 'platform.placeholder.fallback',
        'subject' => 'Assunto {{patient.name}} {{unknown.subject_token}}',
        'body' => "Linha 1 {{patient.name}}\nLinha 2 {{links.payment_url}}\nLinha 3 {{unknown.body_token}}",
        'default_subject' => 'Assunto {{patient.name}} {{unknown.subject_token}}',
        'default_body' => "Linha 1 {{patient.name}}\nLinha 2 {{links.payment_url}}\nLinha 3 {{unknown.body_token}}",
    ]);

    $rendered = app(EmailTemplateTestSendService::class)->renderTemplate($template);

    expect((string) $rendered['subject'])
        ->not->toContain('{{')
        ->and((string) $rendered['body'])
        ->not->toContain('{{')
        ->and((string) $rendered['body'])
        ->toContain('https://example.com/teste');
});
