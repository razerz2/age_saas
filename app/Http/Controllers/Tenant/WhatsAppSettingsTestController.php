<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TestWhatsAppSendRequest;
use App\Models\Tenant\TenantSetting;
use App\Services\WhatsApp\WhatsAppBusinessProvider;
use App\Services\WhatsApp\WahaProvider;
use App\Services\WhatsApp\ZApiProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppSettingsTestController extends Controller
{
    public function testConnection(Request $request, string $service): JsonResponse
    {
        $this->applyTenantWhatsAppConfig();

        return match (strtolower($service)) {
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

        try {
            $provider = new WahaProvider();
            $ok = $provider->sendMessage($request->input('number'), $request->input('message'));

            return response()->json([
                'status' => $ok ? 'OK' : 'ERROR',
                'message' => $ok
                    ? 'Mensagem de teste WAHA enviada com sucesso.'
                    : 'Falha ao enviar mensagem de teste WAHA. Verifique as configuracoes.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage() ?: 'Erro ao enviar mensagem de teste WAHA.',
            ]);
        }
    }

    private function applyTenantWhatsAppConfig(): void
    {
        config([
            'services.whatsapp.provider' => TenantSetting::get('whatsapp.provider', 'whatsapp_business'),
            'services.whatsapp.business.api_url' => config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v18.0'),
            'services.whatsapp.business.token' => TenantSetting::get('whatsapp.meta.access_token', ''),
            'services.whatsapp.business.phone_id' => TenantSetting::get('whatsapp.meta.phone_number_id', ''),
            'services.whatsapp.zapi.api_url' => TenantSetting::get('whatsapp.zapi.api_url', 'https://api.z-api.io'),
            'services.whatsapp.zapi.token' => TenantSetting::get('whatsapp.zapi.token', ''),
            'services.whatsapp.zapi.client_token' => TenantSetting::get('whatsapp.zapi.client_token', ''),
            'services.whatsapp.zapi.instance_id' => TenantSetting::get('whatsapp.zapi.instance_id', ''),
            'services.whatsapp.waha.base_url' => TenantSetting::get('whatsapp.waha.base_url', ''),
            'services.whatsapp.waha.api_key' => TenantSetting::get('whatsapp.waha.api_key', ''),
            'services.whatsapp.waha.session' => TenantSetting::get('whatsapp.waha.session', 'default'),
        ]);
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
        $baseUrl = (string) config('services.whatsapp.waha.base_url', '');
        $apiKey = (string) config('services.whatsapp.waha.api_key', '');
        $session = (string) config('services.whatsapp.waha.session', 'default');

        if ($baseUrl === '' || $apiKey === '' || $session === '') {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'WAHA nao esta configurado corretamente.',
            ]);
        }

        $endpoint = rtrim($baseUrl, '/') . '/api/sessions/' . urlencode($session);
        $response = Http::timeout(8)
            ->withOptions(['verify' => app()->environment('local') ? false : true])
            ->withHeaders(['X-Api-Key' => $apiKey])
            ->get($endpoint);

        if (!$response->successful()) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'HTTP ' . $response->status() . ' - ' . $response->body(),
            ]);
        }

        $data = $response->json();
        $working = isset($data['status']) && $data['status'] === 'WORKING';

        return response()->json([
            'status' => $working ? 'OK' : 'ERROR',
            'message' => $working
                ? 'Sessao WAHA esta conectada (WORKING).'
                : 'Status retornado: ' . ($data['status'] ?? 'indefinido'),
        ]);
    }
}
