<?php

namespace App\Services\Tenant\WhatsAppBot\DTO;

class InboundMessage
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly string $provider,
        public readonly string $channel,
        public readonly string $contactPhone,
        public readonly ?string $contactIdentifier,
        public readonly string $messageType,
        public readonly ?string $text,
        public readonly ?string $externalMessageId,
        public readonly array $payload
    ) {
    }

    public function hasText(): bool
    {
        return $this->text !== null && trim($this->text) !== '';
    }
}

