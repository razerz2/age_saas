<?php

use App\Models\Platform\TwoFactorCode;
use App\Models\Platform\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

function createPlatformUserForTwoFactorTests(array $overrides = []): User
{
    return User::query()->create(array_merge([
        'name' => 'Admin 2FA',
        'name_full' => 'Administrador 2FA',
        'email' => 'twofactor+' . uniqid() . '@plataforma.com',
        'password' => 'password',
        'status' => 'active',
        'modules' => ['settings'],
        'email_verified_at' => now(),
        'two_factor_enabled' => false,
        'two_factor_method' => null,
    ], $overrides));
}

it('blocks whatsapp 2fa activation in platform settings when user has no valid phone', function () {
    Log::spy();
    $user = createPlatformUserForTwoFactorTests();

    $this->actingAs($user, 'web')
        ->from(route('Platform.two-factor.index'))
        ->post(route('Platform.two-factor.set-method'), [
            'method' => 'whatsapp',
        ])
        ->assertRedirect(route('Platform.two-factor.index'))
        ->assertSessionHasErrors('method');

    Log::shouldHaveReceived('warning')
        ->withArgs(function (string $message, array $context): bool {
            return $message === 'platform_2fa_whatsapp_unavailable_on_settings'
                && ($context['reason'] ?? null) !== null;
        })
        ->once();
});

it('falls back to email challenge when configured method is whatsapp without platform phone', function () {
    Notification::fake();

    $user = createPlatformUserForTwoFactorTests([
        'two_factor_enabled' => true,
        'two_factor_method' => 'whatsapp',
    ]);

    $this->withSession([
        'login.id' => $user->id,
        'login.remember' => false,
    ])->get(route('two-factor.challenge'))
        ->assertOk()
        ->assertSee('e-mail');

    expect(session('two_factor_runtime_method'))->toBe('email');

    $code = TwoFactorCode::query()
        ->where('user_id', $user->id)
        ->latest('created_at')
        ->first();

    expect($code)->not->toBeNull()
        ->and($code?->method)->toBe('email');
});
