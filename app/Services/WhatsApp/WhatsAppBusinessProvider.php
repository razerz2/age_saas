<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WhatsAppBusinessProvider implements WhatsAppProviderInterface
{
    private const DEFAULT_META_BASE_URL = 'https://graph.facebook.com';
    private const META_API_VERSION = 'v22.0';

    protected string $baseUrl;
    protected string $token;
    protected string $phoneId;
    protected string $configuredProvider;

    public function __construct()
    {
        // Tenta usar as novas configurações, se não existir, usa as legadas
        $this->configuredProvider = strtolower(trim((string) config('services.whatsapp.provider', '')));
        $this->baseUrl = $this->normalizeBaseUrl($this->resolveBusinessSetting('api_url', self::DEFAULT_META_BASE_URL));
        $this->token = trim($this->resolveBusinessSetting('token', ''));
        $this->phoneId = trim($this->resolveBusinessSetting('phone_id', ''));
    }

    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $this->assertProviderConsistency();
            $this->assertConfiguration();

            $formattedPhone = $this->formatPhone($phone);
            if ($formattedPhone === '') {
                throw new RuntimeException('Configuracao invalida do WhatsApp Meta: numero de destino nao pode ser normalizado.');
            }

            $endpoint = $this->buildMessagesEndpoint();

            Log::info('WhatsApp Meta send attempt', array_merge(
                $this->diagnostics($endpoint),
                ['to' => PhoneNormalizer::maskPhone($phone)]
            ));

            $response = Http::withToken($this->token)
                ->asJson()
                ->post($endpoint, [
                    'messaging_product' => 'whatsapp',
                    'to' => $formattedPhone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => true,
                        'body' => $message,
                    ],
                ]);

            Log::info('📤 WhatsApp Business enviado', [
                'provider' => 'whatsapp_business',
                'base_url' => rtrim($this->baseUrl, '/'),
                'api_version' => self::META_API_VERSION,
                'phone_number_id_present' => trim($this->phoneId, '/') !== '',
                'endpoint' => $endpoint,
                'token_present' => $this->token !== '',
                'token_masked' => $this->maskToken($this->token),
                'to' => PhoneNormalizer::maskPhone($phone),
                'http_status' => $response->status(),
                'successful' => $response->successful(),
                'response_body' => $this->summarizeBody($response->json() ?: $response->body())
            ]);

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('❌ Erro ao enviar mensagem WhatsApp Business', [
                'provider' => 'whatsapp_business',
                'base_url' => rtrim($this->baseUrl, '/'),
                'api_version' => self::META_API_VERSION,
                'phone_number_id_present' => trim($this->phoneId, '/') !== '',
                'endpoint' => $this->safeEndpointForLogs(),
                'token_present' => $this->token !== '',
                'token_masked' => $this->maskToken($this->token),
                'configured_provider' => $this->configuredProvider !== '' ? $this->configuredProvider : null,
                'error' => $e->getMessage()
            ]);

            if ($e instanceof RuntimeException) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Envia uma mensagem template oficial para Meta.
     *
     * @param  array<int, mixed>  $bodyParameters
     */
    public function sendTemplateMessage(
        string $phone,
        string $templateName,
        string $language = 'pt_BR',
        array $bodyParameters = []
    ): bool {
        $result = $this->sendTemplateMessageDetailed($phone, $templateName, $language, $bodyParameters);
        return (bool) ($result['success'] ?? false);
    }

    /**
     * Envia template oficial e retorna diagnostico detalhado da operacao.
     *
     * @param  array<int, mixed>  $bodyParameters
     * @return array<string, mixed>
     */
    public function sendTemplateMessageDetailed(
        string $phone,
        string $templateName,
        string $language = 'pt_BR',
        array $bodyParameters = []
    ): array {
        try {
            $this->assertProviderConsistency();
            $this->assertConfiguration();

            $templateName = trim($templateName);
            if ($templateName === '') {
                throw new RuntimeException('Template oficial invalido: nome do template nao informado.');
            }

            $formattedPhone = $this->formatPhone($phone);
            if ($formattedPhone === '') {
                throw new RuntimeException('Configuracao invalida do WhatsApp Meta: numero de destino nao pode ser normalizado.');
            }

            $endpoint = $this->buildMessagesEndpoint();

            $components = [];
            $normalizedParameters = $this->normalizeTemplateParameters($bodyParameters);
            if ($normalizedParameters !== []) {
                $components[] = [
                    'type' => 'body',
                    'parameters' => $normalizedParameters,
                ];
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $formattedPhone,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => trim($language) !== '' ? trim($language) : 'pt_BR',
                    ],
                ],
            ];

            if ($components !== []) {
                $payload['template']['components'] = $components;
            }

            Log::info('WhatsApp Meta template send attempt', array_merge(
                $this->diagnostics($endpoint),
                [
                    'to' => PhoneNormalizer::maskPhone($phone),
                    'template_name' => $templateName,
                    'language' => $payload['template']['language']['code'],
                    'parameters_count' => count($normalizedParameters),
                    'has_components' => array_key_exists('components', (array) $payload['template']),
                ]
            ));

            $response = Http::withToken($this->token)
                ->asJson()
                ->post($endpoint, $payload);

            $responseBody = $response->json() ?: $response->body();
            $metaError = $response->successful() ? [] : $this->extractMetaError(is_array($response->json()) ? $response->json() : []);

            Log::info('WhatsApp Meta template sent', [
                'provider' => 'whatsapp_business',
                'endpoint' => $endpoint,
                'to' => PhoneNormalizer::maskPhone($phone),
                'template_name' => $templateName,
                'language' => $payload['template']['language']['code'],
                'parameters_count' => count($normalizedParameters),
                'has_components' => array_key_exists('components', (array) $payload['template']),
                'http_status' => $response->status(),
                'successful' => $response->successful(),
                'meta_error' => $metaError,
                'response_body' => $this->summarizeBody($responseBody),
            ]);

            return [
                'success' => $response->successful(),
                'http_status' => $response->status(),
                'response_summary' => $this->summarizeBody($responseBody),
                'meta_error' => $metaError,
            ];
        } catch (Throwable $e) {
            Log::error('WhatsApp Meta template send failed', [
                'provider' => 'whatsapp_business',
                'endpoint' => $this->safeEndpointForLogs(),
                'to' => PhoneNormalizer::maskPhone($phone),
                'template_name' => $templateName,
                'error' => $e->getMessage(),
            ]);

            if ($e instanceof RuntimeException) {
                throw $e;
            }

            return [
                'success' => false,
                'http_status' => null,
                'response_summary' => $e->getMessage(),
                'meta_error' => [],
            ];
        }
    }

    /**
     * Envia template AUTHENTICATION com componentes dinamicos de BODY/BUTTON quando exigidos.
     *
     * @param array<int, mixed> $bodyParameters
     * @param array<int, array<string, mixed>> $buttonComponents
     * @return array<string, mixed>
     */
    public function sendAuthenticationTemplateMessageDetailed(
        string $phone,
        string $templateName,
        string $language,
        array $bodyParameters = [],
        array $buttonComponents = []
    ): array {
        try {
            $this->assertProviderConsistency();
            $this->assertConfiguration();

            $templateName = trim($templateName);
            if ($templateName === '') {
                throw new RuntimeException('Template oficial invalido: nome do template nao informado.');
            }

            $formattedPhone = $this->formatPhone($phone);
            if ($formattedPhone === '') {
                throw new RuntimeException('Configuracao invalida do WhatsApp Meta: numero de destino nao pode ser normalizado.');
            }

            $endpoint = $this->buildMessagesEndpoint();
            $normalizedParameters = $this->normalizeTemplateParameters($bodyParameters);
            $normalizedButtonComponents = $this->normalizeButtonTemplateComponents($buttonComponents);
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $formattedPhone,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => trim($language) !== '' ? trim($language) : 'pt_BR',
                    ],
                ],
            ];

            $components = [];
            if ($normalizedParameters !== []) {
                $components[] = [
                    'type' => 'body',
                    'parameters' => $normalizedParameters,
                ];
            }

            if ($normalizedButtonComponents !== []) {
                $components = array_merge($components, $normalizedButtonComponents);
            }

            if ($components !== []) {
                $payload['template']['components'] = $components;
            }

            Log::info('WhatsApp Meta authentication template send attempt', array_merge(
                $this->diagnostics($endpoint),
                [
                    'to' => PhoneNormalizer::maskPhone($phone),
                    'template_name' => $templateName,
                    'language' => $payload['template']['language']['code'],
                    'parameters_count' => count($normalizedParameters),
                    'button_components_count' => count($normalizedButtonComponents),
                    'has_components' => array_key_exists('components', (array) $payload['template']),
                ]
            ));

            $response = Http::withToken($this->token)
                ->asJson()
                ->post($endpoint, $payload);

            $responseBody = $response->json() ?: $response->body();
            $metaError = $response->successful() ? [] : $this->extractMetaError(is_array($response->json()) ? $response->json() : []);

            Log::info('WhatsApp Meta authentication template sent', [
                'provider' => 'whatsapp_business',
                'endpoint' => $endpoint,
                'to' => PhoneNormalizer::maskPhone($phone),
                'template_name' => $templateName,
                'language' => $payload['template']['language']['code'],
                'parameters_count' => count($normalizedParameters),
                'button_components_count' => count($normalizedButtonComponents),
                'has_components' => array_key_exists('components', (array) $payload['template']),
                'http_status' => $response->status(),
                'successful' => $response->successful(),
                'meta_error' => $metaError,
                'response_body' => $this->summarizeBody($responseBody),
            ]);

            return [
                'success' => $response->successful(),
                'http_status' => $response->status(),
                'response_summary' => $this->summarizeBody($responseBody),
                'meta_error' => $metaError,
            ];
        } catch (Throwable $e) {
            Log::error('WhatsApp Meta authentication template send failed', [
                'provider' => 'whatsapp_business',
                'endpoint' => $this->safeEndpointForLogs(),
                'to' => PhoneNormalizer::maskPhone($phone),
                'template_name' => $templateName,
                'error' => $e->getMessage(),
            ]);

            if ($e instanceof RuntimeException) {
                throw $e;
            }

            return [
                'success' => false,
                'http_status' => null,
                'response_summary' => $e->getMessage(),
                'meta_error' => [],
            ];
        }
    }

    public function formatPhone(string $phone): string
    {
        return PhoneNormalizer::formatForWhatsAppBusiness($phone);
    }

    private function assertProviderConsistency(): void
    {
        if ($this->configuredProvider === '' || in_array($this->configuredProvider, ['whatsapp_business', 'business'], true)) {
            return;
        }

        throw new RuntimeException(
            sprintf(
                'Configuracao inconsistente do provider WhatsApp: esperado "whatsapp_business", recebido "%s".',
                $this->configuredProvider
            )
        );
    }

    private function assertConfiguration(): void
    {
        if ($this->token === '') {
            throw new RuntimeException(
                'Configuracao ausente do WhatsApp Meta: token de acesso nao definido (WHATSAPP_META_TOKEN/WHATSAPP_BUSINESS_TOKEN/META_ACCESS_TOKEN).'
            );
        }

        if (trim($this->phoneId, '/') === '') {
            throw new RuntimeException(
                'Configuracao ausente do WhatsApp Meta: phone_number_id nao definido (WHATSAPP_META_PHONE_NUMBER_ID/WHATSAPP_BUSINESS_PHONE_ID/META_PHONE_NUMBER_ID).'
            );
        }
    }

    private function buildMessagesEndpoint(): string
    {
        $baseUrl = rtrim($this->baseUrl, '/');
        $phoneNumberId = trim((string) $this->phoneId, '/');
        $endpoint = $baseUrl . '/' . $phoneNumberId . '/messages';

        if (str_contains($endpoint, '//messages')) {
            throw new RuntimeException('Endpoint invalido do WhatsApp Meta: detected "//messages".');
        }

        return $endpoint;
    }

    private function safeEndpointForLogs(): string
    {
        $baseUrl = rtrim($this->baseUrl, '/');
        $phoneNumberId = trim((string) $this->phoneId, '/');

        if ($phoneNumberId === '') {
            return $baseUrl . '/<missing-phone-number-id>/messages';
        }

        return $baseUrl . '/' . $phoneNumberId . '/messages';
    }

    private function diagnostics(?string $endpoint = null): array
    {
        return [
            'provider' => 'whatsapp_business',
            'configured_provider' => $this->configuredProvider !== '' ? $this->configuredProvider : null,
            'base_url' => rtrim($this->baseUrl, '/'),
            'api_version' => self::META_API_VERSION,
            'phone_number_id_present' => trim($this->phoneId, '/') !== '',
            'endpoint' => $endpoint,
            'token_present' => $this->token !== '',
            'token_masked' => $this->maskToken($this->token),
        ];
    }

    private function normalizeBaseUrl(string $baseUrl): string
    {
        $sanitized = trim($baseUrl);
        if ($sanitized === '') {
            $sanitized = self::DEFAULT_META_BASE_URL;
        }

        $sanitized = rtrim($sanitized, '/');
        $withoutVersion = preg_replace('#/v\\d+\\.\\d+$#i', '', $sanitized);
        if (is_string($withoutVersion) && $withoutVersion !== '') {
            $sanitized = $withoutVersion;
        }

        if (!preg_match('#^https?://#i', $sanitized)) {
            $sanitized = self::DEFAULT_META_BASE_URL;
        }

        return rtrim($sanitized, '/') . '/' . self::META_API_VERSION;
    }

    private function resolveBusinessSetting(string $setting, string $default = ''): string
    {
        $value = trim((string) config('services.whatsapp.business.' . $setting, ''));
        if ($value !== '') {
            return $value;
        }

        if ($setting === 'api_url') {
            $legacyApiUrl = trim((string) config('services.whatsapp.api_url', ''));
            if ($legacyApiUrl !== '') {
                return $legacyApiUrl;
            }

            return $this->readSystemSettings([
                'WHATSAPP_META_BASE_URL',
                'WHATSAPP_BUSINESS_API_URL',
                'WHATSAPP_API_URL',
            ], $default);
        }

        if ($setting === 'token') {
            $legacyToken = trim((string) config('services.whatsapp.token', ''));
            if ($legacyToken !== '') {
                return $legacyToken;
            }

            return $this->readSystemSettings([
                'WHATSAPP_META_TOKEN',
                'WHATSAPP_BUSINESS_TOKEN',
                'META_ACCESS_TOKEN',
                'BOT_META_ACCESS_TOKEN',
                'bot_meta_access_token',
            ], $default);
        }

        if ($setting === 'phone_id') {
            $legacyPhoneId = trim((string) config('services.whatsapp.phone_id', ''));
            if ($legacyPhoneId !== '') {
                return $legacyPhoneId;
            }

            return $this->readSystemSettings([
                'WHATSAPP_META_PHONE_NUMBER_ID',
                'WHATSAPP_BUSINESS_PHONE_ID',
                'META_PHONE_NUMBER_ID',
                'BOT_META_PHONE_NUMBER_ID',
                'bot_meta_phone_number_id',
            ], $default);
        }

        return $default;
    }

    private function readSystemSettings(array $keys, string $default = ''): string
    {
        if (!function_exists('sysconfig')) {
            return $default;
        }

        foreach ($keys as $key) {
            $value = trim((string) sysconfig($key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return $default;
    }

    private function maskToken(string $token): ?string
    {
        $trimmedToken = trim($token);
        if ($trimmedToken === '') {
            return null;
        }

        $length = strlen($trimmedToken);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($trimmedToken, 0, 4) . str_repeat('*', max(1, $length - 8)) . substr($trimmedToken, -4);
    }

    private function summarizeBody(mixed $body, int $limit = 1000): string
    {
        if (is_array($body)) {
            $encoded = json_encode($body, JSON_UNESCAPED_UNICODE);
            $body = $encoded === false ? '' : $encoded;
        }

        $stringBody = (string) $body;
        if (strlen($stringBody) <= $limit) {
            return $stringBody;
        }

        return substr($stringBody, 0, $limit) . '...';
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    private function extractMetaError(array $response): array
    {
        $error = (array) ($response['error'] ?? []);
        $errorData = (array) ($error['error_data'] ?? []);
        $details = trim((string) ($errorData['details'] ?? ''));

        return [
            'message' => (string) ($error['message'] ?? ''),
            'type' => (string) ($error['type'] ?? ''),
            'code' => $error['code'] ?? null,
            'error_subcode' => $error['error_subcode'] ?? null,
            'details' => $details,
            'fbtrace_id' => (string) ($error['fbtrace_id'] ?? ''),
            'error_data' => $errorData,
        ];
    }

    /**
     * @param  array<int, mixed>  $parameters
     * @return array<int, array<string, string>>
     */
    private function normalizeTemplateParameters(array $parameters): array
    {
        $normalized = [];
        foreach ($parameters as $parameter) {
            if (is_array($parameter) && isset($parameter['type'], $parameter['text'])) {
                $normalized[] = [
                    'type' => (string) $parameter['type'],
                    'text' => (string) $parameter['text'],
                ];
                continue;
            }

            if (is_scalar($parameter) || $parameter === null) {
                $normalized[] = [
                    'type' => 'text',
                    'text' => (string) $parameter,
                ];
            }
        }

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $components
     * @return array<int, array<string, mixed>>
     */
    private function normalizeButtonTemplateComponents(array $components): array
    {
        $normalized = [];

        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            $index = trim((string) ($component['index'] ?? ''));
            $subType = strtolower(trim((string) ($component['sub_type'] ?? 'url')));
            $parameters = $this->normalizeTemplateParameters((array) ($component['parameters'] ?? []));

            if ($index === '' || $subType === '' || $parameters === []) {
                continue;
            }

            $normalized[] = [
                'type' => 'button',
                'sub_type' => $subType,
                'index' => $index,
                'parameters' => $parameters,
            ];
        }

        return $normalized;
    }
}
