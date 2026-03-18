<?php

use App\Exceptions\WhatsAppMetaApiException;
use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\Platform\WhatsAppOfficialTemplateService;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-tenant-test',
        'services.whatsapp.business.waba_id' => '123456789012345',
        'services.whatsapp.business.phone_id' => '109876543210987',
    ]);

    if (function_exists('set_sysconfig')) {
        set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
    }
});

function createPlatformUserForOfficialTenantManualTest(array $modules = ['whatsapp_official_tenant_templates']): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'official-tenant-manual-test+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => $modules,
        'email_verified_at' => now(),
    ]);
}

function createOfficialTenantTemplateForManualTest(array $overrides = []): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create(array_merge([
        'key' => 'appointment.confirmed',
        'meta_template_name' => 'tenant_appointment_confirmed',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => "Ola {{1}}, seu agendamento foi confirmado para {{2}}.",
        'variables' => [
            '1' => 'patient_name',
            '2' => 'appointment_date',
        ],
        'sample_variables' => [
            '1' => 'Ana Souza',
            '2' => '18/03/2026',
        ],
        'version' => 1,
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ], $overrides));
}

/**
 * @return array<string, mixed>
 */
function approvedMetaSyncResponse(string $metaTemplateName, string $language = 'pt_BR'): array
{
    return [
        'data' => [[
            'id' => 'meta-template-tenant-123',
            'name' => $metaTemplateName,
            'language' => $language,
            'status' => 'APPROVED',
        ]],
    ];
}

/**
 * @param array<string, array<string, mixed>> $nameMap
 * @param array<string, mixed>|null $messageResponse
 * @param int $messageStatus
 */
function fakeMetaLookupByName(array $nameMap, ?array $messageResponse = null, int $messageStatus = 200): void
{
    Http::fake(function ($request) use ($nameMap, $messageResponse, $messageStatus) {
        $url = $request->url();

        if (str_contains($url, '/message_templates')) {
            $name = (string) ($request['name'] ?? '');
            return Http::response($nameMap[$name] ?? ['data' => []], 200);
        }

        if (str_contains($url, '/messages')) {
            if ($messageResponse !== null) {
                return Http::response($messageResponse, $messageStatus);
            }

            return Http::response([
                'messages' => [['id' => 'wamid.default.1']],
            ], 200);
        }

        return Http::response([], 404);
    });
}

it('renders manual test modal in official tenant template show page', function () {
    $user = createPlatformUserForOfficialTenantManualTest();
    $template = createOfficialTenantTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertOk()
        ->assertSee('Testar template')
        ->assertSee('manualTemplateTestModal', false)
        ->assertSee('whatsapp-official-tenant-templates', false)
        ->assertSee('test-send', false);
});

it('shows ready state message when template is already synchronized and approved in meta', function () {
    $user = createPlatformUserForOfficialTenantManualTest();
    $template = createOfficialTenantTemplateForManualTest([
        'meta_template_name' => 'tenant_appointment_confirmed',
        'meta_template_id' => 'meta-template-tenant-123',
        'meta_response' => approvedMetaSyncResponse('tenant_appointment_confirmed'),
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertOk()
        ->assertSee('Apto para teste')
        ->assertSee('SIM')
        ->assertDontSee('Template ainda nao foi encontrado na Meta');
});

it('shows republish action only when remote link is stale and not for draft templates', function () {
    $user = createPlatformUserForOfficialTenantManualTest();

    $staleTemplate = createOfficialTenantTemplateForManualTest([
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
        'meta_template_id' => 'old-meta-template-id',
        'meta_waba_id' => 'old-waba-id',
        'meta_response' => ['data' => []],
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-tenant-templates.show', $staleTemplate))
        ->assertOk()
        ->assertSee('Publicar novamente na Meta')
        ->assertDontSee('Enviar para Meta', false);

    $draftTemplate = createOfficialTenantTemplateForManualTest([
        'version' => 2,
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
        'meta_template_id' => null,
        'meta_waba_id' => null,
        'meta_response' => null,
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-tenant-templates.show', $draftTemplate))
        ->assertOk()
        ->assertSee('Enviar para Meta')
        ->assertDontSee('Publicar novamente na Meta');
});

it('shows schema divergence warning when local utility template differs from remote approved schema', function () {
    $user = createPlatformUserForOfficialTenantManualTest();
    $template = createOfficialTenantTemplateForManualTest([
        'meta_template_name' => 'tenant_appointment_confirmed',
        'body_text' => "Ola {{1}}, {{2}}, {{3}}, {{4}}, {{5}}, {{6}}.",
        'variables' => [
            '1' => 'patient_name',
            '2' => 'clinic_name',
            '3' => 'appointment_date',
            '4' => 'appointment_time',
            '5' => 'professional_name',
            '6' => 'appointment_details_link',
        ],
        'sample_variables' => [
            '1' => 'Ana Souza',
            '2' => 'Clinica Vida',
            '3' => '18/03/2026',
            '4' => '14:30',
            '5' => 'Dr. Carlos',
            '6' => 'https://app.exemplo.com/agendamento/123',
        ],
        'meta_template_id' => 'meta-template-tenant-123',
        'meta_response' => [
            'data' => [[
                'id' => 'meta-template-tenant-123',
                'name' => 'tenant_appointment_confirmed',
                'language' => 'pt_BR',
                'status' => 'APPROVED',
                'components' => [[
                    'type' => 'BODY',
                    'text' => 'Ola {{1}}, seu agendamento em {{2}} foi confirmado para {{3}}.',
                ]],
            ]],
        ],
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertOk()
        ->assertSee('Divergencia detectada', false)
        ->assertSee('local=6 parametro(s) e remoto=3', false)
        ->assertSee('Variaveis efetivas para envio', false);
});

it('sends manual test when template exists in meta with canonical tenant name', function () {
    fakeMetaLookupByName([
        'tenant_appointment_confirmed' => approvedMetaSyncResponse('tenant_appointment_confirmed'),
    ], [
        'messages' => [['id' => 'wamid.tenantmanual.1']],
    ]);

    $user = createPlatformUserForOfficialTenantManualTest();
    $template = createOfficialTenantTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-tenant-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [
                'patient_name' => 'Ana Souza',
                'appointment_date' => '18/03/2026',
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
            && (($payload['template']['name'] ?? null) === 'tenant_appointment_confirmed')
            && (($parameters[0]['text'] ?? null) === 'Ana Souza')
            && (($parameters[1]['text'] ?? null) === '18/03/2026');
    });

    $template->refresh();
    expect($template->meta_template_name)->toBe('tenant_appointment_confirmed')
        ->and($template->meta_template_id)->toBe('meta-template-tenant-123');
});

it('maps remote utility body parameters semantically instead of using first local placeholders', function () {
    fakeMetaLookupByName([
        'tenant_appointment_confirmed' => [
            'data' => [[
                'id' => 'meta-template-tenant-789',
                'name' => 'tenant_appointment_confirmed',
                'language' => 'pt_BR',
                'status' => 'APPROVED',
                'components' => [[
                    'type' => 'BODY',
                    'text' => 'Ola {{1}}, seu agendamento foi confirmado. Data: {{2}}. Acesse: {{3}}.',
                ]],
            ]],
        ],
    ], [
        'messages' => [['id' => 'wamid.tenantmanual.3']],
    ]);

    $user = createPlatformUserForOfficialTenantManualTest();
    $template = createOfficialTenantTemplateForManualTest([
        'body_text' => "Ola {{1}}, {{2}}, {{3}}, {{4}}, {{5}}, {{6}}.",
        'variables' => [
            '1' => 'patient_name',
            '2' => 'clinic_name',
            '3' => 'appointment_date',
            '4' => 'appointment_time',
            '5' => 'professional_name',
            '6' => 'appointment_details_link',
        ],
        'sample_variables' => [
            '1' => 'Ana Souza',
            '2' => 'Clinica Vida',
            '3' => '18/03/2026',
            '4' => '14:30',
            '5' => 'Dr. Carlos',
            '6' => 'https://app.exemplo.com/agendamento/123',
        ],
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ]);

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-tenant-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [
                'patient_name' => 'Ana Souza',
                'clinic_name' => 'Clinica Vida',
                'appointment_date' => '18/03/2026',
                'appointment_time' => '14:30',
                'professional_name' => 'Dr. Carlos',
                'appointment_details_link' => 'https://app.exemplo.com/agendamento/123',
            ],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    Http::assertSent(function ($request) {
        if (!str_contains($request->url(), '/messages')) {
            return false;
        }

        $payload = $request->data();
        $parameters = (array) ($payload['template']['components'][0]['parameters'] ?? []);

        return (($payload['template']['name'] ?? null) === 'tenant_appointment_confirmed')
            && count($parameters) === 3
            && (($parameters[0]['text'] ?? null) === 'Ana Souza')
            && (($parameters[1]['text'] ?? null) === '18/03/2026')
            && (($parameters[2]['text'] ?? null) === 'https://app.exemplo.com/agendamento/123');
    });
});

it('returns validation error when required variables are missing in official tenant manual test', function () {
    fakeMetaLookupByName([
        'tenant_appointment_confirmed' => approvedMetaSyncResponse('tenant_appointment_confirmed'),
    ]);

    $user = createPlatformUserForOfficialTenantManualTest();
    $template = createOfficialTenantTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-tenant-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [
                'patient_name' => 'Ana Souza',
            ],
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ])
        ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'Variaveis obrigatorias ausentes'));

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/messages'));
});

it('blocks manual test when template does not exist in meta', function () {
    fakeMetaLookupByName([
        'tenant_appointment_confirmed' => ['data' => []],
    ]);

    $user = createPlatformUserForOfficialTenantManualTest();
    $template = createOfficialTenantTemplateForManualTest([
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ]);

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-tenant-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [
                'patient_name' => 'Ana Souza',
                'appointment_date' => '18/03/2026',
            ],
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ])
        ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'nao foi localizado na Meta para os nomes consultados'));

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/messages'));
});

it('returns friendly meta error when official tenant manual test is rejected by meta', function () {
    fakeMetaLookupByName([
        'tenant_appointment_confirmed' => approvedMetaSyncResponse('tenant_appointment_confirmed'),
    ], [
        'error' => [
            'message' => 'Invalid parameter',
            'type' => 'OAuthException',
            'code' => 100,
            'fbtrace_id' => 'FBTRACE-TENANT-123',
        ],
    ], 400);

    $user = createPlatformUserForOfficialTenantManualTest();
    $template = createOfficialTenantTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-tenant-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [
                'patient_name' => 'Ana Souza',
                'appointment_date' => '18/03/2026',
            ],
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ])
        ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'Falha HTTP na API Meta'));
});

it('reuses official message service in official tenant manual test endpoint', function () {
    $user = createPlatformUserForOfficialTenantManualTest();
    $template = createOfficialTenantTemplateForManualTest();

    $templateServiceMock = Mockery::mock(WhatsAppOfficialTemplateService::class);
    $templateServiceMock
        ->shouldReceive('syncStatus')
        ->once()
        ->withArgs(function (WhatsAppOfficialTemplate $receivedTemplate, ?string $actorId) use ($template, $user): bool {
            return (string) $receivedTemplate->id === (string) $template->id
                && $actorId === (string) $user->id;
        })
        ->andReturn($template->forceFill([
            'meta_template_name' => 'tenant_appointment_confirmed',
            'meta_response' => approvedMetaSyncResponse('tenant_appointment_confirmed'),
            'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
        ]));

    $serviceMock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $serviceMock
        ->shouldReceive('sendManualTest')
        ->once()
        ->withArgs(function (WhatsAppOfficialTemplate $receivedTemplate, string $phone, array $variables, array $context) use ($template, $user): bool {
            return (string) $receivedTemplate->id === (string) $template->id
                && $phone === '5511999999999'
                && ($variables['patient_name'] ?? null) === 'Ana Souza'
                && ($variables['appointment_date'] ?? null) === '18/03/2026'
                && (($context['actor_id'] ?? null) === (string) $user->id);
        })
        ->andReturn([
            'success' => true,
            'http_status' => 200,
            'response_summary' => 'ok',
        ]);

    app()->instance(WhatsAppOfficialTemplateService::class, $templateServiceMock);
    app()->instance(WhatsAppOfficialMessageService::class, $serviceMock);

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-tenant-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [
                'patient_name' => 'Ana Souza',
                'appointment_date' => '18/03/2026',
            ],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
        ]);
});

it('maps meta api exception to friendly 422 response in official tenant manual test endpoint', function () {
    $user = createPlatformUserForOfficialTenantManualTest();
    $template = createOfficialTenantTemplateForManualTest();

    $templateServiceMock = Mockery::mock(WhatsAppOfficialTemplateService::class);
    $templateServiceMock
        ->shouldReceive('syncStatus')
        ->once()
        ->andReturn($template->forceFill([
            'meta_template_name' => 'tenant_appointment_confirmed',
            'meta_response' => approvedMetaSyncResponse('tenant_appointment_confirmed'),
            'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
        ]));

    $serviceMock = Mockery::mock(WhatsAppOfficialMessageService::class);
    $serviceMock
        ->shouldReceive('sendManualTest')
        ->once()
        ->andThrow(new WhatsAppMetaApiException(
            'Falha HTTP na API Meta.',
            400,
            ['message' => 'Invalid parameter', 'code' => 100]
        ));

    app()->instance(WhatsAppOfficialTemplateService::class, $templateServiceMock);
    app()->instance(WhatsAppOfficialMessageService::class, $serviceMock);

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-official-tenant-templates.test-send', $template), [
            'phone' => '5511999999999',
            'variables' => [
                'patient_name' => 'Ana Souza',
                'appointment_date' => '18/03/2026',
            ],
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});
