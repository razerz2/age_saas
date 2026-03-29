<?php

use App\Services\Tenant\WhatsAppBot\DTO\InboundProcessingResult;
use App\Services\Tenant\WhatsAppBot\WhatsAppBotInboundMessageProcessor;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);

it('uses route provider parameter instead of tenant slug in bot webhook', function () {
    $this->mock(WhatsAppBotInboundMessageProcessor::class, function (MockInterface $mock): void {
        $mock->shouldReceive('process')
            ->once()
            ->withArgs(function (string $providerHint, array $payload): bool {
                return $providerHint === 'waha'
                    && ($payload['text'] ?? null) === 'oi';
            })
            ->andReturn(InboundProcessingResult::ignored(
                reason: 'payload_not_supported',
                provider: 'waha'
            ));
    });

    $response = $this->withoutMiddleware()->postJson(
        '/customer/clinica-teste/webhooks/whatsapp/bot/waha',
        ['text' => 'oi']
    );

    $response->assertStatus(202)
        ->assertJson([
            'status' => 'ignored',
            'reason' => 'payload_not_supported',
            'provider' => 'waha',
        ]);
});

