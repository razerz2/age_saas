<?php

use Carbon\CarbonImmutable;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\Tenant\Attendances\AttendanceStartForm;
use Tests\Browser\Concerns\InteractsWithTenantAppointments;
use Tests\Browser\Pages\Tenant\Attendances\AttendanceSessionPage;
use Tests\Browser\Pages\Tenant\Attendances\AttendanceStartPage;

uses(InteractsWithTenantAppointments::class);

test('tenant admin can complete an in-service attendance appointment', function () {
    $seed = (string) floor(microtime(true) * 1000);
    $dependencies = $this->createControlledAppointmentDependencies($seed);
    $sessionDate = $this->nextAppointmentDateString(1);

    $appointment = $this->createControlledAppointmentTarget($seed, [
        'dependencies' => $dependencies,
        'starts_at' => CarbonImmutable::parse($sessionDate . ' 10:30:00', 'America/Campo_Grande'),
        'status' => 'in_service',
        'notes' => sprintf('Dusk attendance complete %s', $seed),
        'test_tag' => 'dusk_attendance_complete',
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
            ->waitFor($sessionPage->completeAppointmentSelector($appointment['id']), 20)
            ->click($sessionPage->completeAppointmentSelector($appointment['id']))
            ->waitFor('[dusk="global-confirm-dialog"]', 10)
            ->press('[dusk="global-confirm-dialog-confirm-button"]')
            ->waitForLocation($sessionPage->url(), 20)
            ->on($sessionPage)
            ->waitUntilMissing($sessionPage->queueItemSelector($appointment['id']), 20)
            ->assertMissing($sessionPage->queueItemSelector($appointment['id']));
    });

    expect($this->tenantAppointmentStatus($appointment['id']))->toBe('completed');
});
