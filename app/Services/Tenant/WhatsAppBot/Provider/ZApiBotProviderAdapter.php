<?php

namespace App\Services\Tenant\WhatsAppBot\Provider;

use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\WhatsApp\ZApiProvider;

class ZApiBotProviderAdapter extends AbstractWhatsAppBotProviderAdapter
{
    public function providerKey(): string
    {
        return 'zapi';
    }

    public function normalizeInbound(array $payload): ?InboundMessage
    {
        $phone = $this->normalizePhone($this->firstNonEmptyString([
            data_get($payload, 'phone'),
            data_get($payload, 'from'),
            data_get($payload, 'chatId'),
            data_get($payload, 'data.phone'),
            data_get($payload, 'data.from'),
            data_get($payload, 'data.chatId'),
            data_get($payload, 'message.phone'),
        ]));

        if ($phone === '') {
            return null;
        }

        $text = $this->firstNonEmptyString([
            data_get($payload, 'text.message'),
            data_get($payload, 'text.body'),
            data_get($payload, 'message'),
            data_get($payload, 'body'),
            data_get($payload, 'data.message'),
            data_get($payload, 'data.body'),
        ]);

        $messageType = strtolower(trim((string) $this->firstNonEmptyString([
            data_get($payload, 'messageType'),
            data_get($payload, 'type'),
            data_get($payload, 'eventType'),
        ]))) ?: 'unknown';

        $externalMessageId = $this->firstNonEmptyString([
            data_get($payload, 'messageId'),
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

        return app(ZApiProvider::class)->sendMessage($message->to, $text);
    }
}
