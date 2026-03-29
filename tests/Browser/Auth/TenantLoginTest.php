<?php

use Laravel\Dusk\Browser;

test('tenant admin can authenticate and reach the dashboard', function () {
    $context = $this->tenantTestContext();
    $dashboardPath = sprintf('/workspace/%s/dashboard', $context->slug);

    $this->browse(function (Browser $browser) use ($dashboardPath) {
        $this->loginAsTenant($browser)
            ->waitFor('aside.sidebar', 10)
            ->assertPathIs($dashboardPath)
            ->assertVisible('aside.sidebar')
            ->assertSeeIn('aside.sidebar', 'Dashboard')
            ->assertSee('Dashboard');
    });
});
