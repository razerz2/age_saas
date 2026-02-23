<?php

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('normalizes WAHA test endpoint numbers before sending', function () {
    config([
        'services.whatsapp.waha.base_url' => 'https://waha.test',
        'services.whatsapp.waha.api_key' => 'token',
        'services.whatsapp.waha.session' => 'default',
    ]);

    Http::fake([
        'https://waha.test/api/sessions/*' => Http::response(['status' => 'WORKING'], 200),
        'https://waha.test/api/sendText' => Http::response(['result' => 'ok'], 200),
    ]);

    $response = $this->withoutMiddleware()->postJson(route('settings.test.waha.send'), [
        'number' => '67992998146',
        'message' => 'Teste',
    ]);

    $response->assertOk()->assertJson([
        'status' => 'OK',
    ]);

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://waha.test/api/sendText') {
            return false;
        }

        $data = $request->data();
        return ($data['chatId'] ?? null) === '556792998146@c.us';
    });
});
