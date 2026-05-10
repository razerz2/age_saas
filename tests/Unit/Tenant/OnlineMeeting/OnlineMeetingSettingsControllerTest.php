<?php

use App\Http\Controllers\Tenant\SettingsController;
use App\Models\Tenant\TenantSetting;
use App\Support\Tenant\OnlineMeeting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

function validAppointmentsSettingsPayload(array $overrides = []): array
{
    return array_merge([
        'appointments_default_duration' => 30,
        'appointments_interval_between' => 5,
        'appointments_auto_confirm' => 1,
        'appointments_allow_cancellation' => 1,
        'appointments_cancellation_hours' => 2,
        'appointments_reminder_hours' => 24,
        'appointments_default_appointment_mode' => 'user_choice',
        'appointments_confirmation_enabled' => 1,
        'appointments_confirmation_ttl_minutes' => 30,
        'appointments_waitlist_enabled' => 1,
        'appointments_waitlist_offer_ttl_minutes' => 15,
        'appointments_waitlist_allow_when_confirmed' => 1,
        'appointments_waitlist_max_per_slot' => 5,
        'online_meetings_auto_generate_enabled' => 1,
        'online_meetings_default_provider' => 'google_meet',
        'online_meetings_generation_timing' => 'on_confirmed',
        'online_meetings_failure_policy' => 'keep_appointment_pending_meeting',
    ], $overrides);
}

it('settings update stores valid online meeting configuration', function () {
    Mockery::mock('alias:App\\Models\\Platform\\Tenant')
        ->shouldReceive('current')
        ->andReturn((object) ['subdomain' => 'tenant-slug']);

    $saved = [];

    $tenantSetting = Mockery::mock('alias:' . TenantSetting::class);
    $tenantSetting->shouldReceive('set')->andReturnUsing(function (string $key, $value) use (&$saved): void {
        $saved[$key] = $value;
    });
    $tenantSetting->shouldReceive('enable')->andReturnNull();
    $tenantSetting->shouldReceive('disable')->andReturnNull();

    $controller = new SettingsController();
    $request = Request::create('/workspace/tenant-slug/settings/appointments', 'POST', validAppointmentsSettingsPayload());

    $response = $controller->updateAppointments($request);

    expect($response->isRedirect())->toBeTrue();
    expect($saved['online_meetings.auto_generate_enabled'] ?? null)->toBe('true');
    expect($saved['online_meetings.default_provider'] ?? null)->toBe(OnlineMeeting::PROVIDER_GOOGLE_MEET);
    expect($saved['online_meetings.generation_timing'] ?? null)->toBe(OnlineMeeting::GENERATION_ON_CONFIRMED);
    expect($saved['online_meetings.failure_policy'] ?? null)->toBe(OnlineMeeting::FAILURE_KEEP_APPOINTMENT_PENDING_MEETING);
});

it('settings update rejects invalid provider', function () {
    $controller = new SettingsController();
    $request = Request::create(
        '/workspace/tenant-slug/settings/appointments',
        'POST',
        validAppointmentsSettingsPayload(['online_meetings_default_provider' => 'zoom'])
    );

    expect(fn () => $controller->updateAppointments($request))
        ->toThrow(ValidationException::class);
});

it('settings update rejects block_online_appointment failure policy for now', function () {
    $controller = new SettingsController();
    $request = Request::create(
        '/workspace/tenant-slug/settings/appointments',
        'POST',
        validAppointmentsSettingsPayload(['online_meetings_failure_policy' => OnlineMeeting::FAILURE_BLOCK_ONLINE_APPOINTMENT])
    );

    expect(fn () => $controller->updateAppointments($request))
        ->toThrow(ValidationException::class);
});
