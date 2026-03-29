<?php

use Laravel\Dusk\Browser;
use Tests\Browser\Components\Tenant\Patients\PatientForm;
use Tests\Browser\Pages\Tenant\Patients\PatientFormPage;
use Tests\Browser\Pages\Tenant\Patients\PatientIndexPage;

test('tenant admin can create a patient through patients module', function () {
    $context = $this->tenantTestContext();
    $seed = (string) floor(microtime(true) * 1000);
    $suffix = substr($seed, -6);

    $patient = [
        'full_name' => "Paciente Dusk {$suffix}",
        'cpf' => substr(str_pad($seed, 11, '0', STR_PAD_LEFT), -11),
        'street' => "Rua Dusk {$suffix}",
        'number' => '123',
        'neighborhood' => "Centro {$suffix}",
        'postal_code' => '78050000',
    ];

    $patientsIndexPath = sprintf('/workspace/%s/patients', $context->slug);

    $this->browse(function (Browser $browser) use ($patient, $patientsIndexPath) {
        $indexPage = new PatientIndexPage();
        $formPage = new PatientFormPage();
        $patientForm = new PatientForm();

        $this->loginAsTenant($browser)
            ->visit($indexPage->url())
            ->on($indexPage)
            ->click('@new-patient-button')
            ->on($formPage)
            ->within($patientForm, function (Browser $formBrowser) use ($patientForm, $patient) {
                $patientForm->fillAndSubmit($formBrowser, $patient);
            })
            ->waitForLocation($patientsIndexPath, 20)
            ->on($indexPage)
            ->waitFor('@success-alert', 10)
            ->assertSeeIn('@success-alert', 'Paciente cadastrado com sucesso.')
            ->waitFor('@grid-search-input', 10)
            ->type('@grid-search-input', $patient['full_name'])
            ->pause(900)
            ->waitForText($patient['full_name'], 20)
            ->assertSee($patient['full_name'])
            ->assertSee($patient['cpf']);
    });
});
