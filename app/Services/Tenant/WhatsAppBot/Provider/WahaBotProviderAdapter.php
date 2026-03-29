<?php

namespace App\Services\Tenant\WhatsAppBot\Provider;

use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\WhatsApp\WahaProvider;
use Illuminate\Support\Facades\Log;

class WahaBotProviderAdapter extends AbstractWhatsAppBotProviderAdapter
{
    public function providerKey(): string
    {
        return 'waha';
    }

    public function normalizeInbound(array $payload): ?InboundMessage
    {
        $rawContact = $this->firstNonEmptyString([
            data_get($payload, 'payload.from'),
            data_get($payload, 'payload.participant'),
            data_get($payload, 'payload.sender'),
            data_get($payload, 'payload.chatId'),
            data_get($payload, 'payload.key.remoteJid'),
            data_get($payload, 'message.from'),
            data_get($payload, 'message.participant'),
            data_get($payload, 'from'),
            data_get($payload, 'participant'),
            data_get($payload, 'sender'),
            data_get($payload, 'chatId'),
            data_get($payload, 'data.from'),
            data_get($payload, 'data.participant'),
            data_get($payload, 'data.sender'),
            data_get($payload, 'data.chatId'),
            data_get($payload, 'data.key.remoteJid'),
        ]);

        $fromMe = $this->resolveBoolean([
            data_get($payload, 'payload.fromMe'),
            data_get($payload, 'fromMe'),
            data_get($payload, 'data.fromMe'),
            data_get($payload, 'message.fromMe'),
        ]);

        if ($fromMe) {
            $this->logRejectedPayload('self_message', $payload, ['contact' => $rawContact]);
            return null;
        }

        if ($this->isUnsupportedChatTarget($rawContact)) {
            $this->logRejectedPayload('unsupported_chat_target', $payload, ['contact' => $rawContact]);
            return null;
        }

        $phone = $this->normalizePhone($rawContact);

        if ($phone === '') {
            $this->logRejectedPayload('missing_contact_phone', $payload, ['contact' => $rawContact]);
            return null;
        }

        $text = $this->firstNonEmptyString([
            data_get($payload, 'payload.body'),
            data_get($payload, 'payload.text.body'),
            data_get($payload, 'payload.text'),
            data_get($payload, 'payload.message.conversation'),
            data_get($payload, 'payload.message.extendedTextMessage.text'),
            data_get($payload, 'payload.message.imageMessage.caption'),
            data_get($payload, 'payload.message.videoMessage.caption'),
            data_get($payload, 'body'),
            data_get($payload, 'text'),
            data_get($payload, 'message.conversation'),
            data_get($payload, 'message.extendedTextMessage.text'),
            data_get($payload, 'message.imageMessage.caption'),
            data_get($payload, 'message.videoMessage.caption'),
            data_get($payload, 'data.body'),
            data_get($payload, 'data.text'),
            data_get($payload, 'data.message.conversation'),
            data_get($payload, 'data.message.extendedTextMessage.text'),
            data_get($payload, 'data.message.imageMessage.caption'),
            data_get($payload, 'data.message.videoMessage.caption'),
        ]);

        $messageType = strtolower(trim((string) $this->firstNonEmptyString([
            data_get($payload, 'payload.type'),
            data_get($payload, 'payload.messageType'),
            data_get($payload, 'payload.message.type'),
            data_get($payload, 'type'),
            data_get($payload, 'event'),
            data_get($payload, 'eventType'),
            data_get($payload, 'message.type'),
            data_get($payload, 'data.type'),
            data_get($payload, 'data.messageType'),
        ]))) ?: 'unknown';

        $externalMessageId = $this->firstNonEmptyString([
            data_get($payload, 'payload.id'),
            data_get($payload, 'payload.messageId'),
            data_get($payload, 'payload.key.id'),
            data_get($payload, 'id'),
            data_get($payload, 'message.id'),
            data_get($payload, 'message.messageId'),
            data_get($payload, 'data.id'),
            data_get($payload, 'data.messageId'),
            data_get($payload, 'data.key.id'),
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

    /**
     * @param array<int, mixed> $candidates
     */
    private function resolveBoolean(array $candidates): bool
    {
        foreach ($candidates as $candidate) {
            if (is_bool($candidate)) {
                return $candidate;
            }

            if ($candidate === null) {
                continue;
            }

            $normalized = filter_var((string) $candidate, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return false;
    }

    private function isUnsupportedChatTarget(?string $value): bool
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return false;
        }

        return str_contains($normalized, '@g.us')
            || str_contains($normalized, '@broadcast')
            || str_contains($normalized, 'status@broadcast');
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     */
    private function logRejectedPayload(string $reason, array $payload, array $context = []): void
    {
        $rootKeys = array_slice(array_keys($payload), 0, 20);

        Log::info('whatsapp_bot.inbound.normalize_rejected', array_merge([
            'provider' => $this->providerKey(),
            'reason' => $reason,
            'root_keys' => $rootKeys,
            'event' => (string) (data_get($payload, 'event') ?? ''),
            'event_type' => (string) (data_get($payload, 'eventType') ?? ''),
            'type' => (string) (data_get($payload, 'type') ?? ''),
        ], $context));
    }
}
