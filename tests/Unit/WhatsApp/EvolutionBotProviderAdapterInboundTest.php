<?php

use App\Services\Tenant\WhatsAppBot\Provider\EvolutionBotProviderAdapter;
use Tests\TestCase;

uses(TestCase::class);

it('normalizes evolution canonical inbound payload', function () {
    $adapter = app(EvolutionBotProviderAdapter::class);

    $payload = [
        'event' => 'messages.upsert',
        'data' => [
            'key' => [
                'remoteJid' => '5567999998888@s.whatsapp.net',
                'fromMe' => false,
                'id' => 'EVT-1',
            ],
            'messageType' => 'conversation',
            'message' => [
                'conversation' => 'Oi Evolution',
            ],
        ],
    ];

    $inbound = $adapter->normalizeInbound($payload);

    expect($inbound)->not->toBeNull()
        ->and($inbound?->contactPhone)->toBe('5567999998888')
        ->and($inbound?->text)->toBe('Oi Evolution')
        ->and($inbound?->messageType)->toBe('conversation')
        ->and($inbound?->externalMessageId)->toBe('EVT-1');
});

it('normalizes evolution payload with data.messages array shape', function () {
    $adapter = app(EvolutionBotProviderAdapter::class);

    $payload = [
        'event' => 'messages.upsert',
        'data' => [
            'messages' => [[
                'key' => [
                    'remoteJid' => '5567999998888@s.whatsapp.net',
                    'fromMe' => false,
                    'id' => 'EVT-2',
                ],
                'messageType' => 'extendedtextmessage',
                'message' => [
                    'extendedTextMessage' => [
                        'text' => 'Texto vindo de data.messages[0]',
                    ],
                ],
            ]],
        ],
    ];

    $inbound = $adapter->normalizeInbound($payload);

    expect($inbound)->not->toBeNull()
        ->and($inbound?->contactPhone)->toBe('5567999998888')
        ->and($inbound?->text)->toBe('Texto vindo de data.messages[0]')
        ->and($inbound?->messageType)->toBe('extendedtextmessage')
        ->and($inbound?->externalMessageId)->toBe('EVT-2');
});

it('rejects evolution self message payload', function () {
    $adapter = app(EvolutionBotProviderAdapter::class);

    $payload = [
        'data' => [
            'messages' => [[
                'key' => [
                    'remoteJid' => '5567999998888@s.whatsapp.net',
                    'fromMe' => true,
                ],
                'message' => [
                    'conversation' => 'mensagem enviada pela propria conta',
                ],
            ]],
        ],
    ];

    expect($adapter->normalizeInbound($payload))->toBeNull();
});

it('rejects evolution group payload', function () {
    $adapter = app(EvolutionBotProviderAdapter::class);

    $payload = [
        'data' => [
            'key' => [
                'remoteJid' => '120363024999999999@g.us',
                'fromMe' => false,
            ],
            'message' => [
                'conversation' => 'Mensagem de grupo',
            ],
        ],
    ];

    expect($adapter->normalizeInbound($payload))->toBeNull();
});

it('rejects evolution payload without contact identifier', function () {
    $adapter = app(EvolutionBotProviderAdapter::class);

    $payload = [
        'event' => 'messages.upsert',
        'data' => [
            'message' => [
                'conversation' => 'Sem remetente',
            ],
        ],
    ];

    expect($adapter->normalizeInbound($payload))->toBeNull();
});

it('keeps processing evolution payload without text when contact exists', function () {
    $adapter = app(EvolutionBotProviderAdapter::class);

    $payload = [
        'event' => 'messages.upsert',
        'data' => [
            'key' => [
                'remoteJid' => '5567999998888@s.whatsapp.net',
                'fromMe' => false,
                'id' => 'EVT-3',
            ],
            'messageType' => 'stickerMessage',
        ],
    ];

    $inbound = $adapter->normalizeInbound($payload);

    expect($inbound)->not->toBeNull()
        ->and($inbound?->contactPhone)->toBe('5567999998888')
        ->and($inbound?->text)->toBeNull()
        ->and($inbound?->messageType)->toBe('stickermessage');
});

