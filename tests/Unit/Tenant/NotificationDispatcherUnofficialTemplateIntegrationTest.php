<?php

use App\Services\Tenant\EmailSender;
use App\Services\Tenant\NotificationContextBuilder;
use App\Services\Tenant\NotificationDispatcher;
use App\Services\Tenant\NotificationTemplateService;
use App\Services\Tenant\TemplateRenderer;
use App\Services\Tenant\WhatsAppSender;
use App\Services\Tenant\WhatsAppUnofficialTemplateResolutionService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

afterEach(function () {
    \Mockery::close();
});

function invokeBuildChannelPayloadForTest(
    NotificationDispatcher $dispatcher,
    string $tenantId,
    string $channel,
    string $key,
    array $context,
    array $meta = []
): array {
    $method = new \ReflectionMethod(NotificationDispatcher::class, 'buildChannelPayload');
    $method->setAccessible(true);

    /** @var array<string,mixed> $payload */
    $payload = $method->invoke($dispatcher, $tenantId, $channel, $key, $context, $meta);

    return $payload;
}

function invokeDispatchWithContextForTest(
    NotificationDispatcher $dispatcher,
    string $tenantId,
    string $key,
    array $context,
    array $meta = []
): void {
    $method = new \ReflectionMethod(NotificationDispatcher::class, 'dispatchWithContext');
    $method->setAccessible(true);
    $method->invoke($dispatcher, $tenantId, $key, $context, $meta);
}

it('uses tenant-first unofficial resolver for whatsapp by default', function () {
    $templateService = \Mockery::mock(NotificationTemplateService::class);
    $templateService->shouldNotReceive('getEffectiveTemplate');

    $contextBuilder = \Mockery::mock(NotificationContextBuilder::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $emailSender = \Mockery::mock(EmailSender::class);
    $resolver = \Mockery::mock(WhatsAppUnofficialTemplateResolutionService::class);
    $renderer = new TemplateRenderer();

    $resolver->shouldReceive('resolve')
        ->once()
        ->with(
            'tenant-1',
            'appointment.confirmed',
            WhatsAppUnofficialTemplateResolutionService::SCOPE_TENANT_ONLY
        )
        ->andReturn([
            'content' => 'Ola {{patient.name}}',
            'source' => 'tenant_custom',
            'used_platform_fallback' => false,
        ]);

    $dispatcher = new NotificationDispatcher(
        $resolver,
        $templateService,
        $contextBuilder,
        $renderer,
        $whatsAppSender,
        $emailSender
    );

    $payload = invokeBuildChannelPayloadForTest(
        $dispatcher,
        'tenant-1',
        'whatsapp',
        'appointment.confirmed',
        ['patient' => ['name' => 'Maria']]
    );

    expect($payload['message'])->toBe('Ola Maria')
        ->and($payload['template_source'])->toBe('tenant_custom')
        ->and($payload['template_resolution_scope'])->toBe(WhatsAppUnofficialTemplateResolutionService::SCOPE_TENANT_ONLY)
        ->and($payload['used_platform_fallback'])->toBeFalse()
        ->and($payload['template_fallback_reason'])->toBeNull();
});

it('uses platform fallback only when explicitly requested', function () {
    $templateService = \Mockery::mock(NotificationTemplateService::class);
    $templateService->shouldNotReceive('getEffectiveTemplate');

    $contextBuilder = \Mockery::mock(NotificationContextBuilder::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $emailSender = \Mockery::mock(EmailSender::class);
    $resolver = \Mockery::mock(WhatsAppUnofficialTemplateResolutionService::class);
    $renderer = new TemplateRenderer();

    $resolver->shouldReceive('resolve')
        ->once()
        ->with(
            'tenant-1',
            'invoice.overdue',
            WhatsAppUnofficialTemplateResolutionService::SCOPE_TENANT_THEN_PLATFORM
        )
        ->andReturn([
            'content' => 'Fatura vencida para {{customer_name}}',
            'source' => 'platform_unofficial_catalog',
            'used_platform_fallback' => true,
        ]);

    $dispatcher = new NotificationDispatcher(
        $resolver,
        $templateService,
        $contextBuilder,
        $renderer,
        $whatsAppSender,
        $emailSender
    );

    $payload = invokeBuildChannelPayloadForTest(
        $dispatcher,
        'tenant-1',
        'whatsapp',
        'invoice.overdue',
        ['customer_name' => 'Ana'],
        ['allow_platform_fallback' => true]
    );

    expect($payload['message'])->toBe('Fatura vencida para Ana')
        ->and($payload['template_source'])->toBe('platform_unofficial_catalog')
        ->and($payload['template_resolution_scope'])->toBe(WhatsAppUnofficialTemplateResolutionService::SCOPE_TENANT_THEN_PLATFORM)
        ->and($payload['used_platform_fallback'])->toBeTrue();
});

it('falls back to legacy notification template service when resolver does not find template', function () {
    $templateService = \Mockery::mock(NotificationTemplateService::class);
    $templateService->shouldReceive('getEffectiveTemplate')
        ->once()
        ->with('tenant-1', 'whatsapp', 'appointment.confirmed')
        ->andReturn([
            'content' => 'Mensagem legado {{patient.name}}',
            'is_override' => false,
        ]);

    $contextBuilder = \Mockery::mock(NotificationContextBuilder::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $emailSender = \Mockery::mock(EmailSender::class);
    $resolver = \Mockery::mock(WhatsAppUnofficialTemplateResolutionService::class);
    $renderer = new TemplateRenderer();

    $resolver->shouldReceive('resolve')
        ->once()
        ->andReturnNull();

    $dispatcher = new NotificationDispatcher(
        $resolver,
        $templateService,
        $contextBuilder,
        $renderer,
        $whatsAppSender,
        $emailSender
    );

    $payload = invokeBuildChannelPayloadForTest(
        $dispatcher,
        'tenant-1',
        'whatsapp',
        'appointment.confirmed',
        ['patient' => ['name' => 'Joao']]
    );

    expect($payload['message'])->toBe('Mensagem legado Joao')
        ->and($payload['template_source'])->toBe('default')
        ->and($payload['template_fallback_reason'])->toBe('resolver_not_found_tenant')
        ->and($payload['used_platform_fallback'])->toBeFalse();
});

it('keeps unresolved placeholders and reports unknown variables', function () {
    $templateService = \Mockery::mock(NotificationTemplateService::class);
    $templateService->shouldNotReceive('getEffectiveTemplate');

    $contextBuilder = \Mockery::mock(NotificationContextBuilder::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $emailSender = \Mockery::mock(EmailSender::class);
    $resolver = \Mockery::mock(WhatsAppUnofficialTemplateResolutionService::class);
    $renderer = new TemplateRenderer();

    $resolver->shouldReceive('resolve')
        ->once()
        ->andReturn([
            'content' => 'Oi {{patient.name}}, horario {{appointment.time}}',
            'source' => 'tenant_custom',
            'used_platform_fallback' => false,
        ]);

    $dispatcher = new NotificationDispatcher(
        $resolver,
        $templateService,
        $contextBuilder,
        $renderer,
        $whatsAppSender,
        $emailSender
    );

    $payload = invokeBuildChannelPayloadForTest(
        $dispatcher,
        'tenant-1',
        'whatsapp',
        'appointment.pending_confirmation',
        ['patient' => ['name' => 'Bia']]
    );

    expect($payload['message'])->toContain('Oi Bia')
        ->and($payload['message'])->toContain('{{appointment.time}}')
        ->and($payload['unknown_placeholders'])->toContain('appointment.time');
});

it('does not break dispatch flow when template key does not exist', function () {
    $templateService = \Mockery::mock(NotificationTemplateService::class);
    $templateService->shouldReceive('getEffectiveTemplate')
        ->twice()
        ->andThrow(ValidationException::withMessages([
            'key' => 'Template inexistente',
        ]));

    $contextBuilder = \Mockery::mock(NotificationContextBuilder::class);
    $whatsAppSender = \Mockery::mock(WhatsAppSender::class);
    $whatsAppSender->shouldNotReceive('send');
    $emailSender = \Mockery::mock(EmailSender::class);
    $emailSender->shouldNotReceive('send');
    $resolver = \Mockery::mock(WhatsAppUnofficialTemplateResolutionService::class);
    $renderer = new TemplateRenderer();

    $resolver->shouldReceive('resolve')
        ->once()
        ->andReturnNull();

    $dispatcher = new NotificationDispatcher(
        $resolver,
        $templateService,
        $contextBuilder,
        $renderer,
        $whatsAppSender,
        $emailSender
    );

    invokeDispatchWithContextForTest(
        $dispatcher,
        'tenant-1',
        'template.inexistente',
        [
            'patient' => [
                'phone' => '5567999999999',
                'email' => 'paciente@example.com',
            ],
        ]
    );

    expect(true)->toBeTrue();
});
