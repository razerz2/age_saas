<?php

use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTemplate;

beforeEach(function (): void {
    config(['services.whatsapp.provider' => 'whatsapp_business']);

    if (function_exists('set_sysconfig')) {
        set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
    }
});

function createPlatformUserWithWhatsAppOfficialModule(): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'admin+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['whatsapp_official_templates'],
        'email_verified_at' => now(),
    ]);
}

function createOfficialTemplate(string $status, int $version = 1): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create([
        'key' => 'platform.billing.invoice_due',
        'meta_template_name' => 'platform_billing_invoice_due',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => "Ola {{1}}.\n\nSua fatura vence em {{2}}.",
        'variables' => [
            '1' => 'tenant.trade_name',
            '2' => 'invoice.due_date',
        ],
        'version' => $version,
        'status' => $status,
    ]);
}

function updatePayload(array $overrides = []): array
{
    return array_merge([
        'key' => 'platform.billing.invoice_due',
        'meta_template_name' => 'platform_billing_invoice_due',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'header_text' => '',
        'body_text' => "Ola {{1}}.\n\nVencimento: {{2}}.\n\nMensagem final fixa.",
        'footer_text' => '',
        'buttons' => [],
        'variables' => [
            '1' => 'tenant.trade_name',
            '2' => 'invoice.due_date',
        ],
    ], $overrides);
}

it('allows edit form for draft templates', function () {
    $user = createPlatformUserWithWhatsAppOfficialModule();
    $template = createOfficialTemplate(WhatsAppOfficialTemplate::STATUS_DRAFT);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.edit', $template))
        ->assertOk()
        ->assertSee('Editar Template WhatsApp Oficial');
});

it('allows edit form and save for rejected templates', function () {
    $user = createPlatformUserWithWhatsAppOfficialModule();
    $template = createOfficialTemplate(WhatsAppOfficialTemplate::STATUS_REJECTED);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.edit', $template))
        ->assertOk()
        ->assertSee('Template rejeitado pela Meta');

    $this->actingAs($user, 'web')
        ->put(route('Platform.whatsapp-official-templates.update', $template), updatePayload())
        ->assertRedirect(route('Platform.whatsapp-official-templates.show', $template));

    $template->refresh();

    expect($template->status)->toBe(WhatsAppOfficialTemplate::STATUS_REJECTED)
        ->and($template->body_text)->toContain('Mensagem final fixa.');
});

it('blocks direct edit page for approved templates', function () {
    $user = createPlatformUserWithWhatsAppOfficialModule();
    $template = createOfficialTemplate(WhatsAppOfficialTemplate::STATUS_APPROVED);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.edit', $template))
        ->assertRedirect(route('Platform.whatsapp-official-templates.show', $template))
        ->assertSessionHas('warning');
});

it('creates a new draft version when updating an approved template', function () {
    $user = createPlatformUserWithWhatsAppOfficialModule();
    $approved = createOfficialTemplate(WhatsAppOfficialTemplate::STATUS_APPROVED, 1);

    $this->actingAs($user, 'web')
        ->put(route('Platform.whatsapp-official-templates.update', $approved), updatePayload([
            'body_text' => "Ola {{1}}.\n\nData: {{2}}.\n\nVersao nova em draft.",
        ]))
        ->assertRedirect();

    $approved->refresh();
    $newVersion = WhatsAppOfficialTemplate::query()
        ->where('provider', WhatsAppOfficialTemplate::PROVIDER)
        ->where('key', $approved->key)
        ->where('version', 2)
        ->first();

    expect($approved->status)->toBe(WhatsAppOfficialTemplate::STATUS_APPROVED)
        ->and($approved->body_text)->toContain('Sua fatura vence em')
        ->and($newVersion)->not->toBeNull()
        ->and($newVersion?->status)->toBe(WhatsAppOfficialTemplate::STATUS_DRAFT)
        ->and($newVersion?->body_text)->toContain('Versao nova em draft.');
});
