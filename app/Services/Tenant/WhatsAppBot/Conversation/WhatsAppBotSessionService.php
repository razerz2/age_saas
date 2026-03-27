<?php

namespace App\Services\Tenant\WhatsAppBot\Conversation;

use App\Models\Tenant\WhatsAppBotSession;
use App\Services\Tenant\WhatsAppBot\DTO\ConversationResult;
use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\WhatsApp\PhoneNormalizer;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WhatsAppBotSessionService
{
    public function openOrCreate(InboundMessage $message, string $provider): WhatsAppBotSession
    {
        $tenantId = $this->currentTenantId();
        $normalizedPhone = PhoneNormalizer::normalizeE164((string) $message->contactPhone);
        if ($normalizedPhone === '') {
            throw new RuntimeException('Unable to resolve normalized phone for WhatsApp bot session.');
        }

        $session = WhatsAppBotSession::query()->firstOrNew([
            'tenant_id' => $tenantId,
            'channel' => 'whatsapp',
            'contact_phone' => $normalizedPhone,
        ]);

        if (!$session->exists) {
            $session->status = 'active';
            $session->current_flow = 'root';
            $session->current_step = 'initial';
            $session->state = [];
            $session->meta = [];
        }

        if (!is_array($session->state)) {
            $session->state = [];
        }

        if (!is_array($session->meta)) {
            $session->meta = [];
        }

        $session->provider = $provider;
        $session->contact_identifier = $message->contactIdentifier ?? $session->contact_identifier;
        $session->last_inbound_message_type = $message->messageType;
        $session->last_inbound_message_at = now();
        $session->last_payload = $message->payload;
        $session->status = 'active';

        $meta = is_array($session->meta) ? $session->meta : [];
        $meta['last_provider_message_id'] = $message->externalMessageId;
        $meta['last_normalized_phone'] = $normalizedPhone;
        $meta['last_inbound_provider'] = $provider;
        $session->meta = $meta;

        if ($this->isCorruptedSession($session)) {
            $this->resetCorruptedSession($session, $meta);
            Log::warning('whatsapp_bot.session.reset', [
                'tenant_id' => $tenantId,
                'session_id' => (string) $session->id,
                'phone' => $normalizedPhone,
                'reason' => 'corrupted_session_state',
            ]);
        }

        $session->save();

        return $session;
    }

    public function applyConversationResult(WhatsAppBotSession $session, ConversationResult $result): void
    {
        if (!$result->processed) {
            return;
        }

        if ($result->flow !== null) {
            $session->current_flow = $result->flow;
        }

        if ($result->step !== null) {
            $session->current_step = $result->step;
        }

        $session->state = array_merge(
            is_array($session->state) ? $session->state : [],
            $result->stateUpdates
        );

        $session->save();
    }

    public function registerOutboundAttempt(WhatsAppBotSession $session, OutboundMessage $message, bool $sent): void
    {
        $meta = is_array($session->meta) ? $session->meta : [];
        $meta['outbound_attempts'] = (int) ($meta['outbound_attempts'] ?? 0) + 1;
        $meta['outbound_sent'] = (int) ($meta['outbound_sent'] ?? 0) + ($sent ? 1 : 0);
        $meta['outbound_failed'] = (int) ($meta['outbound_failed'] ?? 0) + ($sent ? 0 : 1);
        $meta['last_outbound_type'] = $message->type;

        $session->meta = $meta;
        $session->last_outbound_message_at = now();
        $session->save();
    }

    private function currentTenantId(): string
    {
        $tenant = tenant();
        if (!$tenant || !isset($tenant->id)) {
            throw new RuntimeException('Unable to resolve tenant for WhatsApp bot session.');
        }

        return (string) $tenant->id;
    }

    private function isCorruptedSession(WhatsAppBotSession $session): bool
    {
        $flow = (string) ($session->current_flow ?? '');
        $step = (string) ($session->current_step ?? '');

        if ($flow === '' || $step === '') {
            return true;
        }

        if (strlen($flow) > 100 || strlen($step) > 100) {
            return true;
        }

        return !is_array($session->state) || !is_array($session->meta);
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function resetCorruptedSession(WhatsAppBotSession $session, array $meta): void
    {
        $session->status = 'active';
        $session->current_flow = 'menu';
        $session->current_step = 'menu.awaiting_option';
        $session->state = [
            'schedule' => [],
            'cancel' => [],
        ];

        $meta['resets'] = (int) ($meta['resets'] ?? 0) + 1;
        $meta['last_reset_reason'] = 'corrupted_session_state';
        $meta['last_reset_at'] = now()->toDateTimeString();

        $session->meta = $meta;
    }
}
