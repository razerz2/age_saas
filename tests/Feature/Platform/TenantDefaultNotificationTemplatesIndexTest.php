<?php

use App\Models\Platform\User;
use Database\Seeders\TenantDefaultNotificationTemplatesSeeder;

function createPlatformUserForTenantDefaultTemplatesIndex(): User
{
    return User::query()->create([
        'name' => 'Admin',
        'name_full' => 'Administrador',
        'email' => 'tenant-default-index+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['tenant_default_notification_templates'],
        'email_verified_at' => now(),
    ]);
}

it('shows tenant default templates module on dedicated page', function () {
    app(TenantDefaultNotificationTemplatesSeeder::class)->run();
    $user = createPlatformUserForTenantDefaultTemplatesIndex();

    $this->actingAs($user, 'web')
        ->get(route('Platform.tenant-default-notification-templates.index'))
        ->assertOk()
        ->assertSee('Tenant Default Templates')
        ->assertSee('appointment.pending_confirmation')
        ->assertSee('waitlist.offered');
});

