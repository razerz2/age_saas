<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsApp\PhoneNormalizer;

class ZApiProvider implements WhatsAppProviderInterface
{
    protected $apiUrl;
    protected $token;
    protected $clientToken;
    protected $instanceId;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.zapi.api_url', 'https://api.z-api.io');
        $this->token = trim(config('services.whatsapp.zapi.token', '')); // Token da instância
        $this->clientToken = trim(config('services.whatsapp.zapi.client_token', '')); // Client-Token de segurança
        $this->instanceId = trim(config('services.whatsapp.zapi.instance_id', ''));
        
        // Remove espaços e caracteres extras
        $this->token = preg_replace('/\s+/', '', $this->token);
        $this->clientToken = preg_replace('/\s+/', '', $this->clientToken);
        $this->instanceId = preg_replace('/\s+/', '', $this->instanceId);
        
        // Se não tiver client_token configurado, usa o token da instância como fallback
        if (empty($this->clientToken)) {
            $this->clientToken = $this->token;
        }
        
        // Valida configurações básicas
        if (empty($this->token) || empty($this->instanceId)) {
            Log::warning('⚠️ Z-API não configurado corretamente', [
                'token_set' => !empty($this->token),
                'token_length' => strlen($this->token ?? ''),
                'client_token_set' => !empty($this->clientToken),
                'client_token_length' => strlen($this->clientToken ?? ''),
                'instance_id_set' => !empty($this->instanceId),
                'instance_id_length' => strlen($this->instanceId ?? ''),
                'api_url' => $this->apiUrl,
            ]);
        }
    }

    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $formattedPhone = $this->formatPhone($phone);
            
            // Z-API pode usar diferentes formatos de endpoint
            // Tentamos vários formatos comuns baseados na documentação da Z-API
            // IMPORTANTE: Algumas versões da Z-API não aceitam token na URL, apenas no header
            $endpoints = [
                // Formato 1: Sem token na URL (token apenas no header) - TENTAR PRIMEIRO
                "{$this->apiUrl}/instances/{$this->instanceId}/send-text",
                // Formato 2: Com token na URL (pode não funcionar em algumas versões)
                "{$this->apiUrl}/instances/{$this->instanceId}/token/{$this->token}/send-text",
                // Formato 3: Versão alternativa com token como query param
                "{$this->apiUrl}/instances/{$this->instanceId}/send-text?token={$this->token}",
            ];
            
            $response = null;
            $lastError = null;
            $lastEndpoint = null;
            
            foreach ($endpoints as $index => $endpoint) {
                try {
                    Log::debug('🔍 Tentando endpoint Z-API', [
                        'endpoint' => $endpoint,
                        'phone' => $formattedPhone,
                        'format' => $index + 1,
                        'token_length' => strlen($this->token),
                        'token_preview' => substr($this->token, 0, 10) . '...',
                        'instance_id' => $this->instanceId,
                    ]);
                    
                    // Z-API requer o Client-Token de segurança no header
                    // Este é diferente do token da instância usado na URL
                    $headers = [
                        'Content-Type' => 'application/json',
                        'Client-Token' => $this->clientToken, // Client-Token de segurança da conta
                    ];
                    
                    $response = Http::withHeaders($headers)->post($endpoint, [
                        'phone' => $formattedPhone,
                        'message' => $message,
                    ]);
                    
                    Log::debug('📋 Headers enviados Z-API', [
                        'headers' => array_keys($headers),
                        'client_token_length' => strlen($headers['Client-Token']),
                    ]);
                    
                    $body = $response->json();
                    $statusCode = $response->status();
                    
                    Log::info('📤 Z-API resposta recebida', [
                        'provider' => 'zapi',
                        'endpoint' => $endpoint,
                        'to' => $phone,
                        'formatted_phone' => $formattedPhone,
                        'status' => $statusCode,
                        'body' => $body
                    ]);
                    
                    // Se não houver erro "NOT_FOUND", usa este endpoint
                    if (!isset($body['error']) || ($body['error'] !== 'NOT_FOUND' && $body['error'] !== 'Unable to find matching target resource method')) {
                        $lastEndpoint = $endpoint;
                        break;
                    }
                    
                    $lastError = $body;
                    $lastEndpoint = $endpoint;
                } catch (\Throwable $e) {
                    $lastError = ['error' => $e->getMessage()];
                    $lastEndpoint = $endpoint;
                    Log::warning('⚠️ Erro ao tentar endpoint Z-API', [
                        'endpoint' => $endpoint,
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            }
            
            if (!$response) {
                Log::error('❌ Nenhum endpoint Z-API funcionou', [
                    'provider' => 'zapi',
                    'phone' => $formattedPhone,
                    'api_url' => $this->apiUrl,
                    'instance_id' => $this->instanceId,
                    'last_endpoint' => $lastEndpoint,
                    'last_error' => $lastError,
                ]);
                return false;
            }

            $body = $response->json();
            $statusCode = $response->status();

            // Verifica se há erro na resposta (mesmo com status 200, pode haver erro no body)
            if (isset($body['error'])) {
                $errorMessage = $body['error'];
                $errorDetails = $body['message'] ?? 'Sem mensagem de erro';
                
                // Mensagem específica para erro de token não configurado
                if ($errorMessage === 'your client-token is not configured') {
                    Log::error('❌ Token Z-API não configurado no painel', [
                        'provider' => 'zapi',
                        'endpoint_used' => $lastEndpoint,
                        'error' => $errorMessage,
                        'message' => $errorDetails,
                        'phone' => $formattedPhone,
                        'api_url' => $this->apiUrl,
                        'instance_id' => $this->instanceId,
                        'token_length' => strlen($this->token),
                        'suggestion' => 'Verifique se o token está correto no .env e se está configurado no painel da Z-API',
                    ]);
                } else {
                    Log::error('❌ Erro na resposta Z-API', [
                        'provider' => 'zapi',
                        'endpoint_used' => $lastEndpoint,
                        'error' => $errorMessage,
                        'message' => $errorDetails,
                        'phone' => $formattedPhone,
                        'api_url' => $this->apiUrl,
                        'instance_id' => $this->instanceId,
                    ]);
                }
                return false;
            }

            // Verifica se a resposta indica sucesso
            // Z-API pode retornar status 200 com sucesso ou erro no body
            if ($response->successful()) {
                // Verifica se há campo 'status' com valor 'success' ou se não há campo 'error'
                $hasSuccess = isset($body['status']) && $body['status'] === 'success';
                $hasNoError = !isset($body['error']);
                
                // Retorna true apenas se realmente for sucesso
                $success = $hasSuccess || ($hasNoError && $statusCode === 200);
                
                if ($success) {
                    Log::info('✅ Mensagem Z-API enviada com sucesso', [
                        'provider' => 'zapi',
                        'endpoint_used' => $lastEndpoint,
                        'phone' => $formattedPhone,
                    ]);
                }
                
                return $success;
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar mensagem Z-API', [
                'provider' => 'zapi',
                'error' => $e->getMessage(),
                'phone' => $phone,
                'api_url' => $this->apiUrl ?? 'não configurado',
                'instance_id' => $this->instanceId ?? 'não configurado',
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function formatPhone(string $phone): string
    {
        return PhoneNormalizer::formatForZapi($phone);
    }
}

