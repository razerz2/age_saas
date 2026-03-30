<?php

use App\Http\Requests\Tenant\StorePatientRequest;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Patient;
use App\Models\Tenant\WhatsAppBotSession;
use App\Services\Tenant\WhatsAppBot\Conversation\WhatsAppBotMessageFormatter;
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
        messageFormatter: app(WhatsAppBotMessageFormatter::class),
        patientService: $patientService,
        appointmentService: $appointmentService
    );
}

function expectActionAndMenuAsSeparatedMessages($result, string $actionSnippet): void
{
    expect(count($result->outboundMessages))->toBe(2)
        ->and(($result->outboundMessages[0]->meta['kind'] ?? null))->toBe('action_result')
        ->and($result->outboundMessages[0]->text)->toContain($actionSnippet)
        ->and($result->outboundMessages[0]->text)->not->toContain('Como posso ajudar?')
        ->and($result->outboundMessages[0]->text)->not->toContain('1) Agendar consulta')
        ->and(($result->outboundMessages[1]->meta['kind'] ?? null))->toBe('menu')
        ->and($result->outboundMessages[1]->text)->toContain('Como posso ajudar?')
        ->and($result->outboundMessages[1]->text)->toContain('1) Agendar consulta');
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
        ->and(count($result->outboundMessages))->toBe(1)
        ->and($result->outboundMessages[0]->text)->toContain('Como posso ajudar?')
        ->and($result->outboundMessages[0]->text)->toContain('1) Agendar consulta')
        ->and($result->outboundMessages[0]->text)->not->toContain('1. Agendar consulta')
        ->and(strtolower($result->outboundMessages[0]->text))->not->toContain('informe seu nome')
        ->and(strtolower($result->outboundMessages[0]->text))->not->toContain('não localizei seu cadastro');
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
        ->and($result->outboundMessages[0]->text)->toContain('Como posso ajudar?')
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
        ->and(strtolower($result->outboundMessages[0]->text))->not->toContain('vamos criar seu cadastro')
        ->and(($result->stateUpdates['patient_id'] ?? null))->toBe('patient-1');

    expectActionAndMenuAsSeparatedMessages($result, 'agendamentos futuros');
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
        ->and($result->outboundMessages[0]->text)->toContain('Não localizamos cadastro para este CPF.')
        ->and($result->outboundMessages[0]->text)->toContain('Informe seu nome completo.')
        ->and(($result->stateUpdates['registration']['index'] ?? null))->toBe(0)
        ->and(($result->stateUpdates['registration']['data']['cpf'] ?? null))->toBe('39053344705');
});

it('reuses identified cpf and skips cpf question during registration', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $fields = [
        ['key' => 'full_name', 'label' => 'nome completo', 'prompt' => 'Informe seu nome completo.', 'required' => true],
        ['key' => 'cpf', 'label' => 'CPF', 'prompt' => 'Informe seu CPF.', 'required' => true],
        ['key' => 'email', 'label' => 'e-mail', 'prompt' => 'Informe seu e-mail.', 'required' => true],
        ['key' => 'birth_date', 'label' => 'data de nascimento', 'prompt' => 'Informe sua data de nascimento.', 'required' => true],
    ];

    $patientService->shouldReceive('normalizeCpf')->once()->with('39053344705')->andReturn('39053344705');
    $patientService->shouldReceive('isValidCpf')->once()->with('39053344705')->andReturn(true);
    $patientService->shouldReceive('findByCpf')->once()->with('39053344705', false)->andReturnNull();
    $patientService->shouldReceive('findByNormalizedPhone')->once()->with('5567999998888')->andReturnNull();
    $patientService->shouldReceive('registrationFieldDefinitions')->once()->andReturn($fields);
    $patientService->shouldReceive('validateRegistrationField')
        ->once()
        ->withArgs(function (string $field, string $value, array $draft): bool {
            return $field === 'full_name'
                && $value === 'Maria Silva'
                && (($draft['cpf'] ?? null) === '39053344705');
        })
        ->andReturn(['valid' => true, 'value' => 'Maria Silva', 'error' => null]);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $session = botSession('menu', 'identify.awaiting_cpf', [
        'pending_intent' => WhatsAppBotIntentRouter::INTENT_SCHEDULE,
    ]);

    $startRegistration = $orchestrator->handle($session, botInbound('39053344705'));
    $registrationSession = botSession((string) $startRegistration->flow, (string) $startRegistration->step, $startRegistration->stateUpdates);
    $next = $orchestrator->handle($registrationSession, botInbound('Maria Silva'));

    expect($next->flow)->toBe('menu')
        ->and($next->step)->toBe('register.awaiting_field')
        ->and($next->outboundMessages[0]->text)->toContain('Informe seu e-mail.')
        ->and($next->outboundMessages[0]->text)->not->toContain('Informe seu CPF.')
        ->and(($next->stateUpdates['registration']['index'] ?? null))->toBe(2)
        ->and(($next->stateUpdates['registration']['data']['cpf'] ?? null))->toBe('39053344705');
});

it('creates patient with previously identified cpf during registration completion', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $fields = [
        ['key' => 'full_name', 'label' => 'nome completo', 'prompt' => 'Informe seu nome completo.', 'required' => true],
        ['key' => 'cpf', 'label' => 'CPF', 'prompt' => 'Informe seu CPF.', 'required' => true],
        ['key' => 'email', 'label' => 'e-mail', 'prompt' => 'Informe seu e-mail.', 'required' => true],
        ['key' => 'birth_date', 'label' => 'data de nascimento', 'prompt' => 'Informe sua data de nascimento.', 'required' => true],
    ];

    $patientService->shouldReceive('validateRegistrationField')
        ->once()
        ->withArgs(function (string $field, string $value, array $draft): bool {
            return $field === 'birth_date'
                && $value === '01/01/1990'
                && (($draft['cpf'] ?? null) === '39053344705');
        })
        ->andReturn(['valid' => true, 'value' => '1990-01-01', 'error' => null]);

    $createdPatient = new Patient([
        'id' => 'patient-new',
        'full_name' => 'Maria Silva',
        'cpf' => '39053344705',
        'is_active' => true,
    ]);

    $patientService->shouldReceive('createFromRegistration')
        ->once()
        ->withArgs(function (array $registrationData, string $phone): bool {
            return ($registrationData['cpf'] ?? null) === '39053344705'
                && $phone === '5567999998888';
        })
        ->andReturn($createdPatient);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $session = botSession('menu', 'register.awaiting_field', [
        'pending_intent' => '',
        'registration' => [
            'fields' => $fields,
            'index' => 3,
            'data' => [
                'full_name' => 'Maria Silva',
                'cpf' => '39053344705',
                'email' => 'maria@example.com',
            ],
            'identified_cpf' => '39053344705',
            'pending_intent' => '',
        ],
    ]);

    $result = $orchestrator->handle($session, botInbound('01/01/1990'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and(($result->stateUpdates['patient_id'] ?? null))->toBe('patient-new')
        ->and(($result->stateUpdates['patient_name'] ?? null))->toBe('Maria Silva')
        ->and($result->outboundMessages[0]->text)->toContain('Cadastro');
});

it('does not reuse invalid cpf when identification allows non-validated cpf', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'identification' => [
            'require_valid_cpf' => false,
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $patientService->shouldReceive('normalizeCpf')->once()->with('12345678900')->andReturn('12345678900');
    $patientService->shouldReceive('isValidCpf')->once()->with('12345678900')->andReturn(false);
    $patientService->shouldReceive('findByCpf')->once()->with('12345678900', false)->andReturnNull();
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

    $result = $orchestrator->handle($session, botInbound('12345678900'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('register.awaiting_field')
        ->and(($result->stateUpdates['registration']['index'] ?? null))->toBe(0)
        ->and(array_key_exists('cpf', (array) ($result->stateUpdates['registration']['data'] ?? [])))->toBeFalse();
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
            'fallback' => 'Não entendi.',
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

it('returns fallback and menu in separate messages when configured to return after fallback', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => [],
        'exit_keywords' => [],
        'menu' => [
            'options' => WhatsAppBotConfigService::DEFAULT_MENU_OPTIONS,
            'max_options' => 6,
            'show_again_after_action' => true,
            'return_after_fallback' => true,
        ],
        'messages' => [
            'welcome' => '',
            'fallback' => 'Fallback customizado com menu.',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $result = $orchestrator->handle(botSession(), botInbound('mensagem invalida'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option');

    expectActionAndMenuAsSeparatedMessages($result, 'Fallback customizado com menu.');
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
            'inactivity_exit' => 'Sessão encerrada por inatividade (teste).',
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
        ->and($result->outboundMessages[0]->text)->toContain('Sessão encerrada por inatividade (teste).')
        ->and($result->outboundMessages[0]->text)->not->toContain('Como posso ajudar?')
        ->and(isset($result->stateUpdates['patient_id']))->toBeFalse()
        ->and(($result->stateUpdates['schedule'] ?? null))->toBe([]);
});

it('does not repeat passive inactivity warning when session was already closed by active timeout', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => ['oi', 'Oi'],
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
            'inactivity_exit' => 'Sessão encerrada por inatividade (teste).',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);

    $session = botSession('menu', 'menu.awaiting_option', []);
    $session->last_inbound_message_at = now()->subMinutes(120);
    $session->meta = [
        'inactivity_timeout' => [
            'status' => 'sent',
            'sent_at' => now()->subMinutes(5)->toDateTimeString(),
            'closed_at' => now()->subMinutes(5)->toDateTimeString(),
        ],
    ];

    $result = $orchestrator->handle($session, botInbound('Oi'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and($result->outboundMessages[0]->text)->toContain('Como posso ajudar?')
        ->and($result->outboundMessages[0]->text)->not->toContain('Sessão encerrada por inatividade (teste).');
});

it('returns only passive inactivity warning without concatenating welcome or menu', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => ['oi', 'Oi'],
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
            'welcome' => 'Boas-vindas customizadas.',
            'inactivity_exit' => 'Sessão encerrada por inatividade (passivo).',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);

    $session = botSession('menu', 'menu.awaiting_option', []);
    $session->last_inbound_message_at = now()->subMinutes(45);
    $session->meta = [];

    $result = $orchestrator->handle($session, botInbound('Oi'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and(trim($result->outboundMessages[0]->text))->toBe('Sessão encerrada por inatividade (passivo).')
        ->and($result->outboundMessages[0]->text)->not->toContain('Boas-vindas customizadas.')
        ->and($result->outboundMessages[0]->text)->not->toContain('Como posso ajudar?');
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
            'invalid_cpf' => 'CPF inválido customizado.',
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
            'fallback' => 'Não entendi.',
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

it('does not emit invalid date message when the provided date is valid', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn(['timezone' => 'America/Campo_Grande']);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_SCHEDULE)
        ->andReturn(true);

    $patient = new Patient(['id' => 'patient-1', 'full_name' => 'Maria Silva', 'is_active' => true]);

    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $patientService->shouldReceive('findById')->once()->with('patient-1')->andReturn($patient);

    $targetDate = now()->addDays(10);
    $slotStart = $targetDate->copy()->setTime(9, 0, 0);
    $slotEnd = $targetDate->copy()->setTime(9, 30, 0);

    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);
    $appointmentService->shouldReceive('listAvailableSlots')
        ->once()
        ->andReturn(collect([
            [
                'starts_at' => $slotStart->format('Y-m-d H:i:s'),
                'ends_at' => $slotEnd->format('Y-m-d H:i:s'),
                'label' => '09:00',
            ],
        ]));

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $session = botSession('schedule', 'schedule.awaiting_date', [
        'patient_id' => 'patient-1',
        'schedule' => [
            'selected_doctor' => [
                'id' => 'doctor-1',
                'duration_min' => 30,
            ],
        ],
    ]);

    $result = $orchestrator->handle($session, botInbound($targetDate->format('d/m/Y')));

    expect($result->flow)->toBe('schedule')
        ->and($result->step)->toBe('schedule.awaiting_slot')
        ->and($result->outboundMessages[0]->text)->toContain('Escolha o horário disponível:')
        ->and($result->outboundMessages[0]->text)->not->toContain('Data inválida');
});

it('keeps schedule on date step and does not advance when date is invalid', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn(['timezone' => 'America/Campo_Grande']);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_SCHEDULE)
        ->andReturn(true);

    $patient = new Patient(['id' => 'patient-1', 'full_name' => 'Maria Silva', 'is_active' => true]);

    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $patientService->shouldReceive('findById')->once()->with('patient-1')->andReturn($patient);

    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);
    $appointmentService->shouldReceive('listAvailableSlots')->never();

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $session = botSession('schedule', 'schedule.awaiting_date', [
        'patient_id' => 'patient-1',
        'schedule' => [
            'selected_doctor' => [
                'id' => 'doctor-1',
                'duration_min' => 30,
            ],
        ],
    ]);

    $result = $orchestrator->handle($session, botInbound('99/99/9999'));

    expect($result->flow)->toBe('schedule')
        ->and($result->step)->toBe('schedule.awaiting_date')
        ->and($result->outboundMessages[0]->text)->toContain('Data inválida')
        ->and($result->outboundMessages[0]->text)->not->toContain('Escolha o horário disponível:');
});

it('lists professionals as doctor and specialty combinations in schedule step', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'entry_keywords' => [],
        'exit_keywords' => [],
        'identification' => [
            'lookup_order' => ['phone'],
            'reuse_identified_patient' => true,
            'require_cpf_for_intents' => [],
        ],
        'menu' => [
            'max_options' => 6,
            'show_again_after_action' => true,
            'return_after_fallback' => true,
            'options' => [
                ['id' => 'schedule', 'label' => 'Agendar consulta', 'enabled' => true, 'order' => 1, 'requires_identification' => false],
                ['id' => 'view_appointments', 'label' => 'Ver meus agendamentos', 'enabled' => true, 'order' => 2, 'requires_identification' => true],
                ['id' => 'cancel_appointments', 'label' => 'Cancelar agendamento', 'enabled' => true, 'order' => 3, 'requires_identification' => true],
            ],
        ],
        'messages' => [
            'welcome' => '',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_SCHEDULE)
        ->andReturn(true);

    $patient = new Patient(['id' => 'patient-1', 'full_name' => 'Maria Silva', 'is_active' => true]);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $patientService->shouldReceive('findByNormalizedPhone')->once()->with('5567999998888')->andReturn($patient);

    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);
    $appointmentService->shouldReceive('listDoctors')
        ->once()
        ->with(null)
        ->andReturn(collect([
            [
                'id' => 'doctor-ana',
                'name' => 'Dra. Ana Paula Costa Lima',
                'calendar_id' => 'calendar-ana',
                'appointment_type_id' => 'type-ana',
                'duration_min' => 30,
                'specialty_id' => 'specialty-cardio',
                'specialty_name' => 'Cardiologia',
            ],
            [
                'id' => 'doctor-ana',
                'name' => 'Dra. Ana Paula Costa Lima',
                'calendar_id' => 'calendar-ana',
                'appointment_type_id' => 'type-ana',
                'duration_min' => 30,
                'specialty_id' => 'specialty-clinica',
                'specialty_name' => 'Clinica Geral',
            ],
            [
                'id' => 'doctor-joao',
                'name' => 'Dr. Joao Gomes Silva',
                'calendar_id' => 'calendar-joao',
                'appointment_type_id' => 'type-joao',
                'duration_min' => 30,
                'specialty_id' => 'specialty-orto',
                'specialty_name' => 'Ortopedia',
            ],
            [
                'id' => 'doctor-sem-especialidade',
                'name' => 'Dra. Carla Ribeiro',
                'calendar_id' => 'calendar-carla',
                'appointment_type_id' => 'type-carla',
                'duration_min' => 30,
                'specialty_id' => null,
                'specialty_name' => null,
            ],
        ]));

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $result = $orchestrator->handle(botSession(), botInbound('1'));

    expect($result->flow)->toBe('schedule')
        ->and($result->step)->toBe('schedule.awaiting_doctor')
        ->and($result->outboundMessages[0]->text)->toContain('Escolha o profissional:')
        ->and($result->outboundMessages[0]->text)->toContain('1) Dra. Ana Paula Costa Lima - Cardiologia')
        ->and($result->outboundMessages[0]->text)->toContain('2) Dra. Ana Paula Costa Lima - Clinica Geral')
        ->and($result->outboundMessages[0]->text)->toContain('3) Dr. Joao Gomes Silva - Ortopedia')
        ->and($result->outboundMessages[0]->text)->toContain('4) Dra. Carla Ribeiro - Sem especialidade')
        ->and(count((array) data_get($result->stateUpdates, 'schedule.doctors', [])))->toBe(4);
});

it('maps selected doctor option to doctor and specialty in schedule state', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn(['timezone' => 'America/Campo_Grande']);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_SCHEDULE)
        ->andReturn(true);

    $patient = new Patient(['id' => 'patient-1', 'full_name' => 'Maria Silva', 'is_active' => true]);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $patientService->shouldReceive('findById')->once()->with('patient-1')->andReturn($patient);

    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $session = botSession('schedule', 'schedule.awaiting_doctor', [
        'patient_id' => 'patient-1',
        'schedule' => [
            'selected_specialty_id' => 'old-specialty',
            'selected_specialty_name' => 'Valor antigo',
            'doctors' => [
                [
                    'id' => 'doctor-ana',
                    'name' => 'Dra. Ana Paula Costa Lima',
                    'calendar_id' => 'calendar-ana',
                    'appointment_type_id' => 'type-ana',
                    'duration_min' => 30,
                    'specialty_id' => 'specialty-cardio',
                    'specialty_name' => 'Cardiologia',
                ],
                [
                    'id' => 'doctor-ana',
                    'name' => 'Dra. Ana Paula Costa Lima',
                    'calendar_id' => 'calendar-ana',
                    'appointment_type_id' => 'type-ana',
                    'duration_min' => 30,
                    'specialty_id' => 'specialty-clinica',
                    'specialty_name' => 'Clinica Geral',
                ],
            ],
        ],
    ]);

    $result = $orchestrator->handle($session, botInbound('2'));

    expect($result->flow)->toBe('schedule')
        ->and($result->step)->toBe('schedule.awaiting_date')
        ->and($result->outboundMessages[0]->text)->toContain('Informe a data desejada')
        ->and((string) data_get($result->stateUpdates, 'schedule.selected_doctor.id'))->toBe('doctor-ana')
        ->and((string) data_get($result->stateUpdates, 'schedule.selected_doctor_id'))->toBe('doctor-ana')
        ->and((string) data_get($result->stateUpdates, 'schedule.selected_doctor_name'))->toBe('Dra. Ana Paula Costa Lima')
        ->and((string) data_get($result->stateUpdates, 'schedule.selected_specialty_id'))->toBe('specialty-clinica')
        ->and((string) data_get($result->stateUpdates, 'schedule.selected_specialty_name'))->toBe('Clinica Geral')
        ->and((string) data_get($result->stateUpdates, 'schedule.selected_doctor_option.label'))->toBe('Dra. Ana Paula Costa Lima - Clinica Geral');
});

it('uses human readable specialty and doctor values in schedule confirmation summary', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn(['timezone' => 'America/Campo_Grande']);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_SCHEDULE)
        ->andReturn(true);

    $patient = new Patient(['id' => 'patient-1', 'full_name' => 'Maria Silva', 'is_active' => true]);

    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $patientService->shouldReceive('findById')->once()->with('patient-1')->andReturn($patient);

    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $session = botSession('schedule', 'schedule.awaiting_slot', [
        'patient_id' => 'patient-1',
        'schedule' => [
            'selected_specialty_name' => 'Cardiologia',
            'selected_doctor' => [
                'id' => 'doctor-1',
                'name' => 'Dr. João Silva',
                'duration_min' => 30,
            ],
            'slots' => [
                [
                    'starts_at' => now()->addDays(2)->setTime(9, 0, 0)->format('Y-m-d H:i:s'),
                    'ends_at' => now()->addDays(2)->setTime(9, 30, 0)->format('Y-m-d H:i:s'),
                    'label' => '09:00',
                ],
            ],
        ],
    ]);

    $result = $orchestrator->handle($session, botInbound('1'));

    expect($result->flow)->toBe('schedule')
        ->and($result->step)->toBe('schedule.awaiting_confirmation')
        ->and($result->outboundMessages[0]->text)->toContain('Especialidade: Cardiologia')
        ->and($result->outboundMessages[0]->text)->toContain('Profissional: Dr. João Silva')
        ->and($result->outboundMessages[0]->text)->not->toContain('Especialidade não informada')
        ->and($result->outboundMessages[0]->text)->not->toContain('Profissional: Profissional');
});

it('returns appointment creation confirmation and menu in separate messages', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'timezone' => 'America/Campo_Grande',
        'messages' => [
            'welcome' => '',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_SCHEDULE)
        ->andReturn(true);

    $patient = new Patient(['id' => 'patient-1', 'full_name' => 'Maria Silva', 'is_active' => true]);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $patientService->shouldReceive('findById')->once()->with('patient-1')->andReturn($patient);

    $appointmentStartsAt = now()->addDays(3)->setTime(9, 0, 0);
    $createdAppointment = new Appointment();
    $createdAppointment->starts_at = $appointmentStartsAt;

    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);
    $appointmentService->shouldReceive('createAppointment')->once()->andReturn($createdAppointment);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $session = botSession('schedule', 'schedule.awaiting_confirmation', [
        'patient_id' => 'patient-1',
        'schedule' => [
            'selected_specialty_id' => 'specialty-1',
            'selected_doctor' => [
                'id' => 'doctor-1',
                'name' => 'Dr. Joao Silva',
                'calendar_id' => 'calendar-1',
                'appointment_type_id' => 'type-1',
            ],
            'selected_slot' => [
                'starts_at' => $appointmentStartsAt->format('Y-m-d H:i:s'),
                'ends_at' => $appointmentStartsAt->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
            ],
        ],
    ]);

    $result = $orchestrator->handle($session, botInbound('1'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option')
        ->and($result->outboundMessages[0]->text)->toContain('Profissional: Dr. Joao Silva');

    expectActionAndMenuAsSeparatedMessages($result, 'Agendamento realizado com sucesso!');
});

it('uses explicit schedule ids on confirmation even when selected_doctor shape changes', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'timezone' => 'America/Campo_Grande',
        'messages' => [
            'welcome' => '',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_SCHEDULE)
        ->andReturn(true);

    $patient = new Patient(['id' => 'patient-1', 'full_name' => 'Maria Silva', 'is_active' => true]);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $patientService->shouldReceive('findById')->once()->with('patient-1')->andReturn($patient);

    $appointmentStartsAt = now()->addDays(4)->setTime(10, 0, 0);
    $createdAppointment = new Appointment();
    $createdAppointment->starts_at = $appointmentStartsAt;

    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);
    $appointmentService->shouldReceive('createAppointment')
        ->once()
        ->withArgs(function (
            Patient $argPatient,
            string $doctorId,
            string $calendarId,
            ?string $specialtyId,
            ?string $appointmentTypeId,
            string $startsAt,
            string $endsAt
        ) use ($patient, $appointmentStartsAt): bool {
            return $argPatient === $patient
                && $doctorId === 'doctor-explicit'
                && $calendarId === 'calendar-explicit'
                && $specialtyId === 'specialty-explicit'
                && $appointmentTypeId === 'type-explicit'
                && $startsAt === $appointmentStartsAt->format('Y-m-d H:i:s')
                && $endsAt === $appointmentStartsAt->copy()->addMinutes(30)->format('Y-m-d H:i:s');
        })
        ->andReturn($createdAppointment);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $session = botSession('schedule', 'schedule.awaiting_confirmation', [
        'patient_id' => 'patient-1',
        'schedule' => [
            'selected_doctor_id' => 'doctor-explicit',
            'selected_doctor_name' => 'Dra. Ana Paula Costa Lima',
            'selected_specialty_id' => 'specialty-explicit',
            'selected_specialty_name' => 'Clinica Geral',
            'selected_calendar_id' => 'calendar-explicit',
            'selected_appointment_type_id' => 'type-explicit',
            'selected_doctor_option' => [
                'index' => 2,
                'label' => 'Dra. Ana Paula Costa Lima - Clinica Geral',
            ],
            'selected_doctor' => [
                'name' => 'Dra. Ana Paula Costa Lima',
            ],
            'selected_slot' => [
                'starts_at' => $appointmentStartsAt->format('Y-m-d H:i:s'),
                'ends_at' => $appointmentStartsAt->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
            ],
        ],
    ]);

    $result = $orchestrator->handle($session, botInbound('1'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option');

    expectActionAndMenuAsSeparatedMessages($result, 'Agendamento realizado com sucesso!');
});

it('returns cancellation confirmation and menu in separate messages', function () {
    $configService = Mockery::mock(WhatsAppBotConfigService::class);
    $configService->shouldReceive('getSettings')->andReturn([
        'messages' => [
            'welcome' => '',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS)
        ->andReturn(true);

    $patient = new Patient(['id' => 'patient-1', 'full_name' => 'Maria Silva', 'is_active' => true]);
    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $patientService->shouldReceive('findById')->once()->with('patient-1')->andReturn($patient);

    $appointment = new Appointment();
    $appointment->id = 'appointment-1';

    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);
    $appointmentService->shouldReceive('listCancelableAppointments')->once()->with($patient, 50)->andReturn(collect([$appointment]));
    $appointmentService->shouldReceive('cancelAppointment')->once()->with($appointment)->andReturn(true);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);
    $session = botSession('cancel', 'cancel.awaiting_confirmation', [
        'patient_id' => 'patient-1',
        'cancel' => [
            'selected' => ['id' => 'appointment-1'],
        ],
    ]);

    $result = $orchestrator->handle($session, botInbound('1'));

    expect($result->flow)->toBe('menu')
        ->and($result->step)->toBe('menu.awaiting_option');

    expectActionAndMenuAsSeparatedMessages($result, 'Agendamento cancelado com sucesso!');
});

it('does not loop on absolute timeout after resetting session context', function () {
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
        'identification' => [
            'require_cpf_for_intents' => ['schedule', 'view_appointments'],
            'require_valid_cpf' => true,
            'max_attempts' => 3,
            'reuse_identified_patient' => true,
            'lookup_order' => ['cpf', 'phone'],
        ],
        'menu' => [
            'options' => WhatsAppBotConfigService::DEFAULT_MENU_OPTIONS,
            'max_options' => 6,
            'show_again_after_action' => true,
            'return_after_fallback' => true,
        ],
        'messages' => [
            'welcome' => '',
            'inactivity_exit' => 'Sessão encerrada por inatividade (teste).',
        ],
    ]);

    $domainService = Mockery::mock(WhatsAppBotDomainService::class);
    $domainService->shouldReceive('isIntentEnabled')
        ->once()
        ->with(WhatsAppBotIntentRouter::INTENT_SCHEDULE)
        ->andReturn(true);

    $patientService = Mockery::mock(WhatsAppBotPatientService::class);
    $appointmentService = Mockery::mock(WhatsAppBotAppointmentService::class);

    $orchestrator = makeOrchestrator($configService, $domainService, $patientService, $appointmentService);

    $expiredSession = botSession('menu', 'menu.awaiting_option', [
        '_meta' => [
            'session_started_at' => now()->subMinutes(500)->toDateTimeString(),
        ],
    ]);
    $expiredSession->created_at = now()->subDays(10);
    $expiredSession->last_inbound_message_at = now()->subMinutes(60);

    $first = $orchestrator->handle($expiredSession, botInbound('1'));

    expect($first->flow)->toBe('menu')
        ->and($first->step)->toBe('menu.awaiting_option')
        ->and($first->outboundMessages[0]->text)->toContain('Sessão encerrada por inatividade (teste).')
        ->and((string) data_get($first->stateUpdates, '_meta.session_started_at', ''))->not->toBe('');

    $nextSession = botSession((string) $first->flow, (string) $first->step, $first->stateUpdates);
    $nextSession->created_at = now()->subDays(10);
    $nextSession->last_inbound_message_at = now();

    $second = $orchestrator->handle($nextSession, botInbound('1'));

    expect($second->flow)->toBe('menu')
        ->and($second->step)->toBe('identify.awaiting_cpf')
        ->and($second->outboundMessages[0]->text)->toContain('informe seu CPF')
        ->and($second->outboundMessages[0]->text)->not->toContain('Sessão encerrada por inatividade')
        ->and(($second->stateUpdates['pending_intent'] ?? null))->toBe(WhatsAppBotIntentRouter::INTENT_SCHEDULE);
});
