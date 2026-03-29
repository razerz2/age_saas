<?php

use App\Services\Tenant\WhatsAppBotConfigService;
use Tests\TestCase;

uses(TestCase::class);

it('uses fallback keyword lists when tenant has not configured values', function () {
    $service = app(WhatsAppBotConfigService::class);

    expect($service->resolveEntryKeywords([]))->toBe(WhatsAppBotConfigService::DEFAULT_ENTRY_KEYWORDS)
        ->and($service->resolveExitKeywords([]))->toBe(WhatsAppBotConfigService::DEFAULT_EXIT_KEYWORDS);
});

it('uses tenant customized keyword lists when provided', function () {
    $service = app(WhatsAppBotConfigService::class);

    $settings = [
        'entry_keywords' => ['Primeiro contato', 'iniciar agora'],
        'exit_keywords' => ['encerrar atendimento', 'tchau bot'],
    ];

    expect($service->resolveEntryKeywords($settings))->toBe(['Primeiro contato', 'iniciar agora'])
        ->and($service->resolveExitKeywords($settings))->toBe(['encerrar atendimento', 'tchau bot']);
});
