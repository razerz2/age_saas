<?php

namespace App\Services\Tenant\WhatsAppBot\DTO;

class InboundProcessingResult
{
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_IGNORED = 'ignored';
    public const STATUS_FAILED = 'failed';

    public function __construct(
        public readonly string $status,
        public readonly string $reason,
        public readonly ?string $provider = null,
        public readonly ?string $phone = null,
        public readonly ?string $messageType = null,
        public readonly int $outboundSent = 0,
        public readonly int $outboundFailed = 0
    ) {
    }

    public static function processed(
        string $reason = 'ok',
        ?string $provider = null,
        ?string $phone = null,
        ?string $messageType = null,
        int $outboundSent = 0,
        int $outboundFailed = 0
    ): self {
        return new self(
            status: self::STATUS_PROCESSED,
            reason: $reason,
            provider: $provider,
            phone: $phone,
            messageType: $messageType,
            outboundSent: $outboundSent,
            outboundFailed: $outboundFailed
        );
    }

    public static function ignored(
        string $reason,
        ?string $provider = null,
        ?string $phone = null,
        ?string $messageType = null
    ): self {
        return new self(
            status: self::STATUS_IGNORED,
            reason: $reason,
            provider: $provider,
            phone: $phone,
            messageType: $messageType
        );
    }

    public static function failed(string $reason, ?string $provider = null): self
    {
        return new self(
            status: self::STATUS_FAILED,
            reason: $reason,
            provider: $provider
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'reason' => $this->reason,
            'provider' => $this->provider,
            'phone' => $this->phone,
            'message_type' => $this->messageType,
            'outbound_sent' => $this->outboundSent,
            'outbound_failed' => $this->outboundFailed,
        ];
    }
}

