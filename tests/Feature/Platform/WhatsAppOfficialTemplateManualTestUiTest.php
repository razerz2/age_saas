<?php

use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTemplate;

function createPlatformUserForManualTestUi(): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'manual-test-ui+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['whatsapp_official_templates'],
        'email_verified_at' => now(),
    ]);
}

function createTemplateForManualTestUi(string $status, array $overrides = []): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create(array_merge([
        'key' => 'invoice.created.' . $status,
        'meta_template_name' => 'saas_invoice_created_' . $status,
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => "Ola {{1}}. Valor {{2}}.",
        'variables' => [
            '1' => 'customer_name',
            '2' => 'invoice_amount',
        ],
        'sample_variables' => [
            '1' => 'Rafael',
            '2' => 'R$ 299,90',
        ],
        'version' => 1,
        'status' => $status,
    ], $overrides));
}

it('shows manual test modal action enabled for approved templates', function () {
    $user = createPlatformUserForManualTestUi();
    $template = createTemplateForManualTestUi(WhatsAppOfficialTemplate::STATUS_APPROVED);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.show', $template))
        ->assertOk()
        ->assertSee('Testar template')
        ->assertSee('Preencha as variaveis e envie o teste para um numero valido.')
        ->assertDontSee('Teste manual bloqueado: apenas templates com status APPROVED podem ser enviados.');
});

it('shows manual test blocked warning for non-approved templates', function () {
    $user = createPlatformUserForManualTestUi();
    $template = createTemplateForManualTestUi(WhatsAppOfficialTemplate::STATUS_DRAFT);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.show', $template))
        ->assertOk()
        ->assertSee('Testar template')
        ->assertSee('O teste manual so e permitido para status')
        ->assertSee('id="manual-template-send-btn" disabled', false);
});

it('shows authentication remote requirements for body and buttons in manual test modal', function () {
    $user = createPlatformUserForManualTestUi();
    $template = createTemplateForManualTestUi(
        WhatsAppOfficialTemplate::STATUS_APPROVED,
        [
            'key' => 'security.2fa_code',
            'meta_template_name' => 'saas_security_2fa_code',
            'category' => 'SECURITY',
            'meta_response' => [
                'data' => [[
                    'name' => 'saas_security_2fa_code',
                    'language' => 'pt_BR',
                    'status' => 'APPROVED',
                    'category' => 'AUTHENTICATION',
                    'components' => [[
                        'type' => 'BODY',
                        'text' => 'Seu codigo e {{1}}',
                    ], [
                        'type' => 'BUTTONS',
                        'buttons' => [[
                            'type' => 'URL',
                            'url' => 'https://app.allsync.com.br/otp?code={{1}}',
                        ]],
                    ]],
                ]],
            ],
        ]
    );

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.show', $template))
        ->assertOk()
        ->assertSee('Template AUTHENTICATION: o envio de teste segue o schema remoto aprovado da Meta.')
        ->assertSee('O remoto exige 1 parametro(s) no BODY')
        ->assertSee('e 1 parametro(s) em BUTTONS.')
        ->assertSee('BUTTON index 0 (URL):')
        ->assertSee('1 parametro(s).');
});
