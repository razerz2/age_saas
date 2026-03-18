<?php

use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Platform\WhatsAppOfficialTemplateBinding;
use App\Services\Platform\WhatsAppOfficialMessageService;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-bindings',
        'services.whatsapp.business.phone_id' => '1234567890',
    ]);

    if (function_exists('set_sysconfig')) {
        set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
    }
});

function createPlatformUserForOfficialBindings(array $modules = ['whatsapp_official_templates', 'whatsapp_official_tenant_templates']): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'official-bindings+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => $modules,
        'email_verified_at' => now(),
    ]);
}

function createOfficialTemplateForBindings(array $overrides = []): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create(array_merge([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_v1',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => "Ola {{1}}. Fatura {{2}}. Valor {{3}}. Vence {{4}}. Link {{5}}.",
        'variables' => [
            '1' => 'customer_name',
            '2' => 'tenant_name',
            '3' => 'invoice_amount',
            '4' => 'due_date',
            '5' => 'payment_link',
        ],
        'sample_variables' => [
            '1' => 'Rafael',
            '2' => 'Clinica Exemplo',
            '3' => 'R$ 299,90',
            '4' => '20/03/2026',
            '5' => 'https://app.allsync.com.br/faturas/abc',
        ],
        'version' => 1,
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ], $overrides));
}

it('creates platform official binding for supported event key', function () {
    $user = createPlatformUserForOfficialBindings(['whatsapp_official_templates']);
    $template = createOfficialTemplateForBindings([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_bound',
    ]);

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-templates.bindings.upsert'), [
            'event_key' => 'invoice.created',
            'whatsapp_official_template_id' => (string) $template->id,
        ])
        ->assertRedirect(route('Platform.whatsapp-official-templates.bindings.index'))
        ->assertSessionHas('success', 'Vínculo oficial Platform atualizado com sucesso.');

    $binding = WhatsAppOfficialTemplateBinding::query()
        ->where('scope', WhatsAppOfficialTemplateBinding::SCOPE_PLATFORM)
        ->where('event_key', 'invoice.created')
        ->first();

    expect($binding)->not->toBeNull()
        ->and((string) $binding->whatsapp_official_template_id)->toBe((string) $template->id);
});

it('replaces previous platform binding and keeps single active row per event', function () {
    $user = createPlatformUserForOfficialBindings(['whatsapp_official_templates']);
    $templateV1 = createOfficialTemplateForBindings([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_v1',
        'version' => 1,
    ]);
    $templateV2 = createOfficialTemplateForBindings([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_v2',
        'version' => 2,
    ]);

    $this->actingAs($user, 'web')->post(route('Platform.whatsapp-official-templates.bindings.upsert'), [
        'event_key' => 'invoice.created',
        'whatsapp_official_template_id' => (string) $templateV1->id,
    ]);

    $firstBinding = WhatsAppOfficialTemplateBinding::query()
        ->where('scope', WhatsAppOfficialTemplateBinding::SCOPE_PLATFORM)
        ->where('event_key', 'invoice.created')
        ->firstOrFail();

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-templates.bindings.upsert'), [
            'event_key' => 'invoice.created',
            'whatsapp_official_template_id' => (string) $templateV2->id,
        ])
        ->assertRedirect(route('Platform.whatsapp-official-templates.bindings.index'));

    $updatedBinding = WhatsAppOfficialTemplateBinding::query()
        ->where('scope', WhatsAppOfficialTemplateBinding::SCOPE_PLATFORM)
        ->where('event_key', 'invoice.created')
        ->firstOrFail();

    expect((string) $updatedBinding->id)->toBe((string) $firstBinding->id)
        ->and((string) $updatedBinding->whatsapp_official_template_id)->toBe((string) $templateV2->id)
        ->and(WhatsAppOfficialTemplateBinding::query()
            ->where('scope', WhatsAppOfficialTemplateBinding::SCOPE_PLATFORM)
            ->where('event_key', 'invoice.created')
            ->count())->toBe(1);

    expect(WhatsAppOfficialTemplate::query()->find((string) $templateV1->id))->not->toBeNull();
});

it('creates and updates tenant official binding for baseline tenant event', function () {
    $user = createPlatformUserForOfficialBindings(['whatsapp_official_tenant_templates']);
    $templateV1 = createOfficialTemplateForBindings([
        'key' => 'appointment.confirmed',
        'meta_template_name' => 'tenant_appointment_confirmed_v1',
        'version' => 1,
    ]);
    $templateV2 = createOfficialTemplateForBindings([
        'key' => 'appointment.confirmed',
        'meta_template_name' => 'tenant_appointment_confirmed_v2',
        'version' => 2,
    ]);

    $this->actingAs($user, 'web')->post(route('Platform.whatsapp-official-tenant-templates.bindings.upsert'), [
        'event_key' => 'appointment.confirmed',
        'whatsapp_official_template_id' => (string) $templateV1->id,
    ]);

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.bindings.upsert'), [
            'event_key' => 'appointment.confirmed',
            'whatsapp_official_template_id' => (string) $templateV2->id,
        ])
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.bindings.index'))
        ->assertSessionHas('success', 'Vínculo oficial Tenant atualizado com sucesso.');

    $binding = WhatsAppOfficialTemplateBinding::query()
        ->where('scope', WhatsAppOfficialTemplateBinding::SCOPE_TENANT)
        ->where('event_key', 'appointment.confirmed')
        ->firstOrFail();

    expect((string) $binding->whatsapp_official_template_id)->toBe((string) $templateV2->id)
        ->and(WhatsAppOfficialTemplateBinding::query()
            ->where('scope', WhatsAppOfficialTemplateBinding::SCOPE_TENANT)
            ->where('event_key', 'appointment.confirmed')
            ->count())->toBe(1);
});

it('shows equivalent underscore key template as eligible on tenant bindings page', function () {
    $user = createPlatformUserForOfficialBindings(['whatsapp_official_tenant_templates']);

    $underscoreTemplate = createOfficialTemplateForBindings([
        'key' => 'appointment_confirmed',
        'meta_template_name' => 'tenant_appointment_confirmed_underscore',
        'version' => 1,
    ]);

    expect((string) $underscoreTemplate->status)->toBe(WhatsAppOfficialTemplate::STATUS_APPROVED);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-tenant-templates.bindings.index'))
        ->assertOk()
        ->assertSee('appointment.confirmed')
        ->assertSee('tenant_appointment_confirmed_underscore');
});

it('accepts equivalent event key format when saving official tenant binding', function () {
    $user = createPlatformUserForOfficialBindings(['whatsapp_official_tenant_templates']);
    $template = createOfficialTemplateForBindings([
        'key' => 'appointment.confirmed',
        'meta_template_name' => 'tenant_appointment_confirmed_equivalent',
        'version' => 1,
    ]);

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-tenant-templates.bindings.upsert'), [
            'event_key' => 'appointment_confirmed',
            'whatsapp_official_template_id' => (string) $template->id,
        ])
        ->assertRedirect(route('Platform.whatsapp-official-tenant-templates.bindings.index'))
        ->assertSessionHas('success');

    $binding = WhatsAppOfficialTemplateBinding::query()
        ->where('scope', WhatsAppOfficialTemplateBinding::SCOPE_TENANT)
        ->firstOrFail();

    expect((string) $binding->whatsapp_official_template_id)->toBe((string) $template->id)
        ->and(in_array((string) $binding->event_key, ['appointment.confirmed', 'appointment_confirmed'], true))->toBeTrue();
});

it('blocks binding when template key does not match selected event key', function () {
    $user = createPlatformUserForOfficialBindings(['whatsapp_official_templates']);
    $tenantTemplate = createOfficialTemplateForBindings([
        'key' => 'appointment.confirmed',
        'meta_template_name' => 'tenant_appointment_confirmed',
    ]);

    $this->from(route('Platform.whatsapp-official-templates.bindings.index'))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-templates.bindings.upsert'), [
            'event_key' => 'invoice.created',
            'whatsapp_official_template_id' => (string) $tenantTemplate->id,
        ])
        ->assertRedirect(route('Platform.whatsapp-official-templates.bindings.index'))
        ->assertSessionHasErrors('template');

    expect(WhatsAppOfficialTemplateBinding::query()->count())->toBe(0);
});

it('blocks binding for non-official provider template', function () {
    $user = createPlatformUserForOfficialBindings(['whatsapp_official_templates']);
    $template = createOfficialTemplateForBindings([
        'provider' => 'internal_provider',
        'key' => 'invoice.created',
    ]);

    $this->from(route('Platform.whatsapp-official-templates.bindings.index'))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-templates.bindings.upsert'), [
            'event_key' => 'invoice.created',
            'whatsapp_official_template_id' => (string) $template->id,
        ])
        ->assertRedirect(route('Platform.whatsapp-official-templates.bindings.index'))
        ->assertSessionHasErrors('template');
});

it('blocks binding for template that is not approved', function () {
    $user = createPlatformUserForOfficialBindings(['whatsapp_official_templates']);
    $template = createOfficialTemplateForBindings([
        'key' => 'invoice.created',
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
    ]);

    $this->from(route('Platform.whatsapp-official-templates.bindings.index'))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-templates.bindings.upsert'), [
            'event_key' => 'invoice.created',
            'whatsapp_official_template_id' => (string) $template->id,
        ])
        ->assertRedirect(route('Platform.whatsapp-official-templates.bindings.index'))
        ->assertSessionHasErrors('template');
});

it('shows current binding in both platform and tenant official binding pages', function () {
    $user = createPlatformUserForOfficialBindings();

    $platformTemplate = createOfficialTemplateForBindings([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_current',
    ]);
    $tenantTemplate = createOfficialTemplateForBindings([
        'key' => 'appointment.confirmed',
        'meta_template_name' => 'tenant_appointment_confirmed_current',
    ]);

    WhatsAppOfficialTemplateBinding::query()->create([
        'scope' => WhatsAppOfficialTemplateBinding::SCOPE_PLATFORM,
        'event_key' => 'invoice.created',
        'whatsapp_official_template_id' => (string) $platformTemplate->id,
        'provider' => (string) $platformTemplate->provider,
        'language' => (string) $platformTemplate->language,
    ]);

    WhatsAppOfficialTemplateBinding::query()->create([
        'scope' => WhatsAppOfficialTemplateBinding::SCOPE_TENANT,
        'event_key' => 'appointment.confirmed',
        'whatsapp_official_template_id' => (string) $tenantTemplate->id,
        'provider' => (string) $tenantTemplate->provider,
        'language' => (string) $tenantTemplate->language,
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.bindings.index'))
        ->assertOk()
        ->assertSee('invoice.created')
        ->assertSee('saas_invoice_created_current');

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-tenant-templates.bindings.index'))
        ->assertOk()
        ->assertSee('appointment.confirmed')
        ->assertSee('tenant_appointment_confirmed_current');
});

it('platform bindings page keeps tenant templates out of eligible options', function () {
    $user = createPlatformUserForOfficialBindings();

    createOfficialTemplateForBindings([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_platform_binding_scope',
    ]);

    createOfficialTemplateForBindings([
        'key' => 'appointment.confirmed',
        'meta_template_name' => 'tenant_appointment_confirmed_should_not_show_platform_binding',
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.bindings.index'))
        ->assertOk()
        ->assertSee('saas_invoice_created_platform_binding_scope')
        ->assertDontSee('tenant_appointment_confirmed_should_not_show_platform_binding');
});

it('uses official binding as source of truth and keeps fallback when binding is absent', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/1234567890/messages' => Http::response([
            'messages' => [['id' => 'wamid.bindings.1']],
        ], 200),
    ]);

    $boundTemplate = createOfficialTemplateForBindings([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_bound_v1',
        'version' => 1,
    ]);
    createOfficialTemplateForBindings([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_latest_v2',
        'version' => 2,
    ]);

    createOfficialTemplateForBindings([
        'key' => 'subscription.created',
        'meta_template_name' => 'saas_subscription_created_v1',
        'version' => 1,
    ]);
    createOfficialTemplateForBindings([
        'key' => 'subscription.created',
        'meta_template_name' => 'saas_subscription_created_v2',
        'version' => 2,
    ]);

    WhatsAppOfficialTemplateBinding::query()->create([
        'scope' => WhatsAppOfficialTemplateBinding::SCOPE_PLATFORM,
        'event_key' => 'invoice.created',
        'whatsapp_official_template_id' => (string) $boundTemplate->id,
        'provider' => (string) $boundTemplate->provider,
        'language' => (string) $boundTemplate->language,
    ]);

    $service = app(WhatsAppOfficialMessageService::class);

    $boundSent = $service->sendByKey('invoice.created', '5565999999999', [
        'customer_name' => 'Rafael',
        'tenant_name' => 'Clinica Exemplo',
        'invoice_amount' => 'R$ 299,90',
        'due_date' => '20/03/2026',
        'payment_link' => 'https://app.allsync.com.br/faturas/abc',
    ]);

    $fallbackSent = $service->sendByKey('subscription.created', '5565999999999', [
        'customer_name' => 'Rafael',
        'tenant_name' => 'Clinica Exemplo',
        'invoice_amount' => 'R$ 299,90',
        'due_date' => '20/03/2026',
        'payment_link' => 'https://app.allsync.com.br/faturas/abc',
    ]);

    expect($boundSent)->toBeTrue()
        ->and($fallbackSent)->toBeTrue();

    Http::assertSent(function ($request): bool {
        $payload = $request->data();
        return ($payload['template']['name'] ?? null) === 'saas_invoice_created_bound_v1';
    });

    Http::assertSent(function ($request): bool {
        $payload = $request->data();
        return ($payload['template']['name'] ?? null) === 'saas_subscription_created_v2';
    });
});

it('resolves runtime sendByKey when key format differs by dot and underscore', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/1234567890/messages' => Http::response([
            'messages' => [['id' => 'wamid.bindings.2']],
        ], 200),
    ]);

    $boundTemplate = createOfficialTemplateForBindings([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created_equivalent_key',
        'version' => 1,
    ]);

    WhatsAppOfficialTemplateBinding::query()->create([
        'scope' => WhatsAppOfficialTemplateBinding::SCOPE_PLATFORM,
        'event_key' => 'invoice.created',
        'whatsapp_official_template_id' => (string) $boundTemplate->id,
        'provider' => (string) $boundTemplate->provider,
        'language' => (string) $boundTemplate->language,
    ]);

    $sent = app(WhatsAppOfficialMessageService::class)->sendByKey('invoice_created', '5565999999999', [
        'customer_name' => 'Rafael',
        'tenant_name' => 'Clinica Exemplo',
        'invoice_amount' => 'R$ 299,90',
        'due_date' => '20/03/2026',
        'payment_link' => 'https://app.allsync.com.br/faturas/abc',
    ]);

    expect($sent)->toBeTrue();

    Http::assertSent(function ($request): bool {
        $payload = $request->data();
        return ($payload['template']['name'] ?? null) === 'saas_invoice_created_equivalent_key';
    });
});
