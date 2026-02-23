<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TestWhatsAppSendRequest;
use App\Models\Tenant\TenantSetting;
use App\Services\Providers\ProviderConfigResolver;
use App\Services\WhatsApp\WhatsAppBusinessProvider;
use App\Services\WhatsApp\WahaClient;
use App\Services\WhatsApp\WahaProvider;
use App\Services\WhatsApp\ZApiProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppSettingsTestController extends Controller
{
    public function testConnection(Request $request, string $service): JsonResponse
    {
        $this->applyTenantWhatsAppConfig();

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
            default => response()->json([
                'status' => 'ERROR',
                'message' => 'Servico nao suportado.',
            ], 422),
        };
    }

    public function testMetaSend(TestWhatsAppSendRequest $request): JsonResponse
    {
        $this->applyTenantWhatsAppConfig();

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
        $this->applyTenantWhatsAppConfig();

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
        $this->applyTenantWhatsAppConfig();

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
        $chatId = WahaClient::formatChatIdFromPhone($request->input('number'));
        if ($chatId === '') {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Numero de destino invalido.',
            ]);
        }

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

    private function applyTenantWhatsAppConfig(): void
    {
        $providerSettings = TenantSetting::whatsappProvider();
        $resolver = new ProviderConfigResolver();

        $driver = $providerSettings['driver'] ?? 'global';
        $globalProvider = sysconfig('WHATSAPP_PROVIDER', config('services.whatsapp.provider', 'whatsapp_business'));

        config([
            'services.whatsapp.provider' => $driver === 'global'
                ? $globalProvider
                : ($providerSettings['provider'] ?? 'whatsapp_business'),
            'services.whatsapp.business.api_url' => config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v18.0'),
            'services.whatsapp.business.token' => $providerSettings['meta_access_token'] ?? '',
            'services.whatsapp.business.phone_id' => $providerSettings['meta_phone_number_id'] ?? '',
            'services.whatsapp.zapi.api_url' => $providerSettings['zapi_api_url'] ?? 'https://api.z-api.io',
            'services.whatsapp.zapi.token' => $providerSettings['zapi_token'] ?? '',
            'services.whatsapp.zapi.client_token' => $providerSettings['zapi_client_token'] ?? '',
            'services.whatsapp.zapi.instance_id' => $providerSettings['zapi_instance_id'] ?? '',
        ]);

        $resolver->applyWahaConfig($resolver->resolveWahaConfig($providerSettings));
    }

    private function testMetaConnection(): JsonResponse
    {
        $token = (string) config('services.whatsapp.business.token', '');
        $phoneId = (string) config('services.whatsapp.business.phone_id', '');
        $apiUrl = rtrim((string) config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v18.0'), '/');

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
            if (in_array($normalized, ['meta', 'zapi', 'waha'], true)) {
                return $normalized;
            }
        }

        return $this->normalizeService($serviceParam);
    }
}
