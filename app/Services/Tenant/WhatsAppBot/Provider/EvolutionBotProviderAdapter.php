<?php

namespace App\Services\Tenant\WhatsAppBot\Provider;

use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\WhatsApp\EvolutionProvider;

class EvolutionBotProviderAdapter extends AbstractWhatsAppBotProviderAdapter
{
    public function providerKey(): string
    {
        return 'evolution';
    }

    public function normalizeInbound(array $payload): ?InboundMessage
    {
        $fromMe = filter_var((string) (data_get($payload, 'data.key.fromMe') ?? ''), FILTER_VALIDATE_BOOLEAN);
        if ($fromMe) {
            return null;
        }

        $phone = $this->normalizePhone($this->firstNonEmptyString([
            data_get($payload, 'data.key.remoteJid'),
            data_get($payload, 'data.key.participant'),
            data_get($payload, 'data.from'),
            data_get($payload, 'from'),
            data_get($payload, 'sender'),
        ]));

        if ($phone === '') {
            return null;
        }

        $text = $this->firstNonEmptyString([
            data_get($payload, 'data.message.conversation'),
            data_get($payload, 'data.message.extendedTextMessage.text'),
            data_get($payload, 'data.message.imageMessage.caption'),
            data_get($payload, 'data.message.videoMessage.caption'),
            data_get($payload, 'data.message.buttonsResponseMessage.selectedDisplayText'),
            data_get($payload, 'data.message.listResponseMessage.title'),
            data_get($payload, 'data.body'),
            data_get($payload, 'message'),
        ]);

        $messageType = strtolower(trim((string) $this->firstNonEmptyString([
            data_get($payload, 'data.messageType'),
            data_get($payload, 'event'),
            data_get($payload, 'type'),
        ]))) ?: 'unknown';

        $externalMessageId = $this->firstNonEmptyString([
            data_get($payload, 'data.key.id'),
            data_get($payload, 'data.id'),
            data_get($payload, 'id'),
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

        return app(EvolutionProvider::class)->sendMessage($message->to, $text);
    }
}

