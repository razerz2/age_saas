<?php

namespace App\Services\Tenant\WhatsAppBot\Domain;

use App\Services\Tenant\WhatsAppBot\Conversation\WhatsAppBotIntentRouter;
use App\Services\Tenant\WhatsAppBotConfigService;

class WhatsAppBotDomainService
{
    public function __construct(
        private readonly WhatsAppBotConfigService $configService
    ) {
    }

    public function isIntentEnabled(string $intent): bool
    {
        $settings = $this->configService->getSettings();

        return match ($intent) {
            WhatsAppBotIntentRouter::INTENT_SCHEDULE => (bool) ($settings['allow_schedule'] ?? false),
            WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS => (bool) ($settings['allow_view_appointments'] ?? false),
            WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS => (bool) ($settings['allow_cancel_appointments'] ?? false),
            default => true,
        };
    }

    public function unavailableIntentMessage(string $intent): string
    {
        return match ($intent) {
            WhatsAppBotIntentRouter::INTENT_SCHEDULE => 'Agendamento por WhatsApp nao esta habilitado para esta conta.',
            WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS => 'Visualizacao de agendamentos por WhatsApp nao esta habilitada para esta conta.',
            WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS => 'Cancelamento de agendamentos por WhatsApp nao esta habilitado para esta conta.',
            default => 'Esse tipo de solicitacao nao esta habilitado para este bot.',
        };
    }

    public function notImplementedMessage(string $intent): string
    {
        return match ($intent) {
            WhatsAppBotIntentRouter::INTENT_SCHEDULE => 'Fluxo de agendamento em implantacao. Esta etapa prepara apenas a base arquitetural.',
            WhatsAppBotIntentRouter::INTENT_VIEW_APPOINTMENTS => 'Fluxo de consulta de agendamentos em implantacao. Esta etapa prepara apenas a base arquitetural.',
            WhatsAppBotIntentRouter::INTENT_CANCEL_APPOINTMENTS => 'Fluxo de cancelamento em implantacao. Esta etapa prepara apenas a base arquitetural.',
            default => 'Recebemos sua mensagem. Em breve liberaremos o menu completo do bot.',
        };
    }
}

