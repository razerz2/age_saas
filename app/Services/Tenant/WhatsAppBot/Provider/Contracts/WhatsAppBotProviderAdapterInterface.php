<?php

namespace App\Services\Tenant\WhatsAppBot\Provider\Contracts;

use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;

interface WhatsAppBotProviderAdapterInterface
{
    public function providerKey(): string;

    /**
     * @param array<string, mixed> $payload
     */
    public function normalizeInbound(array $payload): ?InboundMessage;

    public function sendOutbound(OutboundMessage $message): bool;
}

