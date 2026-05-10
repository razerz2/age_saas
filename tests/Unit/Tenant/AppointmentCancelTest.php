<?php

use App\Http\Controllers\Tenant\AppointmentController;
use App\Models\Tenant\Appointment;
use App\Services\Tenant\AppleCalendarService;
use App\Services\Tenant\GoogleCalendarService;
use App\Services\Tenant\NotificationDispatcher;
use App\Services\Tenant\WaitlistService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'database.connections.tenant' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ],
    ]);

    DB::purge('tenant');
    DB::reconnect('tenant');

    Schema::connection('tenant')->dropIfExists('appointments');
    Schema::connection('tenant')->create('appointments', function (Blueprint $table): void {
        $table->uuid('id')->primary();
        $table->uuid('calendar_id')->nullable();
        $table->uuid('doctor_id');
        $table->uuid('patient_id')->nullable();
        $table->timestamp('starts_at')->nullable();
        $table->timestamp('ends_at')->nullable();
        $table->string('status')->default('scheduled');
        $table->string('google_event_id')->nullable();
        $table->string('apple_event_id')->nullable();
        $table->dateTime('canceled_at')->nullable();
        $table->dateTime('expired_at')->nullable();
        $table->text('cancellation_reason')->nullable();
        $table->dateTime('confirmation_expires_at')->nullable();
        $table->timestamps();
    });

    $this->originalAppointmentEventDispatcher = Appointment::getEventDispatcher();
    Appointment::unsetEventDispatcher();
});

afterEach(function () {
    Appointment::setEventDispatcher($this->originalAppointmentEventDispatcher);
});

function bindCancelDependencies(bool $expectGoogleDelete, bool $expectAppleDelete, bool $expectDispatcher, bool $expectWaitlist): void
{
    $google = Mockery::mock(GoogleCalendarService::class);
    $googleExpectation = $google->shouldReceive('deleteEvent');
    if ($expectGoogleDelete) {
        $googleExpectation->once()->andReturnTrue();
    } else {
        $googleExpectation->never();
    }

    $apple = Mockery::mock(AppleCalendarService::class);
    $appleExpectation = $apple->shouldReceive('deleteEvent');
    if ($expectAppleDelete) {
        $appleExpectation->once()->andReturnTrue();
    } else {
        $appleExpectation->never();
    }

    $dispatcher = Mockery::mock(NotificationDispatcher::class);
    $dispatcherExpectation = $dispatcher->shouldReceive('dispatchAppointment');
    if ($expectDispatcher) {
        $dispatcherExpectation->once()->withArgs(function (Appointment $appointment, string $template, array $metadata): bool {
            return $template === 'appointment.canceled'
                && ($metadata['event'] ?? null) === 'appointment_canceled'
                && Str::isUuid((string) $appointment->id);
        });
    } else {
        $dispatcherExpectation->never();
    }

    $waitlist = Mockery::mock(WaitlistService::class);
    $waitlistExpectation = $waitlist->shouldReceive('onSlotReleased');
    if ($expectWaitlist) {
        $waitlistExpectation->once()->andReturnNull();
    } else {
        $waitlistExpectation->never();
    }

    app()->instance(GoogleCalendarService::class, $google);
    app()->instance(AppleCalendarService::class, $apple);
    app()->instance(NotificationDispatcher::class, $dispatcher);
    app()->instance(WaitlistService::class, $waitlist);
}

function makeAppointment(array $overrides = []): Appointment
{
    $now = now();

    return Appointment::query()->create(array_merge([
        'id' => (string) Str::uuid(),
        'doctor_id' => (string) Str::uuid(),
        'starts_at' => $now->copy()->addDay(),
        'ends_at' => $now->copy()->addDay()->addMinutes(30),
        'status' => 'scheduled',
        'google_event_id' => null,
        'apple_event_id' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ], $overrides));
}

it('cancels appointment by route using uuid and keeps local record with canceled status', function () {
    $appointment = makeAppointment([
        'google_event_id' => 'google-event-123',
        'apple_event_id' => 'apple-event-123',
    ]);

    bindCancelDependencies(
        expectGoogleDelete: true,
        expectAppleDelete: true,
        expectDispatcher: true,
        expectWaitlist: true
    );

    DB::connection('tenant')->enableQueryLog();

    $response = $this->withoutMiddleware()->post(route('tenant.appointments.cancel', [
        'slug' => 'tenant-teste',
        'appointment' => $appointment->id,
    ]), [
        'reason' => 'Paciente solicitou cancelamento por telefone.',
    ]);

    $response->assertStatus(302);

    $appointment->refresh();

    expect($appointment->status)->toBe('canceled')
        ->and($appointment->canceled_at)->not->toBeNull()
        ->and($appointment->cancellation_reason)->toBe('Paciente solicitou cancelamento por telefone.');

    $queries = collect(DB::connection('tenant')->getQueryLog())
        ->pluck('query')
        ->map(fn (string $sql): string => strtolower($sql));

    expect($queries->contains(fn (string $sql): bool => str_contains($sql, 'where "appointments"."id" in (')))->toBeFalse();
});

it('returns 404 when appointment parameter is not a valid uuid', function () {
    bindCancelDependencies(
        expectGoogleDelete: false,
        expectAppleDelete: false,
        expectDispatcher: false,
        expectWaitlist: false
    );

    $response = $this->withoutMiddleware()->post(route('tenant.appointments.cancel', [
        'slug' => 'tenant-teste',
        'appointment' => 'nao-e-uuid',
    ]));

    $response->assertNotFound();
});

it('accepts appointment model instance without issuing id whereIn lookup', function () {
    $appointment = makeAppointment();

    bindCancelDependencies(
        expectGoogleDelete: false,
        expectAppleDelete: false,
        expectDispatcher: true,
        expectWaitlist: true
    );

    DB::connection('tenant')->enableQueryLog();

    $request = Request::create('/workspace/tenant-teste/appointments/'.$appointment->id.'/cancel', 'POST', [
        'reason' => 'Cancelado no teste interno.',
    ]);
    $request->headers->set('referer', '/workspace/tenant-teste/appointments');

    $controller = app(AppointmentController::class);
    $response = $controller->cancel($request, 'tenant-teste', $appointment);

    expect($response->getStatusCode())->toBe(302);

    $appointment->refresh();

    expect($appointment->status)->toBe('canceled')
        ->and($appointment->canceled_at)->not->toBeNull()
        ->and($appointment->cancellation_reason)->toBe('Cancelado no teste interno.');

    $queries = collect(DB::connection('tenant')->getQueryLog())
        ->pluck('query')
        ->map(fn (string $sql): string => strtolower($sql));

    expect($queries->contains(fn (string $sql): bool => str_contains($sql, 'where "appointments"."id" in (')))->toBeFalse();
});
