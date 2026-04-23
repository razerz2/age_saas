<?php

use App\Models\Platform\Tenant;
use App\Models\Tenant\Campaign;
use App\Services\Tenant\CampaignAudienceBuilder;
use App\Services\Tenant\CampaignRecipientContextBuilder;
use App\Services\Tenant\CampaignRenderer;
use App\Services\Tenant\CampaignTemplateProviderResolver;
use App\Services\Tenant\TemplateRenderer;
use App\Support\Tenant\CampaignTemplateVariableCatalog;
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

    $tenant = new Tenant();
    $tenant->id = 'tenant-campaign-audit';
    $tenant->subdomain = 'clinica-auditoria';
    $tenant->trade_name = 'Clinica Auditoria';
    $tenant->phone = '556793087866';
    $tenant->email = 'contato@clinica-auditoria.test';
    $tenant->address = 'Rua Principal, 10';

    app()->instance(config('multitenancy.current_tenant_container_key', 'currentTenant'), $tenant);
});

afterEach(function (): void {
    \Mockery::close();
    app()->forgetInstance(config('multitenancy.current_tenant_container_key', 'currentTenant'));
});

/**
 * @return array<int,string>
 */
function campaignAuditCatalogPlaceholders(): array
{
    $catalog = app(CampaignTemplateVariableCatalog::class)->all();
    $keys = [];

    foreach ($catalog as $group) {
        $items = is_array($group) ? $group : [];
        foreach ($items as $item) {
            $raw = trim((string) (is_array($item) ? ($item['key'] ?? '') : $item));
            if ($raw === '') {
                continue;
            }

            if (preg_match('/^\{\{\s*([A-Za-z0-9_.-]+)\s*\}\}$/', $raw, $matches) !== 1) {
                continue;
            }

            $keys[] = (string) ($matches[1] ?? '');
        }
    }

    return array_values(array_unique(array_filter($keys)));
}

/**
 * @param array<int,string> $keys
 */
function campaignAuditTemplateFromKeys(array $keys): string
{
    $placeholders = array_map(
        static fn (string $key): string => '{{' . $key . '}}',
        $keys
    );

    return implode(' | ', $placeholders);
}

function campaignAuditRenderer(string $provider = 'waha'): CampaignRenderer
{
    $resolver = \Mockery::mock(CampaignTemplateProviderResolver::class);
    $resolver->shouldReceive('resolveWhatsAppProvider')->andReturn($provider);

    return new CampaignRenderer(new TemplateRenderer(), $resolver);
}

it('hydrates all documented campaign placeholders across manual test, patient test and real run', function () {
    $documented = campaignAuditCatalogPlaceholders();

    expect($documented)->toEqualCanonicalizing([
        'clinic.name',
        'clinic.phone',
        'clinic.email',
        'clinic.address',
        'patient.name',
        'patient.phone',
        'patient.email',
        'links.public_booking',
        'links.portal',
        'links.whatsapp',
    ]);

    DB::connection('tenant')->table('patients')->insert([
        'id' => 'd45a1f34-4f33-4d63-9026-cc34dd8f95ec',
        'full_name' => 'Rafael Flores',
        'cpf' => '12345678901',
        'birth_date' => '1990-04-21',
        'email' => 'rafael@example.com',
        'phone' => '556793087866',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $campaign = new Campaign([
        'content_json' => [
            'whatsapp' => [
                'provider' => 'waha',
                'composition_mode' => 'manual',
                'message_type' => 'text',
                'text' => campaignAuditTemplateFromKeys($documented),
            ],
        ],
    ]);

    $contextBuilder = app(CampaignRecipientContextBuilder::class);
    $renderer = campaignAuditRenderer('waha');

    $manualVars = $contextBuilder->buildBaseContext('2026-04-21');
    $manualPayload = $renderer->renderChannel($campaign, 'whatsapp', $manualVars);

    expect($manualPayload['unknown_placeholders'] ?? [])->toEqualCanonicalizing([
        'patient.name',
        'patient.phone',
        'patient.email',
    ]);

    $patientVars = $contextBuilder->buildFromPatientData(
        patientId: 'd45a1f34-4f33-4d63-9026-cc34dd8f95ec',
        fullName: 'Rafael Flores',
        cpf: '12345678901',
        email: 'rafael@example.com',
        phone: '556793087866',
        date: '2026-04-21'
    );
    $patientPayload = $renderer->renderChannel($campaign, 'whatsapp', $patientVars);

    expect($patientPayload['unknown_placeholders'] ?? [])->toBe([])
        ->and((string) ($patientPayload['text'] ?? ''))->toContain('Rafael Flores')
        ->and((string) ($patientPayload['text'] ?? ''))->toContain('https://wa.me/556793087866');

    $runCampaign = new Campaign([
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

    $audienceItems = app(CampaignAudienceBuilder::class)->build($runCampaign, [
        'context' => [
            'automation' => [
                'timezone' => 'America/Sao_Paulo',
                'local_now' => '2026-04-21 10:00:00',
            ],
        ],
    ]);

    expect($audienceItems)->toHaveCount(1);
    $runPayload = $renderer->renderChannel(
        $campaign,
        'whatsapp',
        is_array($audienceItems[0]['vars_json'] ?? null) ? $audienceItems[0]['vars_json'] : []
    );

    expect($runPayload['unknown_placeholders'] ?? [])->toBe([])
        ->and((string) ($runPayload['text'] ?? ''))->toContain('Rafael Flores')
        ->and((string) ($runPayload['text'] ?? ''))->toContain('/workspace/clinica-auditoria/agendamento/identificar');
});

it('supports legacy and undocumented placeholders in real campaign run context', function () {
    DB::connection('tenant')->table('patients')->insert([
        'id' => 'f116140e-fcb7-4eff-9d16-b3d2627b7e8b',
        'full_name' => 'Maria Clara Silva',
        'cpf' => '99888777666',
        'birth_date' => '1988-12-04',
        'email' => 'maria@example.com',
        'phone' => '5567987654321',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $keys = [
        'patient.full_name',
        'patient.first_name',
        'patient.cpf',
        'patient.id',
        'patient.is_active',
        'patient.birthdate_day_month',
        'inactivity_days',
        'now.date',
    ];

    $campaign = new Campaign([
        'content_json' => [
            'whatsapp' => [
                'provider' => 'waha',
                'composition_mode' => 'manual',
                'message_type' => 'text',
                'text' => campaignAuditTemplateFromKeys($keys),
            ],
        ],
    ]);

    $renderer = campaignAuditRenderer('waha');
    $contextBuilder = app(CampaignRecipientContextBuilder::class);

    $patientOnlyVars = $contextBuilder->buildFromPatientData(
        patientId: 'f116140e-fcb7-4eff-9d16-b3d2627b7e8b',
        fullName: 'Maria Clara Silva',
        cpf: '99888777666',
        email: 'maria@example.com',
        phone: '5567987654321',
        date: '2026-04-21'
    );

    $patientOnlyPayload = $renderer->renderChannel($campaign, 'whatsapp', $patientOnlyVars);

    expect($patientOnlyPayload['unknown_placeholders'] ?? [])->toEqualCanonicalizing([
        'patient.is_active',
        'patient.birthdate_day_month',
        'inactivity_days',
    ]);

    $runCampaign = new Campaign([
        'audience_json' => [
            'source' => 'patients',
        ],
        'automation_json' => [],
        'rules_json' => null,
    ]);

    $audienceItems = app(CampaignAudienceBuilder::class)->build($runCampaign, [
        'context' => [
            'automation' => [
                'timezone' => 'America/Sao_Paulo',
                'local_now' => '2026-04-21 10:00:00',
            ],
        ],
    ]);

    expect($audienceItems)->toHaveCount(1);

    $runVars = is_array($audienceItems[0]['vars_json'] ?? null) ? $audienceItems[0]['vars_json'] : [];
    $runPayload = $renderer->renderChannel($campaign, 'whatsapp', $runVars);

    expect($runPayload['unknown_placeholders'] ?? [])->toBe([])
        ->and((string) ($runPayload['text'] ?? ''))->toContain('Maria Clara Silva')
        ->and((string) ($runPayload['text'] ?? ''))->toContain('Maria')
        ->and((string) ($runPayload['text'] ?? ''))->toContain('99888777666')
        ->and((string) ($runPayload['text'] ?? ''))->toContain('04/12')
        ->and((string) ($runPayload['text'] ?? ''))->toContain('true');
});

it('keeps dotted and underscore aliases in official template variables payload', function () {
    $renderer = campaignAuditRenderer('whatsapp_business');

    $campaign = new Campaign([
        'content_json' => [
            'whatsapp' => [
                'provider' => 'whatsapp_business',
                'composition_mode' => 'template',
                'template_type' => 'official',
                'official_template_id' => '',
            ],
        ],
    ]);

    $payload = $renderer->renderChannel($campaign, 'whatsapp', [
        'patient' => [
            'name' => 'Rafael Flores',
            'full_name' => 'Rafael Flores',
        ],
        'now' => [
            'date' => '2026-04-21',
        ],
    ]);

    $officialVariables = is_array($payload['official_variables'] ?? null)
        ? $payload['official_variables']
        : [];

    expect($officialVariables)->toHaveKey('patient.name')
        ->and($officialVariables)->toHaveKey('patient_name')
        ->and($officialVariables['patient.name'] ?? null)->toBe('Rafael Flores')
        ->and($officialVariables['patient_name'] ?? null)->toBe('Rafael Flores')
        ->and($officialVariables)->toHaveKey('patient.full_name')
        ->and($officialVariables)->toHaveKey('patient_full_name')
        ->and($officialVariables)->toHaveKey('now.date')
        ->and($officialVariables)->toHaveKey('now_date');
});

