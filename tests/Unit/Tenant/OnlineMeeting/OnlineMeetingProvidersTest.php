<?php

use App\Models\Tenant\Appointment;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\GoogleCalendarToken;
use App\Models\Tenant\OnlineAppointmentInstruction;
use App\Models\Tenant\Patient;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\User;
use App\Services\Tenant\GoogleCalendarService;
use App\Services\Tenant\OnlineMeetings\GoogleMeetProvider;
use App\Services\Tenant\OnlineMeetings\ManualMeetingProvider;
use App\Services\Tenant\OnlineMeetings\OnlineMeetingManager;
use App\Support\Tenant\OnlineMeeting;
use Tests\TestCase;

uses(TestCase::class);

it('google meet provider returns skipped for non-online appointments', function () {
    $provider = new GoogleMeetProvider(Mockery::mock(GoogleCalendarService::class));

    $appointment = new Appointment();
    $appointment->id = 'appointment-offline';
    $appointment->appointment_mode = 'presencial';

    $result = $provider->createForAppointment($appointment);

    expect($result->success)->toBeTrue();
    expect($result->status)->toBe(OnlineMeeting::STATUS_SKIPPED);
    expect($result->provider)->toBe(OnlineMeeting::PROVIDER_GOOGLE_MEET);
});

it('google meet provider returns manual required when doctor has no google token', function () {
    $provider = new GoogleMeetProvider(Mockery::mock(GoogleCalendarService::class));

    $appointment = new Appointment();
    $appointment->id = 'appointment-no-token';
    $appointment->appointment_mode = 'online';

    $doctor = new Doctor();
    $doctor->id = 'doctor-1';
    $doctor->setRelation('googleCalendarToken', null);
    $doctor->setRelation('user', new User());

    $calendar = new Calendar();
    $calendar->setRelation('doctor', $doctor);

    $appointment->setRelation('calendar', $calendar);
    $appointment->setRelation('patient', new Patient());
    $appointment->setRelation('type', null);
    $appointment->setRelation('specialty', null);
    $appointment->setRelation('onlineInstructions', null);

    $result = $provider->createForAppointment($appointment);

    expect($result->success)->toBeFalse();
    expect($result->status)->toBe(OnlineMeeting::STATUS_MANUAL_REQUIRED);
    expect($result->provider)->toBe(OnlineMeeting::PROVIDER_GOOGLE_MEET);
    expect($appointment->onlineInstructions)->not->toBeNull();
    expect($appointment->onlineInstructions->meeting_provider)->toBe(OnlineMeeting::PROVIDER_GOOGLE_MEET);
    expect($appointment->onlineInstructions->meeting_status)->toBe(OnlineMeeting::STATUS_MANUAL_REQUIRED);
    expect($appointment->onlineInstructions->meeting_app)->toBe('Google Meet');
});

it('google meet provider stores generated instruction fields using mocked calendar service', function () {
    $service = Mockery::mock(GoogleCalendarService::class);
    $service->shouldReceive('createEventWithMeet')
        ->once()
        ->andReturn([
            'event_id' => 'google-event-123',
            'meeting_link' => 'https://meet.google.com/test-link',
            'conference_id' => 'aaa-bbbb-ccc',
            'html_link' => 'https://calendar.google.com/event?eid=123',
            'raw' => [
                'id' => 'google-event-123',
                'conference_id' => 'aaa-bbbb-ccc',
            ],
        ]);

    $provider = new GoogleMeetProvider($service);

    $appointment = new Appointment();
    $appointment->id = 'appointment-generated';
    $appointment->appointment_mode = 'online';

    $token = new GoogleCalendarToken();
    $token->id = 'token-1';

    $doctor = new Doctor();
    $doctor->id = 'doctor-2';
    $doctor->setRelation('googleCalendarToken', $token);
    $doctor->setRelation('user', new User());

    $calendar = new Calendar();
    $calendar->setRelation('doctor', $doctor);

    $appointment->setRelation('calendar', $calendar);
    $appointment->setRelation('patient', new Patient());
    $appointment->setRelation('type', null);
    $appointment->setRelation('specialty', null);
    $appointment->setRelation('onlineInstructions', null);

    $result = $provider->createForAppointment($appointment);

    expect($result->success)->toBeTrue();
    expect($result->status)->toBe(OnlineMeeting::STATUS_GENERATED);
    expect($result->provider)->toBe(OnlineMeeting::PROVIDER_GOOGLE_MEET);
    expect($result->meetingLink)->toBe('https://meet.google.com/test-link');
    expect($appointment->onlineInstructions)->not->toBeNull();
    expect($appointment->onlineInstructions->meeting_status)->toBe(OnlineMeeting::STATUS_GENERATED);
    expect($appointment->onlineInstructions->meeting_provider)->toBe(OnlineMeeting::PROVIDER_GOOGLE_MEET);
    expect($appointment->onlineInstructions->meeting_link)->toBe('https://meet.google.com/test-link');
    expect($appointment->onlineInstructions->external_event_id)->toBe('google-event-123');
    expect($appointment->onlineInstructions->external_meeting_id)->toBe('aaa-bbbb-ccc');
    expect($appointment->onlineInstructions->meeting_app)->toBe('Google Meet');
    expect($appointment->onlineInstructions->meeting_generated_at)->not->toBeNull();
});

it('google meet provider stores failed status when calendar service throws', function () {
    $service = Mockery::mock(GoogleCalendarService::class);
    $service->shouldReceive('createEventWithMeet')
        ->once()
        ->andThrow(new RuntimeException('calendar failure'));

    $provider = new GoogleMeetProvider($service);

    $appointment = new Appointment();
    $appointment->id = 'appointment-failed';
    $appointment->appointment_mode = 'online';

    $token = new GoogleCalendarToken();
    $token->id = 'token-failed';

    $doctor = new Doctor();
    $doctor->id = 'doctor-failed';
    $doctor->setRelation('googleCalendarToken', $token);
    $doctor->setRelation('user', new User());

    $calendar = new Calendar();
    $calendar->setRelation('doctor', $doctor);

    $appointment->setRelation('calendar', $calendar);
    $appointment->setRelation('patient', new Patient());
    $appointment->setRelation('type', null);
    $appointment->setRelation('specialty', null);
    $appointment->setRelation('onlineInstructions', null);

    $result = $provider->createForAppointment($appointment);

    expect($result->success)->toBeFalse();
    expect($result->status)->toBe(OnlineMeeting::STATUS_FAILED);
    expect($appointment->onlineInstructions)->not->toBeNull();
    expect($appointment->onlineInstructions->meeting_status)->toBe(OnlineMeeting::STATUS_FAILED);
    expect($appointment->onlineInstructions->meeting_generation_error)->not->toBeNull();
});

it('google meet provider is idempotent when instruction is already generated with link', function () {
    $service = Mockery::mock(GoogleCalendarService::class);
    $service->shouldNotReceive('createEventWithMeet');

    $provider = new GoogleMeetProvider($service);

    $appointment = new Appointment();
    $appointment->id = 'appointment-idempotent';
    $appointment->appointment_mode = 'online';
    $appointment->google_event_id = 'google-event-existing';

    $token = new GoogleCalendarToken();
    $token->id = 'token-existing';

    $doctor = new Doctor();
    $doctor->id = 'doctor-existing';
    $doctor->setRelation('googleCalendarToken', $token);
    $doctor->setRelation('user', new User());

    $calendar = new Calendar();
    $calendar->setRelation('doctor', $doctor);

    $instruction = new OnlineAppointmentInstruction();
    $instruction->meeting_status = OnlineMeeting::STATUS_GENERATED;
    $instruction->meeting_provider = OnlineMeeting::PROVIDER_GOOGLE_MEET;
    $instruction->meeting_link = 'https://meet.google.com/existing-link';
    $instruction->external_event_id = 'google-event-existing';
    $instruction->external_meeting_id = 'abc-defg-hij';

    $appointment->setRelation('calendar', $calendar);
    $appointment->setRelation('patient', new Patient());
    $appointment->setRelation('type', null);
    $appointment->setRelation('specialty', null);
    $appointment->setRelation('onlineInstructions', $instruction);

    $result = $provider->createForAppointment($appointment);

    expect($result->success)->toBeTrue();
    expect($result->status)->toBe(OnlineMeeting::STATUS_GENERATED);
    expect($result->meetingLink)->toBe('https://meet.google.com/existing-link');
});

it('google meet provider removes sensitive token keys from meeting meta', function () {
    $service = Mockery::mock(GoogleCalendarService::class);
    $service->shouldReceive('createEventWithMeet')
        ->once()
        ->andReturn([
            'event_id' => 'google-event-raw',
            'meeting_link' => 'https://meet.google.com/raw-link',
            'conference_id' => 'raw-123',
            'html_link' => 'https://calendar.google.com/event?eid=raw',
            'raw' => [
                'id' => 'google-event-raw',
                'access_token' => 'secret-token',
                'nested' => [
                    'refresh_token' => 'secret-refresh',
                    'safe' => 'ok',
                ],
            ],
        ]);

    $provider = new GoogleMeetProvider($service);

    $appointment = new Appointment();
    $appointment->id = 'appointment-raw';
    $appointment->appointment_mode = 'online';

    $token = new GoogleCalendarToken();
    $token->id = 'token-raw';

    $doctor = new Doctor();
    $doctor->id = 'doctor-raw';
    $doctor->setRelation('googleCalendarToken', $token);
    $doctor->setRelation('user', new User());

    $calendar = new Calendar();
    $calendar->setRelation('doctor', $doctor);

    $appointment->setRelation('calendar', $calendar);
    $appointment->setRelation('patient', new Patient());
    $appointment->setRelation('type', null);
    $appointment->setRelation('specialty', null);
    $appointment->setRelation('onlineInstructions', null);

    $provider->createForAppointment($appointment);

    $meta = (array) $appointment->onlineInstructions->meeting_meta;
    expect(array_key_exists('access_token', $meta))->toBeFalse();
    expect(array_key_exists('refresh_token', (array) ($meta['nested'] ?? [])))->toBeFalse();
    expect(($meta['nested']['safe'] ?? null))->toBe('ok');
});

it('online meeting manager returns skipped when auto generation is disabled', function () {
    $tenantSetting = Mockery::mock('alias:' . TenantSetting::class);
    $tenantSetting->shouldReceive('get')
        ->with('online_meetings.auto_generate_enabled', 'false')
        ->once()
        ->andReturn('false');
    $tenantSetting->shouldReceive('get')
        ->with('online_meetings.default_provider', OnlineMeeting::PROVIDER_GOOGLE_MEET)
        ->once()
        ->andReturn(OnlineMeeting::PROVIDER_GOOGLE_MEET);

    $manager = new OnlineMeetingManager(
        manualMeetingProvider: new ManualMeetingProvider(),
        googleMeetProvider: Mockery::mock(GoogleMeetProvider::class)
    );

    $appointment = new Appointment();
    $appointment->id = 'appointment-manager';
    $appointment->appointment_mode = 'online';

    $result = $manager->provisionFor($appointment);

    expect($result->success)->toBeTrue();
    expect($result->status)->toBe(OnlineMeeting::STATUS_SKIPPED);
});

it('manual meeting provider create still returns manual required', function () {
    $provider = new ManualMeetingProvider();
    $appointment = new Appointment();
    $appointment->id = 'appointment-manual';

    $result = $provider->createForAppointment($appointment);

    expect($result->success)->toBeFalse();
    expect($result->status)->toBe(OnlineMeeting::STATUS_MANUAL_REQUIRED);
    expect($result->provider)->toBe(OnlineMeeting::PROVIDER_MANUAL);
});
