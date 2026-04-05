<?php

use App\Http\Requests\Tenant\StoreCampaignRequest;
use App\Models\Platform\Tenant;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignTemplate;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\Providers\ProviderConfigResolver;
use App\Services\Tenant\CampaignChannelGate;
use App\Services\Tenant\CampaignDeliveryService;
use App\Services\Tenant\CampaignRenderer;
use App\Services\Tenant\CampaignTemplateProviderResolver;
use App\Services\Tenant\EmailSender;
use App\Services\Tenant\NotificationDeliveryLogger;
use App\Services\Tenant\TemplateRenderer;
use App\Services\Tenant\TenantWhatsAppConfigService;
use App\Services\Tenant\WhatsAppSender;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ],
        'database.connections.tenant' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ],
    ]);

    DB::purge('sqlite');
    DB::reconnect('sqlite');
    DB::purge('tenant');
    DB::reconnect('tenant');

    Schema::connection('sqlite')->create('whatsapp_official_templates', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->string('tenant_id')->nullable();
        $table->string('key')->nullable();
        $table->string('meta_template_name')->nullable();
        $table->string('provider')->default('whatsapp_business');
        $table->string('category')->nullable();
        $table->string('language')->nullable();
        $table->text('body_text')->nullable();
        $table->json('variables')->nullable();
        $table->json('sample_variables')->nullable();
        $table->unsignedInteger('version')->default(1);
        $table->string('status')->default('approved');
        $table->timestamps();
    });

    Schema::connection('tenant')->create('campaign_templates', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('name', 150);
        $table->string('channel', 32)->default('whatsapp');
        $table->string('provider_type', 32)->default('unofficial');
        $table->string('template_key')->nullable();
        $table->string('title')->nullable();
        $table->text('content');
        $table->json('variables_json')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    Schema::connection('tenant')->create('tenant_settings', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->string('key')->unique();
        $table->text('value')->nullable();
        $table->timestamps();
    });

    $tenant = new Tenant();
    $tenant->id = 'tenant-hardening';
    $tenant->subdomain = 'tenant-hardening';

    app()->instance(config('multitenancy.current_tenant_container_key', 'currentTenant'), $tenant);
});

afterEach(function (): void {
    \Mockery::close();
    app()->forgetInstance(config('multitenancy.current_tenant_container_key', 'currentTenant'));
});

function createOfficialTemplateForCampaignHardening(array $overrides = []): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create(array_merge([
        'id' => 'official-template-1',
        'tenant_id' => 'tenant-hardening',
        'key' => 'campaign.official.template',
        'meta_template_name' => 'campaign_official_template',
        'provider' => 'whatsapp_business',
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => 'Ola {{1}}',
        'variables' => ['1' => 'patient.name'],
        'sample_variables' => ['1' => 'Maria'],
        'version' => 1,
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ], $overrides));
}

function createUnofficialCampaignTemplateForHardening(array $overrides = []): CampaignTemplate
{
    return CampaignTemplate::query()->create(array_merge([
        'name' => 'Template nao oficial',
        'channel' => 'whatsapp',
        'provider_type' => 'unofficial',
        'content' => 'Oi {{patient.name}}',
        'variables_json' => [],
        'is_active' => true,
    ], $overrides));
}

/**
 * @return array{passed:bool,errors:array<string,array<int,string>>,request:StoreCampaignRequest}
 */
function validateCampaignStorePayloadForHardening(array $payload, string $provider): array
{
    $resolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $resolver->shouldReceive('resolveWhatsAppProvider')->andReturn($provider);
    $resolver->shouldReceive('isOfficialWhatsApp')->andReturn($provider === 'whatsapp_business');
    app()->instance(CampaignTemplateProviderResolver::class, $resolver);

    $gate = \Mockery::mock(CampaignChannelGate::class);
    $gate->shouldReceive('availableChannels')->andReturn(['email', 'whatsapp']);
    app()->instance(CampaignChannelGate::class, $gate);

    $request = StoreCampaignRequest::create('/workspace/tenant-hardening/campaigns', 'POST', $payload);
    $request->setContainer(app());
    $request->setRedirector(app('redirect'));

    try {
        $request->validateResolved();
        return ['passed' => true, 'errors' => [], 'request' => $request];
    } catch (ValidationException $exception) {
        return ['passed' => false, 'errors' => $exception->errors(), 'request' => $request];
    }
}

function baseCampaignPayloadForHardening(array $whatsAppPayload): array
{
    return [
        'name' => 'Campanha teste',
        'type' => 'manual',
        'channels' => ['whatsapp'],
        'content_json' => [
            'version' => 1,
            'whatsapp' => $whatsAppPayload,
        ],
        'audience_json' => [
            'version' => 1,
        ],
    ];
}

it('requires approved official template for official provider', function () {
    $template = createOfficialTemplateForCampaignHardening();

    $result = validateCampaignStorePayloadForHardening(
        baseCampaignPayloadForHardening([
            'composition_mode' => 'template',
            'template_type' => 'official',
            'official_template_id' => (string) $template->id,
        ]),
        'whatsapp_business'
    );

    expect($result['passed'])->toBeTrue();

    $normalized = $result['request']->input('content_json.whatsapp');
    expect(is_array($normalized))->toBeTrue()
        ->and($normalized['provider'] ?? null)->toBe('whatsapp_business')
        ->and(array_key_exists('template_id', $normalized))->toBeFalse();
});

it('blocks manual whatsapp composition for official provider', function () {
    $result = validateCampaignStorePayloadForHardening(
        baseCampaignPayloadForHardening([
            'composition_mode' => 'manual',
            'message_type' => 'text',
            'text' => 'mensagem livre',
        ]),
        'whatsapp_business'
    );

    expect($result['passed'])->toBeFalse()
        ->and($result['errors'])->toHaveKey('content_json.whatsapp.composition_mode');
});

it('blocks manual fields when official provider is in template mode', function () {
    $template = createOfficialTemplateForCampaignHardening([
        'id' => 'official-template-manual-block',
    ]);

    $result = validateCampaignStorePayloadForHardening(
        baseCampaignPayloadForHardening([
            'composition_mode' => 'template',
            'template_type' => 'official',
            'official_template_id' => (string) $template->id,
            'message_type' => 'text',
            'text' => 'nao permitido',
        ]),
        'whatsapp_business'
    );

    expect($result['passed'])->toBeFalse()
        ->and($result['errors'])->toHaveKey('content_json.whatsapp.message_type');
});

it('accepts manual whatsapp for unofficial provider', function () {
    $result = validateCampaignStorePayloadForHardening(
        baseCampaignPayloadForHardening([
            'composition_mode' => 'manual',
            'message_type' => 'text',
            'text' => 'Olá {{patient.name}}',
        ]),
        'waha'
    );

    expect($result['passed'])->toBeTrue();
});

it('accepts unofficial template mode when template is active', function () {
    $template = createUnofficialCampaignTemplateForHardening([
        'is_active' => true,
    ]);

    $result = validateCampaignStorePayloadForHardening(
        baseCampaignPayloadForHardening([
            'composition_mode' => 'template',
            'template_type' => 'unofficial',
            'template_id' => $template->id,
        ]),
        'waha'
    );

    expect($result['passed'])->toBeTrue();
});

it('blocks unofficial template mode when template is inactive', function () {
    $template = createUnofficialCampaignTemplateForHardening([
        'is_active' => false,
    ]);

    $result = validateCampaignStorePayloadForHardening(
        baseCampaignPayloadForHardening([
            'composition_mode' => 'template',
            'template_type' => 'unofficial',
            'template_id' => $template->id,
        ]),
        'waha'
    );

    expect($result['passed'])->toBeFalse()
        ->and($result['errors'])->toHaveKey('content_json.whatsapp.template_id');
});

it('renders legacy manual whatsapp campaign payload', function () {
    $resolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $resolver->shouldReceive('resolveWhatsAppProvider')->andReturn('waha');

    $renderer = new CampaignRenderer(new TemplateRenderer(), $resolver);
    $campaign = new Campaign([
        'content_json' => [
            'whatsapp' => [
                'provider' => 'waha',
                'message_type' => 'text',
                'text' => 'Oi {{patient.name}}',
            ],
        ],
    ]);

    $payload = $renderer->renderChannel($campaign, 'whatsapp', [
        'patient' => ['name' => 'Maria'],
    ]);

    expect($payload['composition_mode'] ?? null)->toBe('manual')
        ->and($payload['text'] ?? null)->toBe('Oi Maria')
        ->and($payload['render_error'] ?? null)->toBeNull();
});

it('renders unofficial campaign template payload', function () {
    $template = createUnofficialCampaignTemplateForHardening([
        'content' => 'Ola {{patient.name}}',
    ]);

    $resolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $resolver->shouldReceive('resolveWhatsAppProvider')->andReturn('waha');

    $renderer = new CampaignRenderer(new TemplateRenderer(), $resolver);
    $campaign = new Campaign([
        'content_json' => [
            'whatsapp' => [
                'provider' => 'waha',
                'composition_mode' => 'template',
                'template_type' => 'unofficial',
                'template_id' => $template->id,
            ],
        ],
    ]);

    $payload = $renderer->renderChannel($campaign, 'whatsapp', [
        'patient' => ['name' => 'Joao'],
    ]);

    expect($payload['template_type'] ?? null)->toBe('unofficial')
        ->and($payload['template_resolution_status'] ?? null)->toBe('resolved')
        ->and($payload['render_error'] ?? null)->toBeNull()
        ->and($payload['text'] ?? null)->toBe('Ola Joao');
});

it('prepares official campaign template payload', function () {
    $template = createOfficialTemplateForCampaignHardening([
        'id' => 'official-template-2',
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ]);

    $resolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $resolver->shouldReceive('resolveWhatsAppProvider')->andReturn('whatsapp_business');

    $renderer = new CampaignRenderer(new TemplateRenderer(), $resolver);
    $campaign = new Campaign([
        'content_json' => [
            'whatsapp' => [
                'provider' => 'whatsapp_business',
                'composition_mode' => 'template',
                'template_type' => 'official',
                'official_template_id' => (string) $template->id,
            ],
        ],
    ]);

    $payload = $renderer->renderChannel($campaign, 'whatsapp', [
        'patient' => ['name' => 'Ana'],
    ]);

    expect($payload['template_type'] ?? null)->toBe('official')
        ->and(($payload['official_template']['id'] ?? null))->toBe((string) $template->id)
        ->and($payload['render_error'] ?? null)->toBeNull();
});

it('delivers manual whatsapp branch for unofficial provider', function () {
    $renderer = \Mockery::mock(CampaignRenderer::class);
    $renderer->shouldReceive('renderChannel')->once()->andReturn([
        'composition_mode' => 'manual',
        'message_type' => 'text',
        'text' => 'Mensagem manual',
    ]);

    $emailSender = \Mockery::mock(EmailSender::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $whatsAppSender->shouldReceive('send')->once()->andReturn(true);
    $logger = \Mockery::mock(NotificationDeliveryLogger::class);
    $officialService = \Mockery::mock(WhatsAppOfficialMessageService::class);
    $providerConfigResolver = \Mockery::mock(ProviderConfigResolver::class);
    $tenantWhatsAppConfigService = \Mockery::mock(TenantWhatsAppConfigService::class);
    $providerResolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $providerResolver->shouldReceive('isOfficialWhatsApp')->andReturn(false);

    $service = new CampaignDeliveryService(
        $renderer,
        $emailSender,
        $whatsAppSender,
        $logger,
        $officialService,
        $providerConfigResolver,
        $tenantWhatsAppConfigService,
        $providerResolver
    );

    $result = $service->sendTest(new Campaign(), 'whatsapp', '5567999999999', [], []);

    expect($result['success'])->toBeTrue()
        ->and($result['error_message'])->toBeNull();
});

it('delivers unofficial template whatsapp branch', function () {
    $renderer = \Mockery::mock(CampaignRenderer::class);
    $renderer->shouldReceive('renderChannel')->once()->andReturn([
        'composition_mode' => 'template',
        'template_type' => 'unofficial',
        'template_id' => 10,
        'template_is_active' => true,
        'template_name' => 'Template ativo',
        'text' => 'Mensagem de template',
    ]);

    $emailSender = \Mockery::mock(EmailSender::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $whatsAppSender->shouldReceive('send')->once()->andReturn(true);
    $logger = \Mockery::mock(NotificationDeliveryLogger::class);
    $officialService = \Mockery::mock(WhatsAppOfficialMessageService::class);
    $providerConfigResolver = \Mockery::mock(ProviderConfigResolver::class);
    $tenantWhatsAppConfigService = \Mockery::mock(TenantWhatsAppConfigService::class);
    $providerResolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $providerResolver->shouldReceive('isOfficialWhatsApp')->andReturn(false);

    $service = new CampaignDeliveryService(
        $renderer,
        $emailSender,
        $whatsAppSender,
        $logger,
        $officialService,
        $providerConfigResolver,
        $tenantWhatsAppConfigService,
        $providerResolver
    );

    $result = $service->sendTest(new Campaign(), 'whatsapp', '5567999999999', [], []);

    expect($result['success'])->toBeTrue()
        ->and($result['error_message'])->toBeNull();
});

it('delivers official template whatsapp branch', function () {
    $template = createOfficialTemplateForCampaignHardening([
        'id' => 'official-template-delivery',
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ]);

    $renderer = \Mockery::mock(CampaignRenderer::class);
    $renderer->shouldReceive('renderChannel')->once()->andReturn([
        'composition_mode' => 'template',
        'template_type' => 'official',
        'official_template_id' => (string) $template->id,
        'official_variables' => ['patient_name' => 'Maria'],
        'template_resolution_status' => 'resolved',
        'render_error' => null,
    ]);

    $emailSender = \Mockery::mock(EmailSender::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $logger = \Mockery::mock(NotificationDeliveryLogger::class);
    $logger->shouldReceive('logSuccess')->once();
    $officialService = \Mockery::mock(WhatsAppOfficialMessageService::class);
    $officialService->shouldReceive('sendManualTest')->once()->andReturn([
        'success' => true,
        'http_status' => 200,
        'response_summary' => 'ok',
    ]);
    $providerConfigResolver = \Mockery::mock(ProviderConfigResolver::class);
    $tenantWhatsAppConfigService = \Mockery::mock(TenantWhatsAppConfigService::class);
    $tenantWhatsAppConfigService->shouldReceive('applyRuntimeConfig')->once();
    $providerResolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $providerResolver->shouldReceive('isOfficialWhatsApp')->andReturn(true);

    $service = new CampaignDeliveryService(
        $renderer,
        $emailSender,
        $whatsAppSender,
        $logger,
        $officialService,
        $providerConfigResolver,
        $tenantWhatsAppConfigService,
        $providerResolver
    );

    $result = $service->sendTest(new Campaign(), 'whatsapp', '5567999999999', [], []);

    expect($result['success'])->toBeTrue()
        ->and($result['error_message'])->toBeNull();
});
