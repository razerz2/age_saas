<?php

namespace App\Http\Controllers\Platform;

use App\Models\Platform\Pais; // ✅ importante

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SystemSettingsController extends Controller
{
    /**
     * Exibe a página de configurações.
     */
    public function index()
    {
        $paises = Pais::orderBy('nome')->get();

        $settings = [
            'timezone' => sysconfig('timezone', 'America/Sao_Paulo'),
            'country_id' => sysconfig('country_id'), // armazenará o id_pais
            'language' => sysconfig('language', 'pt_BR'),
            // demais integrações
            'ASAAS_API_URL' => sysconfig('ASAAS_API_URL', env('ASAAS_API_URL')),
            'ASAAS_API_KEY' => sysconfig('ASAAS_API_KEY', env('ASAAS_API_KEY')),
            'ASAAS_ENV' => sysconfig('ASAAS_ENV', env('ASAAS_ENV', 'sandbox')),
            'META_ACCESS_TOKEN' => sysconfig('META_ACCESS_TOKEN', env('META_ACCESS_TOKEN')),
            'META_PHONE_NUMBER_ID' => sysconfig('META_PHONE_NUMBER_ID', env('META_PHONE_NUMBER_ID')),
            'MAIL_HOST' => sysconfig('MAIL_HOST', env('MAIL_HOST')),
            'MAIL_PORT' => sysconfig('MAIL_PORT', env('MAIL_PORT')),
            'MAIL_USERNAME' => sysconfig('MAIL_USERNAME', env('MAIL_USERNAME')),
            'MAIL_FROM_ADDRESS' => sysconfig('MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
            'MAIL_FROM_NAME' => sysconfig('MAIL_FROM_NAME', env('MAIL_FROM_NAME')),
        ];

        return view('platform.settings.index', compact('settings', 'paises'));
    }

    /**
     * Atualiza as configurações gerais (timezone, país, idioma)
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string',
            'country_id' => 'nullable|integer|exists:paises,id_pais',
            'language' => 'required|string',
        ]);

        set_sysconfig('timezone', $request->timezone);
        set_sysconfig('country_id', $request->country_id);
        set_sysconfig('language', $request->language);

        return back()->with('success', 'Configurações gerais atualizadas com sucesso.');
    }

    /**
     * Atualiza integrações ASAAS / Meta / Email
     */
    public function updateIntegrations(Request $request)
    {
        $request->validate([
            'ASAAS_API_KEY' => 'nullable|string',
            'ASAAS_ENV' => 'nullable|string',
            'META_ACCESS_TOKEN' => 'nullable|string',
            'META_PHONE_NUMBER_ID' => 'nullable|string',
            'MAIL_HOST' => 'nullable|string',
            'MAIL_PORT' => 'nullable|string',
            'MAIL_USERNAME' => 'nullable|string',
            'MAIL_PASSWORD' => 'nullable|string',
            'MAIL_FROM_ADDRESS' => 'nullable|string',
            'MAIL_FROM_NAME' => 'nullable|string',
        ]);

        // Lista de campos que serão atualizados
        $fields = [
            'ASAAS_API_KEY',
            'ASAAS_ENV',
            'META_ACCESS_TOKEN',
            'META_PHONE_NUMBER_ID',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_FROM_ADDRESS',
            'MAIL_FROM_NAME',
        ];

        // Atualiza configurações no banco
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                set_sysconfig($field, $request->$field);
            }
        }

        // Atualiza o .env
        updateEnv($request->only($fields));

        return back()->with('success', 'Integrações e configurações de e-mail atualizadas com sucesso.');
    }

    /**
     * Testa conexão de um serviço (ASAAS / META / EMAIL)
     */
    public function testConnection($service)
    {
        $result = testConnection($service);
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }
}
