<?php

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

function trySetSystemProvider(string $provider): void
{
    if (!function_exists('set_sysconfig')) {
        return;
    }

    try {
        set_sysconfig('WHATSAPP_PROVIDER', $provider);
    } catch (\Throwable) {
        // Ignore DB errors in isolated unit tests.
    }
}

it('prefers forced runtime provider over platform sysconfig provider', function () {
    trySetSystemProvider('whatsapp_business');

    config([
        'services.whatsapp.force_runtime_provider' => true,
        'services.whatsapp.runtime_provider' => 'evolution',
        'services.whatsapp.provider' => 'evolution',
        'services.whatsapp.evolution.base_url' => 'https://evolution.test',
        'services.whatsapp.evolution.api_key' => 'runtime-token',
        'services.whatsapp.evolution.instance' => 'tenant-a',
    ]);

    Http::fake([
        'https://evolution.test/instance/connectionState/tenant-a' => Http::response([
            'instance' => ['state' => 'open'],
        ], 200),
        'https://evolution.test/message/sendText/tenant-a' => Http::response([
            'key' => 'msg-rt-1',
        ], 201),
        '*' => Http::response([], 500),
    ]);

    $service = new WhatsAppService();
    $sent = $service->sendMessage('67999998888', 'Teste runtime provider');

    expect($sent)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://evolution.test/message/sendText/tenant-a';
    });
});

it('blocks send when forced runtime provider is unsupported', function () {
    config([
        'services.whatsapp.force_runtime_provider' => true,
        'services.whatsapp.runtime_provider' => '__invalid_tenant_global_provider__',
        'services.whatsapp.provider' => '__invalid_tenant_global_provider__',
    ]);

    Http::fake();

    $service = new WhatsAppService();
    $sent = $service->sendMessage('67999998888', 'Teste bloqueio provider invalido');

    expect($sent)->toBeFalse();
    Http::assertNothingSent();
});
