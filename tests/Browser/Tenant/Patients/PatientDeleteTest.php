<?php

use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\InteractsWithTenantPatients;
use Tests\Browser\Pages\Tenant\Patients\PatientIndexPage;

uses(InteractsWithTenantPatients::class);

test('tenant admin can delete a controlled patient through patients module', function () {
    $context = $this->tenantTestContext();
    $seed = (string) floor(microtime(true) * 1000);
    $patientTarget = $this->createControlledPatientTarget($seed);
    $patientsIndexPath = sprintf('/workspace/%s/patients', $context->slug);

    $this->browse(function (Browser $browser) use ($patientTarget, $patientsIndexPath) {
        $indexPage = new PatientIndexPage();

        $this->loginAsTenant($browser)
            ->visit($indexPage->url())
            ->on($indexPage)
            ->waitFor('@grid-search-input', 10)
            ->type('@grid-search-input', $patientTarget['full_name'])
            ->pause(900)
            ->waitForText($patientTarget['full_name'], 20)
            ->waitFor($indexPage->deleteActionSelector($patientTarget['id']), 20)
            ->click($indexPage->deleteActionSelector($patientTarget['id']))
            ->waitFor('@confirm-dialog', 10)
            ->assertSeeIn('@confirm-dialog-title', 'Excluir paciente')
            ->press('@confirm-dialog-confirm-button')
            ->waitFor('@success-alert', 20)
            ->assertPathIs($patientsIndexPath)
            ->assertSeeIn('@success-alert', 'Paciente removido.')
            ->waitFor('@grid-search-input', 10)
            ->clear('@grid-search-input')
            ->type('@grid-search-input', $patientTarget['full_name'])
            ->pause(900)
            ->assertMissing($indexPage->deleteActionSelector($patientTarget['id']))
            ->assertDontSee($patientTarget['cpf']);
    });

    expect($this->tenantPatientExists($patientTarget['id']))->toBeFalse();
});