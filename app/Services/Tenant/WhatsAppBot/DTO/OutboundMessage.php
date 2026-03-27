<?php

namespace App\Services\Tenant\WhatsAppBot\DTO;

class OutboundMessage
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public readonly string $to,
        public readonly string $type,
        public readonly string $text,
        public readonly array $meta = []
    ) {
    }

    /**
     * @param array<string, mixed> $meta
     */
    public static function text(string $to, string $text, array $meta = []): self
    {
        return new self(
            to: $to,
            type: 'text',
            text: $text,
            meta: $meta
        );
    }
}

