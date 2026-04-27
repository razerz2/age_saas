<?php

use App\Models\Platform\Tenant;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignRecipient;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\Providers\ProviderConfigResolver;
use App\Services\Tenant\CampaignAudienceBuilder;
use App\Services\Tenant\CampaignDeliveryService;
use App\Services\Tenant\CampaignRenderer;
use App\Services\Tenant\CampaignStarter;
use App\Services\Tenant\CampaignTemplateProviderResolver;
use App\Services\Tenant\EmailSender;
use App\Services\Tenant\NotificationDeliveryLogger;
use App\Services\Tenant\TemplateRenderer;
use App\Services\Tenant\TenantWhatsAppConfigService;
use App\Services\Tenant\WhatsAppSender;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    Schema::connection('tenant')->create('patients', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->string('full_name')->nullable();
        $table->string('cpf')->nullable();
        $table->date('birth_date')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    Schema::connection('tenant')->create('campaigns', function (Blueprint $table): void {
        $table->id();
        $table->string('name', 150)->nullable();
        $table->string('type', 32)->default('manual');
        $table->string('status', 32)->default('active');
        $table->json('channels_json')->nullable();
        $table->json('content_json')->nullable();
        $table->json('audience_json')->nullable();
        $table->json('automation_json')->nullable();
        $table->json('rules_json')->nullable();
        $table->timestamp('scheduled_at')->nullable();
        $table->timestamps();
    });

    Schema::connection('tenant')->create('campaign_runs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('campaign_id');
        $table->string('status', 32)->default('running');
        $table->json('context_json')->nullable();
        $table->json('totals_json')->nullable();
        $table->timestamp('started_at')->nullable();
        $table->timestamp('finished_at')->nullable();
        $table->string('error_message', 500)->nullable();
        $table->timestamps();
    });

    Schema::connection('tenant')->create('campaign_recipients', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('campaign_id');
        $table->unsignedBigInteger('campaign_run_id');
        $table->string('target_type', 50);
        $table->unsignedBigInteger('target_id')->nullable();
        $table->string('channel', 20);
        $table->string('destination', 255);
        $table->string('status', 20)->default('pending');
        $table->timestamp('sent_at')->nullable();
        $table->string('error_message', 500)->nullable();
        $table->json('vars_json')->nullable();
        $table->json('meta_json')->nullable();
        $table->timestamps();
    });

    Schema::connection('tenant')->create('tenant_settings', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->string('key')->unique();
        $table->text('value')->nullable();
        $table->timestamps();
    });

    $tenant = new Tenant();
    $tenant->id = 'tenant-campaign-run-flow';
    $tenant->subdomain = 'clinica-run-flow';
    $tenant->trade_name = 'Clinica Run Flow';
    $tenant->phone = '556793087866';
    $tenant->email = 'contato@clinica-run-flow.test';
    $tenant->address = 'Rua do Fluxo, 99';

    app()->instance(config('multitenancy.current_tenant_container_key', 'currentTenant'), $tenant);
});

afterEach(function (): void {
    \Mockery::close();
    app()->forgetInstance(config('multitenancy.current_tenant_container_key', 'currentTenant'));
});

it('persists recipient vars_json and hydrates whatsapp message in real run dispatch path', function () {
    DB::connection('tenant')->table('patients')->insert([
        'id' => 'a1578636-d8e6-4995-bb5a-939bc69b7e31',
        'full_name' => 'Rafael Flores',
        'cpf' => '12345678901',
        'birth_date' => '1990-04-21',
        'email' => 'rafael@example.com',
        'phone' => '556793087866',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $campaign = Campaign::query()->create([
        'name' => 'Campanha fluxo run',
        'type' => 'manual',
        'status' => 'active',
        'channels_json' => ['whatsapp'],
        'content_json' => [
            'whatsapp' => [
                'provider' => 'waha',
                'composition_mode' => 'manual',
                'message_type' => 'text',
                'text' => 'Ola {{patient.name}}. Link {{links.public_booking}}',
            ],
        ],
        'audience_json' => [
            'source' => 'patients',
            'filters' => [
                'patient' => [
                    'is_active' => true,
                ],
            ],
        ],
        'automation_json' => [],
        'rules_json' => null,
    ]);

    $starter = new CampaignStarter(app(CampaignAudienceBuilder::class));
    $startResult = $starter->startCampaign($campaign, null, 'manual', false);

    $run = $startResult['run'];
    expect((int) ($startResult['totals']['pending'] ?? 0))->toBe(1);

    $recipient = CampaignRecipient::query()
        ->where('campaign_run_id', (int) $run->id)
        ->first();

    expect($recipient)->not->toBeNull();
    $vars = is_array($recipient?->vars_json) ? $recipient->vars_json : [];

    expect(data_get($vars, 'patient.name'))->toBe('Rafael Flores')
        ->and(data_get($vars, 'links.public_booking'))->toContain('/customer/clinica-run-flow/agendamento/identificar');

    $providerResolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $providerResolver->shouldReceive('resolveWhatsAppProvider')->andReturn('waha');
    $providerResolver->shouldReceive('isOfficialWhatsApp')->andReturn(false);

    $renderer = new CampaignRenderer(new TemplateRenderer(), $providerResolver);
    $emailSender = \Mockery::mock(EmailSender::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $capturedSendArgs = null;
    $whatsAppSender->shouldReceive('send')
        ->once()
        ->andReturnUsing(function (
            string $tenantId,
            string $destination,
            string $text,
            array $meta,
            mixed $providerOverride
        ) use (&$capturedSendArgs): bool {
            $capturedSendArgs = [
                'tenant_id' => $tenantId,
                'destination' => $destination,
                'text' => $text,
                'meta' => $meta,
                'provider_override' => $providerOverride,
            ];

            return true;
        })
    ;

    $logger = \Mockery::mock(NotificationDeliveryLogger::class);
    $logger->shouldReceive('logError')->never();
    $officialService = \Mockery::mock(WhatsAppOfficialMessageService::class);
    $providerConfigResolver = \Mockery::mock(ProviderConfigResolver::class);
    $tenantWhatsAppConfigService = \Mockery::mock(TenantWhatsAppConfigService::class);

    $deliveryService = new CampaignDeliveryService(
        $renderer,
        $emailSender,
        $whatsAppSender,
        $logger,
        $officialService,
        $providerConfigResolver,
        $tenantWhatsAppConfigService,
        $providerResolver
    );

    $result = $deliveryService->sendRecipient($campaign, $run, $recipient);
    if (($result['success'] ?? false) !== true) {
        throw new \RuntimeException('campaign_run_delivery_failed: ' . (string) ($result['error_message'] ?? 'unknown'));
    }

    expect($result['success'])->toBeTrue()
        ->and($result['error_message'])->toBeNull()
        ->and($capturedSendArgs)->toBeArray()
        ->and($capturedSendArgs['tenant_id'] ?? null)->toBe('tenant-campaign-run-flow')
        ->and($capturedSendArgs['destination'] ?? null)->toBe('556793087866')
        ->and((string) ($capturedSendArgs['text'] ?? ''))->toContain('Rafael Flores')
        ->and((string) ($capturedSendArgs['text'] ?? ''))->toContain('/customer/clinica-run-flow/agendamento/identificar')
        ->and((string) ($capturedSendArgs['text'] ?? ''))->not->toContain('/workspace/clinica-run-flow/agendamento/identificar')
        ->and(($capturedSendArgs['meta']['origin'] ?? null))->toBe('campaign_run')
        ->and(array_key_exists('unknown_placeholders', $capturedSendArgs['meta'] ?? []))->toBeFalse()
        ->and(array_key_exists('provider_override', $capturedSendArgs))->toBeTrue();
});

it('hydrates vars correctly for a newly created scheduled run', function () {
    DB::connection('tenant')->table('patients')->insert([
        'id' => 'b5c4203b-8aa6-4fb1-9491-0922f98ec1a5',
        'full_name' => 'Julia Prado',
        'cpf' => '99988877766',
        'birth_date' => '1995-01-10',
        'email' => 'julia@example.com',
        'phone' => '556791234567',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $campaign = Campaign::query()->create([
        'name' => 'Campanha agendada nova',
        'type' => 'manual',
        'status' => 'active',
        'channels_json' => ['whatsapp'],
        'content_json' => [
            'whatsapp' => [
                'provider' => 'waha',
                'composition_mode' => 'manual',
                'message_type' => 'text',
                'text' => 'Ola {{patient.name}}. Link {{links.public_booking}}',
            ],
        ],
        'audience_json' => [
            'source' => 'patients',
            'filters' => [
                'patient' => [
                    'is_active' => true,
                ],
            ],
        ],
        'automation_json' => [],
        'rules_json' => null,
    ]);

    $starter = new CampaignStarter(app(CampaignAudienceBuilder::class));
    $startResult = $starter->startCampaign($campaign, null, 'scheduled', false);
    $run = $startResult['run'];

    $recipient = CampaignRecipient::query()
        ->where('campaign_run_id', (int) $run->id)
        ->firstOrFail();

    $vars = is_array($recipient->vars_json) ? $recipient->vars_json : [];
    expect(data_get($vars, 'patient.name'))->toBe('Julia Prado')
        ->and(data_get($vars, 'links.public_booking'))->toContain('/customer/clinica-run-flow/agendamento/identificar');

    $providerResolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $providerResolver->shouldReceive('resolveWhatsAppProvider')->andReturn('waha');
    $providerResolver->shouldReceive('isOfficialWhatsApp')->andReturn(false);

    $renderer = new CampaignRenderer(new TemplateRenderer(), $providerResolver);
    $emailSender = \Mockery::mock(EmailSender::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $whatsAppSender->shouldReceive('send')
        ->once()
        ->withArgs(function (
            string $tenantId,
            string $destination,
            string $text,
            array $meta
        ): bool {
            return $tenantId === 'tenant-campaign-run-flow'
                && $destination === '556791234567'
                && str_contains($text, 'Julia Prado')
                && str_contains($text, '/customer/clinica-run-flow/agendamento/identificar')
                && ($meta['origin'] ?? null) === 'campaign_run';
        })
        ->andReturn(true);

    $logger = \Mockery::mock(NotificationDeliveryLogger::class);
    $logger->shouldReceive('logError')->never();
    $officialService = \Mockery::mock(WhatsAppOfficialMessageService::class);
    $providerConfigResolver = \Mockery::mock(ProviderConfigResolver::class);
    $tenantWhatsAppConfigService = \Mockery::mock(TenantWhatsAppConfigService::class);

    $deliveryService = new CampaignDeliveryService(
        $renderer,
        $emailSender,
        $whatsAppSender,
        $logger,
        $officialService,
        $providerConfigResolver,
        $tenantWhatsAppConfigService,
        $providerResolver
    );

    $result = $deliveryService->sendRecipient($campaign, $run, $recipient);
    expect($result['success'])->toBeTrue()
        ->and($result['error_message'])->toBeNull();
});

it('rehydrates legacy scheduled recipient vars before dispatch', function () {
    $patientId = 'a1578636-d8e6-4995-bb5a-939bc69b7e31';
    DB::connection('tenant')->table('patients')->insert([
        'id' => $patientId,
        'full_name' => 'Rafael Flores',
        'cpf' => '12345678901',
        'birth_date' => '1990-04-21',
        'email' => 'rafael@example.com',
        'phone' => '556793087866',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $campaign = Campaign::query()->create([
        'name' => 'Campanha agendada legado',
        'type' => 'manual',
        'status' => 'active',
        'channels_json' => ['whatsapp'],
        'content_json' => [
            'whatsapp' => [
                'provider' => 'waha',
                'composition_mode' => 'manual',
                'message_type' => 'text',
                'text' => 'Ola {{patient.name}}. Link {{links.public_booking}}',
            ],
        ],
        'audience_json' => ['source' => 'patients'],
        'automation_json' => [],
        'rules_json' => null,
    ]);

    $runId = DB::connection('tenant')->table('campaign_runs')->insertGetId([
        'campaign_id' => (int) $campaign->id,
        'status' => 'running',
        'context_json' => json_encode([
            'trigger' => 'scheduled',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'totals_json' => json_encode([
            'total' => 1,
            'success' => 0,
            'error' => 0,
            'skipped' => 0,
            'pending' => 1,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'started_at' => now(),
        'finished_at' => null,
        'error_message' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $legacyVars = [
        'patient' => [
            'id' => $patientId,
            'full_name' => 'Rafael Flores',
            'phone' => '556793087866',
        ],
        'links' => [
            'public_booking' => 'https://allsync.com.br/workspace/clinica-run-flow/agendamento/identificar',
        ],
        'now' => [
            'date' => '2026-04-21',
        ],
    ];

    $recipientId = DB::connection('tenant')->table('campaign_recipients')->insertGetId([
        'campaign_id' => (int) $campaign->id,
        'campaign_run_id' => (int) $runId,
        'target_type' => 'patient',
        'target_id' => null,
        'channel' => 'whatsapp',
        'destination' => '556793087866',
        'status' => 'pending',
        'sent_at' => null,
        'error_message' => null,
        'vars_json' => json_encode($legacyVars, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'meta_json' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $run = \App\Models\Tenant\CampaignRun::query()->findOrFail($runId);
    $recipient = CampaignRecipient::query()->findOrFail($recipientId);

    $providerResolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $providerResolver->shouldReceive('resolveWhatsAppProvider')->andReturn('waha');
    $providerResolver->shouldReceive('isOfficialWhatsApp')->andReturn(false);

    $renderer = new CampaignRenderer(new TemplateRenderer(), $providerResolver);
    $emailSender = \Mockery::mock(EmailSender::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $capturedSendArgs = null;
    $whatsAppSender->shouldReceive('send')
        ->once()
        ->andReturnUsing(function (
            string $tenantId,
            string $destination,
            string $text,
            array $meta,
            mixed $providerOverride
        ) use (&$capturedSendArgs): bool {
            $capturedSendArgs = [
                'tenant_id' => $tenantId,
                'destination' => $destination,
                'text' => $text,
                'meta' => $meta,
                'provider_override' => $providerOverride,
            ];

            return true;
        });

    $logger = \Mockery::mock(NotificationDeliveryLogger::class);
    $logger->shouldReceive('logError')->never();
    $officialService = \Mockery::mock(WhatsAppOfficialMessageService::class);
    $providerConfigResolver = \Mockery::mock(ProviderConfigResolver::class);
    $tenantWhatsAppConfigService = \Mockery::mock(TenantWhatsAppConfigService::class);

    $deliveryService = new CampaignDeliveryService(
        $renderer,
        $emailSender,
        $whatsAppSender,
        $logger,
        $officialService,
        $providerConfigResolver,
        $tenantWhatsAppConfigService,
        $providerResolver
    );

    $result = $deliveryService->sendRecipient($campaign, $run, $recipient);

    expect($result['success'])->toBeTrue()
        ->and($result['error_message'])->toBeNull()
        ->and((string) ($capturedSendArgs['text'] ?? ''))->toContain('Rafael Flores')
        ->and((string) ($capturedSendArgs['text'] ?? ''))->toContain('/customer/clinica-run-flow/agendamento/identificar')
        ->and((string) ($capturedSendArgs['text'] ?? ''))->not->toContain('/workspace/clinica-run-flow/agendamento/identificar');

    $recipient->refresh();
    $updatedVars = is_array($recipient->vars_json) ? $recipient->vars_json : [];

    expect(data_get($updatedVars, 'patient.name'))->toBe('Rafael Flores')
        ->and(data_get($updatedVars, 'links.public_booking'))->toContain('/customer/clinica-run-flow/agendamento/identificar')
        ->and((string) data_get($updatedVars, 'links.public_booking'))->not->toContain('/workspace/clinica-run-flow/agendamento/identificar')
        ->and(data_get($updatedVars, 'clinic.name'))->toBe('Clinica Run Flow');
});
