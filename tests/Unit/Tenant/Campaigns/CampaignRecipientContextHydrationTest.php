<?php

use App\Models\Platform\Tenant;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\Patient;
use App\Services\Tenant\CampaignAudienceBuilder;
use App\Services\Tenant\CampaignRecipientContextBuilder;
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
    $tenant->id = 'tenant-campaign-context';
    $tenant->subdomain = 'clinica-contexto';
    $tenant->trade_name = 'Clinica Contexto';
    $tenant->phone = '67999990000';
    $tenant->email = 'contato@clinica-contexto.test';
    $tenant->address = 'Rua Central, 123';

    app()->instance(config('multitenancy.current_tenant_container_key', 'currentTenant'), $tenant);
});

afterEach(function (): void {
    app()->forgetInstance(config('multitenancy.current_tenant_container_key', 'currentTenant'));
});

it('builds the same patient and clinic context used by campaign test send', function () {
    $patient = new Patient();
    $patient->id = '3ef38dc9-a1c5-4fa0-b0fa-4fdf0f50b8ad';
    $patient->full_name = 'Rafael Flores';
    $patient->cpf = '12345678901';
    $patient->email = 'rafael@example.com';
    $patient->phone = '556793087866';

    $context = app(CampaignRecipientContextBuilder::class)
        ->buildFromPatient($patient, '2026-04-21');

    expect(data_get($context, 'patient.name'))->toBe('Rafael Flores')
        ->and(data_get($context, 'patient.full_name'))->toBe('Rafael Flores')
        ->and(data_get($context, 'patient.first_name'))->toBe('Rafael')
        ->and(data_get($context, 'clinic.name'))->toBe('Clinica Contexto')
        ->and(data_get($context, 'links.public_booking'))->toContain('/workspace/clinica-contexto/agendamento/identificar')
        ->and(data_get($context, 'links.portal'))->toContain('/workspace/clinica-contexto/agendamento/identificar')
        ->and(data_get($context, 'links.whatsapp'))->toBe('https://wa.me/67999990000')
        ->and(data_get($context, 'now.date'))->toBe('21/04/2026');
});

it('hydrates campaign run audience recipients with patient.name and public link', function () {
    DB::connection('tenant')->table('patients')->insert([
        'id' => 'd9f47f85-5789-4453-b629-6a4dbd6473ff',
        'full_name' => 'Rafael Flores',
        'cpf' => '12345678901',
        'email' => 'rafael@example.com',
        'phone' => '556793087866',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $campaign = new Campaign([
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

    $items = app(CampaignAudienceBuilder::class)->build($campaign, [
        'context' => [
            'automation' => [
                'timezone' => 'America/Sao_Paulo',
                'local_now' => '2026-04-21 10:00:00',
            ],
        ],
    ]);

    expect($items)->toHaveCount(1);

    $vars = $items[0]['vars_json'] ?? [];
    expect(data_get($vars, 'patient.name'))->toBe('Rafael Flores')
        ->and(data_get($vars, 'patient.full_name'))->toBe('Rafael Flores')
        ->and(data_get($vars, 'patient.first_name'))->toBe('Rafael')
        ->and(data_get($vars, 'patient.phone'))->toBe('556793087866')
        ->and(data_get($vars, 'clinic.name'))->toBe('Clinica Contexto')
        ->and(data_get($vars, 'links.public_booking'))->toContain('/workspace/clinica-contexto/agendamento/identificar')
        ->and(data_get($vars, 'links.portal'))->toContain('/workspace/clinica-contexto/agendamento/identificar')
        ->and(data_get($vars, 'links.whatsapp'))->toBe('https://wa.me/67999990000')
        ->and(data_get($vars, 'now.date'))->toBe('21/04/2026');
});
