<?php

use App\Services\WhatsApp\EvolutionClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('creates evolution instance using configured endpoint and apikey header', function () {
    $client = new EvolutionClient('https://evolution.test', 'token-evolution', 'clinica-teste');

    Http::fake([
        'https://evolution.test/instance/create' => Http::response([
            'instance' => ['instanceName' => 'clinica-teste'],
        ], 201),
    ]);

    $result = $client->createInstance();

    expect($result['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://evolution.test/instance/create') {
            return false;
        }

        $payload = $request->data();

        return ($payload['instanceName'] ?? null) === 'clinica-teste'
            && $request->hasHeader('apikey', 'token-evolution');
    });
});

it('tests evolution api connection without requiring an instance name', function () {
    $client = new EvolutionClient('https://evolution.test', 'token-evolution', 'clinica-teste');

    Http::fake([
        'https://evolution.test/instance/fetchInstances' => Http::response([
            'instances' => [],
        ], 200),
    ]);

    $result = $client->testConnection();

    expect($result['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://evolution.test/instance/fetchInstances'
            && $request->hasHeader('apikey', 'token-evolution');
    });
});

it('detects missing evolution instance from not found connection state', function () {
    $client = new EvolutionClient('https://evolution.test', 'token-evolution', 'clinica-teste');

    Http::fake([
        'https://evolution.test/instance/connectionState/clinica-teste' => Http::response([
            'message' => 'instance not found',
        ], 404),
        'https://evolution.test/instance/fetchInstances*' => Http::response([
            'message' => 'instance not found',
        ], 404),
    ]);

    $result = $client->instanceExists();

    expect($result['exists'])->toBeFalse();

    Http::assertNotSent(function ($request) {
        return str_contains($request->url(), '/api/instance/');
    });
});

it('falls back to v2 payload when evolution sendText with plain text is rejected', function () {
    $client = new EvolutionClient('https://evolution.test', 'token-evolution', 'clinica-teste');

    Http::fake([
        'https://evolution.test/message/sendText/clinica-teste' => function ($request) {
            $payload = $request->data();

            if (isset($payload['text'])) {
                return Http::response([
                    'error' => 'validation failed',
                ], 422);
            }

            if (isset($payload['textMessage']['text'])) {
                return Http::response([
                    'key' => ['id' => 'abc123'],
                ], 201);
            }

            return Http::response([
                'error' => 'invalid payload',
            ], 422);
        },
    ]);

    $result = $client->sendText('5567999998888', 'Teste Evolution');

    expect($result['ok'])->toBeTrue();
});

it('requests connect endpoint to fetch qr information', function () {
    $client = new EvolutionClient('https://evolution.test', 'token-evolution', 'clinica-teste');

    Http::fake([
        'https://evolution.test/instance/connect/clinica-teste' => Http::response([
            'qrcode' => [
                'base64' => 'ZmFrZV9xcl9kYXRh',
            ],
        ], 200),
    ]);

    $result = $client->connectInstance();

    expect($result['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://evolution.test/instance/connect/clinica-teste'
            && $request->hasHeader('apikey', 'token-evolution');
    });
});

it('falls back to post when restart endpoint rejects put method', function () {
    $client = new EvolutionClient('https://evolution.test', 'token-evolution', 'clinica-teste');

    Http::fake([
        'https://evolution.test/instance/restart/clinica-teste' => function ($request) {
            if ($request->method() === 'PUT') {
                return Http::response([
                    'error' => 'method not allowed',
                ], 405);
            }

            return Http::response([
                'status' => 'SUCCESS',
            ], 200);
        },
    ]);

    $result = $client->restartInstance();

    expect($result['ok'])->toBeTrue();

    Http::assertSentCount(2);
    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://evolution.test/instance/restart/clinica-teste';
    });
});

it('uses delete method to logout evolution instance', function () {
    $client = new EvolutionClient('https://evolution.test', 'token-evolution', 'clinica-teste');

    Http::fake([
        'https://evolution.test/instance/logout/clinica-teste' => Http::response([
            'status' => 'SUCCESS',
            'error' => false,
        ], 200),
    ]);

    $result = $client->logoutInstance();

    expect($result['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://evolution.test/instance/logout/clinica-teste';
    });
});

it('reads evolution webhook configuration by instance', function () {
    $client = new EvolutionClient('https://evolution.test', 'token-evolution', 'clinica-teste');

    Http::fake([
        'https://evolution.test/webhook/find/clinica-teste' => Http::response([
            'webhook' => [
                'instanceName' => 'clinica-teste',
                'webhook' => [
                    'enabled' => true,
                    'url' => 'https://app.test/customer/clinica-teste/webhooks/whatsapp/bot/evolution',
                    'events' => ['MESSAGES_UPSERT'],
                ],
            ],
        ], 200),
    ]);

    $result = $client->getWebhook();

    expect($result['ok'])->toBeTrue()
        ->and($result['webhook']['url'] ?? null)->toBe('https://app.test/customer/clinica-teste/webhooks/whatsapp/bot/evolution');

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://evolution.test/webhook/find/clinica-teste';
    });
});

it('sets evolution webhook using webhook/set endpoint', function () {
    $client = new EvolutionClient('https://evolution.test', 'token-evolution', 'clinica-teste');

    Http::fake([
        'https://evolution.test/webhook/set/clinica-teste' => Http::response([
            'message' => 'Webhook updated',
        ], 200),
    ]);

    $result = $client->setWebhook('https://app.test/customer/clinica-teste/webhooks/whatsapp/bot/evolution');

    expect($result['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        $payload = $request->data();

        return $request->method() === 'POST'
            && $request->url() === 'https://evolution.test/webhook/set/clinica-teste'
            && ($payload['url'] ?? null) === 'https://app.test/customer/clinica-teste/webhooks/whatsapp/bot/evolution'
            && ($payload['webhookByEvents'] ?? null) === true;
    });
});
