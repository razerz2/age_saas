<?php

use App\Models\Platform\User;
use App\Models\Platform\WhatsAppOfficialTemplate;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config([
        'services.whatsapp.provider' => 'whatsapp_business',
        'services.whatsapp.business.api_url' => 'https://graph.facebook.com/v22.0',
        'services.whatsapp.business.token' => 'meta-token-123456',
        'services.whatsapp.business.waba_id' => '123456789012345',
    ]);

    if (function_exists('set_sysconfig')) {
        set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
    }
});

function createPlatformUserForTemplateSubmit(): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'submit+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['whatsapp_official_templates'],
        'email_verified_at' => now(),
    ]);
}

function createTemplateForSubmit(array $overrides = []): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create(array_merge([
        'key' => 'platform.billing.invoice_due',
        'meta_template_name' => 'platform_billing_invoice_due',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => "Ola {{1}}.\n\nSua fatura vence em {{2}}.\n\nLink de pagamento: {{3}}.\nMensagem automatica.",
        'variables' => [
            '1' => 'tenant.trade_name',
            '2' => 'invoice.due_date',
            '3' => 'invoice.payment_link',
        ],
        'sample_variables' => [
            '1' => 'Clinica Exemplo',
            '2' => '14/03/2026 as 09:00',
            '3' => 'https://app.allsync.com.br/faturas/pagar/abc123',
        ],
        'version' => 1,
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
    ], $overrides));
}

function createSecurityTemplateForSubmit(array $overrides = []): WhatsAppOfficialTemplate
{
    return createTemplateForSubmit(array_merge([
        'key' => 'security.2fa_code',
        'meta_template_name' => 'saas_security_2fa_code',
        'category' => 'SECURITY',
        'body_text' => "Ola {{1}}.\n\nSeu codigo de verificacao e {{2}}.\n\nEste codigo expira em {{3}} minutos.",
        'variables' => [
            '1' => 'customer_name',
            '2' => 'code',
            '3' => 'expires_in_minutes',
        ],
        'sample_variables' => [
            '1' => 'Rafael',
            '2' => '123456',
            '3' => '10',
        ],
    ], $overrides));
}

it('submits body example values to meta payload', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/123456789012345/message_templates' => Http::response([
            'id' => 'meta-template-1',
            'status' => 'PENDING_REVIEW',
        ], 200),
    ]);

    $user = createPlatformUserForTemplateSubmit();
    $template = createTemplateForSubmit();

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-templates.submit', $template))
        ->assertRedirect(route('Platform.whatsapp-official-templates.show', $template));

    Http::assertSent(function ($request) {
        $payload = $request->data();
        $components = (array) ($payload['components'] ?? []);
        $body = collect($components)->firstWhere('type', 'BODY');

        return $request->url() === 'https://graph.facebook.com/v22.0/123456789012345/message_templates'
            && is_array($body)
            && (($body['example']['body_text'][0][0] ?? null) === 'Clinica Exemplo')
            && (($body['example']['body_text'][0][1] ?? null) === '14/03/2026 as 09:00')
            && (($body['example']['body_text'][0][2] ?? null) === 'https://app.allsync.com.br/faturas/pagar/abc123');
    });
});

it('blocks submit when sample_variables are missing', function () {
    Http::fake();

    $user = createPlatformUserForTemplateSubmit();
    $template = createTemplateForSubmit([
        'sample_variables' => [
            '1' => 'Clinica Exemplo',
            '2' => '14/03/2026 as 09:00',
        ],
    ]);

    $this->from(route('Platform.whatsapp-official-templates.show', $template))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-templates.submit', $template))
        ->assertRedirect(route('Platform.whatsapp-official-templates.show', $template))
        ->assertSessionHasErrors('template');

    Http::assertNothingSent();
});

it('maps SECURITY category to AUTHENTICATION in meta payload', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/123456789012345/message_templates' => Http::response([
            'id' => 'meta-template-security-1',
            'status' => 'PENDING_REVIEW',
        ], 200),
    ]);

    $user = createPlatformUserForTemplateSubmit();
    $template = createSecurityTemplateForSubmit();

    $this->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-templates.submit', $template))
        ->assertRedirect(route('Platform.whatsapp-official-templates.show', $template));

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
