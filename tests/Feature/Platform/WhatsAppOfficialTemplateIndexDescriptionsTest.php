<?php

use App\Models\Platform\User;
use Database\Seeders\WhatsAppOfficialTemplatesSeeder;

beforeEach(function (): void {
    config(['services.whatsapp.provider' => 'whatsapp_business']);

    if (function_exists('set_sysconfig')) {
        set_sysconfig('WHATSAPP_PROVIDER', 'whatsapp_business');
    }
});

function createPlatformUserForWhatsAppOfficialIndex(): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'index+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['whatsapp_official_templates'],
        'email_verified_at' => now(),
    ]);
}

it('shows saas event descriptions in official whatsapp templates index', function () {
    app(WhatsAppOfficialTemplatesSeeder::class)->run();
    $user = createPlatformUserForWhatsAppOfficialIndex();

    $this->actingAs($user, 'web')
        ->get(route('Platform.whatsapp-official-templates.index'))
        ->assertOk()
        ->assertSee('invoice.created')
        ->assertSee('Fatura criada')
        ->assertSee('security.2fa_code')
        ->assertSee('Codigo de verificacao (2FA)')
        ->assertSee('subscription.recovery_started')
        ->assertSee('Recovery de assinatura iniciado')
        ->assertSee('credentials.resent')
        ->assertSee('Reenvio de credenciais');
});
