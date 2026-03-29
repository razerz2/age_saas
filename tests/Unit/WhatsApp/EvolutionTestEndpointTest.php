<?php

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('uses Evolution runtime form config in platform connection test endpoint', function () {
    config([
        'services.whatsapp.evolution.base_url' => 'https://wrong.test',
        'services.whatsapp.evolution.api_key' => 'token-default',
        'services.whatsapp.evolution.instance' => 'default',
    ]);

    Http::fake([
        'https://evolution.test/instance/fetchInstances' => Http::response([
            'instances' => [],
        ], 200),
    ]);

    $url = route('Platform.settings.test', ['service' => 'evolution'])
        . '?' . http_build_query([
            'EVOLUTION_BASE_URL' => 'https://evolution.test',
            'EVOLUTION_API_KEY' => 'token-form',
        ]);

    $response = $this->withoutMiddleware()->getJson($url);

    $response->assertOk()->assertJson([
        'status' => 'OK',
    ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://evolution.test/instance/fetchInstances'
            && $request->hasHeader('apikey', 'token-form');
    });
});

it('uses Evolution runtime form config in platform send test endpoint', function () {
    config([
        'services.whatsapp.evolution.base_url' => 'https://wrong.test',
        'services.whatsapp.evolution.api_key' => 'token-default',
        'services.whatsapp.evolution.instance' => 'default',
    ]);

    Http::fake([
        'https://evolution.test/instance/connectionState/platform-default' => Http::response([
            'instance' => ['state' => 'open'],
        ], 200),
        'https://evolution.test/message/sendText/platform-default' => Http::response([
            'key' => 'msg-platform-1',
        ], 201),
    ]);

    $response = $this->withoutMiddleware()->postJson(route('Platform.settings.test.evolution.send'), [
        'number' => '67992998146',
        'message' => 'Teste Evolution',
        'EVOLUTION_BASE_URL' => 'https://evolution.test',
        'EVOLUTION_API_KEY' => 'token-form',
        'EVOLUTION_INSTANCE' => 'platform-default',
    ]);

    $response->assertOk()->assertJson([
        'status' => 'OK',
    ]);

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://evolution.test/message/sendText/platform-default') {
            return false;
        }

        $data = $request->data();

        return ($data['number'] ?? null) === '5567992998146'
            && ($data['text'] ?? null) === 'Teste Evolution'
            && $request->hasHeader('apikey', 'token-form');
    });
});
