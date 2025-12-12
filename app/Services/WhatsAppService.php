<?php

namespace App\Services;

use App\Services\WhatsApp\WhatsAppProviderInterface;
use App\Services\WhatsApp\WhatsAppBusinessProvider;
use App\Services\WhatsApp\ZApiProvider;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected WhatsAppProviderInterface $provider;

    public function __construct()
    {
        $this->provider = $this->resolveProvider();
    }

    /**
     * Resolve qual provedor usar baseado na configuração
     *
     * @return WhatsAppProviderInterface
     */
    protected function resolveProvider(): WhatsAppProviderInterface
    {
        $provider = config('services.whatsapp.provider', 'whatsapp_business');

        return match ($provider) {
            'zapi', 'z-api' => new ZApiProvider(),
            'whatsapp_business', 'business' => new WhatsAppBusinessProvider(),
            default => new WhatsAppBusinessProvider(), // Fallback para WhatsApp Business
        };
    }

    /**
     * Envia uma mensagem de texto
     *
     * @param string $phone Número do telefone
     * @param string $message Mensagem a ser enviada
     * @return bool True se enviado com sucesso, False caso contrário
     */
    public function sendMessage(string $phone, string $message): bool
    {
        try {
            return $this->provider->sendMessage($phone, $message);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar mensagem WhatsApp', [
                'provider' => get_class($this->provider),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Formata o número de telefone
     *
     * @param string $phone Número do telefone
     * @return string Número formatado
     */
    public function formatPhone(string $phone): string
    {
        return $this->provider->formatPhone($phone);
    }

    /**
     * Retorna o provedor atual
     *
     * @return WhatsAppProviderInterface
     */
    public function getProvider(): WhatsAppProviderInterface
    {
        return $this->provider;
    }
}
