<?php

namespace App\Services;

use App\Services\WhatsApp\WhatsAppProviderInterface;
use App\Services\WhatsApp\WhatsAppBusinessProvider;
use App\Services\WhatsApp\ZApiProvider;
use App\Services\WhatsApp\WahaProvider;
use App\Services\WhatsApp\EvolutionProvider;
use App\Services\WhatsApp\UnsupportedProvider;
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
        $forceRuntimeProvider = (bool) config('services.whatsapp.force_runtime_provider', false);
        $runtimeProvider = strtolower(trim((string) config('services.whatsapp.runtime_provider', '')));

        if ($forceRuntimeProvider) {
            $provider = $runtimeProvider !== '' ? $runtimeProvider : '__invalid_runtime_provider__';
        } else {
            $provider = function_exists('sysconfig')
                ? (string) sysconfig('WHATSAPP_PROVIDER', config('services.whatsapp.provider', 'whatsapp_business'))
                : (string) config('services.whatsapp.provider', 'whatsapp_business');
        }

        $provider = strtolower(trim($provider));
        if ($provider === '') {
            $provider = 'whatsapp_business';
        }

        return match ($provider) {
            'zapi', 'z-api' => new ZApiProvider(),
            'waha' => new WahaProvider(),
            'evolution', 'evolution_api', 'evolution-api', 'evo_api', 'evo-api', 'whatsapp_evolution', 'whatsapp-evolution' => new EvolutionProvider(),
            'whatsapp_business', 'business', 'meta' => new WhatsAppBusinessProvider(),
            default => $forceRuntimeProvider
                ? $this->unsupportedRuntimeProvider($provider)
                : new WhatsAppBusinessProvider(), // Fallback para WhatsApp Business em escopo nao-forcado
        };
    }

    protected function unsupportedRuntimeProvider(string $provider): WhatsAppProviderInterface
    {
        Log::warning('WhatsApp runtime provider forced with unsupported key', [
            'provider' => $provider,
        ]);

        return new UnsupportedProvider($provider);
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
