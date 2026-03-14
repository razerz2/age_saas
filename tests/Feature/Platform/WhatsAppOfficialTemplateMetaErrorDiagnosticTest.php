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

it('returns detailed and useful diagnostic message when meta create template fails with 400', function () {
    Http::fake([
        'https://graph.facebook.com/v22.0/123456789012345/message_templates' => Http::response([
            'error' => [
                'message' => 'Invalid parameter',
                'type' => 'OAuthException',
                'code' => 100,
                'error_data' => [
                    'details' => 'Invalid AUTHENTICATION template components',
                ],
                'fbtrace_id' => 'ABCD1234',
            ],
        ], 400),
    ]);

    $user = User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'meta-error+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['whatsapp_official_templates'],
        'email_verified_at' => now(),
    ]);

    $template = WhatsAppOfficialTemplate::query()->create([
        'key' => 'security.2fa_code',
        'meta_template_name' => 'saas_security_2fa_code',
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'SECURITY',
        'language' => 'pt_BR',
        'body_text' => "Ola {{1}}.\nSeu codigo e {{2}}.\nExpira em {{3}} minutos.",
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
        'version' => 1,
        'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
    ]);

    $this->from(route('Platform.whatsapp-official-templates.show', $template))
        ->actingAs($user, 'web')
        ->post(route('Platform.whatsapp-official-templates.submit', $template))
        ->assertRedirect(route('Platform.whatsapp-official-templates.show', $template))
        ->assertSessionHasErrors('template');

    $message = session('errors')->first('template');
    expect($message)->toContain('Invalid parameter')
        ->toContain('code=100')
        ->toContain('Invalid AUTHENTICATION template components')
        ->toContain('fbtrace_id=ABCD1234');
});
