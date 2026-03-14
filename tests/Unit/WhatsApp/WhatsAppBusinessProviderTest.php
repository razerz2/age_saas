<?php

use App\Services\WhatsApp\WhatsAppBusinessProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

it('uses v22 endpoint with valid phone_number_id', function () {
    Log::spy();

    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v18.0/',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.phone_id' => '1234567890',
        'services.whatsapp.token' => '',
        'services.whatsapp.phone_id' => '',
    ]);

    Http::fake([
        'https://graph.facebook.com/v22.0/1234567890/messages' => Http::response([
            'messages' => [['id' => 'wamid.HBgM']],
        ], 200),
    ]);

    $provider = new WhatsAppBusinessProvider();
    $result = $provider->sendMessage('67999998888', 'Teste provider');

    expect($result)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://graph.facebook.com/v22.0/1234567890/messages'
            && ($request['messaging_product'] ?? null) === 'whatsapp'
            && ($request['to'] ?? null) === '+5567999998888';
    });
});

it('sends authentication template payload without components or parameters', function () {
    Log::spy();

    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.phone_id' => '1234567890',
        'services.whatsapp.token' => '',
        'services.whatsapp.phone_id' => '',
    ]);

    Http::fake([
        'https://graph.facebook.com/v22.0/1234567890/messages' => Http::response([
            'messages' => [['id' => 'wamid.auth.no.params']],
        ], 200),
    ]);

    $provider = new WhatsAppBusinessProvider();
    $result = $provider->sendAuthenticationTemplateMessageDetailed(
        '5511999999999',
        'saas_security_2fa_code',
        'pt_BR'
    );

    expect($result['success'] ?? false)->toBeTrue();

    Http::assertSent(function ($request) {
        $payload = $request->data();
        $template = (array) ($payload['template'] ?? []);

        return $request->url() === 'https://graph.facebook.com/v22.0/1234567890/messages'
            && ($payload['type'] ?? null) === 'template'
            && (($template['name'] ?? null) === 'saas_security_2fa_code')
            && (($template['language']['code'] ?? null) === 'pt_BR')
            && !array_key_exists('components', $template);
    });
});

it('sends authentication template payload with body parameters when informed', function () {
    Log::spy();

    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.phone_id' => '1234567890',
        'services.whatsapp.token' => '',
        'services.whatsapp.phone_id' => '',
    ]);

    Http::fake([
        'https://graph.facebook.com/v22.0/1234567890/messages' => Http::response([
            'messages' => [['id' => 'wamid.auth.with.params']],
        ], 200),
    ]);

    $provider = new WhatsAppBusinessProvider();
    $result = $provider->sendAuthenticationTemplateMessageDetailed(
        '5511999999999',
        'saas_security_2fa_code',
        'pt_BR',
        ['123456']
    );

    expect($result['success'] ?? false)->toBeTrue();

    Http::assertSent(function ($request) {
        $payload = $request->data();
        $template = (array) ($payload['template'] ?? []);

        return $request->url() === 'https://graph.facebook.com/v22.0/1234567890/messages'
            && ($payload['type'] ?? null) === 'template'
            && (($template['name'] ?? null) === 'saas_security_2fa_code')
            && (($template['language']['code'] ?? null) === 'pt_BR')
            && (($template['components'][0]['type'] ?? null) === 'body')
            && (($template['components'][0]['parameters'][0]['text'] ?? null) === '123456');
    });
});

it('sends authentication template payload with button components when required', function () {
    Log::spy();

    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.phone_id' => '1234567890',
        'services.whatsapp.token' => '',
        'services.whatsapp.phone_id' => '',
    ]);

    Http::fake([
        'https://graph.facebook.com/v22.0/1234567890/messages' => Http::response([
            'messages' => [['id' => 'wamid.auth.with.button.params']],
        ], 200),
    ]);

    $provider = new WhatsAppBusinessProvider();
    $result = $provider->sendAuthenticationTemplateMessageDetailed(
        '5511999999999',
        'saas_security_2fa_code',
        'pt_BR',
        ['123456'],
        [[
            'sub_type' => 'url',
            'index' => '0',
            'parameters' => ['123456'],
        ]]
    );

    expect($result['success'] ?? false)->toBeTrue();

    Http::assertSent(function ($request) {
        $payload = $request->data();
        $template = (array) ($payload['template'] ?? []);

        return $request->url() === 'https://graph.facebook.com/v22.0/1234567890/messages'
            && ($payload['type'] ?? null) === 'template'
            && (($template['name'] ?? null) === 'saas_security_2fa_code')
            && (($template['language']['code'] ?? null) === 'pt_BR')
            && (($template['components'][0]['type'] ?? null) === 'body')
            && (($template['components'][0]['parameters'][0]['text'] ?? null) === '123456')
            && (($template['components'][1]['type'] ?? null) === 'button')
            && (($template['components'][1]['sub_type'] ?? null) === 'url')
            && ((string) ($template['components'][1]['index'] ?? '') === '0')
            && (($template['components'][1]['parameters'][0]['text'] ?? null) === '123456');
    });
});

it('throws clear error when phone_number_id is empty', function () {
    Log::spy();

    if (function_exists('set_sysconfig')) {
        try {
            foreach ([
                'WHATSAPP_META_PHONE_NUMBER_ID',
                'WHATSAPP_BUSINESS_PHONE_ID',
                'META_PHONE_NUMBER_ID',
                'BOT_META_PHONE_NUMBER_ID',
                'bot_meta_phone_number_id',
            ] as $key) {
                set_sysconfig($key, '');
            }
        } catch (\Throwable) {
            // Ignore when platform DB is unavailable in unit tests.
        }
    }

    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.phone_id' => '',
        'services.whatsapp.token' => '',
        'services.whatsapp.phone_id' => '',
    ]);

    Http::fake();

    $provider = new WhatsAppBusinessProvider();

    expect(fn () => $provider->sendMessage('67999998888', 'Teste sem phone id'))
        ->toThrow(\RuntimeException::class, 'phone_number_id nao definido');

    Http::assertNothingSent();
});

it('throws clear error when token is empty', function () {
    Log::spy();

    if (function_exists('set_sysconfig')) {
        try {
            foreach ([
                'WHATSAPP_META_TOKEN',
                'WHATSAPP_BUSINESS_TOKEN',
                'META_ACCESS_TOKEN',
                'BOT_META_ACCESS_TOKEN',
                'bot_meta_access_token',
            ] as $key) {
                set_sysconfig($key, '');
            }
        } catch (\Throwable) {
            // Ignore when platform DB is unavailable in unit tests.
        }
    }

    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => '',
        'services.whatsapp.business.phone_id' => '1234567890',
        'services.whatsapp.token' => '',
        'services.whatsapp.phone_id' => '',
    ]);

    Http::fake();

    $provider = new WhatsAppBusinessProvider();

    expect(fn () => $provider->sendMessage('67999998888', 'Teste sem token'))
        ->toThrow(\RuntimeException::class, 'token de acesso nao definido');

    Http::assertNothingSent();
});

it('normalizes base url to graph v22 even when version is omitted', function () {
    Log::spy();

    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.phone_id' => '999888777',
        'services.whatsapp.token' => '',
        'services.whatsapp.phone_id' => '',
    ]);

    Http::fake([
        'https://graph.facebook.com/v22.0/999888777/messages' => Http::response(['ok' => true], 200),
    ]);

    $provider = new WhatsAppBusinessProvider();
    $result = $provider->sendMessage('5511999999999', 'Teste v22');

    expect($result)->toBeTrue();

    Http::assertSent(fn ($request) => $request->url() === 'https://graph.facebook.com/v22.0/999888777/messages');
});
