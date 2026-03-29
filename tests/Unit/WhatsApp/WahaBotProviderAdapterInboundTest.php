<?php

use App\Services\Tenant\WhatsAppBot\Provider\WahaBotProviderAdapter;
use Tests\TestCase;

uses(TestCase::class);

it('normalizes waha canonical inbound payload', function () {
    $adapter = app(WahaBotProviderAdapter::class);

    $payload = [
        'event' => 'message',
        'payload' => [
            'from' => '5567999998888@c.us',
            'body' => 'Oi bot',
            'type' => 'chat',
            'id' => 'wamid.ABC123',
        ],
    ];

    $inbound = $adapter->normalizeInbound($payload);

    expect($inbound)->not->toBeNull()
        ->and($inbound?->contactPhone)->toBe('5567999998888')
        ->and($inbound?->text)->toBe('Oi bot')
        ->and($inbound?->messageType)->toBe('chat')
        ->and($inbound?->externalMessageId)->toBe('wamid.ABC123');
});

it('ignores internal senderAlt identifiers and keeps real contact phone from chatId', function () {
    $adapter = app(WahaBotProviderAdapter::class);

    $payload = [
        'event' => 'message',
        'payload' => [
            'chatId' => '556793087866@c.us',
            'from' => '556793087866@c.us',
            'senderAlt' => '215084110503978:16@s.whatsapp.net',
            'sender' => '215084110503978:16@s.whatsapp.net',
            'body' => 'teste inbound',
            'type' => 'chat',
            'id' => 'wamid.real.1',
        ],
    ];

    $inbound = $adapter->normalizeInbound($payload);

    expect($inbound)->not->toBeNull()
        ->and($inbound?->contactPhone)->toBe('556793087866')
        ->and($inbound?->text)->toBe('teste inbound');
});

it('falls back to payload.from when chatId contains internal lid identifier', function () {
    $adapter = app(WahaBotProviderAdapter::class);

    $payload = [
        'payload' => [
            'chatId' => '123456789012345@lid',
            'from' => '556793087866@c.us',
            'body' => 'teste fallback',
            'type' => 'chat',
            'id' => 'wamid.real.2',
        ],
    ];

    $inbound = $adapter->normalizeInbound($payload);

    expect($inbound)->not->toBeNull()
        ->and($inbound?->contactPhone)->toBe('556793087866')
        ->and($inbound?->text)->toBe('teste fallback');
});

it('extracts phone from payload _data info senderAlt when from identifiers are internal lid', function () {
    $adapter = app(WahaBotProviderAdapter::class);

    $payload = [
        'event' => 'message',
        'payload' => [
            'from' => '215084110503978@lid',
            'body' => 'mensagem real',
            'type' => 'chat',
            'id' => 'wamid.real.3',
            '_data' => [
                'Info' => [
                    'Chat' => '215084110503978@lid',
                    'Sender' => '215084110503978:3@lid',
                    'SenderAlt' => '556793087866:3@s.whatsapp.net',
                ],
            ],
        ],
    ];

    $inbound = $adapter->normalizeInbound($payload);

    expect($inbound)->not->toBeNull()
        ->and($inbound?->contactPhone)->toBe('556793087866')
        ->and($inbound?->text)->toBe('mensagem real')
        ->and($inbound?->messageType)->toBe('chat')
        ->and($inbound?->externalMessageId)->toBe('wamid.real.3');
});

it('normalizes waha inbound payload with nested data.message fields', function () {
    $adapter = app(WahaBotProviderAdapter::class);

    $payload = [
        'data' => [
            'from' => '5567999998888@c.us',
            'messageType' => 'conversation',
            'message' => [
                'conversation' => 'Texto via data.message',
            ],
            'key' => [
                'id' => 'msg-987',
            ],
        ],
    ];

    $inbound = $adapter->normalizeInbound($payload);

    expect($inbound)->not->toBeNull()
        ->and($inbound?->contactPhone)->toBe('5567999998888')
        ->and($inbound?->text)->toBe('Texto via data.message')
        ->and($inbound?->messageType)->toBe('conversation')
        ->and($inbound?->externalMessageId)->toBe('msg-987');
});

it('rejects waha self message payload', function () {
    $adapter = app(WahaBotProviderAdapter::class);

    $payload = [
        'payload' => [
            'from' => '5567999998888@c.us',
            'fromMe' => true,
            'body' => 'mensagem enviada pelo proprio bot',
        ],
    ];

    expect($adapter->normalizeInbound($payload))->toBeNull();
});

it('rejects waha group payload', function () {
    $adapter = app(WahaBotProviderAdapter::class);

    $payload = [
        'payload' => [
            'chatId' => '120363024999999999@g.us',
            'body' => 'Mensagem de grupo',
        ],
    ];

    expect($adapter->normalizeInbound($payload))->toBeNull();
});

it('rejects waha payload without contact identifier', function () {
    $adapter = app(WahaBotProviderAdapter::class);

    $payload = [
        'event' => 'message',
        'payload' => [
            'body' => 'Sem remetente',
        ],
    ];

    expect($adapter->normalizeInbound($payload))->toBeNull();
});

it('keeps processing waha payload without text when contact is present', function () {
    $adapter = app(WahaBotProviderAdapter::class);

    $payload = [
        'payload' => [
            'from' => '5567999998888@c.us',
            'type' => 'image',
            'id' => 'img-123',
        ],
    ];

    $inbound = $adapter->normalizeInbound($payload);

    expect($inbound)->not->toBeNull()
        ->and($inbound?->contactPhone)->toBe('5567999998888')
        ->and($inbound?->text)->toBeNull()
        ->and($inbound?->messageType)->toBe('image');
});
