<?php

use App\DTO\Tenant\OnlineMeetingResult;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\OnlineAppointmentInstruction;
use App\Models\Tenant\TenantSetting;
use App\Services\Tenant\OnlineMeetings\GoogleMeetProvider;
use App\Services\Tenant\OnlineMeetings\ManualMeetingProvider;
use App\Services\Tenant\OnlineMeetings\OnlineMeetingManager;
use App\Support\Tenant\OnlineMeeting;
use Tests\TestCase;

uses(TestCase::class);

function makeOnlineAppointment(string $status = 'scheduled'): Appointment
{
    $appointment = new Appointment();
    $appointment->id = 'apt-online-1';
    $appointment->appointment_mode = 'online';
    $appointment->status = $status;

    return $appointment;
}

it('shouldHandle returns false for presencial', function () {
    $appointment = new Appointment();
    $appointment->id = 'apt-1';
    $appointment->appointment_mode = 'presencial';

    $manager = new OnlineMeetingManager();

    expect($manager->shouldHandle($appointment))->toBeFalse();
});

it('shouldHandle returns true for online', function () {
    $appointment = makeOnlineAppointment();
    $manager = new OnlineMeetingManager();

    expect($manager->shouldHandle($appointment))->toBeTrue();
});

it('provisionFor returns skipped when auto_generate_enabled is false', function () {
    $tenantSetting = Mockery::mock('alias:' . TenantSetting::class);
    $tenantSetting->shouldReceive('get')->with('online_meetings.auto_generate_enabled', 'false')->once()->andReturn('false');
    $tenantSetting->shouldReceive('get')->with('online_meetings.default_provider', OnlineMeeting::PROVIDER_GOOGLE_MEET)->once()->andReturn(OnlineMeeting::PROVIDER_MANUAL);

    $manual = Mockery::mock(ManualMeetingProvider::class);
    $manual->shouldNotReceive('createForAppointment');

    $manager = new OnlineMeetingManager($manual, Mockery::mock(GoogleMeetProvider::class));
    $result = $manager->provisionFor(makeOnlineAppointment('scheduled'));

    expect($result->status)->toBe(OnlineMeeting::STATUS_SKIPPED);
});

it('provisionFor returns skipped when timing on_confirmed and status pending_confirmation', function () {
    $tenantSetting = Mockery::mock('alias:' . TenantSetting::class);
    $tenantSetting->shouldReceive('get')->with('online_meetings.auto_generate_enabled', 'false')->once()->andReturn('true');
    $tenantSetting->shouldReceive('get')->with('online_meetings.generation_timing', OnlineMeeting::GENERATION_ON_CONFIRMED)->once()->andReturn(OnlineMeeting::GENERATION_ON_CONFIRMED);
    $tenantSetting->shouldReceive('get')->with('online_meetings.default_provider', OnlineMeeting::PROVIDER_GOOGLE_MEET)->once()->andReturn(OnlineMeeting::PROVIDER_MANUAL);

    $manual = Mockery::mock(ManualMeetingProvider::class);
    $manual->shouldNotReceive('createForAppointment');

    $manager = new OnlineMeetingManager($manual, Mockery::mock(GoogleMeetProvider::class));
    $result = $manager->provisionFor(makeOnlineAppointment('pending_confirmation'));

    expect($result->status)->toBe(OnlineMeeting::STATUS_SKIPPED);
});

it('provisionFor allows on_confirmed when status is scheduled', function () {
    $tenantSetting = Mockery::mock('alias:' . TenantSetting::class);
    $tenantSetting->shouldReceive('get')->with('online_meetings.auto_generate_enabled', 'false')->once()->andReturn('true');
    $tenantSetting->shouldReceive('get')->with('online_meetings.generation_timing', OnlineMeeting::GENERATION_ON_CONFIRMED)->once()->andReturn(OnlineMeeting::GENERATION_ON_CONFIRMED);
    $tenantSetting->shouldReceive('get')->with('online_meetings.default_provider', OnlineMeeting::PROVIDER_GOOGLE_MEET)->once()->andReturn(OnlineMeeting::PROVIDER_MANUAL);

    $manual = Mockery::mock(ManualMeetingProvider::class);
    $manual->shouldReceive('createForAppointment')->once()->andReturn(
        OnlineMeetingResult::manualRequired(provider: OnlineMeeting::PROVIDER_MANUAL, errorMessage: 'manual')
    );

    $manager = new OnlineMeetingManager($manual, Mockery::mock(GoogleMeetProvider::class));
    $result = $manager->provisionFor(makeOnlineAppointment('scheduled'));

    expect($result->status)->toBe(OnlineMeeting::STATUS_MANUAL_REQUIRED);
});

it('provisionFor allows on_created when status is pending_confirmation', function () {
    $tenantSetting = Mockery::mock('alias:' . TenantSetting::class);
    $tenantSetting->shouldReceive('get')->with('online_meetings.auto_generate_enabled', 'false')->once()->andReturn('true');
    $tenantSetting->shouldReceive('get')->with('online_meetings.generation_timing', OnlineMeeting::GENERATION_ON_CONFIRMED)->once()->andReturn(OnlineMeeting::GENERATION_ON_CREATED);
    $tenantSetting->shouldReceive('get')->with('online_meetings.default_provider', OnlineMeeting::PROVIDER_GOOGLE_MEET)->once()->andReturn(OnlineMeeting::PROVIDER_MANUAL);

    $manual = Mockery::mock(ManualMeetingProvider::class);
    $manual->shouldReceive('createForAppointment')->once()->andReturn(
        OnlineMeetingResult::manualRequired(provider: OnlineMeeting::PROVIDER_MANUAL, errorMessage: 'manual')
    );

    $manager = new OnlineMeetingManager($manual, Mockery::mock(GoogleMeetProvider::class));
    $result = $manager->provisionFor(makeOnlineAppointment('pending_confirmation'));

    expect($result->status)->toBe(OnlineMeeting::STATUS_MANUAL_REQUIRED);
});

it('provisionFor with force true ignores timing restriction', function () {
    $tenantSetting = Mockery::mock('alias:' . TenantSetting::class);
    $tenantSetting->shouldReceive('get')->with('online_meetings.default_provider', OnlineMeeting::PROVIDER_GOOGLE_MEET)->once()->andReturn(OnlineMeeting::PROVIDER_MANUAL);

    $manual = Mockery::mock(ManualMeetingProvider::class);
    $manual->shouldReceive('createForAppointment')->once()->andReturn(
        OnlineMeetingResult::manualRequired(provider: OnlineMeeting::PROVIDER_MANUAL, errorMessage: 'manual')
    );

    $manager = new OnlineMeetingManager($manual, Mockery::mock(GoogleMeetProvider::class));
    $result = $manager->provisionFor(makeOnlineAppointment('pending_confirmation'), ['force' => true]);

    expect($result->status)->toBe(OnlineMeeting::STATUS_MANUAL_REQUIRED);
});

it('provisionFor with ignore_auto_generate_disabled allows manual trigger flow', function () {
    $tenantSetting = Mockery::mock('alias:' . TenantSetting::class);
    $tenantSetting->shouldReceive('get')->with('online_meetings.generation_timing', OnlineMeeting::GENERATION_ON_CONFIRMED)->once()->andReturn(OnlineMeeting::GENERATION_ON_CONFIRMED);
    $tenantSetting->shouldReceive('get')->with('online_meetings.default_provider', OnlineMeeting::PROVIDER_GOOGLE_MEET)->once()->andReturn(OnlineMeeting::PROVIDER_MANUAL);

    $manual = Mockery::mock(ManualMeetingProvider::class);
    $manual->shouldReceive('createForAppointment')->once()->andReturn(
        OnlineMeetingResult::manualRequired(provider: OnlineMeeting::PROVIDER_MANUAL, errorMessage: 'manual')
    );

    $manager = new OnlineMeetingManager($manual, Mockery::mock(GoogleMeetProvider::class));
    $result = $manager->provisionFor(makeOnlineAppointment('scheduled'), [
        'ignore_auto_generate_disabled' => true,
    ]);

    expect($result->status)->toBe(OnlineMeeting::STATUS_MANUAL_REQUIRED);
});

it('shouldUpdate returns true for relevant fields', function () {
    $manager = new OnlineMeetingManager();
    $appointment = makeOnlineAppointment('scheduled');

    expect($manager->shouldUpdate($appointment, ['starts_at']))->toBeTrue();
    expect($manager->shouldUpdate($appointment, ['ends_at']))->toBeTrue();
    expect($manager->shouldUpdate($appointment, ['calendar_id']))->toBeTrue();
    expect($manager->shouldUpdate($appointment, ['appointment_mode']))->toBeTrue();
});

it('shouldUpdate returns false for irrelevant fields', function () {
    $manager = new OnlineMeetingManager();
    $appointment = makeOnlineAppointment('scheduled');

    expect($manager->shouldUpdate($appointment, ['notes']))->toBeFalse();
    expect($manager->shouldUpdate($appointment, ['updated_at']))->toBeFalse();
});

it('shouldCancel returns true for canceled cancelled and expired statuses', function () {
    $manager = new OnlineMeetingManager();

    $canceled = makeOnlineAppointment('canceled');
    $cancelled = makeOnlineAppointment('cancelled');
    $expired = makeOnlineAppointment('expired');

    expect($manager->shouldCancel($canceled, ['status']))->toBeTrue();
    expect($manager->shouldCancel($cancelled, ['status']))->toBeTrue();
    expect($manager->shouldCancel($expired, ['status']))->toBeTrue();
});

it('shouldCancel returns true when appointment_mode changes from online to presencial', function () {
    $manager = new OnlineMeetingManager();
    $appointment = makeOnlineAppointment('scheduled');
    $appointment->appointment_mode = 'presencial';

    expect($manager->shouldCancel($appointment, ['appointment_mode']))->toBeTrue();
});

it('invalid provider falls back to manual in provisionFor', function () {
    $tenantSetting = Mockery::mock('alias:' . TenantSetting::class);
    $tenantSetting->shouldReceive('get')->with('online_meetings.auto_generate_enabled', 'false')->once()->andReturn('true');
    $tenantSetting->shouldReceive('get')->with('online_meetings.generation_timing', OnlineMeeting::GENERATION_ON_CONFIRMED)->once()->andReturn(OnlineMeeting::GENERATION_ON_CREATED);

    $manual = Mockery::mock(ManualMeetingProvider::class);
    $manual->shouldReceive('createForAppointment')->once()->andReturn(
        OnlineMeetingResult::manualRequired(provider: OnlineMeeting::PROVIDER_MANUAL, errorMessage: 'manual')
    );

    $manager = new OnlineMeetingManager($manual, Mockery::mock(GoogleMeetProvider::class));
    $result = $manager->provisionFor(makeOnlineAppointment('scheduled'), ['provider' => 'invalid-provider']);

    expect($result->provider)->toBe(OnlineMeeting::PROVIDER_MANUAL);
});

it('updateFor calls provisionFor when there is no generated meeting', function () {
    $tenantSetting = Mockery::mock('alias:' . TenantSetting::class);
    $tenantSetting->shouldReceive('get')->with('online_meetings.auto_generate_enabled', 'false')->once()->andReturn('true');
    $tenantSetting->shouldReceive('get')->with('online_meetings.generation_timing', OnlineMeeting::GENERATION_ON_CONFIRMED)->once()->andReturn(OnlineMeeting::GENERATION_ON_CREATED);
    $tenantSetting->shouldReceive('get')->with('online_meetings.default_provider', OnlineMeeting::PROVIDER_GOOGLE_MEET)->once()->andReturn(OnlineMeeting::PROVIDER_MANUAL);

    $manual = Mockery::mock(ManualMeetingProvider::class);
    $manual->shouldReceive('createForAppointment')->once()->andReturn(
        OnlineMeetingResult::manualRequired(provider: OnlineMeeting::PROVIDER_MANUAL, errorMessage: 'manual')
    );
    $manual->shouldNotReceive('updateForAppointment');

    $appointment = makeOnlineAppointment('scheduled');
    $appointment->setRelation('onlineInstructions', null);

    $manager = new OnlineMeetingManager($manual, Mockery::mock(GoogleMeetProvider::class));
    $result = $manager->updateFor($appointment);

    expect($result->status)->toBe(OnlineMeeting::STATUS_MANUAL_REQUIRED);
});

it('cancelFor returns cancelled with manual provider without external api', function () {
    $manual = Mockery::mock(ManualMeetingProvider::class);
    $manual->shouldReceive('cancelForAppointment')->once()->andReturn(
        OnlineMeetingResult::success(provider: OnlineMeeting::PROVIDER_MANUAL, status: OnlineMeeting::STATUS_CANCELLED)
    );

    $instruction = new OnlineAppointmentInstruction();
    $instruction->meeting_status = OnlineMeeting::STATUS_GENERATED;
    $instruction->meeting_provider = OnlineMeeting::PROVIDER_MANUAL;
    $instruction->meeting_link = 'https://meet.test/local';

    $appointment = makeOnlineAppointment('scheduled');
    $appointment->setRelation('onlineInstructions', $instruction);

    $manager = new OnlineMeetingManager($manual, Mockery::mock(GoogleMeetProvider::class));
    $result = $manager->cancelFor($appointment);

    expect($result->status)->toBe(OnlineMeeting::STATUS_CANCELLED);
    expect($appointment->onlineInstructions->meeting_status)->toBe(OnlineMeeting::STATUS_CANCELLED);
});
