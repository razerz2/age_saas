<?php

use App\Exceptions\WhatsAppMetaApiException;
use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Services\Platform\WhatsAppOfficialTemplateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-tenant-submit',
        'services.whatsapp.business.waba_id' => '123456789012345',
    ]);

    if (function_exists('set_sysconfig')) {
        set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
    }
});

function createPlatformUserForOfficialTenantSubmit(array $modules = ['whatsapp_official_tenant_templates']): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'official-tenant-submit+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => $modules,
        'email_verified_at' => now(),
    ]);
}

function createOfficialTenantTemplateForSubmit(array $overrides = []): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create(array_merge([
        'key' => 'appointment.pending_confirmation',
        'meta_template_name' => 'tenant_appointment_pending_confirmation',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => "Ola {{1}}, confirme seu agendamento para {{2}}. Obrigado.",
        'variables' => [
            '1' => 'patient_name',
            '2' => 'appointment_date',
        ],
        'sample_variables' => [
            '1' => 'Ana Souza',
            '2' => '18/03/2026',
        ],
        'version' => 1,
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
    ], $overrides));
}

/**
 * @param array<string, array<string, mixed>> $lookupByName
 * @param array<string, mixed>|null $createResponse
 */
function fakeMetaRepublishFlow(array $lookupByName = [], ?array $createResponse = null, int $createStatus = 200): void
{
    Http::fake(function ($request) use ($lookupByName, $createResponse, $createStatus) {
        if (!str_contains($request->url(), '/message_templates')) {
            return Http::response([], 404);
        }

        if (strtoupper($request->method()) === 'GET') {
            $name = (string) ($request['name'] ?? '');
            return Http::response($lookupByName[$name] ?? ['data' => []], 200);
        }

        return Http::response(
            $createResponse ?? [
                'id' => 'meta-template-new-tenant-001',
                'status' => 'PENDING_REVIEW',
            ],
            $createStatus
        );
    });
}

it('submit action in official tenant reuses official template service', function () {
    $user = createPlatformUserForOfficialTenantSubmit();
    $template = createOfficialTenantTemplateForSubmit();

    $serviceMock = Mockery::mock(WhatsAppOfficialTemplateService::class);
    $serviceMock
        ->shouldReceive('submitToMeta')
        ->once()
        ->withArgs(function (WhatsAppOfficialTemplate $receivedTemplate, ?string $actorId) use ($template, $user): bool {
            return (string) $receivedTemplate->id === (string) $template->id
                && $actorId === (string) $user->id;
        })
        ->andReturn($template);

    app()->instance(WhatsAppOfficialTemplateService::class, $serviceMock);

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.submit', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHas('success', 'Template oficial tenant enviado para a Meta com sucesso.');
});

it('submit action in official tenant handles domain errors with user feedback', function () {
    $user = createPlatformUserForOfficialTenantSubmit();
    $template = createOfficialTenantTemplateForSubmit();

    $serviceMock = Mockery::mock(WhatsAppOfficialTemplateService::class);
    $serviceMock
        ->shouldReceive('submitToMeta')
        ->once()
        ->andThrow(new DomainException('Template invalido para submissao.'));

    app()->instance(WhatsAppOfficialTemplateService::class, $serviceMock);

    $this->from(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.submit', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHasErrors('template');
});

it('submit action in official tenant handles meta api errors with user-safe message', function () {
    $user = createPlatformUserForOfficialTenantSubmit();
    $template = createOfficialTenantTemplateForSubmit();

    $serviceMock = Mockery::mock(WhatsAppOfficialTemplateService::class);
    $serviceMock
        ->shouldReceive('submitToMeta')
        ->once()
        ->andThrow(new WhatsAppMetaApiException(
            'Falha HTTP na criacao de template Meta: status 400.',
            400,
            ['message' => 'Invalid parameter', 'code' => 100]
        ));

    app()->instance(WhatsAppOfficialTemplateService::class, $serviceMock);

    $this->from(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.submit', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHasErrors('template');
});

it('submit action in official tenant normalizes stale local meta name before sending to meta', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/123456789012345/message_templates*' => Http::response([
            'id' => 'meta-template-new-tenant-submit-001',
            'status' => 'PENDING_REVIEW',
        ], 200),
    ]);

    $user = createPlatformUserForOfficialTenantSubmit();
    $template = createOfficialTenantTemplateForSubmit([
        'meta_template_name' => 'appointment_pending_confirmation',
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
        'meta_template_id' => null,
    ]);

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.submit', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHas('success', 'Template oficial tenant enviado para a Meta com sucesso.');

    $template->refresh();

    expect($template->meta_template_name)->toBe('tenant_appointment_pending_confirmation')
        ->and($template->meta_template_id)->toBe('meta-template-new-tenant-submit-001')
        ->and($template->status)->toBe(WhatsAppOfficialTemplate::STATUS_PENDING);

    Http::assertSent(function ($request) {
        if (strtoupper($request->method()) !== 'POST') {
            return false;
        }

        if (!str_contains($request->url(), '/message_templates')) {
            return false;
        }

        $payload = $request->data();
        return (($payload['name'] ?? null) === 'tenant_appointment_pending_confirmation')
            && (($payload['language'] ?? null) === 'pt_BR');
    });
});

it('republish action in official tenant recreates template in meta using local record as source of truth', function () {
    fakeMetaRepublishFlow();

    $user = createPlatformUserForOfficialTenantSubmit();
    $template = createOfficialTenantTemplateForSubmit([
        'meta_template_name' => 'appointment_pending_confirmation',
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
        'meta_template_id' => 'old-meta-template-id',
        'meta_waba_id' => 'old-waba-id',
        'meta_response' => [
            'data' => [[
                'id' => 'old-meta-template-id',
                'name' => 'tenant_appointment_pending_confirmation',
                'language' => 'pt_BR',
                'status' => 'APPROVED',
            ]],
        ],
    ]);

    $localId = (string) $template->id;

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.republish', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHas('success', 'Template oficial tenant publicado novamente na Meta com sucesso.');

    $template->refresh();

    expect((string) $template->id)->toBe($localId)
        ->and($template->meta_template_name)->toBe('tenant_appointment_pending_confirmation')
        ->and($template->meta_template_id)->toBe('meta-template-new-tenant-001')
        ->and($template->status)->toBe(WhatsAppOfficialTemplate::STATUS_PENDING)
        ->and($template->last_synced_at)->not->toBeNull();

    Http::assertSent(function ($request) {
        if (strtoupper($request->method()) !== 'POST') {
            return false;
        }

        if (!str_contains($request->url(), '/message_templates')) {
            return false;
        }

        $payload = $request->data();
        return (($payload['name'] ?? null) === 'tenant_appointment_pending_confirmation')
            && (($payload['language'] ?? null) === 'pt_BR')
            && (($payload['components'][0]['type'] ?? null) === 'BODY');
    });
});

it('republish action in official tenant blocks when local template is incomplete', function () {
    fakeMetaRepublishFlow();

    $user = createPlatformUserForOfficialTenantSubmit();
    $template = createOfficialTenantTemplateForSubmit([
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
        'meta_template_id' => 'old-meta-template-id',
        'sample_variables' => [
            '1' => 'Ana Souza',
        ],
    ]);

    $previousMetaId = (string) $template->meta_template_id;
    $previousStatus = (string) $template->status;

    $this->from(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.republish', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHasErrors('template');

    $template->refresh();

    expect((string) $template->meta_template_id)->toBe($previousMetaId)
        ->and((string) $template->status)->toBe($previousStatus);

    Http::assertNotSent(function ($request) {
        return strtoupper($request->method()) === 'POST'
            && str_contains($request->url(), '/message_templates');
    });
});

it('republish action keeps local record consistent when meta submission fails', function () {
    fakeMetaRepublishFlow([], [
        'error' => [
            'message' => 'Invalid parameter',
            'type' => 'OAuthException',
            'code' => 100,
        ],
    ], 400);

    $user = createPlatformUserForOfficialTenantSubmit();
    $template = createOfficialTenantTemplateForSubmit([
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
        'meta_template_id' => 'old-meta-template-id',
    ]);

    $previousMetaId = (string) $template->meta_template_id;
    $previousStatus = (string) $template->status;

    $this->from(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.republish', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHasErrors('template');

    $template->refresh();

    expect((string) $template->meta_template_id)->toBe($previousMetaId)
        ->and((string) $template->status)->toBe($previousStatus);
});

it('republish action returns objective message on meta name conflict', function () {
    fakeMetaRepublishFlow([], [
        'error' => [
            'message' => 'Template name already exists',
            'type' => 'OAuthException',
            'code' => 100,
            'error_data' => [
                'details' => 'A template with this name already exists',
            ],
        ],
    ], 400);

    $user = createPlatformUserForOfficialTenantSubmit();
    $template = createOfficialTenantTemplateForSubmit([
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
        'meta_template_id' => 'old-meta-template-id',
    ]);

    $this->from(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.republish', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHasErrors([
            'template' => 'Conflito de nome na Meta: ja existe template com este nome/idioma. Ajuste o Nome Meta e tente novamente.',
        ]);
});

it('republish action in official tenant is protected against double submission by lock', function () {
    Http::fake();

    $user = createPlatformUserForOfficialTenantSubmit();
    $template = createOfficialTenantTemplateForSubmit([
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
        'meta_template_id' => 'old-meta-template-id',
    ]);

    $lock = Cache::lock('wa_official_tenant_template_republish:' . (string) $template->id, 20);
    expect($lock->get())->toBeTrue();

    try {
        $this->actingAs($user, 'web')
            ->post(route('Platform.whatsapp-official-tenant-templates.republish', $template))
            ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
            ->assertSessionHas('warning', 'Ja existe uma publicacao em andamento para este template. Aguarde alguns segundos e tente novamente.');
    } finally {
        $lock->release();
    }

    Http::assertNothingSent();
});
