<?php

use App\Jobs\Tenant\SendAppointmentNotificationsJob;
use App\Models\Tenant\Appointment;
use App\Observers\AppointmentObserver;
use App\Services\Tenant\AppleCalendarService;
use App\Services\Tenant\GoogleCalendarService;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

uses(TestCase::class);

it('adds whatsapp suppression metadata for created appointments from whatsapp bot origin', function () {
    Bus::fake();

    Mockery::mock('alias:App\Models\Platform\Tenant')
        ->shouldReceive('current')
        ->andReturn((object) ['id' => 'tenant-1']);

    Mockery::mock('alias:App\Models\Tenant\Form')
        ->shouldReceive('getFormForAppointment')
        ->andReturn(null);

    $googleCalendarService = Mockery::mock(GoogleCalendarService::class);
    $appleCalendarService = Mockery::mock(AppleCalendarService::class);

    $observer = new AppointmentObserver($googleCalendarService, $appleCalendarService);

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-1';
    $appointment->origin = 'whatsapp_bot';
    $appointment->appointment_mode = 'presencial';
    $appointment->recurring_appointment_id = 'recurring-1';
    $appointment->shouldReceive('load')->once()->andReturnSelf();

    $observer->created($appointment);

    Bus::assertDispatched(SendAppointmentNotificationsJob::class, function (SendAppointmentNotificationsJob $job): bool {
        $reflection = new ReflectionClass($job);
        $metadataProperty = $reflection->getProperty('metadata');
        $metadataProperty->setAccessible(true);
        $metadata = (array) $metadataProperty->getValue($job);

        return in_array('whatsapp', (array) ($metadata['suppress_patient_channels'] ?? []), true)
            && (string) ($metadata['origin'] ?? '') === 'whatsapp_bot';
    });
});

it('keeps default notification channels for non-whatsapp-bot appointments', function () {
    Bus::fake();

    Mockery::mock('alias:App\Models\Platform\Tenant')
        ->shouldReceive('current')
        ->andReturn((object) ['id' => 'tenant-1']);

    Mockery::mock('alias:App\Models\Tenant\Form')
        ->shouldReceive('getFormForAppointment')
        ->andReturn(null);

    $googleCalendarService = Mockery::mock(GoogleCalendarService::class);
    $appleCalendarService = Mockery::mock(AppleCalendarService::class);

    $observer = new AppointmentObserver($googleCalendarService, $appleCalendarService);

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-2';
    $appointment->origin = 'portal';
    $appointment->appointment_mode = 'presencial';
    $appointment->recurring_appointment_id = 'recurring-2';
    $appointment->shouldReceive('load')->once()->andReturnSelf();

    $observer->created($appointment);

    Bus::assertDispatched(SendAppointmentNotificationsJob::class, function (SendAppointmentNotificationsJob $job): bool {
        $reflection = new ReflectionClass($job);
        $metadataProperty = $reflection->getProperty('metadata');
        $metadataProperty->setAccessible(true);
        $metadata = (array) $metadataProperty->getValue($job);

        return !array_key_exists('suppress_patient_channels', $metadata)
            && (string) ($metadata['origin'] ?? '') === 'portal';
    });
});
