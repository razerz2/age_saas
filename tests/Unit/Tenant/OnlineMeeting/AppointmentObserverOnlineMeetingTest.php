<?php

use App\DTO\Tenant\OnlineMeetingResult;
use App\Jobs\Tenant\SendAppointmentNotificationsJob;
use App\Models\Tenant\Appointment;
use App\Observers\AppointmentObserver;
use App\Services\Tenant\AppleCalendarService;
use App\Services\Tenant\GoogleCalendarService;
use App\Services\Tenant\NotificationDispatcher;
use App\Services\Tenant\OnlineMeetings\OnlineMeetingManager;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

uses(TestCase::class);

it('observer created calls online meeting manager for online appointment', function () {
    Bus::fake();

    Mockery::mock('alias:App\\Models\\Platform\\Tenant')
        ->shouldReceive('current')
        ->andReturn((object) ['id' => 'tenant-1']);

    Mockery::mock('alias:App\\Models\\Tenant\\Form')
        ->shouldReceive('getFormForAppointment')
        ->andReturn(null);

    $googleCalendarService = Mockery::mock(GoogleCalendarService::class);
    $appleCalendarService = Mockery::mock(AppleCalendarService::class);
    $notificationDispatcher = Mockery::mock(NotificationDispatcher::class);
    $notificationDispatcher->shouldNotReceive('dispatchAppointment');

    $manager = Mockery::mock(OnlineMeetingManager::class);
    $manager->shouldReceive('shouldHandle')->once()->andReturn(true);
    $manager->shouldReceive('provisionFor')->once()->andReturn(OnlineMeetingResult::skipped());
    app()->instance(OnlineMeetingManager::class, $manager);

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-online-created';
    $appointment->origin = Appointment::ORIGIN_INTERNAL;
    $appointment->appointment_mode = 'online';
    $appointment->recurring_appointment_id = 'rec-1';
    $appointment->shouldReceive('load')->once()->andReturnSelf();

    $observer = new AppointmentObserver($googleCalendarService, $appleCalendarService, $notificationDispatcher);
    $observer->created($appointment);

    Bus::assertDispatched(SendAppointmentNotificationsJob::class);
});

it('observer created does not provision when appointment is presencial', function () {
    Bus::fake();

    Mockery::mock('alias:App\\Models\\Platform\\Tenant')
        ->shouldReceive('current')
        ->andReturn((object) ['id' => 'tenant-1']);

    Mockery::mock('alias:App\\Models\\Tenant\\Form')
        ->shouldReceive('getFormForAppointment')
        ->andReturn(null);

    $googleCalendarService = Mockery::mock(GoogleCalendarService::class);
    $appleCalendarService = Mockery::mock(AppleCalendarService::class);
    $notificationDispatcher = Mockery::mock(NotificationDispatcher::class);
    $notificationDispatcher->shouldNotReceive('dispatchAppointment');

    $manager = Mockery::mock(OnlineMeetingManager::class);
    $manager->shouldReceive('shouldHandle')->once()->andReturn(false);
    $manager->shouldNotReceive('provisionFor');
    app()->instance(OnlineMeetingManager::class, $manager);

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-offline-created';
    $appointment->origin = Appointment::ORIGIN_INTERNAL;
    $appointment->appointment_mode = 'presencial';
    $appointment->recurring_appointment_id = 'rec-2';
    $appointment->shouldReceive('load')->once()->andReturnSelf();

    $observer = new AppointmentObserver($googleCalendarService, $appleCalendarService, $notificationDispatcher);
    $observer->created($appointment);

    Bus::assertDispatched(SendAppointmentNotificationsJob::class);
});

it('observer updated provisions when status changes from pending_confirmation to scheduled', function () {
    Bus::fake();

    Mockery::mock('alias:App\\Models\\Platform\\Tenant')
        ->shouldReceive('current')
        ->andReturn((object) ['id' => 'tenant-1']);

    $googleCalendarService = Mockery::mock(GoogleCalendarService::class);
    $appleCalendarService = Mockery::mock(AppleCalendarService::class);
    $notificationDispatcher = Mockery::mock(NotificationDispatcher::class);

    $manager = Mockery::mock(OnlineMeetingManager::class);
    $manager->shouldReceive('shouldCancel')->once()->andReturn(false);
    $manager->shouldReceive('shouldHandle')->once()->andReturn(true);
    $manager->shouldReceive('shouldUpdate')->once()->andReturn(true);
    $manager->shouldReceive('provisionFor')->once()->andReturn(OnlineMeetingResult::skipped());
    app()->instance(OnlineMeetingManager::class, $manager);

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-status-update';
    $appointment->origin = Appointment::ORIGIN_INTERNAL;
    $appointment->appointment_mode = 'online';
    $appointment->status = 'scheduled';
    $appointment->recurring_appointment_id = 'rec-3';
    $appointment->shouldReceive('load')->once()->andReturnSelf();
    $appointment->shouldReceive('getChanges')->once()->andReturn(['status' => 'scheduled']);
    $appointment->shouldReceive('wasChanged')->with('status')->once()->andReturn(true);
    $appointment->shouldReceive('getOriginal')->with('status')->once()->andReturn('pending_confirmation');

    $observer = new AppointmentObserver($googleCalendarService, $appleCalendarService, $notificationDispatcher);
    $observer->updated($appointment);

    Bus::assertDispatched(SendAppointmentNotificationsJob::class);
});

it('observer updated calls updateFor when starts_at changes and meeting already exists', function () {
    Bus::fake();

    Mockery::mock('alias:App\\Models\\Platform\\Tenant')
        ->shouldReceive('current')
        ->andReturn((object) ['id' => 'tenant-1']);

    $googleCalendarService = Mockery::mock(GoogleCalendarService::class);
    $appleCalendarService = Mockery::mock(AppleCalendarService::class);
    $notificationDispatcher = Mockery::mock(NotificationDispatcher::class);

    $manager = Mockery::mock(OnlineMeetingManager::class);
    $manager->shouldReceive('shouldCancel')->once()->andReturn(false);
    $manager->shouldReceive('shouldHandle')->once()->andReturn(true);
    $manager->shouldReceive('shouldUpdate')->once()->andReturn(true);
    $manager->shouldReceive('updateFor')->once()->andReturn(OnlineMeetingResult::success());
    $manager->shouldNotReceive('provisionFor');
    app()->instance(OnlineMeetingManager::class, $manager);

    $instruction = new \App\Models\Tenant\OnlineAppointmentInstruction();
    $instruction->meeting_status = 'generated';
    $instruction->meeting_link = 'https://meet.google.com/existing';

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-time-update';
    $appointment->origin = Appointment::ORIGIN_INTERNAL;
    $appointment->appointment_mode = 'online';
    $appointment->status = 'scheduled';
    $appointment->recurring_appointment_id = 'rec-4';
    $appointment->setRelation('onlineInstructions', $instruction);
    $appointment->shouldReceive('load')->once()->andReturnSelf();
    $appointment->shouldReceive('getChanges')->once()->andReturn(['starts_at' => now()->addHour()->toDateTimeString()]);
    $appointment->shouldReceive('wasChanged')->with('status')->once()->andReturn(false);
    $appointment->shouldReceive('wasChanged')->with(['starts_at', 'ends_at', 'notes'])->once()->andReturn(true);
    $appointment->shouldReceive('loadMissing')->once()->with('onlineInstructions')->andReturnSelf();

    $observer = new AppointmentObserver($googleCalendarService, $appleCalendarService, $notificationDispatcher);
    $observer->updated($appointment);

    Bus::assertDispatched(SendAppointmentNotificationsJob::class);
});

it('observer updated cancels meeting when appointment status becomes canceled', function () {
    Bus::fake();

    Mockery::mock('alias:App\\Models\\Platform\\Tenant')
        ->shouldReceive('current')
        ->andReturn((object) ['id' => 'tenant-1']);

    $googleCalendarService = Mockery::mock(GoogleCalendarService::class);
    $appleCalendarService = Mockery::mock(AppleCalendarService::class);
    $notificationDispatcher = Mockery::mock(NotificationDispatcher::class);

    $manager = Mockery::mock(OnlineMeetingManager::class);
    $manager->shouldReceive('shouldCancel')->once()->andReturn(true);
    $manager->shouldReceive('cancelFor')->once()->andReturn(OnlineMeetingResult::success(status: 'cancelled'));
    app()->instance(OnlineMeetingManager::class, $manager);

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-cancel-update';
    $appointment->origin = Appointment::ORIGIN_INTERNAL;
    $appointment->appointment_mode = 'online';
    $appointment->status = 'canceled';
    $appointment->recurring_appointment_id = 'rec-5';
    $appointment->shouldReceive('load')->once()->andReturnSelf();
    $appointment->shouldReceive('getChanges')->once()->andReturn(['status' => 'canceled']);
    $appointment->shouldReceive('wasChanged')->with('status')->once()->andReturn(true);
    $appointment->shouldReceive('getOriginal')->with('status')->once()->andReturn('scheduled');

    $observer = new AppointmentObserver($googleCalendarService, $appleCalendarService, $notificationDispatcher);
    $observer->updated($appointment);

    Bus::assertDispatched(SendAppointmentNotificationsJob::class);
});

it('observer updated ignores irrelevant google_event_id-only changes', function () {
    Bus::fake();

    Mockery::mock('alias:App\\Models\\Platform\\Tenant')
        ->shouldReceive('current')
        ->andReturn((object) ['id' => 'tenant-1']);

    $googleCalendarService = Mockery::mock(GoogleCalendarService::class);
    $appleCalendarService = Mockery::mock(AppleCalendarService::class);
    $notificationDispatcher = Mockery::mock(NotificationDispatcher::class);

    $manager = Mockery::mock(OnlineMeetingManager::class);
    $manager->shouldNotReceive('shouldCancel');
    app()->instance(OnlineMeetingManager::class, $manager);

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-irrelevant-update';
    $appointment->origin = Appointment::ORIGIN_INTERNAL;
    $appointment->appointment_mode = 'online';
    $appointment->status = 'scheduled';
    $appointment->recurring_appointment_id = 'rec-6';
    $appointment->shouldReceive('load')->once()->andReturnSelf();
    $appointment->shouldReceive('getChanges')->once()->andReturn(['google_event_id' => 'abc']);
    $appointment->shouldReceive('wasChanged')->with('status')->once()->andReturn(false);
    $appointment->shouldReceive('wasChanged')->with(['starts_at', 'ends_at', 'notes'])->once()->andReturn(false);

    $observer = new AppointmentObserver($googleCalendarService, $appleCalendarService, $notificationDispatcher);
    $observer->updated($appointment);

    Bus::assertNotDispatched(SendAppointmentNotificationsJob::class);
});

it('observer updated swallows manager exceptions and does not propagate', function () {
    Bus::fake();

    Mockery::mock('alias:App\\Models\\Platform\\Tenant')
        ->shouldReceive('current')
        ->andReturn((object) ['id' => 'tenant-1']);

    $googleCalendarService = Mockery::mock(GoogleCalendarService::class);
    $appleCalendarService = Mockery::mock(AppleCalendarService::class);
    $notificationDispatcher = Mockery::mock(NotificationDispatcher::class);

    $manager = Mockery::mock(OnlineMeetingManager::class);
    $manager->shouldReceive('shouldCancel')->once()->andReturn(false);
    $manager->shouldReceive('shouldHandle')->once()->andReturn(true);
    $manager->shouldReceive('shouldUpdate')->once()->andReturn(true);
    $manager->shouldReceive('provisionFor')->once()->andThrow(new RuntimeException('manager failure'));
    app()->instance(OnlineMeetingManager::class, $manager);

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-manager-failure';
    $appointment->origin = Appointment::ORIGIN_INTERNAL;
    $appointment->appointment_mode = 'online';
    $appointment->status = 'scheduled';
    $appointment->recurring_appointment_id = 'rec-7';
    $appointment->shouldReceive('load')->once()->andReturnSelf();
    $appointment->shouldReceive('getChanges')->once()->andReturn(['status' => 'scheduled']);
    $appointment->shouldReceive('wasChanged')->with('status')->once()->andReturn(false);
    $appointment->shouldReceive('wasChanged')->with(['starts_at', 'ends_at', 'notes'])->once()->andReturn(false);

    $observer = new AppointmentObserver($googleCalendarService, $appleCalendarService, $notificationDispatcher);

    $observer->updated($appointment);

    expect(true)->toBeTrue();
});
