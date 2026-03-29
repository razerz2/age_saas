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

