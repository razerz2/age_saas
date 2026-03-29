<?php

use Carbon\CarbonImmutable;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\InteractsWithTenantAppointments;
use Tests\Browser\Pages\Tenant\Appointments\AppointmentIndexPage;

uses(InteractsWithTenantAppointments::class);

test('tenant admin can delete a controlled appointment through appointments module', function () {
    $seed = (string) floor(microtime(true) * 1000);
    $dependencies = $this->createControlledAppointmentDependencies($seed);

    $appointment = $this->createControlledAppointmentTarget($seed, [
        'dependencies' => $dependencies,
        'starts_at' => CarbonImmutable::parse($this->nextAppointmentDateString(2) . ' 11:00:00', 'America/Campo_Grande'),
        'notes' => sprintf('Dusk appointment delete %s', $seed),
        'test_tag' => 'dusk_appointment_delete',
    ]);

    $context = $this->tenantTestContext();
    $indexPath = sprintf('/workspace/%s/appointments', $context->slug);

    $this->browse(function (Browser $browser) use ($dependencies, $appointment, $indexPath) {
        $indexPage = new AppointmentIndexPage();

        $this->loginAsTenant($browser)
            ->visit($indexPage->url())
            ->on($indexPage)
            ->waitFor('@grid-search-input', 10)
            ->type('@grid-search-input', $dependencies['patient']['full_name'])
            ->pause(900)
            ->waitForText($dependencies['patient']['full_name'], 20)
            ->waitFor($indexPage->deleteActionSelector($appointment['id']), 20)
            ->click($indexPage->deleteActionSelector($appointment['id']))
            ->waitFor('[dusk="global-confirm-dialog"]', 10)
            ->assertSeeIn('[dusk="global-confirm-dialog-title"]', 'Excluir agendamento')
            ->press('[dusk="global-confirm-dialog-confirm-button"]')
            ->waitForLocation($indexPath, 20)
            ->waitFor('@grid-search-input', 10)
            ->clear('@grid-search-input')
            ->type('@grid-search-input', $dependencies['patient']['full_name'])
            ->pause(900)
            ->assertMissing($indexPage->deleteActionSelector($appointment['id']));
    });

    expect($this->tenantAppointmentExists($appointment['id']))->toBeFalse();
});
