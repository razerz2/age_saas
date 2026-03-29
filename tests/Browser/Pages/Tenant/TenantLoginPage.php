<?php

namespace Tests\Browser\Pages\Tenant;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;
use Tests\Browser\Support\TenantTestContext;

final class TenantLoginPage extends Page
{
    public function url(): string
    {
        return TenantTestContext::fromEnvironment()->loginPath();
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
            ->assertPresent('@login-form')
            ->assertVisible('@email-input')
            ->assertVisible('@password-input')
            ->assertVisible('@submit-button');
    }

    public function elements(): array
    {
        return [
            '@login-form' => '#login-form',
            '@heading' => '.auth-heading',
            '@tenant-subtext' => 'p.auth-subtext',
            '@email-input' => '#email',
            '@password-input' => '#password',
            '@submit-button' => 'button[type="submit"]',
        ];
    }
}
