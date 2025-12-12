<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use App\Services\WhatsApp\ZApiProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZApiController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Exibe o formulário para enviar mensagem via Z-API
     */
    public function index()
    {
        // Verifica se o provedor atual é Z-API
        $provider = $this->whatsapp->getProvider();
        $isZApi = $provider instanceof ZApiProvider;

        // Informações de configuração (sem expor valores sensíveis)
        $configInfo = [];
        if ($isZApi) {
            $configInfo = [
                'api_url' => config('services.whatsapp.zapi.api_url', 'não configurado'),
                'token_set' => !empty(config('services.whatsapp.zapi.token')),
                'instance_id_set' => !empty(config('services.whatsapp.zapi.instance_id')),
                'instance_id' => config('services.whatsapp.zapi.instance_id', 'não configurado'),
            ];
        }

        return view('platform.zapi.index', [
            'isZApi' => $isZApi,
            'providerName' => $isZApi ? 'Z-API' : class_basename($provider),
            'configInfo' => $configInfo,
        ]);
    }

    /**
     * Envia mensagem via Z-API
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:4096',
        ]);

        try {
            // Verifica se o provedor atual é Z-API
            $provider = $this->whatsapp->getProvider();
            $isZApi = $provider instanceof ZApiProvider;

            if (!$isZApi) {
                return back()
                    ->withInput()
                    ->with('error', 'O provedor configurado não é Z-API. Configure o sistema para usar Z-API primeiro.');
            }

            $success = $this->whatsapp->sendMessage($validated['phone'], $validated['message']);

            if ($success) {
                Log::info('📤 Mensagem Z-API enviada via Platform', [
                    'phone' => $validated['phone'],
                    'message_length' => strlen($validated['message']),
                    'sent_by' => auth()->id(),
                ]);

                return back()
                    ->with('success', 'Mensagem enviada com sucesso via Z-API!');
            } else {
                return back()
                    ->withInput()
                    ->with('error', 'Falha ao enviar mensagem. Verifique os logs para mais detalhes.');
            }
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar mensagem Z-API via Platform', [
                'error' => $e->getMessage(),
                'phone' => $validated['phone'],
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao enviar mensagem: ' . $e->getMessage());
        }
    }
}

