<?php

use Carbon\CarbonImmutable;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\Tenant\Appointments\AppointmentForm;
use Tests\Browser\Concerns\InteractsWithTenantAppointments;
use Tests\Browser\Pages\Tenant\Appointments\AppointmentFormPage;
use Tests\Browser\Pages\Tenant\Appointments\AppointmentIndexPage;

uses(InteractsWithTenantAppointments::class);

test('tenant admin can create an appointment through appointments module', function () {
    $seed = (string) floor(microtime(true) * 1000);
    $dependencies = $this->createControlledAppointmentDependencies($seed);
    $date = $this->nextAppointmentDateString(2);
    $dateLabel = CarbonImmutable::parse($date)->format('d/m/Y');
    $notes = sprintf('Dusk appointment create %s', $seed);

    $context = $this->tenantTestContext();
    $indexPath = sprintf('/workspace/%s/appointments', $context->slug);

    $this->browse(function (Browser $browser) use ($dependencies, $date, $dateLabel, $notes, $indexPath) {
        $indexPage = new AppointmentIndexPage();
        $formPage = new AppointmentFormPage();
        $form = new AppointmentForm();

        $this->loginAsTenant($browser)
            ->visit($indexPage->url())
            ->on($indexPage)
            ->click('@new-appointment-button')
            ->on($formPage)
            ->within($form, function (Browser $formBrowser) use ($form, $dependencies, $date, $notes) {
                $form->setPatient($formBrowser, $dependencies['patient']['id'], $dependencies['patient']['full_name']);
                $form->setDoctor($formBrowser, $dependencies['doctor']['id'], $dependencies['doctor']['name']);
                $form->setDate($formBrowser, $date);
                $form->selectFirstFreeTimeSlot($formBrowser);
                $form->setNotes($formBrowser, $notes);
                $form->submit($formBrowser);
            })
            ->waitForLocation($indexPath, 20)
            ->on($indexPage)
            ->waitFor('@grid-search-input', 10)
            ->type('@grid-search-input', $dependencies['patient']['full_name'])
            ->pause(900)
            ->waitForText($dependencies['patient']['full_name'], 20)
            ->assertSee($dependencies['patient']['full_name'])
            ->assertSee($dateLabel);
    });

    $createdAppointmentId = $this->tenantAppointmentIdByNotes($notes);

    expect($createdAppointmentId)->not->toBeNull();
    expect($this->tenantAppointmentExists((string) $createdAppointmentId))->toBeTrue();
});
