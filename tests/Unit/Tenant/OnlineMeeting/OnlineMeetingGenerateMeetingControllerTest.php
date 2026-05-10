<?php

use App\DTO\Tenant\OnlineMeetingResult;
use App\Http\Controllers\Tenant\OnlineAppointmentController;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\TenantSetting;
use App\Services\Tenant\NotificationDispatcher;
use App\Services\Tenant\OnlineMeetings\OnlineMeetingManager;
use App\Support\Tenant\OnlineMeeting;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

it('generateMeeting forbids presencial appointments', function () {
    Mockery::mock('alias:' . TenantSetting::class)
        ->shouldReceive('get')
        ->with('appointments.default_appointment_mode', 'user_choice')
        ->once()
        ->andReturn('user_choice');

    $controller = new OnlineAppointmentController(Mockery::mock(NotificationDispatcher::class));

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-offline';
    $appointment->appointment_mode = 'presencial';
    $appointment->shouldReceive('load')->once()->andReturnSelf();

    expect(fn () => $controller->generateMeeting(new Request(), 'tenant-slug', $appointment))
        ->toThrow(HttpException::class);
});

it('generateMeeting calls manager with force options for online appointments', function () {
    Mockery::mock('alias:' . TenantSetting::class)
        ->shouldReceive('get')
        ->with('appointments.default_appointment_mode', 'user_choice')
        ->once()
        ->andReturn('user_choice');

    $manager = Mockery::mock(OnlineMeetingManager::class);
    $manager->shouldReceive('provisionFor')
        ->once()
        ->withArgs(function ($appointment, array $options): bool {
            return $appointment instanceof Appointment
                && ($options['force'] ?? false) === true
                && ($options['ignore_auto_generate_disabled'] ?? false) === true;
        })
        ->andReturn(OnlineMeetingResult::success(provider: OnlineMeeting::PROVIDER_GOOGLE_MEET));

    app()->instance(OnlineMeetingManager::class, $manager);

    $controller = new OnlineAppointmentController(Mockery::mock(NotificationDispatcher::class));
    app('session')->start();

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-online-success';
    $appointment->appointment_mode = 'online';
    $appointment->shouldReceive('load')->once()->andReturnSelf();

    $response = $controller->generateMeeting(new Request(), 'tenant-slug', $appointment);

    expect($response->isRedirect())->toBeTrue();
    expect($response->getTargetUrl())->toContain('/workspace/tenant-slug/appointments/online/appointment-online-success');
    expect((string) session('success'))->toContain('Reunião online gerada com sucesso.');
});

it('generateMeeting handles manual_required result with redirect', function () {
    Mockery::mock('alias:' . TenantSetting::class)
        ->shouldReceive('get')
        ->with('appointments.default_appointment_mode', 'user_choice')
        ->once()
        ->andReturn('user_choice');

    $manager = Mockery::mock(OnlineMeetingManager::class);
    $manager->shouldReceive('provisionFor')
        ->once()
        ->andReturn(OnlineMeetingResult::manualRequired(provider: OnlineMeeting::PROVIDER_GOOGLE_MEET, errorMessage: 'manual')); 

    app()->instance(OnlineMeetingManager::class, $manager);

    $controller = new OnlineAppointmentController(Mockery::mock(NotificationDispatcher::class));
    app('session')->start();

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-online-manual';
    $appointment->appointment_mode = 'online';
    $appointment->shouldReceive('load')->once()->andReturnSelf();

    $response = $controller->generateMeeting(new Request(), 'tenant-slug', $appointment);

    expect($response->isRedirect())->toBeTrue();
    expect((string) session('warning'))->toContain('manual');
});

it('generateMeeting handles failed result with redirect', function () {
    Mockery::mock('alias:' . TenantSetting::class)
        ->shouldReceive('get')
        ->with('appointments.default_appointment_mode', 'user_choice')
        ->once()
        ->andReturn('user_choice');

    $manager = Mockery::mock(OnlineMeetingManager::class);
    $manager->shouldReceive('provisionFor')
        ->once()
        ->andReturn(OnlineMeetingResult::failed(provider: OnlineMeeting::PROVIDER_GOOGLE_MEET, errorMessage: 'failed')); 

    app()->instance(OnlineMeetingManager::class, $manager);

    $controller = new OnlineAppointmentController(Mockery::mock(NotificationDispatcher::class));
    app('session')->start();

    $appointment = Mockery::mock(Appointment::class)->makePartial();
    $appointment->id = 'appointment-online-failed';
    $appointment->appointment_mode = 'online';
    $appointment->shouldReceive('load')->once()->andReturnSelf();

    $response = $controller->generateMeeting(new Request(), 'tenant-slug', $appointment);

    expect($response->isRedirect())->toBeTrue();
    expect((string) session('error'))->toContain('failed');
});
