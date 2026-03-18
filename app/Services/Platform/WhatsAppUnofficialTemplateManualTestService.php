<?php

namespace App\Services\Platform;

use App\Models\Platform\WhatsAppUnofficialTemplate;
use App\Services\Providers\ProviderConfigResolver;
use App\Services\Tenant\TemplateRenderer;
use App\Services\WhatsAppService;
use App\Support\WhatsAppUnofficialTemplateFakeDataFactory;
use DomainException;
use Illuminate\Support\Facades\Log;

class WhatsAppUnofficialTemplateManualTestService
{
    public function __construct(
        private readonly TemplateRenderer $renderer,
        private readonly WhatsAppUnofficialTemplateFakeDataFactory $fakeDataFactory
    ) {
    }

    /**
     * @param  array<string, mixed>  $variables
     * @return array{
     *   required_variables:list<string>,
     *   missing_variables:list<string>,
     *   resolved_variables:array<string, string>,
     *   rendered_message:string,
     *   preview_source:string
     * }
     */
    public function preview(
        WhatsAppUnofficialTemplate $template,
        array $variables,
        bool $fillMissingWithFake = false
    ): array {
        $requiredVariables = $this->resolveRequiredVariables($template);
        $providedVariables = $this->normalizeVariables($variables);
        $fakeValues = $this->fakeDataFactory->build($requiredVariables);

        if ($fillMissingWithFake) {
            foreach ($requiredVariables as $name) {
                if (!array_key_exists($name, $providedVariables) || trim($providedVariables[$name]) === '') {
                    $providedVariables[$name] = $fakeValues[$name] ?? $this->fakeDataFactory->valueFor($name);
                }
            }
        }

        $missingVariables = $this->missingRequiredVariables($requiredVariables, $providedVariables);
        $context = $this->buildContext($providedVariables);
        $rendered = $this->renderer->render((string) $template->body, $context);

        return [
            'required_variables' => $requiredVariables,
            'missing_variables' => $missingVariables,
            'resolved_variables' => $providedVariables,
            'rendered_message' => $rendered,
            'preview_source' => 'tenant_template_renderer',
        ];
    }

    /**
     * @param  array<string, mixed>  $variables
     * @param  array<string, mixed>  $meta
     * @return array{
     *   sent:bool,
     *   provider:string,
     *   rendered_message:string,
     *   missing_variables:list<string>,
     *   preview_source:string
     * }
     */
    public function send(
        WhatsAppUnofficialTemplate $template,
        string $phone,
        array $variables,
        array $meta = []
    ): array {
        $provider = $this->resolveActiveProvider();
        $readiness = $this->validateProviderReadiness($provider);
        if (($readiness['ok'] ?? false) !== true) {
            Log::warning('wa_unofficial_manual_test_blocked_provider_not_ready', [
                'template_key' => (string) $template->key,
                'template_scope' => 'platform_unofficial',
                'provider' => $provider,
                'to_masked' => $this->maskPhone($phone),
                'result' => 'error',
                'error' => (string) ($readiness['message'] ?? 'Provider nao oficial indisponivel.'),
            ]);

            throw new DomainException((string) ($readiness['message'] ?? 'Provider nao oficial indisponivel.'));
        }

        $preview = $this->preview($template, $variables, false);
        if (($preview['missing_variables'] ?? []) !== []) {
            Log::warning('wa_unofficial_manual_test_blocked_missing_variables', [
                'template_key' => (string) $template->key,
                'template_scope' => 'platform_unofficial',
                'provider' => $provider,
                'to_masked' => $this->maskPhone($phone),
                'preview_source' => $preview['preview_source'] ?? 'tenant_template_renderer',
                'missing_variables' => $preview['missing_variables'] ?? [],
                'result' => 'error',
                'error' => 'Variaveis obrigatorias ausentes.',
            ]);

            throw new DomainException(
                'Variaveis obrigatorias ausentes: ' . implode(', ', (array) $preview['missing_variables'])
            );
        }

        $this->applyProviderConfig($provider);
        $whatsAppService = new WhatsAppService();
        $sent = $whatsAppService->sendMessage($phone, (string) $preview['rendered_message']);

        if ($sent !== true) {
            Log::warning('wa_unofficial_manual_test_send_provider_error', [
                'template_key' => (string) $template->key,
                'template_scope' => 'platform_unofficial',
                'provider' => $provider,
                'to_masked' => $this->maskPhone($phone),
                'preview_source' => $preview['preview_source'] ?? 'tenant_template_renderer',
                'result' => 'error',
                'error' => 'Falha ao enviar mensagem no provider nao oficial ativo.',
            ]);

            throw new DomainException('Falha ao enviar mensagem no provider nao oficial ativo.');
        }

        Log::info('wa_unofficial_manual_test_sent', [
            'template_key' => (string) $template->key,
            'template_scope' => 'platform_unofficial',
            'provider' => $provider,
            'to_masked' => $this->maskPhone($phone),
            'preview_source' => $preview['preview_source'] ?? 'tenant_template_renderer',
            'result' => 'success',
            'meta' => $this->sanitizeMeta($meta),
        ]);

        return [
            'sent' => true,
            'provider' => $provider,
            'rendered_message' => (string) $preview['rendered_message'],
            'missing_variables' => [],
            'preview_source' => (string) ($preview['preview_source'] ?? 'tenant_template_renderer'),
        ];
    }

    /**
     * @return array{
     *   required_variables:list<string>,
     *   fake_values:array<string, string>
     * }
     */
    public function describeTemplate(WhatsAppUnofficialTemplate $template): array
    {
        $requiredVariables = $this->resolveRequiredVariables($template);

        return [
            'required_variables' => $requiredVariables,
            'fake_values' => $this->fakeDataFactory->build($requiredVariables),
        ];
    }

    public function activeProvider(): string
    {
        return $this->resolveActiveProvider();
    }

    /**
     * @return list<string>
     */
    private function resolveRequiredVariables(WhatsAppUnofficialTemplate $template): array
    {
        $placeholderVariables = $this->renderer->extractPlaceholders((string) $template->body);
        $declaredVariables = [];

        if (is_array($template->variables)) {
            foreach ($template->variables as $variable) {
                $normalized = trim((string) $variable);
                if ($normalized !== '') {
                    $declaredVariables[] = $normalized;
                }
            }
        }

        $required = array_values(array_unique(array_merge($placeholderVariables, $declaredVariables)));
        sort($required);

        return $required;
    }

    /**
     * @param  array<string, mixed>  $variables
     * @return array<string, string>
     */
    private function normalizeVariables(array $variables): array
    {
        $normalized = [];

        foreach ($variables as $name => $value) {
            $key = trim((string) $name);
            if ($key === '') {
                continue;
            }

            if ($value === null) {
                $normalized[$key] = '';
                continue;
            }

            if (is_scalar($value)) {
                $normalized[$key] = trim((string) $value);
            }
        }

        return $normalized;
    }

    /**
     * @param  list<string>  $requiredVariables
     * @param  array<string, string>  $providedVariables
     * @return list<string>
     */
    private function missingRequiredVariables(array $requiredVariables, array $providedVariables): array
    {
        $missing = [];

        foreach ($requiredVariables as $required) {
            $value = $providedVariables[$required] ?? null;
            if (!is_string($value) || trim($value) === '') {
                $missing[] = $required;
            }
        }

        return $missing;
    }

    /**
     * @param  array<string, string>  $variables
     * @return array<string, mixed>
     */
    private function buildContext(array $variables): array
    {
        $context = [];

        foreach ($variables as $name => $value) {
            if (trim($value) === '') {
                continue;
            }

            data_set($context, $name, $value);
        }

        return $context;
    }

    private function resolveActiveProvider(): string
    {
        $provider = function_exists('sysconfig')
            ? sysconfig('WHATSAPP_PROVIDER', config('services.whatsapp.provider', 'whatsapp_business'))
            : config('services.whatsapp.provider', 'whatsapp_business');

        $normalized = strtolower(trim((string) $provider));
        $aliases = [
            'z-api' => 'zapi',
            'z_api' => 'zapi',
            'whatsapp_business' => 'whatsapp_business',
            'business' => 'whatsapp_business',
        ];

        return $aliases[$normalized] ?? $normalized;
    }

    /**
     * @return array{ok:bool,message:string}
     */
    private function validateProviderReadiness(string $provider): array
    {
        if (!in_array($provider, ['waha', 'zapi'], true)) {
            return [
                'ok' => false,
                'message' => 'Provider ativo nao e nao oficial. Configure WAHA ou Z-API para este teste.',
            ];
        }

        if ($provider === 'waha') {
            $baseUrl = trim((string) (function_exists('sysconfig')
                ? sysconfig('WAHA_BASE_URL', config('services.whatsapp.waha.base_url', ''))
                : config('services.whatsapp.waha.base_url', '')));
            $apiKey = trim((string) (function_exists('sysconfig')
                ? sysconfig('WAHA_API_KEY', config('services.whatsapp.waha.api_key', ''))
                : config('services.whatsapp.waha.api_key', '')));
            $session = trim((string) (function_exists('sysconfig')
                ? sysconfig('WAHA_SESSION', config('services.whatsapp.waha.session', 'default'))
                : config('services.whatsapp.waha.session', 'default')));

            if ($baseUrl === '' || $apiKey === '' || $session === '') {
                return [
                    'ok' => false,
                    'message' => 'Provider WAHA nao esta apto: configure base_url, api_key e session.',
                ];
            }

            return [
                'ok' => true,
                'message' => 'Provider WAHA apto.',
            ];
        }

        $apiUrl = trim((string) (function_exists('sysconfig')
            ? sysconfig('ZAPI_API_URL', config('services.whatsapp.zapi.api_url', 'https://api.z-api.io'))
            : config('services.whatsapp.zapi.api_url', 'https://api.z-api.io')));
        $token = trim((string) (function_exists('sysconfig')
            ? sysconfig('ZAPI_TOKEN', config('services.whatsapp.zapi.token', ''))
            : config('services.whatsapp.zapi.token', '')));
        $instanceId = trim((string) (function_exists('sysconfig')
            ? sysconfig('ZAPI_INSTANCE_ID', config('services.whatsapp.zapi.instance_id', ''))
            : config('services.whatsapp.zapi.instance_id', '')));

        if ($apiUrl === '' || $token === '' || $instanceId === '') {
            return [
                'ok' => false,
                'message' => 'Provider Z-API nao esta apto: configure api_url, token e instance_id.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Provider Z-API apto.',
        ];
    }

    private function applyProviderConfig(string $provider): void
    {
        if ($provider === 'waha') {
            $resolver = new ProviderConfigResolver();
            $resolver->applyWahaConfig($resolver->resolveWahaConfig());

            config([
                'services.whatsapp.provider' => 'waha',
            ]);

            return;
        }

        $zapiApiUrl = function_exists('sysconfig')
            ? sysconfig('ZAPI_API_URL', config('services.whatsapp.zapi.api_url', 'https://api.z-api.io'))
            : config('services.whatsapp.zapi.api_url', 'https://api.z-api.io');
        $zapiToken = function_exists('sysconfig')
            ? sysconfig('ZAPI_TOKEN', config('services.whatsapp.zapi.token', ''))
            : config('services.whatsapp.zapi.token', '');
        $zapiClientToken = function_exists('sysconfig')
            ? sysconfig('ZAPI_CLIENT_TOKEN', config('services.whatsapp.zapi.client_token', ''))
            : config('services.whatsapp.zapi.client_token', '');
        $zapiInstanceId = function_exists('sysconfig')
            ? sysconfig('ZAPI_INSTANCE_ID', config('services.whatsapp.zapi.instance_id', ''))
            : config('services.whatsapp.zapi.instance_id', '');

        config([
            'services.whatsapp.provider' => 'zapi',
            'services.whatsapp.zapi.api_url' => $zapiApiUrl,
            'services.whatsapp.zapi.token' => $zapiToken,
            'services.whatsapp.zapi.client_token' => $zapiClientToken,
            'services.whatsapp.zapi.instance_id' => $zapiInstanceId,
        ]);
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '***';
        }

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return str_repeat('*', strlen($digits) - 4) . substr($digits, -4);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, scalar|null>
     */
    private function sanitizeMeta(array $meta): array
    {
        $sanitized = [];
        foreach ($meta as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            if ($value === null || is_scalar($value)) {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
