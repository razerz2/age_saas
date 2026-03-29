<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;
use Tests\Browser\Components\Tenant\TenantLoginForm;
use Tests\Browser\Pages\Tenant\TenantLoginPage;
use Tests\Browser\Support\TenantTestContext;

trait LogsIntoTenant
{
    use InteractsWithTenantTestContext;

    protected function loginAsTenant(Browser $browser, ?TenantTestContext $context = null): Browser
    {
        $context ??= $this->tenantTestContext();
        $dashboardPath = sprintf('/workspace/%s/dashboard', $context->slug);
        $loginForm = new TenantLoginForm();

        $browser->visit($context->loginUrl())
            ->on(new TenantLoginPage())
            ->within($loginForm, function (Browser $form) use ($loginForm, $context) {
                $loginForm->submitCredentials($form, $context->email, $context->password);
            })
            ->waitForLocation($dashboardPath, 20)
            ->assertPathIs($dashboardPath)
            ->assertDontSee('Senha incorreta')
            ->assertDontSee('Usuario nao encontrado');

        return $browser;
    }
}
