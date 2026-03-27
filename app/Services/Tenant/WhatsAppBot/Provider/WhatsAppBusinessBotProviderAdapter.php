<?php

namespace App\Services\Tenant\WhatsAppBot\Provider;

use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\WhatsApp\WhatsAppBusinessProvider;

class WhatsAppBusinessBotProviderAdapter extends AbstractWhatsAppBotProviderAdapter
{
    public function providerKey(): string
    {
        return 'whatsapp_business';
    }

    public function normalizeInbound(array $payload): ?InboundMessage
    {
        $entries = (array) ($payload['entry'] ?? []);

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $changes = (array) ($entry['changes'] ?? []);
            foreach ($changes as $change) {
                if (!is_array($change)) {
                    continue;
                }

                $value = (array) ($change['value'] ?? []);
                $messages = (array) ($value['messages'] ?? []);
                $contacts = (array) ($value['contacts'] ?? []);
                $contactWaId = trim((string) data_get($contacts, '0.wa_id', '')) ?: null;

                foreach ($messages as $message) {
                    if (!is_array($message)) {
                        continue;
                    }

                    $phone = $this->normalizePhone((string) ($message['from'] ?? ''));
                    if ($phone === '') {
                        continue;
                    }

                    $type = strtolower(trim((string) ($message['type'] ?? 'unknown'))) ?: 'unknown';
                    $text = $this->extractText($message, $type);
                    $externalMessageId = trim((string) ($message['id'] ?? '')) ?: null;

                    return new InboundMessage(
                        provider: $this->providerKey(),
                        channel: 'whatsapp',
                        contactPhone: $phone,
                        contactIdentifier: $contactWaId,
                        messageType: $type,
                        text: $text,
                        externalMessageId: $externalMessageId,
                        payload: $payload
                    );
                }
            }
        }

        return null;
    }

    public function sendOutbound(OutboundMessage $message): bool
    {
        $text = $this->normalizeOutboundText($message->text);
        if ($text === '') {
            return false;
        }

        return app(WhatsAppBusinessProvider::class)->sendMessage($message->to, $text);
    }

    /**
     * @param array<string, mixed> $message
     */
    private function extractText(array $message, string $type): ?string
    {
        if ($type === 'text') {
            return $this->firstNonEmptyString([
                data_get($message, 'text.body'),
            ]);
        }

        if ($type === 'interactive') {
            return $this->firstNonEmptyString([
                data_get($message, 'interactive.button_reply.title'),
                data_get($message, 'interactive.button_reply.id'),
                data_get($message, 'interactive.list_reply.title'),
                data_get($message, 'interactive.list_reply.id'),
            ]);
        }

        return $this->firstNonEmptyString([
            data_get($message, 'caption'),
            data_get($message, 'button.text'),
        ]);
    }
}
