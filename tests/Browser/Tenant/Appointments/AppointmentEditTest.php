<?php

use Carbon\CarbonImmutable;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\Tenant\Appointments\AppointmentForm;
use Tests\Browser\Concerns\InteractsWithTenantAppointments;
use Tests\Browser\Pages\Tenant\Appointments\AppointmentFormPage;
use Tests\Browser\Pages\Tenant\Appointments\AppointmentIndexPage;

uses(InteractsWithTenantAppointments::class);

test('tenant admin can edit a controlled appointment through appointments module', function () {
    $seed = (string) floor(microtime(true) * 1000);
    $dependencies = $this->createControlledAppointmentDependencies($seed);

    $baseAppointment = $this->createControlledAppointmentTarget($seed, [
        'dependencies' => $dependencies,
        'starts_at' => CarbonImmutable::parse($this->nextAppointmentDateString(2) . ' 10:00:00', 'America/Campo_Grande'),
        'notes' => sprintf('Dusk appointment edit base %s', $seed),
        'test_tag' => 'dusk_appointment_edit',
    ]);

    $updatedDate = $this->nextAppointmentDateString(3);
    $updatedDateLabel = CarbonImmutable::parse($updatedDate)->format('d/m/Y');
    $updatedNotes = sprintf('Dusk appointment edit updated %s', $seed);

    $context = $this->tenantTestContext();
    $indexPath = sprintf('/workspace/%s/appointments', $context->slug);

    $this->browse(function (Browser $browser) use ($dependencies, $baseAppointment, $updatedDate, $updatedDateLabel, $updatedNotes, $indexPath) {
        $indexPage = new AppointmentIndexPage();
        $formPage = AppointmentFormPage::edit($baseAppointment['id']);
        $form = new AppointmentForm();

        $this->loginAsTenant($browser)
            ->visit($indexPage->url())
            ->on($indexPage)
            ->waitFor('@grid-search-input', 10)
            ->type('@grid-search-input', $dependencies['patient']['full_name'])
            ->pause(900)
            ->waitForText($dependencies['patient']['full_name'], 20)
            ->waitFor($indexPage->editActionSelector($baseAppointment['id']), 20)
            ->click($indexPage->editActionSelector($baseAppointment['id']))
            ->on($formPage)
            ->within($form, function (Browser $formBrowser) use ($form, $updatedDate, $updatedNotes) {
                $form->setDate($formBrowser, $updatedDate);
                $form->selectFirstFreeTimeSlot($formBrowser);
                $form->setNotes($formBrowser, $updatedNotes);
                $form->submit($formBrowser);
            })
            ->waitForLocation($indexPath, 20)
            ->on($indexPage)
            ->waitFor('@success-alert', 20)
            ->assertSeeIn('@success-alert', 'Agendamento atualizado com sucesso.')
            ->waitFor('@grid-search-input', 10)
            ->clear('@grid-search-input')
            ->type('@grid-search-input', $dependencies['patient']['full_name'])
            ->pause(900)
            ->waitForText($dependencies['patient']['full_name'], 20)
            ->assertSee($updatedDateLabel);
    });

    $updatedAppointmentId = $this->tenantAppointmentIdByNotes($updatedNotes);

    expect($updatedAppointmentId)->toBe($baseAppointment['id']);
    expect($this->tenantAppointmentExists($baseAppointment['id']))->toBeTrue();
});
