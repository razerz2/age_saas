<?php

use App\Exceptions\WhatsAppMetaApiException;
use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Services\Platform\WhatsAppOfficialTemplateService;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-tenant-sync',
        'services.whatsapp.business.waba_id' => '123456789012345',
    ]);

    if (function_exists('set_sysconfig')) {
        set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
    }
});

function createPlatformUserForOfficialTenantSync(array $modules = ['whatsapp_official_tenant_templates']): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'official-tenant-sync+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => $modules,
        'email_verified_at' => now(),
    ]);
}

function createOfficialTenantTemplateForSync(array $overrides = []): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create(array_merge([
        'key' => 'appointment.pending_confirmation',
        'meta_template_name' => 'tenant_appointment_pending_confirmation',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => "Olá {{1}}.\n\nSeu agendamento está pendente.\nData: {{2}}.\nLink: {{3}}.\n\nSe tiver dúvidas, fale com a clínica.",
        'variables' => [
            '1' => 'patient_name',
            '2' => 'appointment_date',
            '3' => 'appointment_confirm_link',
        ],
        'sample_variables' => [
            '1' => 'Ana Souza',
            '2' => '18/03/2026',
            '3' => 'https://app.exemplo.com/confirmar/abc123',
        ],
        'version' => 1,
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
    ], $overrides));
}

it('sync action in official tenant reuses existing official template service', function () {
    $user = createPlatformUserForOfficialTenantSync();
    $template = createOfficialTenantTemplateForSync();

    $serviceMock = Mockery::mock(WhatsAppOfficialTemplateService::class);
    $serviceMock
        ->shouldReceive('syncStatus')
        ->once()
        ->withArgs(function (WhatsAppOfficialTemplate $receivedTemplate, ?string $actorId) use ($template, $user): bool {
            return (string) $receivedTemplate->id === (string) $template->id
                && $actorId === (string) $user->id;
        })
        ->andReturn($template->forceFill([
            'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
            'meta_response' => [
                'data' => [[
                    'name' => 'tenant_appointment_pending_confirmation',
                    'language' => 'pt_BR',
                    'status' => 'APPROVED',
                ]],
            ],
        ]));

    app()->instance(WhatsAppOfficialTemplateService::class, $serviceMock);

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.sync', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHas('success', 'Status sincronizado com a Meta com sucesso.');
});

it('sync action in official tenant handles domain errors with user feedback', function () {
    $user = createPlatformUserForOfficialTenantSync();
    $template = createOfficialTenantTemplateForSync();

    $serviceMock = Mockery::mock(WhatsAppOfficialTemplateService::class);
    $serviceMock
        ->shouldReceive('syncStatus')
        ->once()
        ->andThrow(new DomainException('Falha de configuração para sincronizar.'));

    app()->instance(WhatsAppOfficialTemplateService::class, $serviceMock);

    $this->from(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.sync', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHasErrors('template');
});

it('sync action in official tenant handles meta api errors with user-safe message', function () {
    $user = createPlatformUserForOfficialTenantSync();
    $template = createOfficialTenantTemplateForSync();

    $serviceMock = Mockery::mock(WhatsAppOfficialTemplateService::class);
    $serviceMock
        ->shouldReceive('syncStatus')
        ->once()
        ->andThrow(new WhatsAppMetaApiException(
            'Falha HTTP na sincronização.',
            400,
            ['message' => 'Invalid parameter', 'code' => 100]
        ));

    app()->instance(WhatsAppOfficialTemplateService::class, $serviceMock);

    $this->from(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.sync', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHasErrors('template');
});

it('sync action in official tenant updates status and last synced timestamp', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/123456789012345/message_templates*' => Http::response([
            'data' => [[
                'id' => 'meta-template-tenant-123',
                'name' => 'tenant_appointment_pending_confirmation',
                'language' => 'pt_BR',
                'status' => 'APPROVED',
            ]],
        ], 200),
    ]);

    $user = createPlatformUserForOfficialTenantSync();
    $template = createOfficialTenantTemplateForSync([
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
        'last_synced_at' => null,
        'meta_template_id' => null,
    ]);

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.sync', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHas('success', 'Status sincronizado com a Meta com sucesso.');

    $template->refresh();

    expect($template->status)->toBe(WhatsAppOfficialTemplate::STATUS_APPROVED)
        ->and($template->last_synced_at)->not->toBeNull()
        ->and($template->meta_template_id)->toBe('meta-template-tenant-123');

    Http::assertSent(function ($request) {
        return str_starts_with($request->url(), 'https://graph.facebook.com/v22.0/123456789012345/message_templates')
            && (($request['name'] ?? null) === 'tenant_appointment_pending_confirmation')
            && (($request['language'] ?? null) === 'pt_BR');
    });
});

it('sync action in official tenant enforces tenant_ canonical naming and ignores non-canonical remote names', function () {
    Http::fake(function ($request) {
        if (!str_starts_with($request->url(), 'https://graph.facebook.com/v22.0/123456789012345/message_templates')) {
            return Http::response([], 404);
        }

        $name = (string) ($request['name'] ?? '');
        if ($name === 'tenant_appointment_pending_confirmation') {
            return Http::response(['data' => []], 200);
        }

        if ($name === 'appointment_pending_confirmation') {
            return Http::response([
                'data' => [[
                    'id' => 'meta-template-real-456',
                    'name' => 'appointment_pending_confirmation',
                    'language' => 'pt_BR',
                    'status' => 'APPROVED',
                ]],
            ], 200);
        }

        return Http::response(['data' => []], 200);
    });

    $user = createPlatformUserForOfficialTenantSync();
    $template = createOfficialTenantTemplateForSync([
        'meta_template_name' => 'appointment_pending_confirmation',
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
        'meta_template_id' => null,
        'last_synced_at' => null,
    ]);

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.sync', $template))
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.show', $template))
        ->assertSessionHas('warning', 'Sincronizacao concluida, mas nenhum template remoto foi localizado para os nomes consultados.');

    $template->refresh();

    expect($template->meta_template_name)->toBe('tenant_appointment_pending_confirmation')
        ->and($template->status)->toBe(WhatsAppOfficialTemplate::STATUS_DRAFT)
        ->and($template->meta_template_id)->toBeNull()
        ->and($template->last_synced_at)->not->toBeNull();

    Http::assertNotSent(function ($request) {
        return str_starts_with($request->url(), 'https://graph.facebook.com/v22.0/123456789012345/message_templates')
            && (($request['name'] ?? null) === 'appointment_pending_confirmation');
    });
});
