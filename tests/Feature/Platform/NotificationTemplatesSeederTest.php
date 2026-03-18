<?php

use App\Models\Platform\NotificationTemplate;
use App\Models\Platform\TenantDefaultNotificationTemplate;
use App\Models\Platform\User;
use App\Models\Platform\WhatsAppUnofficialTemplate;
use Database\Seeders\NotificationTemplatesSeeder;
use Database\Seeders\TenantDefaultNotificationTemplatesSeeder;
use Database\Seeders\WhatsAppUnofficialTemplatesSeeder;

function createPlatformUserForNotificationTemplateSeed(array $modules): User
{
    return User::query()->create([
        'name' => 'Admin Seed',
        'name_full' => 'Administrador Seed',
        'email' => 'seed-notification+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => $modules,
        'email_verified_at' => now(),
    ]);
}

function seedNotificationTemplateSources(): void
{
    app(WhatsAppUnofficialTemplatesSeeder::class)->run();
    app(TenantDefaultNotificationTemplatesSeeder::class)->run();
}

it('creates platform email templates from platform unofficial whatsapp templates', function () {
    seedNotificationTemplateSources();
    app(NotificationTemplatesSeeder::class)->run();

    $source = WhatsAppUnofficialTemplate::query()
        ->active()
        ->where('key', 'invoice.created')
        ->firstOrFail();

    $template = NotificationTemplate::query()
        ->where('scope', NotificationTemplate::SCOPE_PLATFORM)
        ->where('channel', NotificationTemplate::CHANNEL_EMAIL)
        ->where('name', 'invoice.created')
        ->first();

    expect($template)->not->toBeNull()
        ->and((string) $template?->scope)->toBe(NotificationTemplate::SCOPE_PLATFORM)
        ->and((string) $template?->channel)->toBe(NotificationTemplate::CHANNEL_EMAIL)
        ->and((string) $template?->body)->toBe((string) $source->body)
        ->and((string) $template?->subject)->toBe((string) $source->title);
});

it('creates tenant email templates from tenant unofficial whatsapp templates', function () {
    seedNotificationTemplateSources();
    app(NotificationTemplatesSeeder::class)->run();

    $source = TenantDefaultNotificationTemplate::query()
        ->active()
        ->where('channel', NotificationTemplate::CHANNEL_WHATSAPP)
        ->where('key', 'appointment.confirmed')
        ->firstOrFail();

    $template = NotificationTemplate::query()
        ->where('scope', NotificationTemplate::SCOPE_TENANT)
        ->where('channel', NotificationTemplate::CHANNEL_EMAIL)
        ->where('name', 'appointment.confirmed')
        ->first();

    expect($template)->not->toBeNull()
        ->and((string) $template?->scope)->toBe(NotificationTemplate::SCOPE_TENANT)
        ->and((string) $template?->channel)->toBe(NotificationTemplate::CHANNEL_EMAIL)
        ->and((string) $template?->body)->toBe((string) $source->content)
        ->and((string) $template?->subject)->toBe((string) $source->title);
});

it('is idempotent and does not duplicate seeded email templates across scopes', function () {
    seedNotificationTemplateSources();

    app(NotificationTemplatesSeeder::class)->run();
    app(NotificationTemplatesSeeder::class)->run();

    $expectedPlatformCount = WhatsAppUnofficialTemplate::query()->active()->count();
    $expectedTenantCount = TenantDefaultNotificationTemplate::query()
        ->active()
        ->where('channel', NotificationTemplate::CHANNEL_WHATSAPP)
        ->count();

    $platformEmailCount = NotificationTemplate::query()
        ->where('scope', NotificationTemplate::SCOPE_PLATFORM)
        ->where('channel', NotificationTemplate::CHANNEL_EMAIL)
        ->count();

    $tenantEmailCount = NotificationTemplate::query()
        ->where('scope', NotificationTemplate::SCOPE_TENANT)
        ->where('channel', NotificationTemplate::CHANNEL_EMAIL)
        ->count();

    expect($platformEmailCount)->toBe($expectedPlatformCount)
        ->and($tenantEmailCount)->toBe($expectedTenantCount);

    $duplicates = NotificationTemplate::query()
        ->where('channel', NotificationTemplate::CHANNEL_EMAIL)
        ->selectRaw('scope, channel, name, count(*) as total')
        ->groupBy('scope', 'channel', 'name')
        ->havingRaw('count(*) > 1')
        ->get();

    expect($duplicates)->toHaveCount(0);
});

it('fills platform and tenant email template listings after seeding', function () {
    seedNotificationTemplateSources();
    app(NotificationTemplatesSeeder::class)->run();

    $user = createPlatformUserForNotificationTemplateSeed([
        'platform_email_templates',
        'tenant_email_templates',
    ]);

    $this->actingAs($user, 'web')
        ->get(route('Platform.platform-email-templates.index'))
        ->assertOk()
        ->assertSee('Fatura criada')
        ->assertDontSee('Nenhum template de email platform encontrado.');

    $this->actingAs($user, 'web')
        ->get(route('Platform.tenant-email-templates.index'))
        ->assertOk()
        ->assertSee('Agendamento confirmado')
        ->assertDontSee('Nenhum template de email tenant encontrado.');
});
