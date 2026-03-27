<?php

namespace App\Services\Tenant\WhatsAppBot\DTO;

class ConversationResult
{
    /**
     * @param array<int, OutboundMessage> $outboundMessages
     * @param array<string, mixed> $stateUpdates
     */
    public function __construct(
        public readonly bool $processed,
        public readonly ?string $reason,
        public readonly array $outboundMessages = [],
        public readonly ?string $flow = null,
        public readonly ?string $step = null,
        public readonly array $stateUpdates = []
    ) {
    }

    public static function ignored(string $reason): self
    {
        return new self(
            processed: false,
            reason: $reason
        );
    }
}

