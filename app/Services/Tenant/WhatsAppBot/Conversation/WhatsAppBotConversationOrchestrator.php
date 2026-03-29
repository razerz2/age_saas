<?php

namespace App\Services\Tenant\WhatsAppBot\Conversation;

use App\Models\Tenant\Patient;
use App\Models\Tenant\WhatsAppBotSession;
use App\Services\Tenant\WhatsAppBot\Domain\WhatsAppBotAppointmentService;
use App\Services\Tenant\WhatsAppBot\Domain\WhatsAppBotDomainService;
use App\Services\Tenant\WhatsAppBot\Domain\WhatsAppBotPatientService;
use App\Services\Tenant\WhatsAppBot\DTO\ConversationResult;
use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\Tenant\WhatsAppBotConfigService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WhatsAppBotConversationOrchestrator
{
    private const FLOW_MENU = 'menu';
    private const FLOW_SCHEDULE = 'schedule';
    private const FLOW_CANCEL = 'cancel';

    private const STEP_MENU_AWAITING_OPTION = 'menu.awaiting_option';
    private const STEP_IDENTIFY_AWAITING_CPF = 'identify.awaiting_cpf';
    private const STEP_REGISTER_AWAITING_FIELD = 'register.awaiting_field';
    private const STATE_PENDING_INTENT = 'pending_intent';
    private const STATE_REGISTRATION = 'registration';

    /**
     * Legacy step kept only to recover sessions created before CPF-gated identification.
     */
    private const STEP_IDENTIFY_AWAITING_NAME = 'identify.awaiting_name';
    private const STEP_SCHEDULE_AWAITING_SPECIALTY = 'schedule.awaiting_specialty';
    private const STEP_SCHEDULE_AWAITING_DOCTOR = 'schedule.awaiting_doctor';
    private const STEP_SCHEDULE_AWAITING_DATE = 'schedule.awaiting_date';
    private const STEP_SCHEDULE_AWAITING_SLOT = 'schedule.awaiting_slot';
    private const STEP_SCHEDULE_AWAITING_CONFIRMATION = 'schedule.awaiting_confirmation';
    private const STEP_CANCEL_AWAITING_APPOINTMENT = 'cancel.awaiting_appointment';
    private const STEP_CANCEL_AWAITING_CONFIRMATION = 'cancel.awaiting_confirmation';

    /**
     * @var array<string, mixed>
     */
    private array $runtimeSettings = [];

    public function __construct(
        private readonly WhatsAppBotConfigService $configService,
        private readonly WhatsAppBotDomainService $domainService,
        private readonly WhatsAppBotIntentRouter $intentRouter,
        private readonly WhatsAppBotPatientService $patientService,
        private readonly WhatsAppBotAppointmentService $appointmentService
    ) {
    }

    public function handle(WhatsAppBotSession $session, InboundMessage $message): ConversationResult
    {
        $state = is_array($session->state) ? $session->state : [];
        $text = trim((string) ($message->text ?? ''));
        $runtime = $this->initializeRuntimeSettings();
        $settings = $runtime['settings'];
        $entryKeywords = $runtime['entry_keywords'];
        $exitKeywords = $runtime['exit_keywords'];
        $resetKeywords = $runtime['reset_keywords'];

        $flow = (string) ($session->current_flow ?? self::FLOW_MENU);
        $step = (string) ($session->current_step ?? self::STEP_MENU_AWAITING_OPTION);

        $this->logStep($message, $flow, $step, 'incoming', 'success');

        $globalCommandResult = $this->handleGlobalConversationCommands(
            $session,
            $message,
            $state,
            $text,
            $flow,
            $step,
            $settings,
            $entryKeywords,
            $exitKeywords,
            $resetKeywords
        );
        if ($globalCommandResult instanceof ConversationResult) {
            return $globalCommandResult;
        }

        if ($step === self::STEP_IDENTIFY_AWAITING_CPF) {
            return $this->handleIdentifyByCpfStep($message, $state, $text, $settings);
        }

        if ($step === self::STEP_REGISTER_AWAITING_FIELD) {
            return $this->handleRegistrationStep($message, $state, $text, $settings);
        }

        if ($step === self::STEP_IDENTIFY_AWAITING_NAME) {
            return $this->menuResult(
                $message,
                $this->resetConversationState($state),
                'Fluxo de identificacao atualizado. Escolha uma opcao para continuar.'
            );
        }

        $patient = $this->resolveAuthenticatedPatient($state);
        if ($patient instanceof Patient) {
            $state['patient_id'] = (string) $patient->id;
            $state['patient_name'] = (string) $patient->full_name;
        } else {
            unset($state['patient_id'], $state['patient_name']);
        }

        if ($flow === self::FLOW_SCHEDULE) {
            if (!$patient) {
                if ($this->requiresIdentificationForIntent(WhatsAppBotIntentRouter::INTENT_SCHEDULE, $settings)) {
                    return $this->startPatientIdentification($message, $state, WhatsAppBotIntentRouter::INTENT_SCHEDULE);
                }

                $patient = $this->resolvePatientByLookupOrder(
                    null,
                    $message->contactPhone,
                    (array) data_get($settings, 'identification.lookup_order', WhatsAppBotConfigService::DEFAULT_IDENTIFICATION_LOOKUP_ORDER)
                );
            }

            if (!$patient) {
                return $this->menuResult($message, $this->resetConversationState($state), $this->message('patient_not_found'));
            }

            return $this->handleScheduleFlow($message, $state, $patient, $step, $text);
        }

        if ($flow === self::FLOW_CANCEL) {
            if (!$patient) {
                if ($this->requiresIdentificationForIntent(WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS, $settings)) {
                    return $this->startPatientIdentification($message, $state, WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS);
                }

                $patient = $this->resolvePatientByLookupOrder(
                    null,
                    $message->contactPhone,
                    (array) data_get($settings, 'identification.lookup_order', WhatsAppBotConfigService::DEFAULT_IDENTIFICATION_LOOKUP_ORDER)
                );
            }

            if (!$patient) {
                return $this->menuResult($message, $this->clearCancelState($state), $this->message('patient_not_found'));
            }

            return $this->handleCancelFlow($message, $state, $patient, $step, $text);
        }

        return $this->handleMenu($message, $state, $patient, $text, $settings);
    }

    /**
     * @return array{
     *   settings: array<string, mixed>,
     *   entry_keywords: array<int, string>,
     *   exit_keywords: array<int, string>,
     *   reset_keywords: array<int, string>
     * }
     */
    private function initializeRuntimeSettings(): array
    {
        $settings = $this->configService->getSettings();
        $this->runtimeSettings = $settings;

        return [
            'settings' => $settings,
            'entry_keywords' => (array) ($settings['entry_keywords'] ?? []),
            'exit_keywords' => (array) ($settings['exit_keywords'] ?? []),
            'reset_keywords' => (array) data_get($settings, 'session.reset_keywords', WhatsAppBotConfigService::DEFAULT_SESSION_RESET_KEYWORDS),
        ];
    }

    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $settings
     * @param array<int, string> $entryKeywords
     * @param array<int, string> $exitKeywords
     * @param array<int, string> $resetKeywords
     */
    private function handleGlobalConversationCommands(
        WhatsAppBotSession $session,
        InboundMessage $message,
        array $state,
        string $text,
        string $flow,
        string $step,
        array $settings,
        array $entryKeywords,
        array $exitKeywords,
        array $resetKeywords
    ): ?ConversationResult {
        if ($this->hasSessionTimedOut($session, $settings)) {
            $timedOutState = $this->applySessionTimeoutState($state, $settings);

            return $this->menuResult(
                $message,
                $timedOutState,
                $this->message('inactivity_exit'),
                true
            );
        }

        if ($this->shouldResetSessionContext($flow, $step)) {
            return $this->menuResult($message, $this->resetConversationState($state), 'Sessao reiniciada por seguranca.');
        }

        if ($this->intentRouter->isResetCommand($text, $resetKeywords)) {
            return $this->menuResult($message, $this->resetConversationState($state), 'Fluxo reiniciado.');
        }

        if ($text !== '' && $this->intentRouter->matchesAnyKeyword($text, $exitKeywords)) {
            return $this->menuResult(
                $message,
                $this->resetConversationState($state),
                WhatsAppBotConfigService::DEFAULT_EXIT_MESSAGE
            );
        }

        if ($text !== '' && $this->intentRouter->matchesAnyKeyword($text, $entryKeywords)) {
            return $this->menuResult($message, $this->resetConversationState($state));
        }

        return null;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function handleMenu(InboundMessage $message, array $state, ?Patient $patient, string $text, array $settings): ConversationResult
    {
        if ($text === '') {
            return $this->menuResult($message, $state);
        }

        $intent = $this->resolveIntentFromMessage($message, $settings);
        $state['last_intent'] = $intent;

        Log::info('whatsapp_bot.intent.routed', [
            'tenant_id' => (string) (tenant()?->id ?? ''),
            'provider' => $message->provider,
            'phone' => $message->contactPhone,
            'flow' => self::FLOW_MENU,
            'step' => self::STEP_MENU_AWAITING_OPTION,
            'intent' => $intent,
            'action' => 'intent.route',
            'result' => $intent === WhatsAppBotIntentRouter::INTENT_UNKNOWN ? 'unknown' : 'success',
        ]);

        if ($intent === WhatsAppBotIntentRouter::INTENT_UNKNOWN) {
            if ($this->intentRouter->isGreeting($text)) {
                return $this->menuResult($message, $state);
            }

            return $this->fallbackMenuResult($message, $state, $settings);
        }

        if (!$this->domainService->isIntentEnabled($intent) || !$this->isIntentEnabledInMenu($intent, $settings)) {
            return $this->menuResult($message, $state, $this->domainService->unavailableIntentMessage($intent));
        }

        $patientForIntent = $patient;
        if (!(bool) data_get($settings, 'identification.reuse_identified_patient', true)) {
            $patientForIntent = null;
        }

        if ($intent === WhatsAppBotIntentRouter::INTENT_SCHEDULE) {
            if (!$patientForIntent && $this->requiresIdentificationForIntent($intent, $settings)) {
                return $this->startPatientIdentification($message, $state, $intent);
            }

            if (!$patientForIntent) {
                $patientForIntent = $this->resolvePatientByLookupOrder(
                    null,
                    $message->contactPhone,
                    (array) data_get($settings, 'identification.lookup_order', WhatsAppBotConfigService::DEFAULT_IDENTIFICATION_LOOKUP_ORDER)
                );
            }

            if (!$patientForIntent) {
                return $this->menuResult($message, $state, $this->message('patient_not_found'));
            }

            $state['patient_id'] = (string) $patientForIntent->id;
            $state['patient_name'] = (string) $patientForIntent->full_name;

            return $this->startScheduleFlow($message, $state);
        }

        if ($intent === WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS) {
            if (!$patientForIntent && $this->requiresIdentificationForIntent($intent, $settings)) {
                return $this->startPatientIdentification($message, $state, $intent);
            }

            if (!$patientForIntent) {
                $patientForIntent = $this->resolvePatientByLookupOrder(
                    null,
                    $message->contactPhone,
                    (array) data_get($settings, 'identification.lookup_order', WhatsAppBotConfigService::DEFAULT_IDENTIFICATION_LOOKUP_ORDER)
                );
            }

            if (!$patientForIntent) {
                return $this->menuResult($message, $state, $this->message('patient_not_found'));
            }

            $state['patient_id'] = (string) $patientForIntent->id;
            $state['patient_name'] = (string) $patientForIntent->full_name;

            try {
                $appointments = $this->appointmentService->listUpcomingAppointments($patientForIntent);
            } catch (\Throwable $exception) {
                $this->logStep($message, self::FLOW_MENU, self::STEP_MENU_AWAITING_OPTION, 'error:view_appointments_failed', 'error', [
                    'error' => $exception->getMessage(),
                ]);
                return $this->menuResult($message, $state, $this->friendlyTechnicalErrorMessage());
            }

            if ($appointments->isEmpty()) {
                return $this->menuResult($message, $state, 'Voce nao possui agendamentos futuros.');
            }

            $lines = ['Seus proximos agendamentos:'];
            foreach ($appointments as $index => $appointment) {
                $doctorName = trim((string) ($appointment->doctor?->user?->name_full ?? $appointment->doctor?->user?->name ?? 'Profissional'));
                $startsAt = $appointment->starts_at ? $appointment->starts_at->copy()->timezone($this->timezone()) : null;
                $lines[] = sprintf('%d. %s - %s', $index + 1, $doctorName, $startsAt ? $startsAt->format('d/m H:i') : '-');
            }

            return $this->menuResult($message, $state, implode("\n", $lines));
        }

        if ($intent === WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS) {
            if (!$patientForIntent && $this->requiresIdentificationForIntent($intent, $settings)) {
                return $this->startPatientIdentification($message, $state, $intent);
            }

            if (!$patientForIntent) {
                $patientForIntent = $this->resolvePatientByLookupOrder(
                    null,
                    $message->contactPhone,
                    (array) data_get($settings, 'identification.lookup_order', WhatsAppBotConfigService::DEFAULT_IDENTIFICATION_LOOKUP_ORDER)
                );
            }

            if (!$patientForIntent) {
                return $this->menuResult($message, $state, $this->message('patient_not_found'));
            }

            $state['patient_id'] = (string) $patientForIntent->id;
            $state['patient_name'] = (string) $patientForIntent->full_name;

            return $this->startCancelFlow($message, $state, $patientForIntent);
        }

        return $this->fallbackMenuResult($message, $state, $settings);
    }

    private function startScheduleFlow(InboundMessage $message, array $state): ConversationResult
    {
        try {
            $specialties = $this->appointmentService->listSpecialties()->values()->all();
        } catch (\Throwable $exception) {
            $this->logStep($message, self::FLOW_MENU, self::STEP_MENU_AWAITING_OPTION, 'error:list_specialties_failed', 'error', [
                'error' => $exception->getMessage(),
            ]);
            return $this->menuResult($message, $state, $this->friendlyTechnicalErrorMessage());
        }

        $state['schedule'] = ['specialties' => $specialties];

        if (count($specialties) > 1) {
            $state = $this->clearInvalidAttempts($state);
            $lines = ['Escolha a especialidade:'];
            foreach ($specialties as $index => $specialty) {
                $lines[] = sprintf('%d. %s', $index + 1, (string) ($specialty['name'] ?? 'Especialidade'));
            }

            return new ConversationResult(
                processed: true,
                reason: null,
                outboundMessages: [OutboundMessage::text($message->contactPhone, implode("\n", $lines), ['kind' => 'schedule_specialty'])],
                flow: self::FLOW_SCHEDULE,
                step: self::STEP_SCHEDULE_AWAITING_SPECIALTY,
                stateUpdates: $state
            );
        }

        if (count($specialties) === 1) {
            $state['schedule']['selected_specialty_id'] = (string) $specialties[0]['id'];
            $state['schedule']['selected_specialty_name'] = (string) $specialties[0]['name'];
        } else {
            $state['schedule']['selected_specialty_id'] = null;
            $state['schedule']['selected_specialty_name'] = null;
        }

        return $this->promptDoctorSelection($message, $state);
    }
    private function handleScheduleFlow(
        InboundMessage $message,
        array $state,
        Patient $patient,
        string $step,
        string $text
    ): ConversationResult {
        if (!$this->domainService->isIntentEnabled(WhatsAppBotIntentRouter::INTENT_SCHEDULE)) {
            return $this->menuResult($message, $this->clearScheduleState($state), $this->domainService->unavailableIntentMessage(WhatsAppBotIntentRouter::INTENT_SCHEDULE));
        }

        if ($step === self::STEP_SCHEDULE_AWAITING_SPECIALTY) {
            $specialties = array_values((array) data_get($state, 'schedule.specialties', []));
            $index = $this->intentRouter->parseSelectionNumber($text);

            if ($index === null || !isset($specialties[$index - 1])) {
                return $this->repeatScheduleSpecialty($message, $state);
            }

            $selected = $specialties[$index - 1];
            $state['schedule']['selected_specialty_id'] = (string) ($selected['id'] ?? '');
            $state['schedule']['selected_specialty_name'] = (string) ($selected['name'] ?? '');

            return $this->promptDoctorSelection($message, $state);
        }

        if ($step === self::STEP_SCHEDULE_AWAITING_DOCTOR) {
            $doctors = array_values((array) data_get($state, 'schedule.doctors', []));
            $index = $this->intentRouter->parseSelectionNumber($text);

            if ($index === null || !isset($doctors[$index - 1])) {
                return $this->repeatScheduleDoctors($message, $state);
            }

            $state['schedule']['selected_doctor'] = $doctors[$index - 1];
            $state = $this->clearInvalidAttempts($state);

            return new ConversationResult(
                processed: true,
                reason: null,
                outboundMessages: [OutboundMessage::text($message->contactPhone, 'Informe a data desejada (DD/MM ou AAAA-MM-DD). Exemplo: 25/03', ['kind' => 'schedule_date'])],
                flow: self::FLOW_SCHEDULE,
                step: self::STEP_SCHEDULE_AWAITING_DATE,
                stateUpdates: $state
            );
        }

        if ($step === self::STEP_SCHEDULE_AWAITING_DATE) {
            $date = $this->parseDateInput($text);
            if (!$date) {
                return $this->invalidStepResult($message, $state, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_DATE, 'Data invalida. Informe no formato DD/MM ou AAAA-MM-DD.', 'schedule_date_invalid');
            }

            if ($date->lt(Carbon::now($this->timezone())->startOfDay())) {
                return $this->invalidStepResult($message, $state, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_DATE, 'Nao e possivel agendar em data passada. Informe uma data de hoje em diante.', 'schedule_date_past');
            }

            $doctor = (array) data_get($state, 'schedule.selected_doctor', []);
            $duration = (int) ($doctor['duration_min'] ?? 30);
            $doctorId = (string) ($doctor['id'] ?? '');
            $dateKey = $date->format('Y-m-d');
            $cachedDate = (string) data_get($state, 'schedule.selected_date', '');
            $cachedDoctor = (string) data_get($state, 'schedule.slots_doctor_id', '');
            $cachedSlots = array_values((array) data_get($state, 'schedule.slots', []));

            if ($cachedDate === $dateKey && $cachedDoctor === $doctorId && $cachedSlots !== []) {
                $slots = $cachedSlots;
            } else {
                try {
                    $slots = $this->appointmentService->listAvailableSlots($doctorId, $date, $duration)->take(10)->values()->all();
                } catch (\Throwable $exception) {
                    $this->logStep($message, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_DATE, 'error:list_slots_failed', 'error', [
                        'error' => $exception->getMessage(),
                        'doctor_id' => $doctorId,
                        'date' => $dateKey,
                    ]);
                    return $this->menuResult($message, $this->clearScheduleState($state), $this->friendlyTechnicalErrorMessage());
                }
            }

            $state['schedule']['selected_date'] = $dateKey;
            $state['schedule']['slots_doctor_id'] = $doctorId;

            if ($slots === []) {
                $noSlotsText = $this->message('no_slots_available') . "\nInforme outra data.";
                return $this->invalidStepResult($message, $state, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_DATE, $noSlotsText, 'schedule_no_slots');
            }

            $state['schedule']['slots'] = $slots;
            $state = $this->clearInvalidAttempts($state);

            $lines = ['Escolha o horario disponivel:'];
            foreach ($slots as $slotIndex => $slot) {
                $lines[] = sprintf('%d. %s', $slotIndex + 1, (string) ($slot['label'] ?? '-'));
            }

            return new ConversationResult(
                processed: true,
                reason: null,
                outboundMessages: [OutboundMessage::text($message->contactPhone, implode("\n", $lines), ['kind' => 'schedule_slot'])],
                flow: self::FLOW_SCHEDULE,
                step: self::STEP_SCHEDULE_AWAITING_SLOT,
                stateUpdates: $state
            );
        }

        if ($step === self::STEP_SCHEDULE_AWAITING_SLOT) {
            $slots = array_values((array) data_get($state, 'schedule.slots', []));
            $index = $this->intentRouter->parseSelectionNumber($text);

            if ($index === null || !isset($slots[$index - 1])) {
                return $this->repeatScheduleSlots($message, $state);
            }

            $selectedSlot = $slots[$index - 1];
            $state['schedule']['selected_slot'] = $selectedSlot;
            $state = $this->clearInvalidAttempts($state);

            $doctorName = (string) data_get($state, 'schedule.selected_doctor.name', 'Profissional');
            $startsAt = Carbon::parse((string) ($selectedSlot['starts_at'] ?? now()), $this->timezone());
            $confirmationText = sprintf("Confirmar agendamento com %s em %s as %s?\n1. Confirmar\n2. Cancelar", $doctorName, $startsAt->format('d/m'), $startsAt->format('H:i'));

            return new ConversationResult(
                processed: true,
                reason: null,
                outboundMessages: [OutboundMessage::text($message->contactPhone, $confirmationText, ['kind' => 'schedule_confirmation'])],
                flow: self::FLOW_SCHEDULE,
                step: self::STEP_SCHEDULE_AWAITING_CONFIRMATION,
                stateUpdates: $state
            );
        }

        if ($step === self::STEP_SCHEDULE_AWAITING_CONFIRMATION) {
            if ($this->intentRouter->isNegative($text)) {
                return $this->menuResult($message, $this->clearScheduleState($state), 'Agendamento cancelado.');
            }

            if (!$this->intentRouter->isAffirmative($text)) {
                return $this->invalidStepResult($message, $state, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_CONFIRMATION, "Resposta invalida.\n1. Confirmar\n2. Cancelar", 'schedule_confirmation_invalid');
            }

            $doctor = (array) data_get($state, 'schedule.selected_doctor', []);
            $slot = (array) data_get($state, 'schedule.selected_slot', []);

            try {
                $appointment = $this->appointmentService->createAppointment(
                    patient: $patient,
                    doctorId: (string) ($doctor['id'] ?? ''),
                    calendarId: (string) ($doctor['calendar_id'] ?? ''),
                    specialtyId: data_get($state, 'schedule.selected_specialty_id'),
                    appointmentTypeId: (string) ($doctor['appointment_type_id'] ?? ''),
                    startsAt: (string) ($slot['starts_at'] ?? ''),
                    endsAt: (string) ($slot['ends_at'] ?? '')
                );
            } catch (ValidationException $validationException) {
                $errorMessage = collect($validationException->errors())->flatten()->first() ?? $this->friendlyTechnicalErrorMessage();
                return $this->menuResult($message, $this->clearScheduleState($state), (string) $errorMessage);
            } catch (\Throwable $exception) {
                $this->logStep($message, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_CONFIRMATION, 'error:create_failed', 'error', ['error' => $exception->getMessage()]);
                return $this->menuResult($message, $this->clearScheduleState($state), $this->friendlyTechnicalErrorMessage());
            }

            $startsAt = $appointment->starts_at ? $appointment->starts_at->copy()->timezone($this->timezone()) : null;
            $baseSuccess = $this->message('appointment_created');
            $success = $startsAt
                ? trim($baseSuccess . ' ' . 'Para ' . $startsAt->format('d/m') . ' as ' . $startsAt->format('H:i') . '.')
                : $baseSuccess;

            return $this->menuResult($message, $this->clearScheduleState($state), $success);
        }

        return $this->menuResult($message, $this->clearScheduleState($state), 'Reiniciando fluxo de agendamento.');
    }

    private function startCancelFlow(InboundMessage $message, array $state, Patient $patient): ConversationResult
    {
        try {
            $appointments = $this->appointmentService->listCancelableAppointments($patient);
        } catch (\Throwable $exception) {
            $this->logStep($message, self::FLOW_MENU, self::STEP_MENU_AWAITING_OPTION, 'error:list_cancelable_failed', 'error', ['error' => $exception->getMessage()]);
            return $this->menuResult($message, $state, $this->friendlyTechnicalErrorMessage());
        }

        if ($appointments->isEmpty()) {
            return $this->menuResult($message, $state, 'Nao ha agendamentos disponiveis para cancelamento.');
        }

        $items = [];
        $lines = ['Escolha o agendamento que deseja cancelar:'];
        foreach ($appointments as $index => $appointment) {
            $startsAt = $appointment->starts_at ? $appointment->starts_at->copy()->timezone($this->timezone()) : null;
            $doctorName = trim((string) ($appointment->doctor?->user?->name_full ?? $appointment->doctor?->user?->name ?? 'Profissional'));
            $label = $doctorName . ' - ' . ($startsAt ? $startsAt->format('d/m H:i') : '-');
            $items[] = ['id' => (string) $appointment->id, 'label' => $label];
            $lines[] = sprintf('%d. %s', $index + 1, $label);
        }

        $state['cancel'] = ['appointments' => $items];
        $state = $this->clearInvalidAttempts($state);

        return new ConversationResult(
            processed: true,
            reason: null,
            outboundMessages: [OutboundMessage::text($message->contactPhone, implode("\n", $lines), ['kind' => 'cancel_list'])],
            flow: self::FLOW_CANCEL,
            step: self::STEP_CANCEL_AWAITING_APPOINTMENT,
            stateUpdates: $state
        );
    }

    private function handleCancelFlow(InboundMessage $message, array $state, Patient $patient, string $step, string $text): ConversationResult
    {
        if (!$this->domainService->isIntentEnabled(WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS)) {
            return $this->menuResult($message, $this->clearCancelState($state), $this->domainService->unavailableIntentMessage(WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS));
        }

        if ($step === self::STEP_CANCEL_AWAITING_APPOINTMENT) {
            $items = array_values((array) data_get($state, 'cancel.appointments', []));
            $index = $this->intentRouter->parseSelectionNumber($text);
            if ($index === null || !isset($items[$index - 1])) {
                return $this->repeatCancelList($message, $state);
            }

            $state['cancel']['selected'] = $items[$index - 1];
            $state = $this->clearInvalidAttempts($state);

            $textMessage = "Confirmar cancelamento do agendamento:\n" . (string) ($items[$index - 1]['label'] ?? 'Agendamento') . "\n1. Sim\n2. Nao";
            return new ConversationResult(
                processed: true,
                reason: null,
                outboundMessages: [OutboundMessage::text($message->contactPhone, $textMessage, ['kind' => 'cancel_confirmation'])],
                flow: self::FLOW_CANCEL,
                step: self::STEP_CANCEL_AWAITING_CONFIRMATION,
                stateUpdates: $state
            );
        }
        if ($step === self::STEP_CANCEL_AWAITING_CONFIRMATION) {
            if ($this->intentRouter->isNegative($text)) {
                return $this->menuResult($message, $this->clearCancelState($state), 'Cancelamento abortado.');
            }

            if (!$this->intentRouter->isAffirmative($text)) {
                return $this->invalidStepResult($message, $state, self::FLOW_CANCEL, self::STEP_CANCEL_AWAITING_CONFIRMATION, "Resposta invalida.\n1. Sim\n2. Nao", 'cancel_confirmation_invalid');
            }

            $appointmentId = (string) data_get($state, 'cancel.selected.id', '');
            $appointment = $this->appointmentService->listCancelableAppointments($patient, 50)->firstWhere('id', $appointmentId);

            if (!$appointment) {
                return $this->menuResult($message, $this->clearCancelState($state), 'Nao foi possivel localizar o agendamento para cancelamento.');
            }

            try {
                $cancelled = $this->appointmentService->cancelAppointment($appointment);
            } catch (ValidationException $validationException) {
                $errorMessage = collect($validationException->errors())->flatten()->first() ?? $this->friendlyTechnicalErrorMessage();
                return $this->menuResult($message, $this->clearCancelState($state), (string) $errorMessage);
            } catch (\Throwable $exception) {
                $this->logStep($message, self::FLOW_CANCEL, self::STEP_CANCEL_AWAITING_CONFIRMATION, 'error:cancel_failed', 'error', [
                    'appointment_id' => $appointmentId,
                    'error' => $exception->getMessage(),
                ]);
                return $this->menuResult($message, $this->clearCancelState($state), $this->friendlyTechnicalErrorMessage());
            }

            if (!$cancelled) {
                return $this->menuResult($message, $this->clearCancelState($state), 'Esse agendamento ja estava cancelado.');
            }

            return $this->menuResult($message, $this->clearCancelState($state), $this->message('appointment_canceled'));
        }

        return $this->menuResult($message, $this->clearCancelState($state), 'Reiniciando fluxo de cancelamento.');
    }

    private function resolveAuthenticatedPatient(array $state): ?Patient
    {
        $patientId = trim((string) ($state['patient_id'] ?? ''));
        if ($patientId === '') {
            return null;
        }

        return $this->patientService->findById($patientId);
    }

    private function startPatientIdentification(InboundMessage $message, array $state, string $intent): ConversationResult
    {
        $state[self::STATE_PENDING_INTENT] = $intent;
        unset($state[self::STATE_REGISTRATION]);
        $state = $this->clearInvalidAttempts($state);

        return new ConversationResult(
            processed: true,
            reason: null,
            outboundMessages: [OutboundMessage::text($message->contactPhone, 'Para continuar, informe seu CPF.', ['kind' => 'patient_identification_cpf'])],
            flow: self::FLOW_MENU,
            step: self::STEP_IDENTIFY_AWAITING_CPF,
            stateUpdates: $state
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function handleIdentifyByCpfStep(InboundMessage $message, array $state, string $text, array $settings): ConversationResult
    {
        $maxAttempts = max(1, (int) data_get($settings, 'identification.max_attempts', 3));
        $requireValidCpf = (bool) data_get($settings, 'identification.require_valid_cpf', true);

        if ($text === '') {
            $result = $this->invalidStepResult(
                $message,
                $state,
                self::FLOW_MENU,
                self::STEP_IDENTIFY_AWAITING_CPF,
                $this->message('invalid_cpf'),
                'patient_cpf_required'
            );

            return $this->resolveMaxIdentificationAttempts($message, $result, $maxAttempts);
        }

        $cpf = $this->patientService->normalizeCpf($text);
        if ($requireValidCpf && !$this->patientService->isValidCpf($cpf)) {
            $result = $this->invalidStepResult(
                $message,
                $state,
                self::FLOW_MENU,
                self::STEP_IDENTIFY_AWAITING_CPF,
                $this->message('invalid_cpf'),
                'patient_cpf_invalid'
            );

            return $this->resolveMaxIdentificationAttempts($message, $result, $maxAttempts);
        }

        if ($cpf === '') {
            $result = $this->invalidStepResult(
                $message,
                $state,
                self::FLOW_MENU,
                self::STEP_IDENTIFY_AWAITING_CPF,
                $this->message('invalid_cpf'),
                'patient_cpf_invalid'
            );

            return $this->resolveMaxIdentificationAttempts($message, $result, $maxAttempts);
        }

        $lookupOrder = (array) data_get($settings, 'identification.lookup_order', WhatsAppBotConfigService::DEFAULT_IDENTIFICATION_LOOKUP_ORDER);
        $patient = $this->resolvePatientByLookupOrder($cpf, $message->contactPhone, $lookupOrder);
        if ($patient instanceof Patient) {
            if (!$patient->is_active) {
                return $this->menuResult(
                    $message,
                    $this->resetConversationState($state),
                    'Seu cadastro foi localizado, mas esta inativo. Procure a clinica para regularizar.'
                );
            }

            $state = $this->clearPatientRegistrationState($state);
            $state['patient_id'] = (string) $patient->id;
            $state['patient_name'] = (string) $patient->full_name;

            return $this->continueIntentWithPatient($message, $state, $patient, null);
        }

        $fields = $this->patientService->registrationFieldDefinitions();
        $registrationState = [
            'fields' => $fields,
            'index' => 0,
            'data' => [],
            'identified_cpf' => $cpf,
            'pending_intent' => (string) ($state[self::STATE_PENDING_INTENT] ?? ''),
        ];

        $state[self::STATE_REGISTRATION] = $registrationState;
        $state = $this->clearInvalidAttempts($state);

        $introParts = [
            $this->message('patient_not_found'),
            $this->message('registration_start'),
            (string) data_get($fields, '0.prompt', 'Informe seu nome completo.'),
        ];
        $intro = implode("\n", array_values(array_filter(array_map(static fn ($part): string => trim((string) $part), $introParts), static fn (string $part): bool => $part !== '')));

        return new ConversationResult(
            processed: true,
            reason: null,
            outboundMessages: [OutboundMessage::text($message->contactPhone, $intro, ['kind' => 'patient_registration_start'])],
            flow: self::FLOW_MENU,
            step: self::STEP_REGISTER_AWAITING_FIELD,
            stateUpdates: $state
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function handleRegistrationStep(InboundMessage $message, array $state, string $text, array $settings): ConversationResult
    {
        unset($settings);
        $registration = is_array($state[self::STATE_REGISTRATION] ?? null) ? $state[self::STATE_REGISTRATION] : [];
        $fields = array_values((array) ($registration['fields'] ?? []));
        $index = (int) ($registration['index'] ?? 0);

        if (!isset($fields[$index])) {
            return $this->menuResult($message, $this->resetConversationState($state), 'Reiniciando cadastro. Escolha uma opcao para continuar.');
        }

        $field = (array) $fields[$index];
        $fieldKey = (string) ($field['key'] ?? '');
        if ($fieldKey === '') {
            return $this->menuResult($message, $this->resetConversationState($state), 'Reiniciando cadastro. Escolha uma opcao para continuar.');
        }

        $draft = is_array($registration['data'] ?? null) ? $registration['data'] : [];
        $validation = $this->patientService->validateRegistrationField($fieldKey, $text, $draft);

        if (!($validation['valid'] ?? false)) {
            $errorText = trim((string) ($validation['error'] ?? 'Valor invalido.')) ?: 'Valor invalido.';
            $prompt = (string) ($field['prompt'] ?? '');
            $messageText = $prompt !== '' ? $errorText . "\n" . $prompt : $errorText;

            return $this->invalidStepResult(
                $message,
                $state,
                self::FLOW_MENU,
                self::STEP_REGISTER_AWAITING_FIELD,
                $messageText,
                'patient_registration_invalid'
            );
        }

        $normalizedValue = (string) ($validation['value'] ?? '');
        $draft[$fieldKey] = $normalizedValue;

        if ($fieldKey === 'cpf') {
            $existing = $this->patientService->findByCpf($normalizedValue, false);
            if ($existing instanceof Patient) {
                if (!$existing->is_active) {
                    return $this->menuResult(
                        $message,
                        $this->resetConversationState($state),
                        'Seu cadastro foi localizado, mas esta inativo. Procure a clinica para regularizar.'
                    );
                }

                $state = $this->clearPatientRegistrationState($state);
                $state['patient_id'] = (string) $existing->id;
                $state['patient_name'] = (string) $existing->full_name;

                return $this->continueIntentWithPatient($message, $state, $existing, 'Cadastro localizado pelo CPF informado.');
            }
        }

        $nextIndex = $index + 1;
        if (!isset($fields[$nextIndex])) {
            try {
                $patient = $this->patientService->createFromRegistration($draft, $message->contactPhone);
            } catch (ValidationException $validationException) {
                $errorMessage = collect($validationException->errors())->flatten()->first() ?? 'Nao foi possivel concluir o cadastro.';
                return $this->invalidStepResult(
                    $message,
                    $state,
                    self::FLOW_MENU,
                    self::STEP_REGISTER_AWAITING_FIELD,
                    (string) $errorMessage,
                    'patient_registration_create_invalid'
                );
            } catch (\Throwable $exception) {
                $this->logStep($message, self::FLOW_MENU, self::STEP_REGISTER_AWAITING_FIELD, 'error:patient_registration_create_failed', 'error', [
                    'error' => $exception->getMessage(),
                ]);

                return $this->menuResult($message, $this->resetConversationState($state), $this->friendlyTechnicalErrorMessage());
            }

            $state = $this->clearPatientRegistrationState($state);
            $state['patient_id'] = (string) $patient->id;
            $state['patient_name'] = (string) $patient->full_name;

            return $this->continueIntentWithPatient($message, $state, $patient, $this->message('registration_completed'));
        }

        $registration['data'] = $draft;
        $registration['index'] = $nextIndex;
        $state[self::STATE_REGISTRATION] = $registration;
        $state = $this->clearInvalidAttempts($state);

        $nextPrompt = (string) data_get($fields[$nextIndex], 'prompt', 'Informe o proximo campo.');

        return new ConversationResult(
            processed: true,
            reason: null,
            outboundMessages: [OutboundMessage::text($message->contactPhone, $nextPrompt, ['kind' => 'patient_registration_field'])],
            flow: self::FLOW_MENU,
            step: self::STEP_REGISTER_AWAITING_FIELD,
            stateUpdates: $state
        );
    }

    private function continueIntentWithPatient(
        InboundMessage $message,
        array $state,
        Patient $patient,
        ?string $prefix
    ): ConversationResult {
        $intent = trim((string) ($state[self::STATE_PENDING_INTENT] ?? ''));
        unset($state[self::STATE_PENDING_INTENT]);

        if ($intent === WhatsAppBotIntentRouter::INTENT_SCHEDULE) {
            $result = $this->startScheduleFlow($message, $state);
            if ($prefix === null || trim($prefix) === '' || $result->outboundMessages === []) {
                return $result;
            }

            $outbound = $result->outboundMessages;
            $first = $outbound[0];
            $outbound[0] = OutboundMessage::text(
                $message->contactPhone,
                trim($prefix) . "\n\n" . $first->text,
                $first->meta
            );

            return new ConversationResult(
                processed: $result->processed,
                reason: $result->reason,
                outboundMessages: $outbound,
                flow: $result->flow,
                step: $result->step,
                stateUpdates: $result->stateUpdates
            );
        }

        if ($intent === WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS) {
            try {
                $appointments = $this->appointmentService->listUpcomingAppointments($patient);
            } catch (\Throwable $exception) {
                $this->logStep($message, self::FLOW_MENU, self::STEP_MENU_AWAITING_OPTION, 'error:view_appointments_failed', 'error', [
                    'error' => $exception->getMessage(),
                ]);

                return $this->menuResult($message, $state, $this->friendlyTechnicalErrorMessage());
            }

            $prefixParts = [];
            if ($prefix !== null && trim($prefix) !== '') {
                $prefixParts[] = trim($prefix);
            }

            if ($appointments->isEmpty()) {
                $prefixParts[] = 'Voce nao possui agendamentos futuros.';
                return $this->menuResult($message, $state, implode("\n", $prefixParts));
            }

            $lines = ['Seus proximos agendamentos:'];
            foreach ($appointments as $index => $appointment) {
                $doctorName = trim((string) ($appointment->doctor?->user?->name_full ?? $appointment->doctor?->user?->name ?? 'Profissional'));
                $startsAt = $appointment->starts_at ? $appointment->starts_at->copy()->timezone($this->timezone()) : null;
                $lines[] = sprintf('%d. %s - %s', $index + 1, $doctorName, $startsAt ? $startsAt->format('d/m H:i') : '-');
            }

            $prefixParts[] = implode("\n", $lines);
            return $this->menuResult($message, $state, implode("\n\n", array_filter($prefixParts)));
        }

        if ($prefix !== null && trim($prefix) !== '') {
            return $this->menuResult($message, $state, trim($prefix));
        }

        return $this->menuResult($message, $state);
    }

    private function clearPatientRegistrationState(array $state): array
    {
        unset($state[self::STATE_REGISTRATION]);

        return $state;
    }

    private function menuResult(InboundMessage $message, array $state, ?string $prefix = null, ?bool $forceShowMenu = null): ConversationResult
    {
        $settings = $this->runtimeSettings !== [] ? $this->runtimeSettings : $this->configService->getSettings();
        $welcome = trim((string) data_get($settings, 'messages.welcome', data_get($settings, 'welcome_message', '')));
        $showConfiguredWelcome = !($state['welcome_sent'] ?? false) && $welcome !== '';
        $showMenuAgainAfterAction = (bool) data_get($settings, 'menu.show_again_after_action', true);

        $state['welcome_sent'] = true;
        $state['schedule'] = $state['schedule'] ?? [];
        $state['cancel'] = $state['cancel'] ?? [];
        $state = $this->clearInvalidAttempts($state);

        $parts = [];
        if ($showConfiguredWelcome) {
            $parts[] = $welcome;
        }
        if ($prefix !== null && trim($prefix) !== '') {
            $parts[] = trim($prefix);
        }
        $shouldShowMenu = $forceShowMenu ?? true;
        if ($forceShowMenu === null && $prefix !== null && trim($prefix) !== '') {
            $shouldShowMenu = $showMenuAgainAfterAction;
        }

        if ($shouldShowMenu || $parts === []) {
            $parts[] = $this->menuText($settings);
        }

        return new ConversationResult(
            processed: true,
            reason: null,
            outboundMessages: [OutboundMessage::text($message->contactPhone, implode("\n\n", $parts), ['kind' => 'menu'])],
            flow: self::FLOW_MENU,
            step: self::STEP_MENU_AWAITING_OPTION,
            stateUpdates: $state
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function fallbackMenuResult(InboundMessage $message, array $state, array $settings): ConversationResult
    {
        $fallback = $this->message('fallback');
        $returnAfterFallback = (bool) data_get($settings, 'menu.return_after_fallback', true);

        if ($returnAfterFallback) {
            return $this->menuResult($message, $state, $fallback, true);
        }

        return $this->invalidStepResult($message, $state, self::FLOW_MENU, self::STEP_MENU_AWAITING_OPTION, $fallback, 'fallback');
    }

    private function promptDoctorSelection(InboundMessage $message, array $state): ConversationResult
    {
        $specialtyId = data_get($state, 'schedule.selected_specialty_id');
        try {
            $doctors = $this->appointmentService->listDoctors($specialtyId)->values()->all();
        } catch (\Throwable $exception) {
            $this->logStep($message, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_DOCTOR, 'error:list_doctors_failed', 'error', ['error' => $exception->getMessage()]);
            return $this->menuResult($message, $this->clearScheduleState($state), $this->friendlyTechnicalErrorMessage());
        }

        if ($doctors === []) {
            return $this->menuResult($message, $this->clearScheduleState($state), $this->message('no_slots_available'));
        }

        $state['schedule']['doctors'] = $doctors;
        $state = $this->clearInvalidAttempts($state);

        $lines = ['Escolha o profissional:'];
        foreach ($doctors as $index => $doctor) {
            $lines[] = sprintf('%d. %s', $index + 1, (string) ($doctor['name'] ?? 'Profissional'));
        }

        return new ConversationResult(
            processed: true,
            reason: null,
            outboundMessages: [OutboundMessage::text($message->contactPhone, implode("\n", $lines), ['kind' => 'schedule_doctor'])],
            flow: self::FLOW_SCHEDULE,
            step: self::STEP_SCHEDULE_AWAITING_DOCTOR,
            stateUpdates: $state
        );
    }

    private function repeatScheduleSpecialty(InboundMessage $message, array $state): ConversationResult
    {
        $lines = ['Opcao invalida. Escolha a especialidade pelo numero:'];
        foreach ((array) data_get($state, 'schedule.specialties', []) as $index => $specialty) {
            $lines[] = sprintf('%d. %s', $index + 1, (string) ($specialty['name'] ?? 'Especialidade'));
        }

        return $this->invalidStepResult($message, $state, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_SPECIALTY, implode("\n", $lines), 'schedule_specialty_retry');
    }

    private function repeatScheduleDoctors(InboundMessage $message, array $state): ConversationResult
    {
        $lines = ['Opcao invalida. Escolha o profissional pelo numero:'];
        foreach ((array) data_get($state, 'schedule.doctors', []) as $index => $doctor) {
            $lines[] = sprintf('%d. %s', $index + 1, (string) ($doctor['name'] ?? 'Profissional'));
        }

        return $this->invalidStepResult($message, $state, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_DOCTOR, implode("\n", $lines), 'schedule_doctor_retry');
    }

    private function repeatScheduleSlots(InboundMessage $message, array $state): ConversationResult
    {
        $lines = ['Opcao invalida. Escolha o horario pelo numero:'];
        foreach ((array) data_get($state, 'schedule.slots', []) as $index => $slot) {
            $lines[] = sprintf('%d. %s', $index + 1, (string) ($slot['label'] ?? '-'));
        }

        return $this->invalidStepResult($message, $state, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_SLOT, implode("\n", $lines), 'schedule_slot_retry');
    }

    private function repeatCancelList(InboundMessage $message, array $state): ConversationResult
    {
        $lines = ['Opcao invalida. Escolha qual agendamento deseja cancelar:'];
        foreach ((array) data_get($state, 'cancel.appointments', []) as $index => $item) {
            $lines[] = sprintf('%d. %s', $index + 1, (string) ($item['label'] ?? 'Agendamento'));
        }

        return $this->invalidStepResult($message, $state, self::FLOW_CANCEL, self::STEP_CANCEL_AWAITING_APPOINTMENT, implode("\n", $lines), 'cancel_retry');
    }
    private function clearScheduleState(array $state): array
    {
        $state['schedule'] = [];
        return $state;
    }

    private function clearCancelState(array $state): array
    {
        $state['cancel'] = [];
        return $state;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function menuText(array $settings): string
    {
        $tenant = tenant();
        $clinicName = trim((string) ($tenant?->trade_name ?: $tenant?->legal_name ?: 'Clinica'));
        $options = $this->limitedEnabledMenuOptions($settings);

        if ($options === []) {
            $options = WhatsAppBotConfigService::DEFAULT_MENU_OPTIONS;
        }

        $lines = ["Ola, sou o assistente da Clinica {$clinicName}.", 'Escolha uma opcao:'];
        foreach ($options as $index => $option) {
            $lines[] = sprintf('%d. %s', $index + 1, (string) ($option['label'] ?? 'Opcao'));
        }

        return implode("\n", $lines);
    }

    private function parseDateInput(string $value): ?Carbon
    {
        $text = trim(strtolower($value));
        if ($text === '') {
            return null;
        }

        $timezone = $this->timezone();
        $today = Carbon::now($timezone)->startOfDay();

        $aliases = [
            'hoje' => 0,
            'amanha' => 1,
        ];

        if (array_key_exists($text, $aliases)) {
            return $today->copy()->addDays($aliases[$text]);
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $text, $matches) === 1) {
            try {
                return Carbon::createFromFormat('d/m/Y', sprintf('%s/%s/%s', $matches[1], $matches[2], $matches[3]), $timezone)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        if (preg_match('/^(\d{2})\/(\d{2})$/', $text, $matches) === 1) {
            try {
                return Carbon::createFromFormat('d/m/Y', sprintf('%s/%s/%s', $matches[1], $matches[2], $today->year), $timezone)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $text) === 1) {
            try {
                return Carbon::createFromFormat('Y-m-d', $text, $timezone)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function timezone(): string
    {
        return (string) tenant_setting('timezone', config('app.timezone', 'America/Campo_Grande'));
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function logStep(InboundMessage $message, string $flow, string $step, string $action, string $result = 'success', array $extra = []): void
    {
        $level = match ($result) {
            'error' => 'error',
            'warning' => 'warning',
            default => 'info',
        };

        Log::log($level, 'whatsapp_bot.flow.step', array_merge([
            'tenant_id' => (string) (tenant()?->id ?? ''),
            'provider' => $message->provider,
            'phone' => $message->contactPhone,
            'flow' => $flow,
            'step' => $step,
            'action' => $action,
            'result' => $result,
            'message_type' => $message->messageType,
        ], $extra));
    }

    private function shouldResetSessionContext(string $flow, string $step): bool
    {
        $allowedFlows = [self::FLOW_MENU, self::FLOW_SCHEDULE, self::FLOW_CANCEL, 'root'];
        $allowedSteps = [
            self::STEP_MENU_AWAITING_OPTION,
            self::STEP_IDENTIFY_AWAITING_CPF,
            self::STEP_REGISTER_AWAITING_FIELD,
            self::STEP_IDENTIFY_AWAITING_NAME,
            self::STEP_SCHEDULE_AWAITING_SPECIALTY,
            self::STEP_SCHEDULE_AWAITING_DOCTOR,
            self::STEP_SCHEDULE_AWAITING_DATE,
            self::STEP_SCHEDULE_AWAITING_SLOT,
            self::STEP_SCHEDULE_AWAITING_CONFIRMATION,
            self::STEP_CANCEL_AWAITING_APPOINTMENT,
            self::STEP_CANCEL_AWAITING_CONFIRMATION,
            'initial',
        ];

        if (!in_array($flow, $allowedFlows, true)) {
            return true;
        }

        return !in_array($step, $allowedSteps, true);
    }

    private function resetConversationState(array $state): array
    {
        $state['schedule'] = [];
        $state['cancel'] = [];
        unset($state[self::STATE_REGISTRATION], $state[self::STATE_PENDING_INTENT], $state['patient_id'], $state['patient_name']);

        return $this->clearInvalidAttempts($state);
    }

    private function invalidStepResult(InboundMessage $message, array $state, string $flow, string $step, string $text, string $kind): ConversationResult
    {
        $state = $this->registerInvalidAttempt($state, $flow, $step);
        $attempts = (int) data_get($state, '_meta.invalid_attempts', 0);
        $maxAttempts = max(1, (int) data_get($this->runtimeSettings, 'identification.max_attempts', 3));

        if ($attempts >= $maxAttempts) {
            $text .= "\n\n" . $this->message('back_to_menu');
        }

        $this->logStep($message, $flow, $step, 'invalid_input:' . $kind, 'warning', [
            'invalid_attempts' => $attempts,
        ]);

        return new ConversationResult(
            processed: true,
            reason: null,
            outboundMessages: [OutboundMessage::text($message->contactPhone, $text, ['kind' => $kind, 'invalid_attempts' => $attempts])],
            flow: $flow,
            step: $step,
            stateUpdates: $state
        );
    }

    private function registerInvalidAttempt(array $state, string $flow, string $step): array
    {
        $meta = is_array($state['_meta'] ?? null) ? $state['_meta'] : [];
        $meta['invalid_attempts'] = (int) ($meta['invalid_attempts'] ?? 0) + 1;
        $meta['invalid_flow'] = $flow;
        $meta['invalid_step'] = $step;
        $meta['invalid_at'] = now()->toDateTimeString();

        $state['_meta'] = $meta;

        return $state;
    }

    private function clearInvalidAttempts(array $state): array
    {
        $meta = is_array($state['_meta'] ?? null) ? $state['_meta'] : [];
        $meta['invalid_attempts'] = 0;

        $state['_meta'] = $meta;

        return $state;
    }

    private function friendlyTechnicalErrorMessage(): string
    {
        return $this->message('internal_error');
    }

    private function resolveMaxIdentificationAttempts(
        InboundMessage $message,
        ConversationResult $result,
        int $maxAttempts
    ): ConversationResult {
        $attempts = (int) data_get($result->stateUpdates, '_meta.invalid_attempts', 0);
        if ($attempts < max(1, $maxAttempts)) {
            return $result;
        }

        return $this->menuResult(
            $message,
            $this->resetConversationState($result->stateUpdates),
            $this->message('back_to_menu'),
            true
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function hasSessionTimedOut(WhatsAppBotSession $session, array $settings): bool
    {
        $now = now();
        $absoluteTimeoutMinutes = max(1, (int) data_get($settings, 'session.absolute_timeout_minutes', 240));
        $idleTimeoutMinutes = max(1, (int) data_get($settings, 'session.idle_timeout_minutes', 30));
        $endOnInactivity = (bool) data_get($settings, 'session.end_on_inactivity', true);

        $createdAt = $session->created_at;
        if ($createdAt instanceof Carbon && $createdAt->lt($now->copy()->subMinutes($absoluteTimeoutMinutes))) {
            return true;
        }

        if (!$endOnInactivity) {
            return false;
        }

        $lastInboundAt = $session->last_inbound_message_at;
        if (!($lastInboundAt instanceof Carbon)) {
            return false;
        }

        return $lastInboundAt->lt($now->copy()->subMinutes($idleTimeoutMinutes));
    }

    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function applySessionTimeoutState(array $state, array $settings): array
    {
        $clearContextOnEnd = (bool) data_get($settings, 'session.clear_context_on_end', true);
        $allowResumePrevious = (bool) data_get($settings, 'session.allow_resume_previous', false);

        if ($clearContextOnEnd || !$allowResumePrevious) {
            $state = $this->resetConversationState($state);
        } else {
            $state = $this->clearInvalidAttempts($state);
        }

        $meta = is_array($state['_meta'] ?? null) ? $state['_meta'] : [];
        $meta['last_end_reason'] = 'inactivity_timeout';
        $meta['last_end_at'] = now()->toDateTimeString();
        $state['_meta'] = $meta;

        return $state;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function resolveIntentFromMessage(InboundMessage $message, array $settings): string
    {
        $selection = $this->intentRouter->parseSelectionNumber((string) $message->text);
        if ($selection !== null) {
            $options = $this->limitedEnabledMenuOptions($settings);
            $selectedOption = $options[$selection - 1] ?? null;

            if (is_array($selectedOption)) {
                $intent = $this->intentFromMenuOptionId((string) ($selectedOption['id'] ?? ''));
                if ($intent !== null) {
                    return $intent;
                }
            }

            return WhatsAppBotIntentRouter::INTENT_UNKNOWN;
        }

        return $this->intentRouter->resolve($message);
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<int, array{id:string,label:string,enabled:bool,order:int,requires_identification:bool}>
     */
    private function limitedEnabledMenuOptions(array $settings): array
    {
        $options = $this->enabledMenuOptions($settings);
        $maxOptions = max(1, (int) data_get($settings, 'menu.max_options', 6));

        return array_slice($options, 0, $maxOptions);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function isIntentEnabledInMenu(string $intent, array $settings): bool
    {
        $options = (array) data_get($settings, 'menu.options', []);
        $targetId = $this->menuOptionIdFromIntent($intent);
        if ($targetId === null) {
            return true;
        }

        foreach ($options as $option) {
            if (!is_array($option)) {
                continue;
            }

            $optionId = $this->normalizeMenuOptionId((string) ($option['id'] ?? ''));
            if ($optionId !== $targetId) {
                continue;
            }

            return (bool) ($option['enabled'] ?? false);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function requiresIdentificationForIntent(string $intent, array $settings): bool
    {
        $targetId = $this->menuOptionIdFromIntent($intent);
        $options = (array) data_get($settings, 'menu.options', []);

        foreach ($options as $option) {
            if (!is_array($option)) {
                continue;
            }

            $optionId = $this->normalizeMenuOptionId((string) ($option['id'] ?? ''));
            if ($targetId !== null && $optionId === $targetId && array_key_exists('requires_identification', $option)) {
                return (bool) $option['requires_identification'];
            }
        }

        $requiredIntents = array_values(array_map(
            static fn ($item): string => trim(strtolower((string) $item)),
            (array) data_get($settings, 'identification.require_cpf_for_intents', WhatsAppBotConfigService::DEFAULT_REQUIRE_CPF_FOR_INTENTS)
        ));

        return in_array(trim(strtolower($intent)), $requiredIntents, true);
    }

    /**
     * @param array<int, string> $lookupOrder
     */
    private function resolvePatientByLookupOrder(?string $cpf, string $normalizedPhone, array $lookupOrder): ?Patient
    {
        foreach ($lookupOrder as $lookupType) {
            $normalizedLookup = trim(strtolower((string) $lookupType));
            if ($normalizedLookup === 'cpf' && $cpf !== null && trim($cpf) !== '') {
                $patient = $this->patientService->findByCpf($cpf, false);
                if ($patient instanceof Patient) {
                    return $patient;
                }
            }

            if ($normalizedLookup === 'phone') {
                $patient = $this->patientService->findByNormalizedPhone($normalizedPhone);
                if ($patient instanceof Patient) {
                    return $patient;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<int, array{id:string,label:string,enabled:bool,order:int,requires_identification:bool}>
     */
    private function enabledMenuOptions(array $settings): array
    {
        $rawOptions = (array) data_get($settings, 'menu.options', []);
        $normalized = [];

        foreach ($rawOptions as $option) {
            if (!is_array($option)) {
                continue;
            }

            $id = $this->normalizeMenuOptionId((string) ($option['id'] ?? ''));
            if ($id === '') {
                continue;
            }

            $normalized[] = [
                'id' => $id,
                'label' => trim((string) ($option['label'] ?? '')) ?: $this->defaultMenuLabelById($id),
                'enabled' => (bool) ($option['enabled'] ?? false),
                'order' => max(1, (int) ($option['order'] ?? 99)),
                'requires_identification' => (bool) ($option['requires_identification'] ?? true),
            ];
        }

        if ($normalized === []) {
            $normalized = WhatsAppBotConfigService::DEFAULT_MENU_OPTIONS;
        }

        usort($normalized, static function (array $left, array $right): int {
            $orderCompare = ((int) ($left['order'] ?? 0)) <=> ((int) ($right['order'] ?? 0));
            if ($orderCompare !== 0) {
                return $orderCompare;
            }

            return strcmp((string) ($left['id'] ?? ''), (string) ($right['id'] ?? ''));
        });

        return array_values(array_filter($normalized, static fn (array $option): bool => (bool) ($option['enabled'] ?? false)));
    }

    private function intentFromMenuOptionId(string $id): ?string
    {
        return match ($this->normalizeMenuOptionId($id)) {
            'schedule' => WhatsAppBotIntentRouter::INTENT_SCHEDULE,
            'view_appointments' => WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS,
            'cancel_appointments' => WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS,
            default => null,
        };
    }

    private function menuOptionIdFromIntent(string $intent): ?string
    {
        return match ($intent) {
            WhatsAppBotIntentRouter::INTENT_SCHEDULE => 'schedule',
            WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS => 'view_appointments',
            WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS => 'cancel_appointments',
            default => null,
        };
    }

    private function normalizeMenuOptionId(string $id): string
    {
        $normalized = trim(strtolower($id));

        return match ($normalized) {
            'schedule' => 'schedule',
            'view_appointments' => 'view_appointments',
            'cancel_appointments', 'cancel_appointment' => 'cancel_appointments',
            default => '',
        };
    }

    private function defaultMenuLabelById(string $id): string
    {
        return match ($id) {
            'schedule' => 'Agendar consulta',
            'view_appointments' => 'Ver meus agendamentos',
            'cancel_appointments' => 'Cancelar agendamento',
            default => 'Opcao',
        };
    }

    private function message(string $key): string
    {
        $messages = (array) data_get($this->runtimeSettings, 'messages', []);
        $value = trim((string) ($messages[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }

        return match ($key) {
            'fallback' => WhatsAppBotConfigService::DEFAULT_FALLBACK_MESSAGE,
            'invalid_cpf' => WhatsAppBotConfigService::DEFAULT_INVALID_CPF_MESSAGE,
            'patient_not_found' => WhatsAppBotConfigService::DEFAULT_PATIENT_NOT_FOUND_MESSAGE,
            'registration_start' => WhatsAppBotConfigService::DEFAULT_REGISTRATION_START_MESSAGE,
            'registration_completed' => WhatsAppBotConfigService::DEFAULT_REGISTRATION_COMPLETED_MESSAGE,
            'internal_error' => WhatsAppBotConfigService::DEFAULT_INTERNAL_ERROR_MESSAGE,
            'no_slots_available' => WhatsAppBotConfigService::DEFAULT_NO_SLOTS_MESSAGE,
            'appointment_created' => WhatsAppBotConfigService::DEFAULT_APPOINTMENT_CREATED_MESSAGE,
            'appointment_canceled' => WhatsAppBotConfigService::DEFAULT_APPOINTMENT_CANCELED_MESSAGE,
            'back_to_menu' => WhatsAppBotConfigService::DEFAULT_BACK_TO_MENU_MESSAGE,
            'inactivity_exit' => WhatsAppBotConfigService::DEFAULT_INACTIVITY_EXIT_MESSAGE,
            default => '',
        };
    }
}
