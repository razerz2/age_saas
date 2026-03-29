<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;

class EvolutionProvider implements WhatsAppProviderInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $instance;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.whatsapp.evolution.base_url', ''), '/');
        $this->apiKey = trim((string) config('services.whatsapp.evolution.api_key', ''));
        $this->instance = trim((string) config('services.whatsapp.evolution.instance', 'default'));

        if ($this->instance === '') {
            $this->instance = 'default';
        }

        if ($this->baseUrl === '' || $this->apiKey === '' || $this->instance === '') {
            Log::warning('Evolution API nao configurada corretamente', [
                'base_url_set' => $this->baseUrl !== '',
                'api_key_set' => $this->apiKey !== '',
                'instance' => $this->instance,
            ]);
        }
    }

    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $client = EvolutionClient::fromConfig();
            $formattedPhone = $this->formatPhone($phone);

            if ($formattedPhone === '') {
                Log::error('Telefone invalido para Evolution', [
                    'phone' => PhoneNormalizer::maskPhone($phone),
                ]);
                return false;
            }

            if (!$client->isConfigured()) {
                Log::error('Tentativa de uso do Evolution sem configuracao completa', [
                    'base_url_set' => $this->baseUrl !== '',
                    'api_key_set' => $this->apiKey !== '',
                    'instance' => $this->instance,
                    'phone' => PhoneNormalizer::maskPhone($formattedPhone),
                ]);
                return false;
            }

            $stateResult = $client->getConnectionState();
            $state = strtoupper(trim((string) ($stateResult['state'] ?? '')));
            $readyStates = ['OPEN', 'CONNECTED', 'ONLINE', 'WORKING', 'READY'];

            if ($state !== '' && !in_array($state, $readyStates, true)) {
                Log::error('Instancia Evolution nao esta pronta para envio', [
                    'instance' => $client->getInstance(),
                    'state' => $state,
                    'status_code' => $stateResult['status'] ?? null,
                    'body' => $stateResult['body'] ?? null,
                ]);
                return false;
            }

            if (empty($stateResult['ok']) && $state === '') {
                Log::warning('Falha ao validar estado da instancia Evolution; envio sera tentado', [
                    'instance' => $client->getInstance(),
                    'status_code' => $stateResult['status'] ?? null,
                ]);
            }

            $sendResult = $client->sendText($formattedPhone, $message);
            $sendBody = $sendResult['body'] ?? null;

            Log::info('Evolution resposta recebida', [
                'provider' => 'evolution',
                'base_url' => $client->getBaseUrl(),
                'instance' => $client->getInstance(),
                'to' => PhoneNormalizer::maskPhone($formattedPhone),
                'status_code' => $sendResult['status'] ?? null,
            ]);

            if (empty($sendResult['ok'])) {
                Log::error('Erro HTTP ao enviar mensagem Evolution', [
                    'status_code' => $sendResult['status'] ?? null,
                    'endpoint' => $sendResult['endpoint'] ?? null,
                    'body' => $sendBody,
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Excecao ao enviar mensagem Evolution', [
                'error' => $e->getMessage(),
                'phone' => PhoneNormalizer::maskPhone($phone),
            ]);
            return false;
        }
    }

    public function formatPhone(string $phone): string
    {
        return PhoneNormalizer::normalizeE164($phone);
    }
}

