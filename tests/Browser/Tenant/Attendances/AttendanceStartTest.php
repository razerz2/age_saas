<?php

use Carbon\CarbonImmutable;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\Tenant\Attendances\AttendanceStartForm;
use Tests\Browser\Concerns\InteractsWithTenantAppointments;
use Tests\Browser\Pages\Tenant\Attendances\AttendanceSessionPage;
use Tests\Browser\Pages\Tenant\Attendances\AttendanceStartPage;

uses(InteractsWithTenantAppointments::class);

test('tenant admin can start attendance session and load a controlled appointment', function () {
    $seed = (string) floor(microtime(true) * 1000);
    $dependencies = $this->createControlledAppointmentDependencies($seed);
    $sessionDate = $this->nextAppointmentDateString(1);

    $appointment = $this->createControlledAppointmentTarget($seed, [
        'dependencies' => $dependencies,
        'starts_at' => CarbonImmutable::parse($sessionDate . ' 09:30:00', 'America/Campo_Grande'),
        'status' => 'scheduled',
        'notes' => sprintf('Dusk attendance start %s', $seed),
        'test_tag' => 'dusk_attendance_start',
    ]);

    $this->browse(function (Browser $browser) use ($dependencies, $appointment, $sessionDate) {
        $startPage = new AttendanceStartPage();
        $sessionPage = new AttendanceSessionPage($sessionDate);
        $startForm = new AttendanceStartForm();

        $this->loginAsTenant($browser)
            ->visit($startPage->url())
            ->on($startPage)
            ->within($startForm, function (Browser $formBrowser) use ($startForm, $dependencies, $sessionDate) {
                $startForm->selectDoctor($formBrowser, $dependencies['doctor']['id']);
                $startForm->setDate($formBrowser, $sessionDate);
                $startForm->submit($formBrowser);
            })
            ->waitForLocation($sessionPage->url(), 20)
            ->on($sessionPage)
            ->waitFor($sessionPage->queueItemSelector($appointment['id']), 20)
            ->waitFor($sessionPage->openDetailsSelector($appointment['id']), 20)
            ->click($sessionPage->openDetailsSelector($appointment['id']))
            ->waitForTextIn('@appointment-details', $appointment['patient_name'], 20)
            ->assertPresent($sessionPage->queueItemSelector($appointment['id']))
            ->assertSeeIn('@appointment-details', $appointment['patient_name']);
    });

    expect($this->tenantAppointmentExists($appointment['id']))->toBeTrue();
});
