<?php

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

function forceUnofficialProvider(string $provider): void
{
    if (!function_exists('set_sysconfig')) {
        return;
    }

    try {
        set_sysconfig('WHATSAPP_PROVIDER', $provider);
    } catch (\Throwable) {
        // Ignore when platform DB is unavailable in unit tests.
    }
}

it('sends the same rendered text through WAHA provider', function () {
    forceUnofficialProvider('waha');

    config([
        'services.whatsapp.provider' => 'waha',
        'services.whatsapp.waha.base_url' => 'https://waha.test',
        'services.whatsapp.waha.api_key' => 'token-test',
        'services.whatsapp.waha.session' => 'default',
    ]);

    Http::fake([
        'https://waha.test/api/sessions/default' => Http::response(['status' => 'WORKING'], 200),
        'https://waha.test/api/sendText' => Http::response(['result' => 'ok'], 200),
    ]);

    $message = "Mensagem final renderizada\nLinha 2";
    $service = new WhatsAppService();
    $sent = $service->sendMessage('67992998146', $message);

    expect($sent)->toBeTrue();

    Http::assertSent(function ($request) use ($message) {
        if ($request->url() !== 'https://waha.test/api/sendText') {
            return false;
        }

        $payload = $request->data();

        return ($payload['chatId'] ?? null) === '556792998146@c.us'
            && ($payload['text'] ?? null) === $message;
    });
});

it('sends the same rendered text through Z-API provider', function () {
    forceUnofficialProvider('zapi');

    config([
        'services.whatsapp.provider' => 'zapi',
        'services.whatsapp.zapi.api_url' => 'https://api.z-api.test',
        'services.whatsapp.zapi.token' => 'token-instance',
        'services.whatsapp.zapi.client_token' => 'token-client',
        'services.whatsapp.zapi.instance_id' => 'instance-1',
    ]);

    Http::fake([
        'https://api.z-api.test/instances/instance-1/send-text' => Http::response([
            'status' => 'success',
        ], 200),
    ]);

    $message = "Mensagem final renderizada\nLinha 2";
    $service = new WhatsAppService();
    $sent = $service->sendMessage('67999998888', $message);

    expect($sent)->toBeTrue();

    Http::assertSent(function ($request) use ($message) {
        if ($request->url() !== 'https://api.z-api.test/instances/instance-1/send-text') {
            return false;
        }

        $payload = $request->data();

        return ($payload['message'] ?? null) === $message
            && ($payload['phone'] ?? null) === '5567999998888';
    });
});
