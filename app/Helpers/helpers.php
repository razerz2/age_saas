<?php

use App\Models\Platform\SystemSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;


/*
|--------------------------------------------------------------------------
| 📦 Funções globais do sistema
|--------------------------------------------------------------------------
| Este arquivo contém funções auxiliares acessíveis de qualquer parte
| do sistema (controllers, views, jobs, etc.).
|
| Para funcionar corretamente, adicione ao composer.json:
| 
| "autoload": {
|     "psr-4": { "App\\": "app/" },
|     "files": ["app/Helpers/helpers.php"]
| }
|
| E depois rode:
| composer dump-autoload
|
*/

/**
 * 🔹 Obtém um valor de configuração do sistema.
 */
if (!function_exists('sysconfig')) {
    function sysconfig(string $key, $default = null)
    {
        return SystemSetting::where('key', $key)->value('value') ?? $default;
    }
}

/**
 * 🔹 Atualiza ou cria uma configuração do sistema.
 */
if (!function_exists('set_sysconfig')) {
    function set_sysconfig(string $key, $value)
    {
        return SystemSetting::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}

/**
 * 🔧 Atualiza variáveis do arquivo .env com segurança.
 */
if (!function_exists('updateEnv')) {
    function updateEnv(array $data): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            throw new \Exception(".env file not found.");
        }

        $content = File::get($envPath);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}=\"{$value}\"";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                // adiciona no final do arquivo caso não exista
                $content .= "\n{$key}=\"{$value}\"";
            }
        }

        File::put($envPath, $content);

        // limpa cache de configuração
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
        } catch (\Exception $e) {
            Log::warning("Não foi possível limpar cache automaticamente: " . $e->getMessage());
        }
    }
}

/**
 * 🧠 Testa integração de serviços externos (Asaas, Meta, E-mail).
 */
if (!function_exists('testConnection')) {
    function testConnection(string $service): array
    {
        try {
            switch (strtolower($service)) {

                // 🔸 Teste ASAAS
                case 'asaas':
                    $baseUrl = env('ASAAS_API_URL', 'https://api.asaas.com/v3');
                    $token   = env('ASAAS_API_KEY');

                    if (!$token) {
                        return ['status' => false, 'message' => 'Chave ASAAS não configurada.'];
                    }

                    // Se estiver em sandbox, usa /customers?limit=1
                    $endpoint = str_contains($baseUrl, 'sandbox')
                        ? "{$baseUrl}/customers?limit=1"
                        : "{$baseUrl}/me";

                    $response = Http::withHeaders([
                        'access_token' => $token
                    ])->get($endpoint);

                    if ($response->successful()) {
                        return ['status' => true, 'message' => 'Conexão ASAAS bem-sucedida!'];
                    }

                    $status = $response->status();
                    $body = $response->json() ?: $response->body();

                    return [
                        'status' => false,
                        'message' => "Falha ASAAS (HTTP {$status}): " . json_encode($body, JSON_UNESCAPED_UNICODE)
                    ];

                    // 🔸 Teste META (WhatsApp)
                case 'meta':
                    $token = env('META_ACCESS_TOKEN');
                    $phoneId = env('META_PHONE_NUMBER_ID');

                    if (!$token || !$phoneId) {
                        return ['status' => false, 'message' => 'Credenciais Meta não configuradas.'];
                    }

                    $response = Http::withToken($token)
                        ->get("https://graph.facebook.com/v18.0/{$phoneId}/");

                    return $response->successful()
                        ? ['status' => true, 'message' => 'Conexão Meta API OK!']
                        : ['status' => false, 'message' => 'Falha Meta: ' . $response->body()];

                    // 🔸 Teste E-mail
                case 'email':
                    $to = env('MAIL_FROM_ADDRESS', 'teste@localhost');
                    try {
                        Mail::raw('Teste de envio do sistema', function ($msg) use ($to) {
                            $msg->to($to)->subject('Teste de E-mail do Sistema');
                        });
                        return ['status' => true, 'message' => "E-mail de teste enviado para {$to}."];
                    } catch (\Exception $e) {
                        return ['status' => false, 'message' => 'Falha ao enviar e-mail: ' . $e->getMessage()];
                    }

                default:
                    return ['status' => false, 'message' => 'Serviço não reconhecido.'];
            }
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }
}

/**
 * 🧩 Helper rápido para verificar se estamos em ambiente de produção.
 */
if (!function_exists('isProduction')) {
    function isProduction(): bool
    {
        return app()->environment('production');
    }
}

/**
 * 🧰 Retorna a versão atual do sistema.
 */
if (!function_exists('systemVersion')) {
    function systemVersion(): string
    {
        return config('app.version', '1.0.0');
    }
}
