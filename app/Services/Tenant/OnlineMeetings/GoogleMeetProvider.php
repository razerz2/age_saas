<?php

namespace App\Services\Tenant\OnlineMeetings;

use App\Contracts\Tenant\OnlineMeetingProviderInterface;
use App\DTO\Tenant\OnlineMeetingResult;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\OnlineAppointmentInstruction;
use App\Services\Tenant\GoogleCalendarService;
use App\Support\Tenant\OnlineMeeting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GoogleMeetProvider implements OnlineMeetingProviderInterface
{
    public function __construct(
        private readonly GoogleCalendarService $googleCalendarService
    ) {
    }

    public function key(): string
    {
        return OnlineMeeting::PROVIDER_GOOGLE_MEET;
    }

    public function label(): string
    {
        return 'Google Meet';
    }

    public function createForAppointment(Appointment $appointment): OnlineMeetingResult
    {
        if ($appointment->appointment_mode !== 'online') {
            return OnlineMeetingResult::skipped(
                provider: $this->key(),
                errorMessage: 'Appointment is not online.',
                meta: ['appointment_id' => $appointment->id]
            );
        }

        $appointment->loadMissing([
            'patient',
            'calendar.doctor.user',
            'calendar.doctor.googleCalendarToken',
            'type',
            'specialty',
            'onlineInstructions',
        ]);

        if (!$appointment->calendar || !$appointment->calendar->doctor) {
            return $this->manualRequired(
                $appointment,
                'Nao foi possivel identificar o profissional deste agendamento online.'
            );
        }

        $doctor = $appointment->calendar->doctor;
        $token = $doctor->googleCalendarToken;
        if (!$token) {
            Log::warning('Google Meet nao gerado: medico sem token Google Calendar', [
                'appointment_id' => $appointment->id,
                'doctor_id' => $doctor->id ?? null,
                'provider' => $this->key(),
            ]);

            return $this->manualRequired(
                $appointment,
                $this->missingGoogleCalendarConnectionMessage()
            );
        }

        $instruction = $this->getOrCreateInstruction($appointment);
        if (
            $instruction
            && $instruction->meeting_status === OnlineMeeting::STATUS_GENERATED
            && filled($instruction->meeting_link)
        ) {
            return OnlineMeetingResult::success(
                provider: $this->key(),
                meetingLink: $instruction->meeting_link,
                externalEventId: $instruction->external_event_id ?: $appointment->google_event_id,
                externalMeetingId: $instruction->external_meeting_id,
                meta: ['appointment_id' => $appointment->id]
            );
        }

        if (
            filled($appointment->google_event_id)
            && $instruction
            && filled($instruction->meeting_link)
        ) {
            return OnlineMeetingResult::success(
                provider: $this->key(),
                meetingLink: $instruction->meeting_link,
                externalEventId: $appointment->google_event_id,
                externalMeetingId: $instruction->external_meeting_id,
                meta: ['appointment_id' => $appointment->id]
            );
        }

        try {
            Log::info('Google Meet provider iniciou geracao', [
                'appointment_id' => $appointment->id,
                'provider' => $this->key(),
            ]);

            $eventData = $this->googleCalendarService->createEventWithMeet($appointment);
            $instruction = $this->storeGeneratedInstruction($appointment, $eventData);

            Log::info('Google Meet gerado com sucesso', [
                'appointment_id' => $appointment->id,
                'provider' => $this->key(),
                'event_id' => $eventData['event_id'] ?? null,
            ]);

            return OnlineMeetingResult::success(
                provider: $this->key(),
                meetingLink: $instruction->meeting_link,
                externalEventId: $instruction->external_event_id,
                externalMeetingId: $instruction->external_meeting_id,
                meta: [
                    'appointment_id' => $appointment->id,
                    'meeting_status' => $instruction->meeting_status,
                    'meeting_meta' => $instruction->meeting_meta ?? [],
                ]
            );
        } catch (Throwable $e) {
            Log::error('Falha ao gerar Google Meet', [
                'appointment_id' => $appointment->id,
                'provider' => $this->key(),
                'error' => $e->getMessage(),
            ]);

            $this->storeFailedInstruction(
                $appointment,
                'Nao foi possivel gerar a reuniao Google Meet automaticamente.'
            );

            return OnlineMeetingResult::failed(
                provider: $this->key(),
                errorMessage: 'Nao foi possivel gerar a reuniao Google Meet automaticamente.',
                meta: [
                    'appointment_id' => $appointment->id,
                    'exception' => $e->getMessage(),
                ]
            );
        }
    }

    public function updateForAppointment(Appointment $appointment): OnlineMeetingResult
    {
        if ($appointment->appointment_mode !== 'online') {
            return OnlineMeetingResult::skipped(
                provider: $this->key(),
                errorMessage: 'Appointment is not online.',
                meta: ['appointment_id' => $appointment->id]
            );
        }

        $appointment->loadMissing([
            'calendar.doctor.googleCalendarToken',
            'onlineInstructions',
        ]);

        $instruction = $appointment->onlineInstructions;
        if (
            !$instruction
            || $instruction->meeting_status !== OnlineMeeting::STATUS_GENERATED
            || !filled($instruction->meeting_link)
        ) {
            return $this->createForAppointment($appointment);
        }

        if (!$appointment->calendar || !$appointment->calendar->doctor) {
            return $this->manualRequired(
                $appointment,
                'Nao foi possivel identificar o profissional para atualizar o Google Meet.'
            );
        }

        if (!$appointment->calendar->doctor->googleCalendarToken) {
            return $this->manualRequired(
                $appointment,
                $this->missingGoogleCalendarConnectionMessage()
            );
        }

        try {
            $eventData = $this->googleCalendarService->updateEventWithMeet($appointment);
            $instruction = $this->storeGeneratedInstruction($appointment, $eventData);

            return OnlineMeetingResult::success(
                provider: $this->key(),
                meetingLink: $instruction->meeting_link,
                externalEventId: $instruction->external_event_id,
                externalMeetingId: $instruction->external_meeting_id,
                meta: [
                    'appointment_id' => $appointment->id,
                    'meeting_status' => $instruction->meeting_status,
                    'meeting_meta' => $instruction->meeting_meta ?? [],
                ]
            );
        } catch (Throwable $e) {
            Log::error('Falha ao atualizar Google Meet', [
                'appointment_id' => $appointment->id,
                'provider' => $this->key(),
                'error' => $e->getMessage(),
            ]);

            $this->storeFailedInstruction(
                $appointment,
                'Nao foi possivel atualizar a reuniao Google Meet automaticamente.'
            );

            return OnlineMeetingResult::failed(
                provider: $this->key(),
                errorMessage: 'Nao foi possivel atualizar a reuniao Google Meet automaticamente.',
                meta: [
                    'appointment_id' => $appointment->id,
                    'exception' => $e->getMessage(),
                ]
            );
        }
    }

    public function cancelForAppointment(Appointment $appointment): OnlineMeetingResult
    {
        if ($appointment->appointment_mode !== 'online') {
            return OnlineMeetingResult::skipped(
                provider: $this->key(),
                errorMessage: 'Appointment is not online.',
                meta: ['appointment_id' => $appointment->id]
            );
        }

        $appointment->loadMissing([
            'calendar.doctor.googleCalendarToken',
            'onlineInstructions',
        ]);

        $instruction = $appointment->onlineInstructions;
        $eventId = $appointment->google_event_id ?: ($instruction->external_event_id ?? null);

        if (!filled($eventId)) {
            if ($instruction) {
                try {
                    $this->persistInstruction($appointment, $instruction, [
                        'meeting_status' => OnlineMeeting::STATUS_CANCELLED,
                        'meeting_generation_error' => null,
                    ]);
                } catch (Throwable $e) {
                    Log::warning('Falha ao salvar status cancelled sem evento externo', [
                        'appointment_id' => $appointment->id,
                        'provider' => $this->key(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return OnlineMeetingResult::success(
                provider: $this->key(),
                status: OnlineMeeting::STATUS_CANCELLED,
                meta: ['appointment_id' => $appointment->id]
            );
        }

        if (!$appointment->calendar || !$appointment->calendar->doctor || !$appointment->calendar->doctor->googleCalendarToken) {
            $message = 'Nao foi possivel cancelar automaticamente no Google Calendar porque o profissional nao esta conectado.';
            $this->storeFailedInstruction($appointment, $message);

            return OnlineMeetingResult::failed(
                provider: $this->key(),
                errorMessage: $message,
                meta: ['appointment_id' => $appointment->id]
            );
        }

        $originalEventId = $appointment->google_event_id;
        if (!filled($appointment->google_event_id)) {
            $appointment->google_event_id = $eventId;
        }

        $deleted = $this->googleCalendarService->deleteEventForAppointment($appointment);

        if (!filled($originalEventId)) {
            $appointment->google_event_id = $originalEventId;
        }

        if (!$deleted) {
            $message = 'Nao foi possivel cancelar a reuniao no Google Calendar.';
            $this->storeFailedInstruction($appointment, $message);

            return OnlineMeetingResult::failed(
                provider: $this->key(),
                errorMessage: $message,
                meta: ['appointment_id' => $appointment->id]
            );
        }

        if ($instruction) {
            try {
                $this->persistInstruction($appointment, $instruction, [
                    'meeting_status' => OnlineMeeting::STATUS_CANCELLED,
                    'meeting_generation_error' => null,
                    'meeting_meta' => array_merge((array) $instruction->meeting_meta, [
                        'cancelled_at' => now()->toIso8601String(),
                    ]),
                ]);
            } catch (Throwable $e) {
                Log::warning('Falha ao salvar status cancelled da reuniao online', [
                    'appointment_id' => $appointment->id,
                    'provider' => $this->key(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Google Meet cancelado com sucesso', [
            'appointment_id' => $appointment->id,
            'provider' => $this->key(),
            'event_id' => $eventId,
        ]);

        return OnlineMeetingResult::success(
            provider: $this->key(),
            status: OnlineMeeting::STATUS_CANCELLED,
            meta: ['appointment_id' => $appointment->id]
        );
    }

    protected function manualRequired(Appointment $appointment, string $message): OnlineMeetingResult
    {
        $instruction = $this->getOrCreateInstruction($appointment);

        if ($instruction) {
            try {
                $this->persistInstruction($appointment, $instruction, [
                    'meeting_provider' => $this->key(),
                    'meeting_status' => OnlineMeeting::STATUS_MANUAL_REQUIRED,
                    'meeting_generation_error' => $message,
                    'meeting_app' => 'Google Meet',
                ]);
            } catch (Throwable $e) {
                Log::warning('Falha ao salvar status manual_required da reuniao online', [
                    'appointment_id' => $appointment->id,
                    'provider' => $this->key(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return OnlineMeetingResult::manualRequired(
            provider: $this->key(),
            errorMessage: $message,
            meta: ['appointment_id' => $appointment->id]
        );
    }

    protected function storeFailedInstruction(Appointment $appointment, string $message): void
    {
        $instruction = $this->getOrCreateInstruction($appointment);
        if (!$instruction) {
            return;
        }

        try {
            $this->persistInstruction($appointment, $instruction, [
                'meeting_provider' => $this->key(),
                'meeting_status' => OnlineMeeting::STATUS_FAILED,
                'meeting_generation_error' => $message,
                'meeting_app' => 'Google Meet',
            ]);
        } catch (Throwable $e) {
            Log::warning('Falha ao salvar status failed da reuniao online', [
                'appointment_id' => $appointment->id,
                'provider' => $this->key(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $eventData
     */
    protected function storeGeneratedInstruction(Appointment $appointment, array $eventData): OnlineAppointmentInstruction
    {
        $instruction = $this->getOrCreateInstruction($appointment);
        if (!$instruction) {
            throw new \RuntimeException('Nao foi possivel preparar online_appointment_instructions.');
        }

        $this->persistInstruction($appointment, $instruction, [
            'meeting_link' => $eventData['meeting_link'] ?? null,
            'meeting_app' => 'Google Meet',
            'meeting_provider' => $this->key(),
            'meeting_status' => OnlineMeeting::STATUS_GENERATED,
            'external_event_id' => $eventData['event_id'] ?? $appointment->google_event_id,
            'external_meeting_id' => $eventData['conference_id'] ?? null,
            'meeting_generated_at' => now(),
            'meeting_generation_error' => null,
            'meeting_meta' => $this->sanitizeMeetingMeta((array) ($eventData['raw'] ?? [])),
        ]);

        return $instruction;
    }

    protected function getOrCreateInstruction(Appointment $appointment): ?OnlineAppointmentInstruction
    {
        if ($appointment->onlineInstructions) {
            return $appointment->onlineInstructions;
        }

        $instruction = new OnlineAppointmentInstruction();
        $instruction->id = (string) Str::uuid();
        $instruction->appointment_id = $appointment->id;

        $appointment->setRelation('onlineInstructions', $instruction);

        if ($appointment->exists) {
            try {
                $instruction->save();
            } catch (Throwable $e) {
                Log::warning('Falha ao criar online_appointment_instruction inicial', [
                    'appointment_id' => $appointment->id,
                    'provider' => $this->key(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $instruction;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function persistInstruction(
        Appointment $appointment,
        OnlineAppointmentInstruction $instruction,
        array $attributes
    ): void {
        $instruction->fill($attributes);

        if (!$appointment->exists) {
            return;
        }

        if (!$instruction->exists) {
            if (!filled($instruction->id)) {
                $instruction->id = (string) Str::uuid();
            }
            $instruction->appointment_id = $appointment->id;
        }

        $instruction->save();
    }

    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    protected function sanitizeMeetingMeta(array $meta): array
    {
        $blockedKeys = [
            'access_token',
            'refresh_token',
            'token',
            'id_token',
            'authorization',
            'client_secret',
            'secret',
        ];

        $sanitized = [];

        foreach ($meta as $key => $value) {
            $keyString = strtolower((string) $key);
            if (in_array($keyString, $blockedKeys, true)) {
                continue;
            }

            if (is_array($value)) {
                /** @var array<string, mixed> $value */
                $sanitized[(string) $key] = $this->sanitizeMeetingMeta($value);
                continue;
            }

            $sanitized[(string) $key] = $value;
        }

        return $sanitized;
    }

    protected function missingGoogleCalendarConnectionMessage(): string
    {
        return 'O profissional responsável ainda não conectou o Google Calendar. Conecte a conta Google do profissional ou informe o link da reunião manualmente.';
    }
}
