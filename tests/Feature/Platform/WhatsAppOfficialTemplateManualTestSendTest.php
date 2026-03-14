<?php

use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTemplate;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.phone_id' => '109876543210987',
    ]);

    if (function_exists('set_sysconfig')) {
        set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
    }
});

function createPlatformUserForManualTemplateTest(): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'manual-template-test+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['whatsapp_official_templates'],
        'email_verified_at' => now(),
    ]);
}

function createOfficialTemplateForManualTest(array $overrides = []): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create(array_merge([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => "Ola {{1}}. Valor {{2}}. Vence em {{3}}.",
        'variables' => [
            '1' => 'customer_name',
            '2' => 'invoice_amount',
            '3' => 'due_date',
        ],
        'sample_variables' => [
            '1' => 'Rafael',
            '2' => 'R$ 199,90',
            '3' => '20/03/2026',
        ],
        'version' => 1,
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ], $overrides));
}

it('sends manual test only when template is approved', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/109876543210987/messages' => Http::response([
            'messages' => [['id' => 'wamid.manualtest.1']],
        ], 200),
    ]);

    $user = createPlatformUserForManualTemplateTest();
    $approved = createOfficialTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-templates.test-send', $approved), [
            'phone' => '5511999999999',
            'variables' => [
                'due_date' => '20/03/2026',
                'invoice_amount' => 'R$ 199,90',
                'customer_name' => 'Rafael',
            ],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    Http::assertSent(function ($request) {
        $payload = $request->data();
        $parameters = (array) ($payload['template']['components'][0]['parameters'] ?? []);

        return $request->url() === 'https://graph.facebook.com/v22.0/109876543210987/messages'
            && (($payload['template']['name'] ?? null) === 'saas_invoice_created')
            && (($parameters[0]['text'] ?? null) === 'Rafael')
            && (($parameters[1]['text'] ?? null) === 'R$ 199,90')
            && (($parameters[2]['text'] ?? null) === '20/03/2026');
    });
});

it('blocks manual test for draft pending and rejected statuses', function () {
    Http::fake();

    $user = createPlatformUserForManualTemplateTest();

    foreach ([
        WhatsAppOfficialTemplate::STATUS_DRAFT,
        WhatsAppOfficialTemplate::STATUS_PENDING,
        WhatsAppOfficialTemplate::STATUS_REJECTED,
    ] as $status) {
        $template = createOfficialTemplateForManualTest([
            'key' => 'invoice.created.' . $status,
            'meta_template_name' => 'saas_invoice_created_' . $status,
            'status' => $status,
        ]);

        $this->actingAs($user, 'web')
            ->postJson(route('Platform.whatsapp-official-templates.test-send', $template), [
                'phone' => '5511999999999',
                'variables' => [
                    'customer_name' => 'Rafael',
                    'invoice_amount' => 'R$ 199,90',
                    'due_date' => '20/03/2026',
                ],
            ])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    Http::assertNothingSent();
});

it('returns friendly validation error when required variables are missing', function () {
    Http::fake();

    $user = createPlatformUserForManualTemplateTest();
    $template = createOfficialTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [
                'customer_name' => 'Rafael',
                'invoice_amount' => 'R$ 199,90',
            ],
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ])
        ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'Variaveis obrigatorias ausentes'));

    Http::assertNothingSent();
});

it('sends authentication manual test with remote body and button parameters', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/109876543210987/messages' => Http::response([
            'messages' => [['id' => 'wamid.auth.1']],
        ], 200),
    ]);

    $user = createPlatformUserForManualTemplateTest();
    $template = createOfficialTemplateForManualTest([
        'key' => 'security.2fa_code',
        'meta_template_name' => 'saas_security_2fa_code',
        'category' => 'SECURITY',
        'body_text' => "Ola {{1}}. Seu codigo {{2}} expira em {{3}} minutos.",
        'variables' => [
            '1' => 'customer_name',
            '2' => 'code',
            '3' => 'expires_in_minutes',
        ],
        'sample_variables' => [
            '1' => 'Rafael',
            '2' => '123456',
            '3' => '10',
        ],
        'meta_response' => [
            'data' => [[
                'name' => 'saas_security_2fa_code',
                'language' => 'pt_BR',
                'status' => 'APPROVED',
                'category' => 'AUTHENTICATION',
                'components' => [[
                    'type' => 'BODY',
                    'text' => 'Seu codigo e {{1}}',
                    'example' => [
                        'body_text' => [['123456']],
                    ],
                ], [
                    'type' => 'BUTTONS',
                    'buttons' => [[
                        'type' => 'URL',
                        'url' => 'https://app.allsync.com.br/otp?code={{1}}',
                        'example' => [
                            'url' => ['https://app.allsync.com.br/otp?code=123456'],
                        ],
                    ]],
                ]],
            ]],
        ],
    ]);

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [
                'customer_name' => 'Rafael',
                'code' => '123456',
                'expires_in_minutes' => '10',
            ],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    Http::assertSent(function ($request) {
        $payload = $request->data();

        return $request->url() === 'https://graph.facebook.com/v22.0/109876543210987/messages'
            && (($payload['template']['name'] ?? null) === 'saas_security_2fa_code')
            && (($payload['template']['language']['code'] ?? null) === 'pt_BR')
            && (($payload['template']['components'][0]['type'] ?? null) === 'body')
            && (($payload['template']['components'][0]['parameters'][0]['text'] ?? null) === '123456')
            && (($payload['template']['components'][1]['type'] ?? null) === 'button')
            && (($payload['template']['components'][1]['sub_type'] ?? null) === 'url')
            && ((string) ($payload['template']['components'][1]['index'] ?? '') === '0')
            && (($payload['template']['components'][1]['parameters'][0]['text'] ?? null) === '123456');
    });
});

it('blocks authentication manual test when remote schema expects body params and no variable is provided', function () {
    Http::fake();

    $user = createPlatformUserForManualTemplateTest();
    $template = createOfficialTemplateForManualTest([
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
                    'example' => [
                        'body_text' => [['123456']],
                    ],
                ]],
            ]],
        ],
    ]);

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [],
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ])
        ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'exige 1 parametro de BODY'));

    Http::assertNothingSent();
});

it('blocks authentication manual test when remote schema requires button params and no variable is provided', function () {
    Http::fake();

    $user = createPlatformUserForManualTemplateTest();
    $template = createOfficialTemplateForManualTest([
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
                    'text' => 'Sem placeholders no body',
                ], [
                    'type' => 'BUTTONS',
                    'buttons' => [[
                        'type' => 'URL',
                        'url' => 'https://app.allsync.com.br/otp?code={{1}}',
                        'example' => [
                            'url' => ['https://app.allsync.com.br/otp?code=123456'],
                        ],
                    ]],
                ]],
            ]],
        ],
    ]);

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [],
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ])
        ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'BUTTON index 0'));

    Http::assertNothingSent();
});

it('returns friendly meta error details when manual test is rejected by meta', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/109876543210987/messages' => Http::response([
            'error' => [
                'message' => 'Invalid parameter',
                'type' => 'OAuthException',
                'code' => 100,
                'error_data' => ['details' => 'Template parameter count mismatch'],
                'fbtrace_id' => 'FBTRACE123',
            ],
        ], 400),
    ]);

    $user = createPlatformUserForManualTemplateTest();
    $template = createOfficialTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [
                'customer_name' => 'Rafael',
                'invoice_amount' => 'R$ 199,90',
                'due_date' => '20/03/2026',
            ],
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ])
        ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'Falha HTTP na API Meta')
            && !str_contains((string) $message, 'criacao de template')
            && str_contains((string) $message, 'Invalid parameter')
            && str_contains((string) $message, 'code=100')
            && str_contains((string) $message, 'FBTRACE123'));
});
