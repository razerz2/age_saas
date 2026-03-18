<?php

use App\Models\Platform\User;
use App\Models\Platform\WhatsAppUnofficialTemplate;
use Illuminate\Support\Facades\Http;

function createPlatformUserForUnofficialManualTest(): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'unofficial-manual-test+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['whatsapp_unofficial_templates'],
        'email_verified_at' => now(),
    ]);
}

function createUnofficialTemplateForManualTest(array $overrides = []): WhatsAppUnofficialTemplate
{
    return WhatsAppUnofficialTemplate::query()->create(array_merge([
        'key' => 'invoice.overdue',
        'title' => 'Fatura vencida',
        'category' => 'billing',
        'body' => 'Ola {{customer_name}}, vencimento {{due_date}}. Link: {{payment_link}}',
        'variables' => ['customer_name', 'due_date', 'payment_link'],
        'is_active' => true,
    ], $overrides));
}

function setGlobalProviderForUnofficialManualTest(string $provider): void
{
    config(['services.whatsapp.provider' => $provider]);

    if (!function_exists('set_sysconfig')) {
        return;
    }

    try {
        set_sysconfig('WHATSAPP_PROVIDER', $provider);
    } catch (\Throwable) {
        // Ignore when settings DB is unavailable in test runtime.
    }
}

function setGlobalWahaConfigForUnofficialManualTest(string $baseUrl, string $apiKey, string $session = 'default'): void
{
    config([
        'services.whatsapp.waha.base_url' => $baseUrl,
        'services.whatsapp.waha.api_key' => $apiKey,
        'services.whatsapp.waha.session' => $session,
    ]);

    if (!function_exists('set_sysconfig')) {
        return;
    }

    try {
        set_sysconfig('WAHA_BASE_URL', $baseUrl);
        set_sysconfig('WAHA_API_KEY', $apiKey);
        set_sysconfig('WAHA_SESSION', $session);
    } catch (\Throwable) {
        // Ignore when settings DB is unavailable in test runtime.
    }
}

function setGlobalZapiConfigForUnofficialManualTest(
    string $apiUrl,
    string $token,
    string $clientToken,
    string $instanceId
): void {
    config([
        'services.whatsapp.zapi.api_url' => $apiUrl,
        'services.whatsapp.zapi.token' => $token,
        'services.whatsapp.zapi.client_token' => $clientToken,
        'services.whatsapp.zapi.instance_id' => $instanceId,
    ]);

    if (!function_exists('set_sysconfig')) {
        return;
    }

    try {
        set_sysconfig('ZAPI_API_URL', $apiUrl);
        set_sysconfig('ZAPI_TOKEN', $token);
        set_sysconfig('ZAPI_CLIENT_TOKEN', $clientToken);
        set_sysconfig('ZAPI_INSTANCE_ID', $instanceId);
    } catch (\Throwable) {
        // Ignore when settings DB is unavailable in test runtime.
    }
}

it('shows manual test modal on unofficial template show page', function () {
    $user = createPlatformUserForUnofficialManualTest();
    $template = createUnofficialTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-unofficial-templates.show', $template))
        ->assertOk()
        ->assertSee('Testar mensagem')
        ->assertSee('id="manualUnofficialTestModal"', false)
        ->assertSee('Preview renderizado');
});

it('renders preview using runtime renderer and reports missing variables', function () {
    $user = createPlatformUserForUnofficialManualTest();
    $template = createUnofficialTemplateForManualTest();

    $response = $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-unofficial-templates.preview', $template), [
            'variables' => [
                'customer_name' => 'Rafael',
                'payment_link' => 'https://app.allsync.com.br/faturas/pagar/teste',
            ],
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'preview_source' => 'tenant_template_renderer',
        ])
        ->assertJsonPath('preview', fn ($preview) => str_contains((string) $preview, 'Rafael'))
        ->assertJsonPath('missing_variables', fn ($missing) => is_array($missing) && in_array('due_date', $missing, true));
});

it('sends manual test through unofficial runtime path with WAHA', function () {
    setGlobalProviderForUnofficialManualTest('waha');
    setGlobalWahaConfigForUnofficialManualTest('https://waha.test', 'token-test', 'default');

    Http::fake([
        'https://waha.test/api/sessions/default' => Http::response(['status' => 'WORKING'], 200),
        'https://waha.test/api/sendText' => Http::response(['result' => 'ok'], 200),
    ]);

    $user = createPlatformUserForUnofficialManualTest();
    $template = createUnofficialTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-unofficial-templates.test-send', $template), [
            'phone' => '67992998146',
            'variables' => [
                'customer_name' => 'Rafael',
                'due_date' => '20/03/2026',
                'payment_link' => 'https://app.allsync.com.br/faturas/pagar/teste',
            ],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'provider' => 'waha',
        ]);

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://waha.test/api/sendText') {
            return false;
        }

        $payload = $request->data();
        return ($payload['chatId'] ?? null) === '556792998146@c.us'
            && str_contains((string) ($payload['text'] ?? ''), 'Rafael');
    });
});

it('validates required variables before sending manual test', function () {
    setGlobalProviderForUnofficialManualTest('waha');
    setGlobalWahaConfigForUnofficialManualTest('https://waha.test', 'token-test', 'default');

    Http::fake();

    $user = createPlatformUserForUnofficialManualTest();
    $template = createUnofficialTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-unofficial-templates.test-send', $template), [
            'phone' => '67992998146',
            'variables' => [
                'customer_name' => 'Rafael',
                'payment_link' => 'https://app.allsync.com.br/faturas/pagar/teste',
            ],
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ])
        ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'Variaveis obrigatorias ausentes'));

    Http::assertNothingSent();
});

it('sends manual test through unofficial runtime path with Z-API', function () {
    setGlobalProviderForUnofficialManualTest('zapi');
    setGlobalZapiConfigForUnofficialManualTest(
        'https://api.z-api.test',
        'token-instance',
        'token-client',
        'instance-1'
    );

    Http::fake([
        'https://api.z-api.test/instances/instance-1/send-text' => Http::response([
            'status' => 'success',
        ], 200),
    ]);

    $user = createPlatformUserForUnofficialManualTest();
    $template = createUnofficialTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-unofficial-templates.test-send', $template), [
            'phone' => '67999998888',
            'variables' => [
                'customer_name' => 'Rafael',
                'due_date' => '20/03/2026',
                'payment_link' => 'https://app.allsync.com.br/faturas/pagar/teste',
            ],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'provider' => 'zapi',
        ]);

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://api.z-api.test/instances/instance-1/send-text') {
            return false;
        }

        $payload = $request->data();
        return ($payload['phone'] ?? null) === '5567999998888'
            && str_contains((string) ($payload['message'] ?? ''), 'Rafael');
    });
});

it('returns friendly error when unofficial provider is not ready', function () {
    setGlobalProviderForUnofficialManualTest('waha');
    setGlobalWahaConfigForUnofficialManualTest('https://waha.test', '', 'default');

    Http::fake();

    $user = createPlatformUserForUnofficialManualTest();
    $template = createUnofficialTemplateForManualTest();

    $this->actingAs($user, 'web')
        ->postJson(route('Platform.whatsapp-unofficial-templates.test-send', $template), [
            'phone' => '67992998146',
            'variables' => [
                'customer_name' => 'Rafael',
                'due_date' => '20/03/2026',
                'payment_link' => 'https://app.allsync.com.br/faturas/pagar/teste',
            ],
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ])
        ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'Provider WAHA nao esta apto'));

    Http::assertNothingSent();
});

