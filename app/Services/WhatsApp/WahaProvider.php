<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;
use App\Services\WhatsApp\PhoneNormalizer;

class WahaProvider implements WhatsAppProviderInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $session;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.whatsapp.waha.base_url', ''), '/');
        $this->apiKey = (string) config('services.whatsapp.waha.api_key', '');
        $this->session = (string) config('services.whatsapp.waha.session', 'default');

        if (empty($this->baseUrl) || empty($this->apiKey) || empty($this->session)) {
            Log::warning('⚠️ WAHA não configurado corretamente', [
                'base_url_set' => !empty($this->baseUrl),
                'api_key_set' => !empty($this->apiKey),
                'session' => $this->session,
            ]);
        }
    }

    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $client = WahaClient::fromConfig();
            try {
                $chatId = WahaClient::formatChatIdFromPhone($phone);
            } catch (\InvalidArgumentException $e) {
                Log::error('❌ Telefone inválido para WAHA', [
                    'phone' => PhoneNormalizer::maskPhone($phone),
                    'error' => $e->getMessage(),
                ]);
                return false;
            }

            if (!$client->isConfigured()) {
                Log::error('❌ Tentativa de uso do WAHA sem configuração completa', [
                    'phone' => $chatId,
                    'base_url_set' => !empty($this->baseUrl),
                    'api_key_set' => !empty($this->apiKey),
                    'session' => $this->session,
                ]);
                return false;
            }

            if ($chatId === '') {
                Log::error('❌ Número inválido para envio WAHA', [
                    'phone' => $phone,
                ]);
                return false;
            }

            // 1) Valida sessão
            $sessionResult = $client->getSessionStatus();
            $sessionBody = is_array($sessionResult['body'] ?? null) ? $sessionResult['body'] : [];
            $sessionState = strtoupper((string) ($sessionBody['status'] ?? $sessionBody['state'] ?? ''));
            $workingStates = ['WORKING', 'CONNECTED', 'READY', 'ONLINE'];

            Log::info('🔍 WAHA sessão verificada', [
                'base_url' => $client->getBaseUrl(),
                'session' => $client->getSession(),
                'status_code' => $sessionResult['status'] ?? null,
                'status' => $sessionState,
            ]);

            if (empty($sessionResult['ok']) || !in_array($sessionState, $workingStates, true)) {
                Log::error('❌ Sessão WAHA não está pronta, envio abortado', [
                    'session' => $client->getSession(),
                    'status' => $sessionState ?: null,
                    'status_code' => $sessionResult['status'] ?? null,
                    'body' => self::summarizeBody($sessionBody),
                ]);
                return false;
            }

            // 2) Envia mensagem
            $sendResult = $client->sendText($chatId, $message);
            $sendBody = $sendResult['body'] ?? null;

            Log::info('📤 WAHA resposta recebida', [
                'provider' => 'waha',
                'base_url' => $client->getBaseUrl(),
                'to' => $chatId,
                'status_code' => $sendResult['status'] ?? null,
            ]);

            if (empty($sendResult['ok'])) {
                Log::error('❌ Erro HTTP ao enviar mensagem WAHA', [
                    'status_code' => $sendResult['status'] ?? null,
                    'body' => $sendBody,
                ]);
                return false;
            }

            if (is_array($sendBody) && isset($sendBody['error'])) {
                Log::error('❌ Erro na resposta WAHA', [
                    'error' => $sendBody['error'],
                    'body' => $sendBody,
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('❌ Exceção ao enviar mensagem WAHA', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'phone' => $phone,
            ]);
            return false;
        }
    }

    public function testSession(): array
    {
        $client = WahaClient::fromConfig();
        if (!$client->isConfigured()) {
            return [
                'status' => 'ERROR',
                'message' => 'WAHA nao esta configurado corretamente.',
                'http_status' => null,
                'data' => [],
            ];
        }

        $result = $client->getSessionStatus();
        $httpStatus = $result['status'] ?? null;
        $body = is_array($result['body'] ?? null) ? $result['body'] : [];

        if (in_array($httpStatus, [401, 403], true)) {
            return [
                'status' => 'ERROR',
                'message' => 'Unauthenticated (WAHA).',
                'http_status' => $httpStatus,
                'data' => $body,
            ];
        }

        if ($httpStatus === 404) {
            return [
                'status' => 'ERROR',
                'message' => 'Sessao nao encontrada.',
                'http_status' => $httpStatus,
                'data' => $body,
            ];
        }

        if (empty($result['ok'])) {
            return [
                'status' => 'ERROR',
                'message' => 'HTTP ' . ($httpStatus ?? 'erro') . ' - ' . ($body['message'] ?? $body['error'] ?? 'Falha ao consultar sessao.'),
                'http_status' => $httpStatus,
                'data' => $body,
            ];
        }

        $state = strtoupper((string) ($body['status'] ?? $body['state'] ?? ''));

        return [
            'status' => 'OK',
            'message' => $state !== ''
                ? 'Sessao WAHA esta conectada (' . $state . ').'
                : 'Sessao WAHA esta conectada.',
            'http_status' => $httpStatus,
            'data' => $body,
        ];
    }

    public function formatPhone(string $phone): string
    {
        try {
            return PhoneNormalizer::normalizeWahaBrPhone($phone);
        } catch (\InvalidArgumentException $e) {
            Log::warning('Telefone inválido para WAHA ao formatar', [
                'phone' => PhoneNormalizer::maskPhone($phone),
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }

    private static function summarizeBody(mixed $body, int $limit = 800): string
    {
        if (is_array($body)) {
            $body = json_encode($body, JSON_UNESCAPED_UNICODE);
        }

        $body = (string) ($body ?? '');
        if (strlen($body) <= $limit) {
            return $body;
        }

        return substr($body, 0, $limit) . '...';
    }
}
