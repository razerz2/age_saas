<?php

namespace Tests\Browser\Components\Tenant;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;

final class TenantLoginForm extends Component
{
    public function selector(): string
    {
        return '#login-form';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertVisible($this->selector())
            ->assertVisible('@email-input')
            ->assertVisible('@password-input')
            ->assertVisible('@submit-button');
    }

    public function elements(): array
    {
        return [
            '@email-input' => '#email',
            '@password-input' => '#password',
            '@submit-button' => 'button[type="submit"]',
        ];
    }

    public function submitCredentials(Browser $browser, string $email, string $password): void
    {
        $browser->type('@email-input', $email)
            ->type('@password-input', $password)
            ->press('@submit-button');
    }
}
