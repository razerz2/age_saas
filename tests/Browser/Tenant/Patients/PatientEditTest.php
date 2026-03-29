<?php

use Laravel\Dusk\Browser;
use Tests\Browser\Components\Tenant\Patients\PatientForm;
use Tests\Browser\Concerns\InteractsWithTenantPatients;
use Tests\Browser\Pages\Tenant\Patients\PatientFormPage;
use Tests\Browser\Pages\Tenant\Patients\PatientIndexPage;

uses(InteractsWithTenantPatients::class);

test('tenant admin can edit a controlled patient through patients module', function () {
    $context = $this->tenantTestContext();
    $seed = (string) floor(microtime(true) * 1000);
    $suffix = substr($seed, -6);
    $patientTarget = $this->createControlledPatientTarget($seed);

    $patientsIndexPath = sprintf('/workspace/%s/patients', $context->slug);
    $updatedPatient = [
        'full_name' => sprintf('Paciente Editado Dusk %s', $suffix),
        'street' => sprintf('Rua Editada %s', $suffix),
        'number' => '456',
        'neighborhood' => sprintf('Bairro Editado %s', $suffix),
        'postal_code' => '78050000',
    ];

    $this->browse(function (Browser $browser) use ($patientTarget, $patientsIndexPath, $updatedPatient) {
        $indexPage = new PatientIndexPage();
        $editPage = PatientFormPage::edit($patientTarget['id']);
        $patientForm = new PatientForm();

        $this->loginAsTenant($browser)
            ->visit($indexPage->url())
            ->on($indexPage)
            ->waitFor('@grid-search-input', 10)
            ->type('@grid-search-input', $patientTarget['full_name'])
            ->pause(900)
            ->waitForText($patientTarget['full_name'], 20)
            ->waitFor($indexPage->editActionSelector($patientTarget['id']), 20)
            ->click($indexPage->editActionSelector($patientTarget['id']))
            ->on($editPage)
            ->within($patientForm, function (Browser $formBrowser) use ($patientForm, $updatedPatient) {
                $patientForm->updateAndSubmit($formBrowser, $updatedPatient);
            })
            ->waitForLocation($patientsIndexPath, 20)
            ->on($indexPage)
            ->waitFor('@success-alert', 10)
            ->assertSeeIn('@success-alert', 'Paciente atualizado com sucesso.')
            ->waitFor('@grid-search-input', 10)
            ->clear('@grid-search-input')
            ->type('@grid-search-input', $updatedPatient['full_name'])
            ->pause(900)
            ->waitForText($updatedPatient['full_name'], 20)
            ->assertSee($updatedPatient['full_name'])
            ->assertSee($patientTarget['cpf']);
    });
});
