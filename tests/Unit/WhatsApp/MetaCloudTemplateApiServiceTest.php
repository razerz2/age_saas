<?php

use App\Exceptions\WhatsAppMetaApiException;
use App\Exceptions\WhatsAppMetaConfigurationException;
use App\Services\WhatsApp\MetaCloudTemplateApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

function forceOfficialProvider(): void
{
    if (function_exists('set_sysconfig')) {
        try {
            set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
        } catch (\Throwable) {
            // Ignore when platform DB is unavailable in unit tests.
        }
    }
}

it('uses graph api v22 endpoint to create meta templates', function () {
    Log::spy();
    forceOfficialProvider();

    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v18.0/',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.waba_id' => '123456789012345',
    ]);

    Http::fake([
        'https://graph.facebook.com/v22.0/123456789012345/message_templates' => Http::response([
            'id' => '67890',
            'status' => 'PENDING',
        ], 200),
    ]);

    $service = new MetaCloudTemplateApiService();
    $response = $service->createTemplate([
        'name' => 'platform_billing_invoice_due',
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'components' => [
            [
                'type' => 'BODY',
                'text' => 'Ola {{1}}',
                'example' => [
                    'body_text' => [['Rafael']],
                ],
            ],
        ],
    ], [
        'key' => 'platform.billing.invoice_due',
        'version' => 1,
    ]);

    expect($response['id'])->toBe('67890');

    Http::assertSent(fn ($request) => $request->url() === 'https://graph.facebook.com/v22.0/123456789012345/message_templates');
});

it('throws clear config error when waba_id is missing', function () {
    Log::spy();
    forceOfficialProvider();
    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.waba_id' => '',
    ]);

    Http::fake();

    $service = new MetaCloudTemplateApiService();

    expect(fn () => $service->createTemplate([
        'name' => 'platform_billing_invoice_due',
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'components' => [[
            'type' => 'BODY',
            'text' => 'Ola {{1}}',
            'example' => ['body_text' => [['Rafael']]],
        ]],
    ]))->toThrow(WhatsAppMetaConfigurationException::class, 'WABA ID');

    Http::assertNothingSent();
});

it('throws clear config error when token is missing', function () {
    Log::spy();
    forceOfficialProvider();
    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => '',
        'services.whatsapp.business.waba_id' => '123456789012345',
    ]);

    Http::fake();

    $service = new MetaCloudTemplateApiService();

    expect(fn () => $service->createTemplate([
        'name' => 'platform_billing_invoice_due',
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'components' => [[
            'type' => 'BODY',
            'text' => 'Ola {{1}}',
            'example' => ['body_text' => [['Rafael']]],
        ]],
    ]))->toThrow(WhatsAppMetaConfigurationException::class, 'token');

    Http::assertNothingSent();
});

it('throws clear config error when provider is not official', function () {
    Log::spy();

    if (function_exists('set_sysconfig')) {
        try {
            set_sysconfig('WHATSAPP_PROVIDER', 'zapi');
        } catch (\Throwable) {
            // Ignore when platform DB is unavailable in unit tests.
        }
    }

    config([
        'services.whatsapp.provider' => 'zapi',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.waba_id' => '123456789012345',
    ]);

    Http::fake();

    $service = new MetaCloudTemplateApiService();

    expect(fn () => $service->fetchTemplateByNameAndLanguage('platform_billing_invoice_due', 'pt_BR'))
        ->toThrow(WhatsAppMetaConfigurationException::class, 'Provider inconsistente');

    Http::assertNothingSent();
});

it('throws clear config error when body placeholders have no examples', function () {
    Log::spy();
    forceOfficialProvider();
    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.waba_id' => '123456789012345',
    ]);

    Http::fake();

    $service = new MetaCloudTemplateApiService();

    expect(fn () => $service->createTemplate([
        'name' => 'platform_billing_invoice_due',
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'components' => [['type' => 'BODY', 'text' => 'Ola {{1}}']],
    ]))->toThrow(WhatsAppMetaConfigurationException::class, 'example.body_text');

    Http::assertNothingSent();
});

it('accepts authentication payload with otp components', function () {
    Log::spy();
    forceOfficialProvider();

    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.waba_id' => '123456789012345',
    ]);

    Http::fake([
        'https://graph.facebook.com/v22.0/123456789012345/message_templates' => Http::response([
            'id' => 'meta-auth-1',
            'status' => 'PENDING_REVIEW',
        ], 200),
    ]);

    $service = new MetaCloudTemplateApiService();
    $response = $service->createTemplate([
        'name' => 'saas_security_2fa_code',
        'category' => 'AUTHENTICATION',
        'language' => 'pt_BR',
        'components' => [
            ['type' => 'BODY', 'add_security_recommendation' => true],
            ['type' => 'FOOTER', 'code_expiration_minutes' => 10],
            [
                'type' => 'BUTTONS',
                'buttons' => [
                    ['type' => 'OTP', 'otp_type' => 'COPY_CODE'],
                ],
            ],
        ],
    ]);

    expect($response['id'])->toBe('meta-auth-1');

    Http::assertSent(function ($request) {
        $payload = $request->data();
        $components = (array) ($payload['components'] ?? []);
        $body = collect($components)->firstWhere('type', 'BODY');
        $footer = collect($components)->firstWhere('type', 'FOOTER');
        $buttons = collect($components)->firstWhere('type', 'BUTTONS');

        return $request->url() === 'https://graph.facebook.com/v22.0/123456789012345/message_templates'
            && (($payload['category'] ?? null) === 'AUTHENTICATION')
            && (($body['add_security_recommendation'] ?? null) === true)
            && (($footer['code_expiration_minutes'] ?? null) === 10)
            && (($buttons['buttons'][0]['type'] ?? null) === 'OTP')
            && (($buttons['buttons'][0]['otp_type'] ?? null) === 'COPY_CODE');
    });
});

it('returns detailed meta error information on http 400', function () {
    Log::spy();
    forceOfficialProvider();

    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.waba_id' => '123456789012345',
    ]);

    Http::fake([
        'https://graph.facebook.com/v22.0/123456789012345/message_templates' => Http::response([
            'error' => [
                'message' => 'Invalid parameter',
                'type' => 'OAuthException',
                'code' => 100,
                'error_data' => ['details' => 'Invalid AUTHENTICATION template components'],
                'fbtrace_id' => 'ABCD1234',
            ],
        ], 400),
    ]);

    $service = new MetaCloudTemplateApiService();

    try {
        $service->createTemplate([
            'name' => 'saas_security_2fa_code',
            'category' => 'AUTHENTICATION',
            'language' => 'pt_BR',
            'components' => [
                ['type' => 'BODY', 'add_security_recommendation' => true],
                ['type' => 'FOOTER', 'code_expiration_minutes' => 10],
                [
                    'type' => 'BUTTONS',
                    'buttons' => [
                        ['type' => 'OTP', 'otp_type' => 'COPY_CODE'],
                    ],
                ],
            ],
        ]);

        $this->fail('Expected WhatsAppMetaApiException was not thrown.');
    } catch (WhatsAppMetaApiException $e) {
        expect($e->httpStatus())->toBe(400)
            ->and($e->metaError()['message'] ?? null)->toBe('Invalid parameter')
            ->and($e->metaError()['code'] ?? null)->toBe(100)
            ->and($e->metaError()['details'] ?? null)->toBe('Invalid AUTHENTICATION template components')
            ->and($e->metaError()['fbtrace_id'] ?? null)->toBe('ABCD1234')
            ->and($e->userSafeMessage())->toContain('Invalid parameter')
            ->and($e->userSafeMessage())->toContain('code=100')
            ->and($e->userSafeMessage())->toContain('fbtrace_id=ABCD1234');
    }
});
