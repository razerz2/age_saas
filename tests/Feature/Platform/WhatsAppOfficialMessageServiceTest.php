<?php

use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Services\Platform\WhatsAppOfficialMessageService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.phone_id' => '1234567890',
    ]);

    if (function_exists('set_sysconfig')) {
        set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
    }
});

function createApprovedOfficialTemplateForRuntime(array $overrides = []): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create(array_merge([
        'key' => 'invoice.created',
        'meta_template_name' => 'saas_invoice_created',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => "Ola {{1}}. Fatura {{2}}. Valor {{3}}. Vence {{4}}. Link {{5}}.",
        'variables' => [
            '1' => 'customer_name',
            '2' => 'tenant_name',
            '3' => 'invoice_amount',
            '4' => 'due_date',
            '5' => 'payment_link',
        ],
        'sample_variables' => [
            '1' => 'Cliente',
            '2' => 'Clinica Exemplo',
            '3' => 'R$ 299,90',
            '4' => '20/03/2026',
            '5' => 'https://app.allsync.com.br/faturas/abc',
        ],
        'version' => 1,
        'status' => WhatsAppOfficialTemplate::STATUS_APPROVED,
    ], $overrides));
}

it('sends official template message using approved key lookup', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/1234567890/messages' => Http::response([
            'messages' => [['id' => 'wamid.123']],
        ], 200),
    ]);

    createApprovedOfficialTemplateForRuntime();

    $sent = app(WhatsAppOfficialMessageService::class)->sendByKey(
        'invoice.created',
        '5565999999999',
        [
            'customer_name' => 'Rafael',
            'tenant_name' => 'Clinica Exemplo',
            'invoice_amount' => 'R$ 299,90',
            'due_date' => '20/03/2026',
            'payment_link' => 'https://app.allsync.com.br/faturas/abc',
        ],
        ['test_case' => 'approved_lookup']
    );

    expect($sent)->toBeTrue();

    Http::assertSent(function ($request): bool {
        $payload = $request->data();
        $parameters = $payload['template']['components'][0]['parameters'] ?? [];

        return $request->url() === 'https://graph.facebook.com/v22.0/1234567890/messages'
            && ($payload['type'] ?? null) === 'template'
            && ($payload['template']['name'] ?? null) === 'saas_invoice_created'
            && ($payload['template']['language']['code'] ?? null) === 'pt_BR'
            && (($parameters[0]['text'] ?? null) === 'Rafael')
            && (($parameters[1]['text'] ?? null) === 'Clinica Exemplo')
            && (($parameters[2]['text'] ?? null) === 'R$ 299,90')
            && (($parameters[3]['text'] ?? null) === '20/03/2026')
            && (($parameters[4]['text'] ?? null) === 'https://app.allsync.com.br/faturas/abc');
    });
});

it('sends authentication template by key using remote body and button parameters', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/1234567890/messages' => Http::response([
            'messages' => [['id' => 'wamid.auth.123']],
        ], 200),
    ]);

    createApprovedOfficialTemplateForRuntime([
        'key' => 'security.2fa_code',
        'meta_template_name' => 'saas_security_2fa_code',
        'category' => 'SECURITY',
        'body_text' => 'Seu codigo de verificacao.',
        'variables' => [
            '1' => 'code',
        ],
        'sample_variables' => [
            '1' => '123456',
        ],
        'meta_response' => [
            'data' => [[
                'name' => 'saas_security_2fa_code',
                'language' => 'pt_BR',
                'status' => 'APPROVED',
                'category' => 'AUTHENTICATION',
                'components' => [[
                    'type' => 'BODY',
                    'text' => 'Seu codigo e {{1}}',
                    'example' => [
                        'body_text' => [['123456']],
                    ],
                ], [
                    'type' => 'BUTTONS',
                    'buttons' => [[
                        'type' => 'URL',
                        'url' => 'https://app.allsync.com.br/otp?code={{1}}',
                        'example' => [
                            'url' => ['https://app.allsync.com.br/otp?code=123456'],
                        ],
                    ]],
                ]],
            ]],
        ],
    ]);

    $sent = app(WhatsAppOfficialMessageService::class)->sendByKey(
        'security.2fa_code',
        '5565999999999',
        [
            'code' => '998877',
        ],
        ['test_case' => 'auth_without_parameters']
    );

    expect($sent)->toBeTrue();

    Http::assertSent(function ($request): bool {
        $payload = $request->data();
        $template = (array) ($payload['template'] ?? []);

        return $request->url() === 'https://graph.facebook.com/v22.0/1234567890/messages'
            && ($payload['type'] ?? null) === 'template'
            && (($template['name'] ?? null) === 'saas_security_2fa_code')
            && (($template['language']['code'] ?? null) === 'pt_BR')
            && (($template['components'][0]['type'] ?? null) === 'body')
            && (($template['components'][0]['parameters'][0]['text'] ?? null) === '998877')
            && (($template['components'][1]['type'] ?? null) === 'button')
            && (($template['components'][1]['sub_type'] ?? null) === 'url')
            && ((string) ($template['components'][1]['index'] ?? '') === '0')
            && (($template['components'][1]['parameters'][0]['text'] ?? null) === '998877');
    });
});

it('does not send when template key does not exist', function () {
    Http::fake();

    $sent = app(WhatsAppOfficialMessageService::class)->sendByKey(
        'invoice.created',
        '5565999999999',
        [
            'customer_name' => 'Rafael',
        ],
        ['test_case' => 'missing_template']
    );

    expect($sent)->toBeFalse();
    Http::assertNothingSent();
});

it('does not send when template exists but is not approved', function () {
    Http::fake();

    createApprovedOfficialTemplateForRuntime([
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
    ]);

    $sent = app(WhatsAppOfficialMessageService::class)->sendByKey(
        'invoice.created',
        '5565999999999',
        [
            'customer_name' => 'Rafael',
            'tenant_name' => 'Clinica Exemplo',
            'invoice_amount' => 'R$ 299,90',
            'due_date' => '20/03/2026',
            'payment_link' => 'https://app.allsync.com.br/faturas/abc',
        ],
        ['test_case' => 'draft_template']
    );

    expect($sent)->toBeFalse();
    Http::assertNothingSent();
});

it('logs structured skip context when template is not approved', function () {
    Http::fake();
    Log::spy();

    createApprovedOfficialTemplateForRuntime([
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
    ]);

    $sent = app(WhatsAppOfficialMessageService::class)->sendByKey(
        'invoice.created',
        '5565999999999',
        [
            'customer_name' => 'Rafael',
            'tenant_name' => 'Clinica Exemplo',
            'invoice_amount' => 'R$ 299,90',
            'due_date' => '20/03/2026',
            'payment_link' => 'https://app.allsync.com.br/faturas/abc',
        ],
        ['test_case' => 'draft_template_log']
    );

    expect($sent)->toBeFalse();
    Http::assertNothingSent();

    Log::shouldHaveReceived('warning')
        ->withArgs(function (string $message, array $context): bool {
            return $message === 'platform_whatsapp_official_send_skipped'
                && ($context['key'] ?? null) === 'invoice.created'
                && ($context['reason'] ?? null) === 'template_not_approved'
                && ($context['provider'] ?? null) === 'whatsapp_business'
                && ($context['latest_status'] ?? null) === 'draft';
        })
        ->once();
});

it('does not send when required variables are missing', function () {
    Http::fake();

    createApprovedOfficialTemplateForRuntime();

    $sent = app(WhatsAppOfficialMessageService::class)->sendByKey(
        'invoice.created',
        '5565999999999',
        [
            'customer_name' => 'Rafael',
            'tenant_name' => 'Clinica Exemplo',
            'invoice_amount' => 'R$ 299,90',
        ],
        ['test_case' => 'missing_variables']
    );

    expect($sent)->toBeFalse();
    Http::assertNothingSent();
});
