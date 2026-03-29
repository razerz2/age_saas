<?php

use Laravel\Dusk\Browser;
use Tests\Browser\Components\Tenant\TenantLoginForm;
use Tests\Browser\Pages\Tenant\TenantLoginPage;

test('tenant login page smoke test renders expected base elements', function () {
    $context = $this->tenantTestContext();

    $this->browse(function (Browser $browser) use ($context) {
        $browser->visit($context->loginUrl())
            ->on(new TenantLoginPage())
            ->assertTitleContains('Login')
            ->assertSee('Bem-vindo!')
            ->assertSee("Tenant: {$context->slug}")
            ->within(new TenantLoginForm(), function (Browser $form) {
                $form->assertSee('Entrar');
            });
    });
});
