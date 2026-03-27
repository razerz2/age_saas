<?php

namespace App\Services\Tenant\WhatsAppBot\Provider;

use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\WhatsApp\WahaProvider;

class WahaBotProviderAdapter extends AbstractWhatsAppBotProviderAdapter
{
    public function providerKey(): string
    {
        return 'waha';
    }

    public function normalizeInbound(array $payload): ?InboundMessage
    {
        $phone = $this->normalizePhone($this->firstNonEmptyString([
            data_get($payload, 'payload.from'),
            data_get($payload, 'payload.participant'),
            data_get($payload, 'from'),
            data_get($payload, 'participant'),
            data_get($payload, 'chatId'),
            data_get($payload, 'data.from'),
            data_get($payload, 'data.chatId'),
        ]));

        if ($phone === '') {
            return null;
        }

        $text = $this->firstNonEmptyString([
            data_get($payload, 'payload.body'),
            data_get($payload, 'payload.text.body'),
            data_get($payload, 'body'),
            data_get($payload, 'text'),
            data_get($payload, 'data.body'),
        ]);

        $messageType = strtolower(trim((string) $this->firstNonEmptyString([
            data_get($payload, 'payload.type'),
            data_get($payload, 'type'),
            data_get($payload, 'event'),
            data_get($payload, 'eventType'),
        ]))) ?: 'unknown';

        $externalMessageId = $this->firstNonEmptyString([
            data_get($payload, 'payload.id'),
            data_get($payload, 'id'),
            data_get($payload, 'data.id'),
        ]);

        return new InboundMessage(
            provider: $this->providerKey(),
            channel: 'whatsapp',
            contactPhone: $phone,
            contactIdentifier: $phone,
            messageType: $messageType,
            text: $text,
            externalMessageId: $externalMessageId,
            payload: $payload
        );
    }

    public function sendOutbound(OutboundMessage $message): bool
    {
        $text = $this->normalizeOutboundText($message->text);
        if ($text === '') {
            return false;
        }

        return app(WahaProvider::class)->sendMessage($message->to, $text);
    }
}
