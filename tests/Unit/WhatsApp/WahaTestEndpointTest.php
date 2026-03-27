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

    $response = $this->withoutMiddleware()->postJson(route('Platform.settings.test.waha.send'), [
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

it('uses WAHA runtime form config in platform session test endpoint', function () {
    config([
        'services.whatsapp.waha.base_url' => 'https://wrong.test',
        'services.whatsapp.waha.api_key' => 'token-default',
        'services.whatsapp.waha.session' => 'default',
    ]);

    Http::fake([
        'https://waha.test/api/sessions/AllSync' => Http::response(['status' => 'WORKING'], 200),
    ]);

    $url = route('Platform.settings.test', ['service' => 'waha'])
        . '?' . http_build_query([
            'WAHA_BASE_URL' => 'https://waha.test',
            'WAHA_API_KEY' => 'token-form',
            'WAHA_SESSION' => 'AllSync',
        ]);

    $response = $this->withoutMiddleware()->getJson($url);

    $response->assertOk()->assertJson([
        'status' => 'OK',
    ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://waha.test/api/sessions/AllSync'
            && $request->hasHeader('X-Api-Key', 'token-form')
            && !$request->hasHeader('Authorization');
    });
});

it('uses WAHA runtime form config in platform send test endpoint', function () {
    config([
        'services.whatsapp.waha.base_url' => 'https://wrong.test',
        'services.whatsapp.waha.api_key' => 'token-default',
        'services.whatsapp.waha.session' => 'default',
    ]);

    Http::fake([
        'https://waha.test/api/sessions/AllSync' => Http::response(['status' => 'WORKING'], 200),
        'https://waha.test/api/sendText' => Http::response(['result' => 'ok'], 200),
    ]);

    $response = $this->withoutMiddleware()->postJson(route('Platform.settings.test.waha.send'), [
        'number' => '67992998146',
        'message' => 'Teste com override',
        'WAHA_BASE_URL' => 'https://waha.test',
        'WAHA_API_KEY' => 'token-form',
        'WAHA_SESSION' => 'AllSync',
    ]);

    $response->assertOk()->assertJson([
        'status' => 'OK',
    ]);

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://waha.test/api/sendText') {
            return false;
        }

        $data = $request->data();

        return ($data['session'] ?? null) === 'AllSync'
            && ($data['chatId'] ?? null) === '556792998146@c.us'
            && $request->hasHeader('X-Api-Key', 'token-form')
            && !$request->hasHeader('Authorization');
    });
});
