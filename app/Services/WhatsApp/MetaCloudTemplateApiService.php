<?php

namespace App\Services\WhatsApp;

use App\Exceptions\WhatsAppMetaApiException;
use App\Exceptions\WhatsAppMetaConfigurationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaCloudTemplateApiService
{
    private const DEFAULT_META_BASE_URL = 'https://graph.facebook.com';
    private const META_API_VERSION = 'v22.0';

    private string $baseUrl;
    private string $token;
    private string $wabaId;
    private string $provider;

    public function __construct()
    {
        $this->provider = $this->resolveProvider();
        $this->baseUrl = $this->normalizeBaseUrl($this->resolveConfigValue(
            ['services.whatsapp.business.api_url', 'services.whatsapp.api_url'],
            [
                'WHATSAPP_META_BASE_URL',
                'WHATSAPP_BUSINESS_API_URL',
                'WHATSAPP_API_URL',
            ],
            self::DEFAULT_META_BASE_URL
        ));
        $this->token = trim($this->resolveConfigValue(
            ['services.whatsapp.business.token', 'services.whatsapp.token'],
            [
                'WHATSAPP_META_TOKEN',
                'WHATSAPP_BUSINESS_TOKEN',
                'META_ACCESS_TOKEN',
                'BOT_META_ACCESS_TOKEN',
                'bot_meta_access_token',
            ]
        ));
        $this->wabaId = trim($this->resolveConfigValue(
            ['services.whatsapp.business.waba_id'],
            [
                'WHATSAPP_META_WABA_ID',
                'WHATSAPP_BUSINESS_ACCOUNT_ID',
                'META_WABA_ID',
                'BOT_META_WABA_ID',
                'bot_meta_waba_id',
            ]
        ));
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function createTemplate(array $payload, array $context = []): array
    {
        $this->assertProviderConsistency();
        $this->assertConfiguration();
        $this->assertPayload($payload);

        $endpoint = $this->buildEndpoint('message_templates');
        Log::info('meta_template_create_request', array_merge(
            $this->diagnostics($endpoint),
            $context,
            [
                'http_method' => 'POST',
                'payload_summary' => $this->summarizePayload($payload),
                'payload_preview' => $this->previewPayload($payload),
            ]
        ));

        $response = Http::withToken($this->token)
            ->asJson()
            ->post($endpoint, $payload);

        $responseBody = $response->json();
        if (!$response->successful()) {
            $metaError = $this->extractMetaError(is_array($responseBody) ? $responseBody : []);

            Log::warning('meta_template_create_http_error', array_merge(
                $this->diagnostics($endpoint),
                $context,
                [
                    'http_method' => 'POST',
                    'http_status' => $response->status(),
                    'meta_error' => $metaError,
                    'payload_summary' => $this->summarizePayload($payload),
                    'payload_preview' => $this->previewPayload($payload),
                    'response_summary' => $this->summarizeBody($responseBody ?: $response->body()),
                ]
            ));

            throw new WhatsAppMetaApiException(
                'Falha HTTP na criacao de template Meta: status ' . $response->status() . '.',
                $response->status(),
                $metaError,
                $this->summarizeBody($responseBody ?: $response->body())
            );
        }

        Log::info('meta_template_create_response', array_merge(
            $this->diagnostics($endpoint),
            $context,
            [
                'http_status' => $response->status(),
                'response_summary' => $this->summarizeBody($responseBody ?: $response->body()),
            ]
        ));

        return is_array($responseBody) ? $responseBody : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchTemplateByNameAndLanguage(string $name, string $language): array
    {
        $this->assertProviderConsistency();
        $this->assertConfiguration();

        $endpoint = $this->buildEndpoint('message_templates');
        $query = [
            'name' => $name,
            'language' => $language,
            'limit' => 100,
        ];

        Log::info('meta_template_sync_request', array_merge(
            $this->diagnostics($endpoint),
            [
                'meta_template_name' => $name,
                'language' => $language,
            ]
        ));

        $response = Http::withToken($this->token)
            ->asJson()
            ->get($endpoint, $query);

        $responseBody = $response->json();
        if (!$response->successful()) {
            Log::warning('meta_template_sync_http_error', array_merge(
                $this->diagnostics($endpoint),
                [
                    'meta_template_name' => $name,
                    'language' => $language,
                    'http_status' => $response->status(),
                    'response_summary' => $this->summarizeBody($responseBody ?: $response->body()),
                ]
            ));

            throw new WhatsAppMetaApiException(
                'Falha HTTP na sincronizacao de template Meta: status ' . $response->status() . '.',
                $response->status(),
                $this->extractMetaError(is_array($responseBody) ? $responseBody : []),
                $this->summarizeBody($responseBody ?: $response->body())
            );
        }

        Log::info('meta_template_sync_response', array_merge(
            $this->diagnostics($endpoint),
            [
                'meta_template_name' => $name,
                'language' => $language,
                'http_status' => $response->status(),
                'response_summary' => $this->summarizeBody($responseBody ?: $response->body()),
            ]
        ));

        return is_array($responseBody) ? $responseBody : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function diagnostics(?string $endpoint = null): array
    {
        return [
            'provider' => 'whatsapp_business',
            'configured_provider' => $this->provider,
            'base_url' => rtrim($this->baseUrl, '/'),
            'api_version' => self::META_API_VERSION,
            'waba_id_present' => trim($this->wabaId, '/') !== '',
            'endpoint' => $endpoint,
            'token_present' => $this->token !== '',
            'token_masked' => $this->maskToken($this->token),
        ];
    }

    public function getWabaId(): string
    {
        return trim($this->wabaId, '/');
    }

    private function assertProviderConsistency(): void
    {
        if (in_array($this->provider, ['whatsapp_business', 'business'], true)) {
            return;
        }

        throw new WhatsAppMetaConfigurationException(
            sprintf(
                'Provider inconsistente para Meta Cloud API de templates: esperado "whatsapp_business", recebido "%s".',
                $this->provider
            )
        );
    }

    private function assertConfiguration(): void
    {
        if ($this->token === '') {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao ausente da Meta Cloud API: token nao definido (WHATSAPP_META_TOKEN/WHATSAPP_BUSINESS_TOKEN/META_ACCESS_TOKEN).'
            );
        }

        if (trim($this->wabaId, '/') === '') {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao ausente da Meta Cloud API: WABA ID nao definido (WHATSAPP_META_WABA_ID/WHATSAPP_BUSINESS_ACCOUNT_ID/META_WABA_ID).'
            );
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertPayload(array $payload): void
    {
        $name = strtolower(trim((string) ($payload['name'] ?? '')));
        if ($name === '' || !preg_match('/^[a-z0-9_]+$/', $name)) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida de template Meta: nome deve conter apenas letras minusculas, numeros e underscore.'
            );
        }

        $category = strtoupper(trim((string) ($payload['category'] ?? '')));
        if ($category === '') {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida de template Meta: category e obrigatoria.'
            );
        }

        $language = trim((string) ($payload['language'] ?? ''));
        if ($language === '') {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida de template Meta: language e obrigatorio.'
            );
        }

        $components = (array) ($payload['components'] ?? []);
        if ($category === 'AUTHENTICATION') {
            $this->assertAuthenticationPayload($components);
            return;
        }

        $this->assertUtilityPayload($components);
    }

    /**
     * @param array<int, mixed> $components
     */
    private function assertUtilityPayload(array $components): void
    {
        $bodyComponent = null;
        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            if (strtoupper((string) ($component['type'] ?? '')) === 'BODY') {
                $bodyComponent = $component;
                break;
            }
        }

        if (!is_array($bodyComponent)) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida de template Meta: componente BODY obrigatorio.'
            );
        }

        $bodyText = (string) ($bodyComponent['text'] ?? '');
        preg_match_all('/\{\{(\d+)\}\}/', $bodyText, $matches);
        $placeholders = array_values(array_unique($matches[1] ?? []));
        sort($placeholders, SORT_NATURAL);

        if ($placeholders === []) {
            return;
        }

        $exampleRow = $bodyComponent['example']['body_text'][0] ?? null;
        if (!is_array($exampleRow)) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida de template Meta: BODY com placeholders exige example.body_text.'
            );
        }

        $exampleValues = array_map(static fn ($value) => trim((string) $value), $exampleRow);
        if (count($exampleValues) < count($placeholders)) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida de template Meta: faltam exemplos para placeholders do BODY.'
            );
        }

        foreach ($placeholders as $index => $placeholder) {
            if (($exampleValues[$index] ?? '') === '') {
                throw new WhatsAppMetaConfigurationException(
                    'Configuracao invalida de template Meta: exemplo vazio para placeholder {{' . $placeholder . '}}.'
                );
            }
        }
    }

    /**
     * @param array<int, mixed> $components
     */
    private function assertAuthenticationPayload(array $components): void
    {
        $bodyComponent = null;
        $footerComponent = null;
        $buttonsComponent = null;

        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            $type = strtoupper((string) ($component['type'] ?? ''));
            if ($type === 'BODY') {
                $bodyComponent = $component;
                continue;
            }
            if ($type === 'FOOTER') {
                $footerComponent = $component;
                continue;
            }
            if ($type === 'BUTTONS') {
                $buttonsComponent = $component;
            }
        }

        if (!is_array($bodyComponent)) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida AUTHENTICATION: componente BODY obrigatorio.'
            );
        }

        if (!array_key_exists('add_security_recommendation', $bodyComponent)) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida AUTHENTICATION: BODY deve informar add_security_recommendation.'
            );
        }

        if (!is_array($footerComponent)) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida AUTHENTICATION: componente FOOTER obrigatorio.'
            );
        }

        $minutes = (int) ($footerComponent['code_expiration_minutes'] ?? 0);
        if ($minutes <= 0) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida AUTHENTICATION: FOOTER deve informar code_expiration_minutes > 0.'
            );
        }

        if (!is_array($buttonsComponent)) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida AUTHENTICATION: componente BUTTONS obrigatorio.'
            );
        }

        $buttons = (array) ($buttonsComponent['buttons'] ?? []);
        $otpButton = null;
        foreach ($buttons as $button) {
            if (!is_array($button)) {
                continue;
            }

            if (strtoupper((string) ($button['type'] ?? '')) === 'OTP') {
                $otpButton = $button;
                break;
            }
        }

        if (!is_array($otpButton)) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida AUTHENTICATION: BUTTONS deve conter botao OTP.'
            );
        }

        $otpType = strtoupper(trim((string) ($otpButton['otp_type'] ?? '')));
        if (!in_array($otpType, ['COPY_CODE', 'ONE_TAP', 'ZERO_TAP'], true)) {
            throw new WhatsAppMetaConfigurationException(
                'Configuracao invalida AUTHENTICATION: otp_type invalido para botao OTP.'
            );
        }
    }

    private function buildEndpoint(string $path): string
    {
        $baseUrl = rtrim($this->baseUrl, '/');
        $wabaId = trim((string) $this->wabaId, '/');
        $path = trim($path, '/');

        $endpoint = $baseUrl . '/' . $wabaId . '/' . $path;
        if (str_contains($endpoint, '//message_templates')) {
            throw new WhatsAppMetaConfigurationException(
                'Endpoint invalido da Meta Cloud API de templates: detectado "//message_templates".'
            );
        }

        return $endpoint;
    }

    private function normalizeBaseUrl(string $baseUrl): string
    {
        $sanitized = trim($baseUrl);
        if ($sanitized === '') {
            $sanitized = self::DEFAULT_META_BASE_URL;
        }

        $sanitized = rtrim($sanitized, '/');
        $withoutVersion = preg_replace('#/v\d+\.\d+$#i', '', $sanitized);
        if (is_string($withoutVersion) && $withoutVersion !== '') {
            $sanitized = $withoutVersion;
        }

        if (!preg_match('#^https?://#i', $sanitized)) {
            $sanitized = self::DEFAULT_META_BASE_URL;
        }

        return rtrim($sanitized, '/') . '/' . self::META_API_VERSION;
    }

    private function resolveProvider(): string
    {
        $provider = function_exists('sysconfig')
            ? (string) sysconfig('WHATSAPP_PROVIDER', config('services.whatsapp.provider', 'whatsapp_business'))
            : (string) config('services.whatsapp.provider', 'whatsapp_business');

        $provider = strtolower(trim($provider));
        return $provider === '' ? 'whatsapp_business' : $provider;
    }

    /**
     * @param array<int, string> $configKeys
     * @param array<int, string> $sysconfigKeys
     */
    private function resolveConfigValue(array $configKeys, array $sysconfigKeys, string $default = ''): string
    {
        foreach ($configKeys as $configKey) {
            $value = trim((string) config($configKey, ''));
            if ($value !== '') {
                return $value;
            }
        }

        if (function_exists('sysconfig')) {
            foreach ($sysconfigKeys as $key) {
                $value = trim((string) sysconfig($key, ''));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return trim($default);
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

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function summarizePayload(array $payload): array
    {
        $components = (array) ($payload['components'] ?? []);
        $componentTypes = [];
        $bodyExampleCount = 0;

        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            $type = strtoupper((string) ($component['type'] ?? ''));
            if ($type !== '') {
                $componentTypes[] = $type;
            }

            if ($type === 'BODY') {
                $bodyExampleCount = count((array) ($component['example']['body_text'][0] ?? []));
            }
        }

        return [
            'name' => $payload['name'] ?? null,
            'category' => $payload['category'] ?? null,
            'language' => $payload['language'] ?? null,
            'components_count' => count($components),
            'components_types' => $componentTypes,
            'body_example_values_count' => $bodyExampleCount,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function previewPayload(array $payload): array
    {
        $preview = $payload;
        $components = (array) ($preview['components'] ?? []);
        $normalized = [];

        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            $normalized[] = $this->normalizeComponentForPreview($component);
        }

        $preview['components'] = $normalized;
        return $preview;
    }

    /**
     * @param array<string, mixed> $component
     * @return array<string, mixed>
     */
    private function normalizeComponentForPreview(array $component): array
    {
        $type = strtoupper((string) ($component['type'] ?? ''));

        if ($type === 'BODY') {
            $text = (string) ($component['text'] ?? '');
            preg_match_all('/\{\{(\d+)\}\}/', $text, $matches);

            return [
                'type' => 'BODY',
                'text_preview' => $this->summarizeBody($text, 300),
                'placeholders' => array_values(array_unique($matches[1] ?? [])),
                'has_example' => isset($component['example']),
                'example_values_count' => count((array) ($component['example']['body_text'][0] ?? [])),
                'add_security_recommendation' => $component['add_security_recommendation'] ?? null,
            ];
        }

        if ($type === 'FOOTER') {
            return [
                'type' => 'FOOTER',
                'text_preview' => isset($component['text']) ? $this->summarizeBody((string) $component['text'], 200) : null,
                'code_expiration_minutes' => $component['code_expiration_minutes'] ?? null,
            ];
        }

        if ($type === 'BUTTONS') {
            return [
                'type' => 'BUTTONS',
                'buttons' => (array) ($component['buttons'] ?? []),
            ];
        }

        return [
            'type' => $type !== '' ? $type : 'UNKNOWN',
        ];
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    private function extractMetaError(array $response): array
    {
        $error = (array) ($response['error'] ?? []);
        $errorData = (array) ($error['error_data'] ?? []);

        $details = trim((string) ($errorData['details'] ?? $errorData['messaging_product'] ?? ''));

        return [
            'message' => (string) ($error['message'] ?? ''),
            'type' => (string) ($error['type'] ?? ''),
            'code' => $error['code'] ?? null,
            'error_subcode' => $error['error_subcode'] ?? null,
            'details' => $details,
            'error_data' => $errorData,
            'fbtrace_id' => (string) ($error['fbtrace_id'] ?? ''),
        ];
    }

    private function summarizeBody(mixed $body, int $limit = 1500): string
    {
        if (is_array($body)) {
            $json = json_encode($body, JSON_UNESCAPED_UNICODE);
            $body = $json === false ? '' : $json;
        }

        $text = (string) $body;
        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit) . '...';
    }
}
