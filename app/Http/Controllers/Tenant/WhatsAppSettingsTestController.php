<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TestWhatsAppSendRequest;
use App\Models\Tenant\TenantSetting;
use App\Services\Providers\ProviderConfigResolver;
use App\Services\WhatsApp\TenantGlobalProviderCatalogService;
use App\Services\WhatsApp\WhatsAppBusinessProvider;
use App\Services\WhatsApp\WahaClient;
use App\Services\WhatsApp\WahaProvider;
use App\Services\WhatsApp\ZApiProvider;
use App\Services\WhatsApp\EvolutionClient;
use App\Services\WhatsApp\EvolutionProvider;
use App\Services\WhatsApp\PhoneNormalizer;
use App\Services\Tenant\WhatsAppBotConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppSettingsTestController extends Controller
{
    public function testConnection(Request $request, string $service): JsonResponse
    {
        $this->applyTenantWhatsAppConfig($this->resolveConfigScope($request));

        $providerSettings = TenantSetting::whatsappProvider();
        $providerParam = (string) $request->input('provider', $request->input('service', ''));
        $serviceKey = $this->resolveProviderKey($service, $providerParam);

        Log::info('🔍 Tenant WhatsApp teste conexao', [
            'service_param' => $service,
            'provider_param' => $providerParam,
            'driver_setting' => $providerSettings['driver'] ?? null,
            'provider_setting' => $providerSettings['provider'] ?? null,
            'resolved_provider' => $serviceKey,
            'sysconfig_provider' => sysconfig('WHATSAPP_PROVIDER', ''),
            'waha_base_url' => config('services.whatsapp.waha.base_url'),
            'waha_session' => config('services.whatsapp.waha.session'),
        ]);

        return match ($serviceKey) {
            'meta' => $this->testMetaConnection(),
            'zapi' => $this->testZapiConnection(),
            'waha' => $this->testWahaConnection(),
            'evolution' => $this->testEvolutionConnection(),
            default => response()->json([
                'status' => 'ERROR',
                'message' => 'Servico nao suportado.',
            ], 422),
        };
    }

    public function testMetaSend(TestWhatsAppSendRequest $request): JsonResponse
    {
        $this->applyTenantWhatsAppConfig($this->resolveConfigScope($request));

        try {
            $provider = new WhatsAppBusinessProvider();
            $ok = $provider->sendMessage($request->input('number'), $request->input('message'));

            return response()->json([
                'status' => $ok ? 'OK' : 'ERROR',
                'message' => $ok
                    ? 'Mensagem enviada com sucesso.'
                    : 'Falha ao enviar mensagem de teste Meta. Verifique as configuracoes.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage() ?: 'Erro ao enviar mensagem de teste Meta.',
            ]);
        }
    }

    public function testZapiSend(TestWhatsAppSendRequest $request): JsonResponse
    {
        $this->applyTenantWhatsAppConfig($this->resolveConfigScope($request));

        try {
            $provider = new ZApiProvider();
            $ok = $provider->sendMessage($request->input('number'), $request->input('message'));

            return response()->json([
                'status' => $ok ? 'OK' : 'ERROR',
                'message' => $ok
                    ? 'Mensagem enviada com sucesso.'
                    : 'Falha ao enviar mensagem de teste Z-API. Verifique as configuracoes.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage() ?: 'Erro ao enviar mensagem de teste Z-API.',
            ]);
        }
    }

    public function testWahaSend(TestWhatsAppSendRequest $request): JsonResponse
    {
        try {
            $chatId = WahaClient::formatChatIdFromPhone($request->input('number'));
        } catch (\InvalidArgumentException $e) {
            Log::warning('Numero invalido no teste WAHA (tenant)', [
                'number' => PhoneNormalizer::maskPhone((string) $request->input('number')),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Telefone inválido para WhatsApp. Use DDD + número (ex: 67999998888).',
            ]);
        }

        $this->applyTenantWhatsAppConfig($this->resolveConfigScope($request));

        $provider = new WahaProvider();
        $sessionCheck = $provider->testSession();
        if (($sessionCheck['status'] ?? 'ERROR') !== 'OK') {
            return response()->json([
                'status' => 'ERROR',
                'message' => $sessionCheck['message'] ?? 'Sessao WAHA nao esta pronta para envio.',
                'data' => $sessionCheck['data'] ?? [],
                'http_status' => $sessionCheck['http_status'] ?? null,
            ]);
        }

        $client = WahaClient::fromConfig();

        try {
            $sendResult = $client->sendText($chatId, $request->input('message'));
            $sendBody = $sendResult['body'] ?? null;
            $ok = !empty($sendResult['ok']) && !(is_array($sendBody) && isset($sendBody['error']));

            Log::info('📤 WAHA teste de envio - resposta', [
                'base_url' => $client->getBaseUrl(),
                'session' => $client->getSession(),
                'chat' => $chatId,
                'status_code' => $sendResult['status'] ?? null,
            ]);

            $payload = [
                'status' => $ok ? 'OK' : 'ERROR',
                'message' => $ok
                    ? 'Mensagem de teste WAHA enviada com sucesso.'
                    : 'Falha ao enviar mensagem de teste WAHA. Verifique as configuracoes.',
            ];

            if (config('app.debug')) {
                $payload['debug'] = [
                    'http_status' => $sendResult['status'] ?? null,
                    'body' => $sendBody,
                ];
            }

            return response()->json($payload);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage() ?: 'Erro ao enviar mensagem de teste WAHA.',
            ]);
        }
    }

    public function testEvolutionSend(TestWhatsAppSendRequest $request): JsonResponse
    {
        $this->applyTenantWhatsAppConfig($this->resolveConfigScope($request));

        try {
            $provider = new EvolutionProvider();
            $ok = $provider->sendMessage($request->input('number'), $request->input('message'));

            return response()->json([
                'status' => $ok ? 'OK' : 'ERROR',
                'message' => $ok
                    ? 'Mensagem enviada com sucesso.'
                    : 'Falha ao enviar mensagem de teste Evolution. Verifique as configuracoes.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage() ?: 'Erro ao enviar mensagem de teste Evolution.',
            ]);
        }
    }

    private function applyTenantWhatsAppConfig(string $scope = 'notifications'): void
    {
        if ($scope === 'bot') {
            $effectiveConfig = app(WhatsAppBotConfigService::class)->resolveEffectiveProviderConfig();
            $provider = strtolower(trim((string) ($effectiveConfig['provider'] ?? 'whatsapp_business')));
            if ($provider === '') {
                $provider = 'whatsapp_business';
            }

            config([
                'services.whatsapp.force_runtime_provider' => true,
                'services.whatsapp.runtime_provider' => $provider,
                'services.whatsapp.provider' => $provider,
                'services.whatsapp.business.api_url' => (string) config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0'),
                'services.whatsapp.business.token' => (string) ($effectiveConfig['meta_access_token'] ?? ''),
                'services.whatsapp.business.phone_id' => (string) ($effectiveConfig['meta_phone_number_id'] ?? ''),
                'services.whatsapp.business.waba_id' => (string) ($effectiveConfig['meta_waba_id'] ?? ''),
                'services.whatsapp.zapi.api_url' => (string) ($effectiveConfig['zapi_api_url'] ?? 'https://api.z-api.io'),
                'services.whatsapp.zapi.token' => (string) ($effectiveConfig['zapi_token'] ?? ''),
                'services.whatsapp.zapi.client_token' => (string) ($effectiveConfig['zapi_client_token'] ?? ''),
                'services.whatsapp.zapi.instance_id' => (string) ($effectiveConfig['zapi_instance_id'] ?? ''),
                'services.whatsapp.waha.base_url' => (string) ($effectiveConfig['waha_base_url'] ?? ''),
                'services.whatsapp.waha.api_key' => (string) ($effectiveConfig['waha_api_key'] ?? ''),
                'services.whatsapp.waha.session' => (string) ($effectiveConfig['waha_session'] ?? 'default'),
                'services.whatsapp.evolution.base_url' => (string) ($effectiveConfig['evolution_base_url'] ?? ''),
                'services.whatsapp.evolution.api_key' => (string) ($effectiveConfig['evolution_api_key'] ?? ''),
                'services.whatsapp.evolution.instance' => (string) ($effectiveConfig['evolution_instance'] ?? 'default'),
            ]);

            return;
        }

        $providerSettings = TenantSetting::whatsappProvider();
        $resolver = new ProviderConfigResolver();

        $driver = $providerSettings['driver'] ?? 'global';
        $tenantGlobalProviderCatalog = app(TenantGlobalProviderCatalogService::class);
        $globalProvider = $tenantGlobalProviderCatalog->resolveTenantGlobalProvider(
            (string) ($providerSettings['global_provider'] ?? '')
        );
        $effectiveGlobalProvider = $globalProvider ?? '__invalid_tenant_global_provider__';
        $globalMetaApiUrl = $this->resolveGlobalWhatsAppMetaValue(
            ['WHATSAPP_META_BASE_URL', 'WHATSAPP_BUSINESS_API_URL', 'WHATSAPP_API_URL'],
            (string) config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0')
        );
        $globalMetaToken = $this->resolveGlobalWhatsAppMetaValue(
            ['WHATSAPP_META_TOKEN', 'WHATSAPP_BUSINESS_TOKEN', 'META_ACCESS_TOKEN', 'BOT_META_ACCESS_TOKEN', 'bot_meta_access_token'],
            (string) config('services.whatsapp.business.token', config('services.whatsapp.token', ''))
        );
        $globalMetaPhoneId = $this->resolveGlobalWhatsAppMetaValue(
            ['WHATSAPP_META_PHONE_NUMBER_ID', 'WHATSAPP_BUSINESS_PHONE_ID', 'META_PHONE_NUMBER_ID', 'BOT_META_PHONE_NUMBER_ID', 'bot_meta_phone_number_id'],
            (string) config('services.whatsapp.business.phone_id', config('services.whatsapp.phone_id', ''))
        );
        $globalMetaWabaId = $this->resolveGlobalWhatsAppMetaValue(
            ['WHATSAPP_META_WABA_ID', 'WHATSAPP_BUSINESS_ACCOUNT_ID', 'META_WABA_ID', 'BOT_META_WABA_ID', 'bot_meta_waba_id'],
            (string) config('services.whatsapp.business.waba_id', '')
        );

        config([
            'services.whatsapp.force_runtime_provider' => true,
            'services.whatsapp.runtime_provider' => $driver === 'global'
                ? strtolower(trim($effectiveGlobalProvider))
                : (strtolower(trim((string) ($providerSettings['provider'] ?? 'whatsapp_business'))) ?: 'whatsapp_business'),
            'services.whatsapp.provider' => $driver === 'global'
                ? $effectiveGlobalProvider
                : ($providerSettings['provider'] ?? 'whatsapp_business'),
            'services.whatsapp.business.api_url' => $driver === 'global'
                ? $globalMetaApiUrl
                : config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0'),
            'services.whatsapp.business.token' => $driver === 'global'
                ? $globalMetaToken
                : ($providerSettings['meta_access_token'] ?? ''),
            'services.whatsapp.business.phone_id' => $driver === 'global'
                ? $globalMetaPhoneId
                : ($providerSettings['meta_phone_number_id'] ?? ''),
            'services.whatsapp.business.waba_id' => $driver === 'global'
                ? $globalMetaWabaId
                : ($providerSettings['meta_waba_id'] ?? ''),
            'services.whatsapp.zapi.api_url' => $driver === 'global'
                ? config('services.whatsapp.zapi.api_url', 'https://api.z-api.io')
                : ($providerSettings['zapi_api_url'] ?? 'https://api.z-api.io'),
            'services.whatsapp.zapi.token' => $driver === 'global'
                ? config('services.whatsapp.zapi.token', '')
                : ($providerSettings['zapi_token'] ?? ''),
            'services.whatsapp.zapi.client_token' => $driver === 'global'
                ? config('services.whatsapp.zapi.client_token', '')
                : ($providerSettings['zapi_client_token'] ?? ''),
            'services.whatsapp.zapi.instance_id' => $driver === 'global'
                ? config('services.whatsapp.zapi.instance_id', '')
                : ($providerSettings['zapi_instance_id'] ?? ''),
        ]);

        $resolver->applyUnofficialRuntimeConfigs($providerSettings);
    }

    private function resolveConfigScope(Request $request): string
    {
        $scope = strtolower(trim((string) $request->input('scope', 'notifications')));
        return $scope === 'bot' ? 'bot' : 'notifications';
    }

    private function testMetaConnection(): JsonResponse
    {
        $token = (string) config('services.whatsapp.business.token', '');
        $phoneId = (string) config('services.whatsapp.business.phone_id', '');
        $apiUrl = rtrim((string) config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0'), '/');

        if ($token === '' || $phoneId === '') {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Credenciais Meta nao configuradas.',
            ]);
        }

        $response = Http::withToken($token)->get($apiUrl . '/' . $phoneId . '/');

        return response()->json([
            'status' => $response->successful() ? 'OK' : 'ERROR',
            'message' => $response->successful()
                ? 'Conexao Meta API OK!'
                : 'Falha Meta: ' . $response->body(),
        ]);
    }

    private function testZapiConnection(): JsonResponse
    {
        $apiUrl = rtrim((string) config('services.whatsapp.zapi.api_url', 'https://api.z-api.io'), '/');
        $clientToken = (string) config('services.whatsapp.zapi.client_token', '');
        $instanceId = (string) config('services.whatsapp.zapi.instance_id', '');

        if ($clientToken === '' || $instanceId === '') {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Credenciais Z-API nao configuradas completamente.',
            ]);
        }

        $endpoint = $apiUrl . '/instances/' . $instanceId . '/status';
        $response = Http::withHeaders([
            'Client-Token' => $clientToken,
        ])->get($endpoint);

        if (!$response->successful()) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Falha Z-API: ' . $response->body(),
            ]);
        }

        $data = $response->json();
        $connected = isset($data['status']) && $data['status'] === 'connected';

        return response()->json([
            'status' => $connected ? 'OK' : 'ERROR',
            'message' => $connected
                ? 'Conexao Z-API OK! Instancia conectada.'
                : 'Z-API: Instancia nao esta conectada. Status: ' . ($data['status'] ?? 'desconhecido'),
        ]);
    }

    private function testWahaConnection(): JsonResponse
    {
        $provider = new WahaProvider();
        $result = $provider->testSession();

        $payload = [
            'status' => $result['status'] ?? 'ERROR',
            'message' => $result['message'] ?? 'Falha ao testar sessao WAHA.',
            'data' => $result['data'] ?? [],
            'http_status' => $result['http_status'] ?? null,
        ];

        return response()->json($payload);
    }

    private function testEvolutionConnection(): JsonResponse
    {
        $client = EvolutionClient::fromConfig();
        $connection = $client->testConnection();
        $state = $client->getConnectionState();

        $stateValue = strtoupper(trim((string) ($state['state'] ?? '')));
        $readyStates = ['OPEN', 'CONNECTED', 'ONLINE', 'WORKING', 'READY'];
        $isReady = $stateValue !== '' && in_array($stateValue, $readyStates, true);

        if (!empty($connection['ok']) && !empty($state['ok']) && $isReady) {
            return response()->json([
                'status' => 'OK',
                'message' => 'Conexao Evolution API OK! Instancia conectada.',
                'data' => [
                    'state' => $stateValue,
                ],
            ]);
        }

        $fallbackMessage = !empty($connection['ok'])
            ? 'Conexao Evolution estabelecida, mas a instancia nao esta conectada.'
            : 'Falha ao validar Evolution API.';

        return response()->json([
            'status' => 'ERROR',
            'message' => $fallbackMessage,
            'data' => [
                'state' => $stateValue !== '' ? $stateValue : null,
                'connection_status' => $connection['status'] ?? null,
                'state_status' => $state['status'] ?? null,
            ],
        ]);
    }

    private function resolveGlobalWhatsAppMetaValue(array $keys, string $fallback = ''): string
    {
        foreach ($keys as $key) {
            $value = function_exists('sysconfig')
                ? (string) sysconfig((string) $key, '')
                : '';

            $value = trim($value);
            if ($value !== '') {
                return $value;
            }
        }

        return trim($fallback);
    }

    private function normalizeService(string $service): string
    {
        $normalized = strtolower(trim($service));
        $aliases = [
            'whatsapp_business' => 'meta',
            'whatsapp-business' => 'meta',
            'business' => 'meta',
            'z-api' => 'zapi',
            'z_api' => 'zapi',
            'waha_gateway' => 'waha',
            'waha-gateway' => 'waha',
            'whatsapp_gateway' => 'waha',
            'whatsapp-gateway' => 'waha',
            'waha_core' => 'waha',
            'waha-core' => 'waha',
            'whatsapp_waha' => 'waha',
            'whatsapp-waha' => 'waha',
            'evolution_api' => 'evolution',
            'evolution-api' => 'evolution',
            'evo_api' => 'evolution',
            'evo-api' => 'evolution',
            'whatsapp_evolution' => 'evolution',
            'whatsapp-evolution' => 'evolution',
        ];

        return $aliases[$normalized] ?? $normalized;
    }

    private function resolveProviderKey(string $serviceParam, string $providerParam): string
    {
        $candidates = [
            $serviceParam,
            $providerParam,
            (string) config('services.whatsapp.provider', ''),
        ];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeService((string) $candidate);
            if (in_array($normalized, ['meta', 'zapi', 'waha', 'evolution'], true)) {
                return $normalized;
            }
        }

        return $this->normalizeService($serviceParam);
    }
}
