<?php

use App\Http\Requests\Tenant\StorePatientRequest;
use App\Models\Tenant\Patient;
use App\Models\Tenant\WhatsAppBotSession;
use App\Services\Tenant\WhatsAppBot\Conversation\WhatsAppBotConversationOrchestrator;
use App\Services\Tenant\WhatsAppBot\Conversation\WhatsAppBotIntentRouter;
use App\Services\Tenant\WhatsAppBot\Domain\WhatsAppBotAppointmentService;
use App\Services\Tenant\WhatsAppBot\Domain\WhatsAppBotDomainService;
use App\Services\Tenant\WhatsAppBot\Domain\WhatsAppBotPatientService;
use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBotConfigService;
use Tests\TestCase;

uses(TestCase::class);

function botInbound(string $text, string $phone = '5567999998888'): InboundMessage
{
    return new InboundMessage(
        provider: 'waha',
        channel: 'whatsapp',
        contactPhone: $phone,
        contactIdentifier: $phone,
        messageType: 'chat',
        text: $text,
        externalMessageId: 'msg-test',
        payload: []
    );
}

/**
 * @param array<string, mixed> $state
 */
function botSession(string $flow = 'menu', string $step = 'menu.awaiting_option', array $state = []): WhatsAppBotSession
{
    $session = new WhatsAppBotSession();
    $session->current_flow = $flow;
    $session->current_step = $step;
    $session->state = $state;

    return $session;
}

function makeOrchestrator(
    WhatsAppBotConfigService $configService,
    WhatsAppBotDomainService $domainService,
    WhatsAppBotPatientService $patientService,
    WhatsAppBotAppointmentService $appointmentService
): WhatsAppBotConversationOrchestrator {
    return new WhatsAppBotConversationOrchestrator(
        configService: $configService,
        domainService: $domainService,
        intentRouter: app(WhatsAppBotIntentRouter::class),
        patientService: $patientService,
        appointmentService: $appointmentService
    );
}

it('shows greeting and menu on oi without triggering registration flow', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')->never();

    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $result = $orchestrator->handle(botSession('root', 'initial'), botInbound('Oi'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and($result->outboundMessages[0]->text)->toContain('Escolha uma opcao')
        ->and($result->outboundMessages[0]->text)->toContain('1. Agendar consulta')
        ->and(strtolower($result->outboundMessages[0]->text))->not->toContain('informe seu nome')
        ->and(strtolower($result->outboundMessages[0]->text))->not->toContain('nao localizei seu cadastro');
});

it('recognizes configured entry keyword and returns to main menu', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => ['Inicio personalizado'],
        'exit_keywords' => ['encerrar personalizado'],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);

    $session = botSession('schedule', 'schedule.awaiting_date', [
        'schedule' => ['selected_specialty_id' => 'sp-1'],
        'patient_id' => 'patient-1',
    ]);

    $result = $orchestrator->handle($session, botInbound('inicio personalizado'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and($result->outboundMessages[0]->text)->toContain('Escolha uma opcao')
        ->and(($result->stateUpdates['schedule'] ?? null))->toBe([])
        ->and(isset($result->stateUpdates['patient_id']))->toBeFalse();
});

it('recognizes configured exit keyword and clears conversation state', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => ['inicio personalizado'],
        'exit_keywords' => ['Encerrar personalizado'],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);

    $session = botSession('cancel', 'cancel.awaiting_confirmation', [
        'schedule' => ['selected_specialty_id' => 'sp-1'],
        'cancel' => ['selected' => ['id' => 'apt-1']],
        'patient_id' => 'patient-1',
    ]);

    $result = $orchestrator->handle($session, botInbound('encerrar personalizado'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and($result->outboundMessages[0]->text)->toContain('Atendimento encerrado')
        ->and(($result->stateUpdates['schedule'] ?? null))->toBe([])
        ->and(($result->stateUpdates['cancel'] ?? null))->toBe([])
        ->and(isset($result->stateUpdates['patient_id']))->toBeFalse();
});

it('asks cpf when user chooses option 1 schedule', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_SCHEDULE)
        ->andReturn(true);

    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $result = $orchestrator->handle(botSession(), botInbound('1'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('identify.awaiting_cpf')
        ->and(strtolower($result->outboundMessages[0]->text))->toContain('informe seu cpf')
        ->and(($result->stateUpdates['pending_intent'] ?? null))->toBe(WhatsAppBotIntentRouter::INTENT_SCHEDULE);
});

it('asks cpf when user chooses option 2 view appointments', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS)
        ->andReturn(true);

    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $result = $orchestrator->handle(botSession(), botInbound('2'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('identify.awaiting_cpf')
        ->and(strtolower($result->outboundMessages[0]->text))->toContain('informe seu cpf')
        ->and(($result->stateUpdates['pending_intent'] ?? null))->toBe(WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS);
});

it('continues requested flow when cpf is found without starting registration', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);

    $patient = new Patient([
        'id' => 'patient-1',
        'full_name' => 'Maria Silva',
        'cpf' => '39053344705',
        'is_active' => true,
    ]);

    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $patientService->shouldReceive('normalizeCpf')->once()->with('39053344705')->andReturn('39053344705');
    $patientService->shouldReceive('isValidCpf')->once()->with('39053344705')->andReturn(true);
    $patientService->shouldReceive('findByCpf')->once()->with('39053344705', false)->andReturn($patient);

    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);
    $appointmentService->shouldReceive('listUpcomingAppointments')->once()->with($patient)->andReturn(collect());

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);

    $session = botSession('menu', 'identify.awaiting_cpf', [
        'pending_intent' => WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS,
    ]);

    $result = $orchestrator->handle($session, botInbound('39053344705'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and($result->outboundMessages[0]->text)->toContain('Voce nao possui agendamentos futuros.')
        ->and(strtolower($result->outboundMessages[0]->text))->not->toContain('vamos criar seu cadastro')
        ->and(($result->stateUpdates['patient_id'] ?? null))->toBe('patient-1');
});

it('starts conversational registration when cpf is not found', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $patientService->shouldReceive('normalizeCpf')->once()->with('39053344705')->andReturn('39053344705');
    $patientService->shouldReceive('isValidCpf')->once()->with('39053344705')->andReturn(true);
    $patientService->shouldReceive('findByCpf')->once()->with('39053344705', false)->andReturnNull();
    $patientService->shouldReceive('findByNormalizedPhone')->once()->with('5567999998888')->andReturnNull();
    $patientService->shouldReceive('registrationFieldDefinitions')->once()->andReturn([
        ['key' => 'full_name', 'label' => 'nome completo', 'prompt' => 'Informe seu nome completo.', 'required' => true],
        ['key' => 'cpf', 'label' => 'CPF', 'prompt' => 'Informe seu CPF.', 'required' => true],
        ['key' => 'email', 'label' => 'e-mail', 'prompt' => 'Informe seu e-mail.', 'required' => true],
        ['key' => 'birth_date', 'label' => 'data de nascimento', 'prompt' => 'Informe sua data de nascimento.', 'required' => true],
    ]);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);

    $session = botSession('menu', 'identify.awaiting_cpf', [
        'pending_intent' => WhatsAppBotIntentRouter::INTENT_SCHEDULE,
    ]);

    $result = $orchestrator->handle($session, botInbound('39053344705'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('register.awaiting_field')
        ->and($result->outboundMessages[0]->text)->toContain('Nao localizei cadastro para este CPF.')
        ->and($result->outboundMessages[0]->text)->toContain('Informe seu nome completo.')
        ->and(($result->stateUpdates['registration']['index'] ?? null))->toBe(0);
});

it('registration flow includes base fields and all required patient form fields', function () {
    $service = app(WhatsAppBotPatientService::class);

    $definitions = $service->registrationFieldDefinitions();
    $keys = array_map(static fn (array $field): string => (string) ($field['key'] ?? ''), $definitions);

    expect(array_slice($keys, 0, 4))->toBe(['full_name', 'cpf', 'email', 'birth_date']);

    $requiredFields = [];
    $request = new StorePatientRequest();
    foreach ($request->rules() as $field => $rules) {
        $ruleList = is_array($rules) ? $rules : [$rules];
        foreach ($ruleList as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'required')) {
                $requiredFields[] = (string) $field;
                break;
            }
        }
    }

    foreach (array_unique($requiredFields) as $requiredField) {
        expect($keys)->toContain($requiredField);
    }
});

it('uses tenant-configured reset keyword list', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => [],
        'exit_keywords' => [],
        'session' => [
            'reset_keywords' => ['reinicio geral'],
        ],
        'menu' => [
            'options' => WhatsAppBotConfigService::DEFAULT_MENU_OPTIONS,
            'max_options' => 6,
            'show_again_after_action' => true,
            'return_after_fallback' => true,
        ],
        'messages' => [
            'welcome' => '',
            'fallback' => 'Nao entendi.',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $session = botSession('schedule', 'schedule.awaiting_date', [
        'schedule' => ['selected_specialty_id' => 'sp-1'],
        'patient_id' => 'patient-1',
    ]);

    $result = $orchestrator->handle($session, botInbound('reinicio geral'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and(($result->stateUpdates['schedule'] ?? null))->toBe([])
        ->and(isset($result->stateUpdates['patient_id']))->toBeFalse();
});

it('uses configured fallback message without forcing menu when disabled', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => [],
        'exit_keywords' => [],
        'menu' => [
            'options' => WhatsAppBotConfigService::DEFAULT_MENU_OPTIONS,
            'max_options' => 6,
            'show_again_after_action' => true,
            'return_after_fallback' => false,
        ],
        'messages' => [
            'welcome' => '',
            'fallback' => 'Fallback customizado sem menu.',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $result = $orchestrator->handle(botSession(), botInbound('mensagem invalida'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and($result->outboundMessages[0]->text)->toBe('Fallback customizado sem menu.');
});

it('returns to menu with inactivity message when idle timeout is exceeded', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => [],
        'exit_keywords' => [],
        'session' => [
            'idle_timeout_minutes' => 30,
            'absolute_timeout_minutes' => 240,
            'end_on_inactivity' => true,
            'clear_context_on_end' => true,
            'allow_resume_previous' => false,
            'reset_keywords' => ['menu'],
        ],
        'menu' => [
            'options' => WhatsAppBotConfigService::DEFAULT_MENU_OPTIONS,
            'max_options' => 6,
            'show_again_after_action' => true,
            'return_after_fallback' => true,
        ],
        'messages' => [
            'welcome' => '',
            'inactivity_exit' => 'Sessao encerrada por inatividade (teste).',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);

    $session = botSession('menu', 'menu.awaiting_option', [
        'patient_id' => 'patient-1',
        'schedule' => ['selected_specialty_id' => 'sp-1'],
    ]);
    $session->last_inbound_message_at = now()->subMinutes(31);
    $session->created_at = now()->subMinutes(60);

    $result = $orchestrator->handle($session, botInbound('oi'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and($result->outboundMessages[0]->text)->toContain('Sessao encerrada por inatividade (teste).')
        ->and(isset($result->stateUpdates['patient_id']))->toBeFalse()
        ->and(($result->stateUpdates['schedule'] ?? null))->toBe([]);
});

it('returns to menu after max invalid cpf attempts', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => [],
        'exit_keywords' => [],
        'identification' => [
            'max_attempts' => 3,
            'require_valid_cpf' => true,
            'lookup_order' => ['cpf'],
            'require_cpf_for_intents' => ['schedule', 'view_appointments'],
            'reuse_identified_patient' => true,
        ],
        'menu' => [
            'options' => WhatsAppBotConfigService::DEFAULT_MENU_OPTIONS,
            'max_options' => 6,
            'show_again_after_action' => true,
            'return_after_fallback' => true,
        ],
        'messages' => [
            'welcome' => '',
            'invalid_cpf' => 'CPF invalido customizado.',
            'back_to_menu' => 'Voltando ao menu principal (customizado).',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $patientService->shouldReceive('normalizeCpf')->times(3)->with('cpf-invalido')->andReturn('');
    $patientService->shouldReceive('isValidCpf')->times(3)->with('')->andReturn(false);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);

    $session = botSession('menu', 'identify.awaiting_cpf', [
        'pending_intent' => WhatsAppBotIntentRouter::INTENT_SCHEDULE,
    ]);

    $first = $orchestrator->handle($session, botInbound('cpf-invalido'));
    $secondSession = botSession((string) $first->flow, (string) $first->step, $first->stateUpdates);
    $second = $orchestrator->handle($secondSession, botInbound('cpf-invalido'));
    $thirdSession = botSession((string) $second->flow, (string) $second->step, $second->stateUpdates);
    $third = $orchestrator->handle($thirdSession, botInbound('cpf-invalido'));

    expect($third->flow)->toBe('menu')
        ->and($third->step)->toBe('menu.awaiting_option')
        ->and($third->outboundMessages[0]->text)->toContain('Voltando ao menu principal (customizado).');
});

it('maps numeric menu selection based on tenant menu option order', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => [],
        'exit_keywords' => [],
        'identification' => [
            'require_cpf_for_intents' => ['schedule', 'view_appointments'],
            'require_valid_cpf' => true,
            'max_attempts' => 3,
            'reuse_identified_patient' => true,
            'lookup_order' => ['cpf', 'phone'],
        ],
        'menu' => [
            'max_options' => 6,
            'show_again_after_action' => true,
            'return_after_fallback' => true,
            'options' => [
                ['id' => 'view_appointments', 'label' => 'Meus agendamentos primeiro', 'enabled' => true, 'order' => 1, 'requires_identification' => true],
                ['id' => 'schedule', 'label' => 'Agendar depois', 'enabled' => true, 'order' => 2, 'requires_identification' => true],
                ['id' => 'cancel_appointments', 'label' => 'Cancelar', 'enabled' => true, 'order' => 3, 'requires_identification' => true],
            ],
        ],
        'messages' => [
            'welcome' => '',
            'fallback' => 'Nao entendi.',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS)
        ->andReturn(true);

    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $result = $orchestrator->handle(botSession(), botInbound('1'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('identify.awaiting_cpf')
        ->and(($result->stateUpdates['pending_intent'] ?? null))->toBe(WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS);
});
