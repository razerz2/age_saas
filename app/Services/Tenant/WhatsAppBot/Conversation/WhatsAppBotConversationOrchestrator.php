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
    private const STEP_IDENTIFY_AWAITING_NAME = 'identify.awaiting_name';
    private const STEP_SCHEDULE_AWAITING_SPECIALTY = 'schedule.awaiting_specialty';
    private const STEP_SCHEDULE_AWAITING_DOCTOR = 'schedule.awaiting_doctor';
    private const STEP_SCHEDULE_AWAITING_DATE = 'schedule.awaiting_date';
    private const STEP_SCHEDULE_AWAITING_SLOT = 'schedule.awaiting_slot';
    private const STEP_SCHEDULE_AWAITING_CONFIRMATION = 'schedule.awaiting_confirmation';
    private const STEP_CANCEL_AWAITING_APPOINTMENT = 'cancel.awaiting_appointment';
    private const STEP_CANCEL_AWAITING_CONFIRMATION = 'cancel.awaiting_confirmation';

    private const INVALID_ATTEMPTS_THRESHOLD = 3;

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

        $flow = (string) ($session->current_flow ?? self::FLOW_MENU);
        $step = (string) ($session->current_step ?? self::STEP_MENU_AWAITING_OPTION);

        $this->logStep($message, $flow, $step, 'incoming', 'success');

        if ($this->shouldResetSessionContext($flow, $step)) {
            return $this->menuResult($message, $this->resetConversationState($state), 'Sessao reiniciada por seguranca.');
        }

        if ($this->intentRouter->isResetCommand($text)) {
            return $this->menuResult($message, $this->resetConversationState($state), 'Fluxo reiniciado.');
        }

        $patient = $this->patientService->findByNormalizedPhone($message->contactPhone);
        if (!$patient) {
            return $this->handleMissingPatient($session, $message, $state, $text);
        }

        $state['patient_id'] = (string) $patient->id;
        $state['patient_name'] = (string) $patient->full_name;

        if ($step === self::STEP_IDENTIFY_AWAITING_NAME) {
            return $this->menuResult($message, $state);
        }

        if ($flow === self::FLOW_SCHEDULE) {
            return $this->handleScheduleFlow($message, $state, $patient, $step, $text);
        }

        if ($flow === self::FLOW_CANCEL) {
            return $this->handleCancelFlow($message, $state, $patient, $step, $text);
        }

        return $this->handleMenu($message, $state, $patient, $text);
    }

    private function handleMenu(InboundMessage $message, array $state, Patient $patient, string $text): ConversationResult
    {
        if ($text === '') {
            return $this->menuResult($message, $state);
        }

        $intent = $this->intentRouter->resolve($message);
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
            return $this->fallbackMenuResult($message, $state);
        }

        if (!$this->domainService->isIntentEnabled($intent)) {
            return $this->menuResult($message, $state, $this->domainService->unavailableIntentMessage($intent));
        }

        if ($intent === WhatsAppBotIntentRouter::INTENT_SCHEDULE) {
            return $this->startScheduleFlow($message, $state);
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
            return $this->startCancelFlow($message, $state, $patient);
        }

        return $this->fallbackMenuResult($message, $state);
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
                return $this->invalidStepResult($message, $state, self::FLOW_SCHEDULE, self::STEP_SCHEDULE_AWAITING_DATE, 'Nao encontrei horarios disponiveis nessa data. Informe outra data.', 'schedule_no_slots');
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
            $success = $startsAt ? 'Agendamento confirmado para ' . $startsAt->format('d/m') . ' as ' . $startsAt->format('H:i') . '.' : 'Agendamento confirmado com sucesso.';

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

            return $this->menuResult($message, $this->clearCancelState($state), 'Agendamento cancelado com sucesso.');
        }

        return $this->menuResult($message, $this->clearCancelState($state), 'Reiniciando fluxo de cancelamento.');
    }

    private function handleMissingPatient(WhatsAppBotSession $session, InboundMessage $message, array $state, string $text): ConversationResult
    {
        $step = (string) ($session->current_step ?? '');
        if ($step === self::STEP_IDENTIFY_AWAITING_NAME && $text !== '') {
            if (strlen(trim($text)) < 3) {
                return $this->invalidStepResult($message, $state, self::FLOW_MENU, self::STEP_IDENTIFY_AWAITING_NAME, 'Nome invalido. Informe seu nome completo para continuar.', 'patient_name_invalid');
            }

            try {
                $patient = $this->patientService->createFromPhoneAndName($message->contactPhone, $text);
            } catch (\Throwable $exception) {
                $this->logStep($message, self::FLOW_MENU, self::STEP_IDENTIFY_AWAITING_NAME, 'error:patient_create_failed', 'error', ['error' => $exception->getMessage()]);
                return $this->invalidStepResult($message, $state, self::FLOW_MENU, self::STEP_IDENTIFY_AWAITING_NAME, 'Nao consegui criar seu cadastro agora. Envie seu nome novamente.', 'patient_create_error');
            }

            $state['patient_id'] = (string) $patient->id;
            $state['patient_name'] = (string) $patient->full_name;

            return $this->menuResult($message, $state, 'Cadastro criado com sucesso, ' . $patient->full_name . '.');
        }

        return new ConversationResult(
            processed: true,
            reason: null,
            outboundMessages: [OutboundMessage::text($message->contactPhone, 'Nao localizei seu cadastro. Para continuar, informe seu nome completo.', ['kind' => 'patient_identification'])],
            flow: self::FLOW_MENU,
            step: self::STEP_IDENTIFY_AWAITING_NAME,
            stateUpdates: $this->clearInvalidAttempts($state)
        );
    }

    private function menuResult(InboundMessage $message, array $state, ?string $prefix = null): ConversationResult
    {
        $settings = $this->configService->getSettings();
        $welcome = trim((string) ($settings['welcome_message'] ?? ''));
        $showConfiguredWelcome = !($state['welcome_sent'] ?? false) && $welcome !== '';

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
        $parts[] = $this->menuText();

        return new ConversationResult(
            processed: true,
            reason: null,
            outboundMessages: [OutboundMessage::text($message->contactPhone, implode("\n\n", $parts), ['kind' => 'menu'])],
            flow: self::FLOW_MENU,
            step: self::STEP_MENU_AWAITING_OPTION,
            stateUpdates: $state
        );
    }

    private function fallbackMenuResult(InboundMessage $message, array $state): ConversationResult
    {
        return $this->invalidStepResult($message, $state, self::FLOW_MENU, self::STEP_MENU_AWAITING_OPTION, "Nao entendi. Escolha uma opcao:\n1. Agendar\n2. Ver agendamentos\n3. Cancelar", 'fallback');
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
            return $this->menuResult($message, $this->clearScheduleState($state), 'Nao ha profissionais disponiveis para essa selecao.');
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

    private function menuText(): string
    {
        $tenant = tenant();
        $clinicName = trim((string) ($tenant?->trade_name ?: $tenant?->legal_name ?: 'Clinica'));

        return "Ola, sou o assistente da Clinica {$clinicName}.\nEscolha uma opcao:\n1. Agendar consulta\n2. Ver meus agendamentos\n3. Cancelar agendamento";
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

        return $this->clearInvalidAttempts($state);
    }

    private function invalidStepResult(InboundMessage $message, array $state, string $flow, string $step, string $text, string $kind): ConversationResult
    {
        $state = $this->registerInvalidAttempt($state, $flow, $step);
        $attempts = (int) data_get($state, '_meta.invalid_attempts', 0);

        if ($attempts >= self::INVALID_ATTEMPTS_THRESHOLD) {
            $text .= "\n\nDigite 0 para voltar ao menu principal.";
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
        return 'Ocorreu um problema ao processar sua solicitacao. Tente novamente ou digite 0 para voltar ao menu.';
    }
}
