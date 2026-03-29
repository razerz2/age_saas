<?php

namespace App\Services\Tenant\WhatsAppBot\Provider;

use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\WhatsApp\EvolutionProvider;
use Illuminate\Support\Facades\Log;

class EvolutionBotProviderAdapter extends AbstractWhatsAppBotProviderAdapter
{
    public function providerKey(): string
    {
        return 'evolution';
    }

    public function normalizeInbound(array $payload): ?InboundMessage
    {
        $fromMe = $this->resolveBoolean([
            data_get($payload, 'data.key.fromMe'),
            data_get($payload, 'data.fromMe'),
            data_get($payload, 'data.messages.0.key.fromMe'),
            data_get($payload, 'data.messages.0.fromMe'),
            data_get($payload, 'key.fromMe'),
            data_get($payload, 'fromMe'),
        ]);
        if ($fromMe) {
            $this->logRejectedPayload('self_message', $payload);
            return null;
        }

        $rawContact = $this->firstNonEmptyString([
            data_get($payload, 'data.key.remoteJid'),
            data_get($payload, 'data.key.participant'),
            data_get($payload, 'data.messages.0.key.remoteJid'),
            data_get($payload, 'data.messages.0.key.participant'),
            data_get($payload, 'data.from'),
            data_get($payload, 'data.sender'),
            data_get($payload, 'data.chatId'),
            data_get($payload, 'from'),
            data_get($payload, 'sender'),
            data_get($payload, 'chatId'),
            data_get($payload, 'key.remoteJid'),
            data_get($payload, 'key.participant'),
        ]);

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
            data_get($payload, 'data.message.conversation'),
            data_get($payload, 'data.message.extendedTextMessage.text'),
            data_get($payload, 'data.message.imageMessage.caption'),
            data_get($payload, 'data.message.videoMessage.caption'),
            data_get($payload, 'data.message.buttonsResponseMessage.selectedDisplayText'),
            data_get($payload, 'data.message.listResponseMessage.title'),
            data_get($payload, 'data.messages.0.message.conversation'),
            data_get($payload, 'data.messages.0.message.extendedTextMessage.text'),
            data_get($payload, 'data.messages.0.message.imageMessage.caption'),
            data_get($payload, 'data.messages.0.message.videoMessage.caption'),
            data_get($payload, 'data.messages.0.message.buttonsResponseMessage.selectedDisplayText'),
            data_get($payload, 'data.messages.0.message.listResponseMessage.title'),
            data_get($payload, 'data.messages.0.body'),
            data_get($payload, 'data.body'),
            data_get($payload, 'message'),
            data_get($payload, 'body'),
        ]);

        $messageType = strtolower(trim((string) $this->firstNonEmptyString([
            data_get($payload, 'data.messageType'),
            data_get($payload, 'data.messages.0.messageType'),
            data_get($payload, 'data.messages.0.type'),
            data_get($payload, 'event'),
            data_get($payload, 'type'),
        ]))) ?: 'unknown';

        $externalMessageId = $this->firstNonEmptyString([
            data_get($payload, 'data.key.id'),
            data_get($payload, 'data.messages.0.key.id'),
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
            'type' => (string) (data_get($payload, 'type') ?? ''),
        ], $context));
    }
}
