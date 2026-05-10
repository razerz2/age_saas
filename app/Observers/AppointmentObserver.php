<?php

namespace App\Observers;

use App\Jobs\Tenant\SendAppointmentNotificationsJob;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Form;
use App\Models\Tenant\OnlineAppointmentInstruction;
use App\Models\Tenant\TenantSetting;
use App\Services\Tenant\AppleCalendarService;
use App\Services\Tenant\GoogleCalendarService;
use App\Services\Tenant\NotificationDispatcher;
use App\Services\Tenant\OnlineMeetings\OnlineMeetingManager;
use App\Support\Tenant\OnlineMeeting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AppointmentObserver
{
    private static bool $handlingOnlineMeeting = false;

    protected GoogleCalendarService $googleCalendarService;
    protected AppleCalendarService $appleCalendarService;
    protected NotificationDispatcher $notificationDispatcher;

    public function __construct(
        GoogleCalendarService $googleCalendarService,
        AppleCalendarService $appleCalendarService,
        NotificationDispatcher $notificationDispatcher
    ) {
        $this->googleCalendarService = $googleCalendarService;
        $this->appleCalendarService = $appleCalendarService;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        $appointment->load(['patient', 'calendar.doctor.user', 'specialty']);

        if ($appointment->appointment_mode === 'online') {
            try {
                OnlineAppointmentInstruction::create([
                    'id' => Str::uuid(),
                    'appointment_id' => $appointment->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao criar instrucoes online automaticamente', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->handleOnlineMeetingCreated($appointment);

        $metadata = [
            'origin' => (string) ($appointment->origin ?? ''),
        ];

        if ($this->isWhatsAppBotOrigin($appointment)) {
            $metadata['suppress_patient_channels'] = ['whatsapp'];
            $metadata['notification_context'] = 'whatsapp_bot_inline_confirmation';
        }

        $this->dispatchAppointmentNotificationJob('created', $appointment, $metadata);

        $form = Form::getFormForAppointment($appointment);
        if ($form) {
            try {
                $formRequestMeta = [
                    'event' => 'appointment_form_requested_patient',
                    'origin' => (string) ($appointment->origin ?? ''),
                ];

                if ($this->isWhatsAppBotOrigin($appointment)) {
                    $formRequestMeta['suppress_patient_channels'] = ['whatsapp'];
                }

                $this->notificationDispatcher->dispatchAppointment(
                    $appointment,
                    'appointment.form_requested.patient',
                    $formRequestMeta
                );
            } catch (\Exception $e) {
                Log::error('Erro ao disparar notificacao de solicitacao de formulario apos criar agendamento', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($appointment->recurring_appointment_id) {
            return;
        }

        if ($this->isGoogleAutoSyncEnabled()) {
            try {
                $this->googleCalendarService->syncEvent($appointment);
            } catch (\Exception $e) {
                Log::error('Erro ao sincronizar agendamento com Google Calendar (Observer)', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($this->isAppleAutoSyncEnabled()) {
            try {
                $this->appleCalendarService->syncEvent($appointment);
            } catch (\Exception $e) {
                Log::error('Erro ao sincronizar agendamento com Apple Calendar (Observer)', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        $appointment->load(['patient', 'calendar.doctor.user']);
        $changedFields = array_keys($appointment->getChanges());

        $this->handleOnlineMeetingUpdated($appointment, $changedFields);

        if ($appointment->wasChanged('status')) {
            $oldStatus = $appointment->getOriginal('status');
            $newStatus = $appointment->status;

            $actionMap = [
                'canceled' => 'cancelled',
                'rescheduled' => 'rescheduled',
                'scheduled' => 'scheduled',
                'attended' => 'attended',
                'no_show' => 'no_show',
            ];

            if (isset($actionMap[$newStatus])) {
                $this->dispatchAppointmentNotificationJob(
                    $actionMap[$newStatus],
                    $appointment,
                    ['old_status' => $oldStatus, 'new_status' => $newStatus]
                );

                if ($newStatus === 'rescheduled') {
                    $this->notificationDispatcher->dispatchAppointment(
                        $appointment,
                        'appointment.rescheduled.doctor',
                        [
                            'event' => 'appointment_rescheduled_doctor',
                            'origin' => (string) ($appointment->origin ?? ''),
                        ]
                    );
                }
            }
        } else {
            if ($appointment->wasChanged(['starts_at', 'ends_at', 'notes'])) {
                $this->dispatchAppointmentNotificationJob('updated', $appointment);
            }
        }

        if ($appointment->recurring_appointment_id) {
            return;
        }

        if ($appointment->wasChanged('status') && $appointment->status === 'canceled') {
            if ($this->isGoogleAutoSyncEnabled()) {
                try {
                    $this->googleCalendarService->deleteEvent($appointment);
                } catch (\Exception $e) {
                    Log::error('Erro ao remover agendamento cancelado do Google Calendar (Observer)', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($this->isAppleAutoSyncEnabled()) {
                try {
                    $this->appleCalendarService->deleteEvent($appointment);
                } catch (\Exception $e) {
                    Log::error('Erro ao remover agendamento cancelado do Apple Calendar (Observer)', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return;
        }

        $relevantFields = ['starts_at', 'ends_at', 'status', 'notes', 'patient_id', 'calendar_id'];

        $hasRelevantChange = false;
        foreach ($relevantFields as $field) {
            if (in_array($field, $changedFields, true)) {
                $hasRelevantChange = true;
                break;
            }
        }

        if (!$hasRelevantChange && count($changedFields) === 1
            && (in_array('google_event_id', $changedFields, true) || in_array('apple_event_id', $changedFields, true))) {
            return;
        }

        if ($hasRelevantChange) {
            if ($this->isGoogleAutoSyncEnabled()) {
                try {
                    $this->googleCalendarService->syncEvent($appointment);
                } catch (\Exception $e) {
                    Log::error('Erro ao sincronizar agendamento com Google Calendar (Observer)', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($this->isAppleAutoSyncEnabled()) {
                try {
                    $this->appleCalendarService->syncEvent($appointment);
                } catch (\Exception $e) {
                    Log::error('Erro ao sincronizar agendamento com Apple Calendar (Observer)', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        if ($appointment->recurring_appointment_id) {
            return;
        }

        if ($this->isGoogleAutoSyncEnabled()) {
            try {
                $this->googleCalendarService->deleteEvent($appointment);
            } catch (\Exception $e) {
                Log::error('Erro ao remover agendamento do Google Calendar (Observer)', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($this->isAppleAutoSyncEnabled()) {
            try {
                $this->appleCalendarService->deleteEvent($appointment);
            } catch (\Exception $e) {
                Log::error('Erro ao remover agendamento do Apple Calendar (Observer)', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function dispatchAppointmentNotificationJob(
        string $action,
        Appointment $appointment,
        ?array $metadata = null
    ): void {
        $tenant = Tenant::current();
        if (!$tenant) {
            Log::warning('Tenant atual nao encontrado para enfileirar notificacao de agendamento', [
                'appointment_id' => $appointment->id,
                'action' => $action,
            ]);
            return;
        }

        $queueConnection = (string) config('queue.default', 'sync');
        $queueName = (string) config("queue.connections.{$queueConnection}.queue", 'default');

        $pendingDispatch = SendAppointmentNotificationsJob::dispatch(
            $tenant->id,
            $appointment->id,
            $action,
            $metadata
        );

        if (method_exists($pendingDispatch, 'afterCommit')) {
            $pendingDispatch->afterCommit();
        }

        if (in_array($queueConnection, ['database', 'redis'], true)) {
            Log::info('Appointment notification job enqueued', [
                'tenant_id' => $tenant->id,
                'appointment_id' => $appointment->id,
                'action' => $action,
                'queue_connection' => $queueConnection,
                'queue' => $queueName,
            ]);
        } else {
            Log::info('Appointment notification dispatch scheduled', [
                'tenant_id' => $tenant->id,
                'appointment_id' => $appointment->id,
                'action' => $action,
                'queue_connection' => $queueConnection,
            ]);
        }
    }

    private function isWhatsAppBotOrigin(Appointment $appointment): bool
    {
        return trim(strtolower((string) ($appointment->origin ?? ''))) === Appointment::ORIGIN_WHATSAPP_BOT;
    }

    private function isGoogleAutoSyncEnabled(): bool
    {
        return TenantSetting::isEnabled('integrations.google_calendar.enabled')
            && TenantSetting::isEnabled('integrations.google_calendar.auto_sync');
    }

    private function isAppleAutoSyncEnabled(): bool
    {
        return TenantSetting::isEnabled('integrations.apple_calendar.enabled')
            && TenantSetting::isEnabled('integrations.apple_calendar.auto_sync');
    }

    private function handleOnlineMeetingCreated(Appointment $appointment): void
    {
        if (self::$handlingOnlineMeeting) {
            return;
        }

        self::$handlingOnlineMeeting = true;
        try {
            $manager = app(OnlineMeetingManager::class);
            if (!$manager->shouldHandle($appointment)) {
                return;
            }

            $manager->provisionFor($appointment);
        } catch (\Throwable $e) {
            Log::error('Erro ao processar reuniao online no created do AppointmentObserver', [
                'appointment_id' => $appointment->id,
                'status' => $appointment->status,
                'appointment_mode' => $appointment->appointment_mode,
                'error' => $e->getMessage(),
            ]);
        } finally {
            self::$handlingOnlineMeeting = false;
        }
    }

    /**
     * @param array<int, string> $changedFields
     */
    private function handleOnlineMeetingUpdated(Appointment $appointment, array $changedFields): void
    {
        if (self::$handlingOnlineMeeting) {
            return;
        }

        if ($this->hasOnlyIrrelevantOnlineMeetingChanges($changedFields)) {
            return;
        }

        self::$handlingOnlineMeeting = true;
        try {
            $manager = app(OnlineMeetingManager::class);

            if ($manager->shouldCancel($appointment, $changedFields)) {
                $manager->cancelFor($appointment);
                return;
            }

            if (!$manager->shouldHandle($appointment)) {
                return;
            }

            if (in_array('appointment_mode', $changedFields, true) && $appointment->appointment_mode === 'online') {
                $manager->provisionFor($appointment);
                return;
            }

            if (!$manager->shouldUpdate($appointment, $changedFields)) {
                return;
            }

            $statusNow = strtolower(trim((string) $appointment->status));
            if (in_array('status', $changedFields, true) && in_array($statusNow, ['scheduled', 'rescheduled'], true)) {
                $manager->provisionFor($appointment);
                return;
            }

            $appointment->loadMissing('onlineInstructions');
            $instruction = $appointment->onlineInstructions;

            if (
                $instruction
                && $instruction->meeting_status === OnlineMeeting::STATUS_GENERATED
                && filled($instruction->meeting_link)
            ) {
                $manager->updateFor($appointment);
                return;
            }

            $manager->provisionFor($appointment);
        } catch (\Throwable $e) {
            Log::error('Erro ao processar reuniao online no updated do AppointmentObserver', [
                'appointment_id' => $appointment->id,
                'status' => $appointment->status,
                'appointment_mode' => $appointment->appointment_mode,
                'changed_fields' => $changedFields,
                'error' => $e->getMessage(),
            ]);
        } finally {
            self::$handlingOnlineMeeting = false;
        }
    }

    /**
     * @param array<int, string> $changedFields
     */
    private function hasOnlyIrrelevantOnlineMeetingChanges(array $changedFields): bool
    {
        if ($changedFields === []) {
            return true;
        }

        $irrelevant = [
            'google_event_id',
            'apple_event_id',
            'updated_at',
            'created_at',
        ];

        foreach ($changedFields as $field) {
            if (!in_array($field, $irrelevant, true)) {
                return false;
            }
        }

        return true;
    }
}
