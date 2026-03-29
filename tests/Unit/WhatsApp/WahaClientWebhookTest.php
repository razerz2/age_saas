<?php

use App\Services\WhatsApp\WahaClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('reads waha webhook configuration from session payload', function () {
    $client = new WahaClient('https://waha.test', 'token-waha', 'clinica-teste');

    Http::fake([
        'https://waha.test/api/sessions/clinica-teste' => Http::response([
            'status' => 'WORKING',
            'config' => [
                'webhooks' => [
                    [
                        'url' => 'https://app.test/customer/clinica-teste/webhooks/whatsapp/bot/waha',
                        'events' => ['message'],
                        'enabled' => true,
                    ],
                ],
            ],
        ], 200),
    ]);

    $result = $client->getSessionWebhookConfig();

    expect($result['ok'])->toBeTrue()
        ->and($result['webhook']['url'] ?? null)->toBe('https://app.test/customer/clinica-teste/webhooks/whatsapp/bot/waha');
});

it('updates waha webhook configuration through session update endpoint', function () {
    $client = new WahaClient('https://waha.test', 'token-waha', 'clinica-teste');

    Http::fake([
        'https://waha.test/api/sessions/clinica-teste' => function ($request) {
            if ($request->method() === 'GET') {
                return Http::response([
                    'status' => 'WORKING',
                    'config' => [
                        'webhooks' => [],
                    ],
                ], 200);
            }

            return Http::response([
                'success' => true,
            ], 200);
        },
    ]);

    $result = $client->setSessionWebhook('https://app.test/customer/clinica-teste/webhooks/whatsapp/bot/waha');

    expect($result['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        if ($request->method() !== 'PUT') {
            return false;
        }

        if ($request->url() !== 'https://waha.test/api/sessions/clinica-teste') {
            return false;
        }

        $payload = $request->data();
        $configuredWebhookUrl = data_get($payload, 'config.webhooks.0.url');

        return $configuredWebhookUrl === 'https://app.test/customer/clinica-teste/webhooks/whatsapp/bot/waha'
            && $request->hasHeader('X-Api-Key', 'token-waha');
    });
});

