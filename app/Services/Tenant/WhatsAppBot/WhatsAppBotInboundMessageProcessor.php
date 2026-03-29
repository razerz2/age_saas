<?php

namespace App\Services\Tenant\WhatsAppBot;

use App\Exceptions\Tenant\WhatsAppBotConfigurationException;
use App\Services\FeatureAccessService;
use App\Services\Tenant\WhatsAppBot\Conversation\WhatsAppBotConversationOrchestrator;
use App\Services\Tenant\WhatsAppBot\Conversation\WhatsAppBotSessionService;
use App\Services\Tenant\WhatsAppBot\DTO\InboundProcessingResult;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\Tenant\WhatsAppBot\Provider\WhatsAppBotProviderAdapterFactory;
use App\Services\Tenant\WhatsAppBot\Provider\WhatsAppBotProviderResolver;
use App\Services\Tenant\WhatsAppBotConfigService;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppBotInboundMessageProcessor
{
    public function __construct(
        private readonly FeatureAccessService $featureAccessService,
        private readonly WhatsAppBotProviderResolver $providerResolver,
        private readonly WhatsAppBotProviderAdapterFactory $adapterFactory,
        private readonly WhatsAppBotSessionService $sessionService,
        private readonly WhatsAppBotConversationOrchestrator $conversationOrchestrator
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function process(string $providerHint, array $payload): InboundProcessingResult
    {
        $startedAt = microtime(true);
        $tenant = tenant();
        if (!$tenant) {
            return InboundProcessingResult::failed('tenant_not_resolved');
        }

        $tenantId = (string) $tenant->id;
        $incomingProvider = $this->adapterFactory->normalizeProvider($providerHint);

        if (!$this->featureAccessService->hasFeature(WhatsAppBotConfigService::FEATURE_NAME, $tenant)) {
            Log::warning('whatsapp_bot.inbound.ignored', [
                'tenant_id' => $tenantId,
                'provider_hint' => $providerHint,
                'incoming_provider' => $incomingProvider,
                'action' => 'inbound.receive',
                'result' => 'ignored',
                'reason' => 'feature_not_enabled_for_plan',
                'processing_ms' => $this->processingMs($startedAt),
            ]);

            return InboundProcessingResult::ignored('feature_not_enabled_for_plan');
        }

        try {
            $resolved = $this->providerResolver->resolveForCurrentTenant(requireEnabled: false);
            $configuredProvider = (string) ($resolved['provider'] ?? '');

            Log::info('whatsapp_bot.inbound.received', [
                'tenant_id' => $tenantId,
                'channel' => 'whatsapp',
                'provider_hint' => $providerHint,
                'incoming_provider' => $incomingProvider,
                'provider' => $configuredProvider,
                'action' => 'inbound.receive',
                'result' => 'success',
            ]);

            if ($incomingProvider !== $configuredProvider) {
                Log::warning('whatsapp_bot.inbound.ignored', [
                    'tenant_id' => $tenantId,
                    'provider' => $configuredProvider,
                    'incoming_provider' => $incomingProvider,
                    'action' => 'provider.check',
                    'result' => 'ignored',
                    'reason' => 'provider_mismatch',
                    'payload_snapshot' => $this->summarizePayload($payload),
                    'processing_ms' => $this->processingMs($startedAt),
                ]);

                return InboundProcessingResult::ignored(
                    reason: 'provider_mismatch',
                    provider: $configuredProvider
                );
            }

            $this->providerResolver->applyRuntimeConfig($resolved);
            $adapter = $this->adapterFactory->make($configuredProvider);
            $inbound = $adapter->normalizeInbound($payload);

            if ($inbound === null) {
                Log::info('whatsapp_bot.inbound.ignored', [
                    'tenant_id' => $tenantId,
                    'provider' => $configuredProvider,
                    'action' => 'inbound.normalize',
                    'result' => 'ignored',
                    'reason' => 'payload_not_supported',
                    'payload_snapshot' => $this->summarizePayload($payload),
                    'processing_ms' => $this->processingMs($startedAt),
                ]);

                return InboundProcessingResult::ignored(
                    reason: 'payload_not_supported',
                    provider: $configuredProvider
                );
            }

            if (!($resolved['enabled'] ?? false)) {
                return $this->handleDisabledBot($configuredProvider, $adapter, $inbound->contactPhone, $inbound->messageType, $resolved, $tenantId, $startedAt);
            }

            $session = $this->sessionService->openOrCreate($inbound, $configuredProvider);
            $previousFlow = (string) ($session->current_flow ?? '');
            $previousStep = (string) ($session->current_step ?? '');

            $conversationResult = $this->conversationOrchestrator->handle($session, $inbound);
            $this->sessionService->applyConversationResult($session, $conversationResult);

            $outboundSent = 0;
            $outboundFailed = 0;

            foreach ($conversationResult->outboundMessages as $outboundMessage) {
                $sent = $adapter->sendOutbound($outboundMessage);
                $this->sessionService->registerOutboundAttempt($session, $outboundMessage, $sent);
                $sent ? $outboundSent++ : $outboundFailed++;
            }

            Log::info('whatsapp_bot.flow.transition', [
                'tenant_id' => $tenantId,
                'provider' => $configuredProvider,
                'phone' => $inbound->contactPhone,
                'from_flow' => $previousFlow,
                'from_step' => $previousStep,
                'to_flow' => (string) ($session->current_flow ?? ''),
                'to_step' => (string) ($session->current_step ?? ''),
                'action' => 'flow.transition',
                'result' => 'success',
            ]);

            $resultStatus = $outboundFailed > 0 ? 'partial_success' : 'success';

            Log::info('whatsapp_bot.inbound.processed', [
                'tenant_id' => $tenantId,
                'provider' => $configuredProvider,
                'channel' => $inbound->channel,
                'phone' => $inbound->contactPhone,
                'message_type' => $inbound->messageType,
                'flow' => (string) ($session->current_flow ?? ''),
                'step' => (string) ($session->current_step ?? ''),
                'action' => 'inbound.process',
                'result' => $resultStatus,
                'session_id' => (string) $session->id,
                'outbound_sent' => $outboundSent,
                'outbound_failed' => $outboundFailed,
                'processing_ms' => $this->processingMs($startedAt),
            ]);

            return InboundProcessingResult::processed(
                reason: $outboundFailed > 0 ? 'partial_success' : 'ok',
                provider: $configuredProvider,
                phone: $inbound->contactPhone,
                messageType: $inbound->messageType,
                outboundSent: $outboundSent,
                outboundFailed: $outboundFailed
            );
        } catch (WhatsAppBotConfigurationException $exception) {
            Log::error('whatsapp_bot.inbound.failed', [
                'tenant_id' => $tenantId,
                'provider_hint' => $providerHint,
                'incoming_provider' => $incomingProvider,
                'action' => 'inbound.process',
                'result' => 'error',
                'reason' => 'invalid_configuration',
                'error' => $exception->getMessage(),
                'processing_ms' => $this->processingMs($startedAt),
            ]);

            return InboundProcessingResult::failed('invalid_configuration');
        } catch (Throwable $exception) {
            Log::error('whatsapp_bot.inbound.failed', [
                'tenant_id' => $tenantId,
                'provider_hint' => $providerHint,
                'incoming_provider' => $incomingProvider,
                'action' => 'inbound.process',
                'result' => 'error',
                'reason' => 'processing_error',
                'error' => $exception->getMessage(),
                'processing_ms' => $this->processingMs($startedAt),
            ]);

            return InboundProcessingResult::failed('processing_error');
        }
    }

    /**
     * @param array<string, mixed> $resolved
     */
    private function handleDisabledBot(
        string $provider,
        $adapter,
        string $phone,
        string $messageType,
        array $resolved,
        string $tenantId,
        float $startedAt
    ): InboundProcessingResult {
        $disabledMessage = trim((string) data_get($resolved, 'settings.disabled_message', ''));

        if ($disabledMessage === '') {
            Log::info('whatsapp_bot.inbound.ignored', [
                'tenant_id' => $tenantId,
                'provider' => $provider,
                'phone' => $phone,
                'message_type' => $messageType,
                'action' => 'bot.enabled.check',
                'result' => 'ignored',
                'reason' => 'bot_disabled',
                'processing_ms' => $this->processingMs($startedAt),
            ]);

            return InboundProcessingResult::ignored('bot_disabled', $provider, $phone, $messageType);
        }

        $sent = $adapter->sendOutbound(OutboundMessage::text(
            to: $phone,
            text: $disabledMessage,
            meta: ['kind' => 'bot_disabled']
        ));

        Log::info('whatsapp_bot.inbound.disabled_response', [
            'tenant_id' => $tenantId,
            'provider' => $provider,
            'phone' => $phone,
            'message_type' => $messageType,
            'action' => 'bot.disabled.response',
            'result' => $sent ? 'success' : 'error',
            'processing_ms' => $this->processingMs($startedAt),
        ]);

        if (!$sent) {
            return InboundProcessingResult::ignored('bot_disabled', $provider, $phone, $messageType);
        }

        return InboundProcessingResult::processed(
            reason: 'bot_disabled_with_response',
            provider: $provider,
            phone: $phone,
            messageType: $messageType,
            outboundSent: 1,
            outboundFailed: 0
        );
    }

    private function processingMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function summarizePayload(array $payload): array
    {
        return [
            'root_keys' => array_slice(array_keys($payload), 0, 20),
            'event' => (string) (data_get($payload, 'event') ?? ''),
            'event_type' => (string) (data_get($payload, 'eventType') ?? ''),
            'type' => (string) (data_get($payload, 'type') ?? ''),
            'has_payload_node' => is_array(data_get($payload, 'payload')),
            'has_data_node' => is_array(data_get($payload, 'data')),
            'has_messages_node' => is_array(data_get($payload, 'data.messages')),
            'has_message_node' => is_array(data_get($payload, 'data.message')),
        ];
    }
}
