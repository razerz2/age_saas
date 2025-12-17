<?php

use App\Models\Platform\SystemSetting;
use App\Models\Platform\Tenant;
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
|     "files": ["app/Helpers/helpers.php", "app/Helpers/FinanceHelpers.php"]
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
            // Em produção, o .env pode não existir ou não ser editável
            // Nesse caso, apenas logamos um aviso e continuamos
            Log::warning("Arquivo .env não encontrado em: {$envPath}. As configurações serão salvas apenas no banco de dados.");
            return;
        }

        try {
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
        } catch (\Exception $e) {
            // Se houver erro ao escrever no .env, apenas logamos
            // As configurações já foram salvas no banco de dados
            Log::warning("Não foi possível atualizar o arquivo .env: " . $e->getMessage());
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

                    // 🔸 Teste Z-API (WhatsApp)
                case 'zapi':
                case 'z-api':
                    $apiUrl = env('ZAPI_API_URL', 'https://api.z-api.io');
                    $token = env('ZAPI_TOKEN');
                    $clientToken = env('ZAPI_CLIENT_TOKEN');
                    $instanceId = env('ZAPI_INSTANCE_ID');

                    if (!$token || !$clientToken || !$instanceId) {
                        return ['status' => false, 'message' => 'Credenciais Z-API não configuradas completamente. Verifique Token, Client Token e Instance ID.'];
                    }

                    // Tenta verificar o status da instância
                    $endpoint = rtrim($apiUrl, '/') . '/instances/' . $instanceId . '/status';
                    
                    try {
                        $response = Http::withHeaders([
                            'Client-Token' => $clientToken,
                        ])->get($endpoint);

                        if ($response->successful()) {
                            $data = $response->json();
                            if (isset($data['status']) && $data['status'] === 'connected') {
                                return ['status' => true, 'message' => 'Conexão Z-API OK! Instância conectada.'];
                            } else {
                                return ['status' => false, 'message' => 'Z-API: Instância não está conectada. Status: ' . ($data['status'] ?? 'desconhecido')];
                            }
                        }

                        return ['status' => false, 'message' => 'Falha Z-API: ' . $response->body()];
                    } catch (\Exception $e) {
                        return ['status' => false, 'message' => 'Erro ao testar Z-API: ' . $e->getMessage()];
                    }

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

if (! function_exists('tenant')) {
    function tenant()
    {
        return Tenant::current();
    }
}

/**
 * 🧩 Gera URL de rota pública do tenant
 */
if (! function_exists('tenant_route')) {
    function tenant_route($tenant, string $routeName, array $parameters = [])
    {
        // Se $tenant for um objeto Tenant, pega o subdomain
        $tenantSlug = is_object($tenant) ? $tenant->subdomain : $tenant;
        
        // Verifica se é uma rota pública (usa 'slug') ou autenticada (também usa 'slug')
        // Rotas públicas começam com 'public.'
        if (str_starts_with($routeName, 'public.')) {
            $parameters['slug'] = $tenantSlug;
        } else {
            // Para rotas autenticadas, também usa 'slug' agora
            $parameters['slug'] = $tenantSlug;
        }
        
        // Gera a rota
        return route($routeName, $parameters);
    }
}

/**
 * 🔧 Gera URL de rota do tenant autenticado (workspace)
 * Automaticamente adiciona o slug do tenant atual
 */
if (! function_exists('workspace_route')) {
    function workspace_route(string $routeName, $parameters = [])
    {
        // Converte parâmetro único (int, string) para array associativo se necessário
        // Assumimos que um valor único é o parâmetro 'id'
        if (!is_array($parameters)) {
            $parameters = $parameters !== null ? ['id' => $parameters] : [];
        }
        
        // Pega o slug do tenant atual (da rota, segment da URL, sessão ou tenant ativo)
        $slug = request()->route('slug') 
            ?? request()->segment(2) // Pega do segundo segmento da URL (workspace/{slug}/...)
            ?? session('tenant_slug') 
            ?? (tenant() ? tenant()->subdomain : null);
        
        if ($slug) {
            // Adiciona o slug aos parâmetros, garantindo que não sobrescreva se já existir
            if (!isset($parameters['slug'])) {
                // Coloca o slug primeiro no array para garantir a ordem correta
                $parameters = array_merge(['slug' => $slug], $parameters);
            }
        }
        
        return route($routeName, $parameters);
    }
}

/**
 * 🔹 Verifica se o usuário tem acesso a um módulo específico
 */
if (! function_exists('has_module')) {
    function has_module(string $module): bool
    {
        $user = auth('tenant')->user();
        
        if (!$user) {
            return false;
        }
        
        // Admin tem acesso a todos os módulos
        if ($user->role === 'admin') {
            return true;
        }
        
        // Garantir que modules seja sempre um array
        $userModules = [];
        if ($user->modules) {
            if (is_array($user->modules)) {
                $userModules = $user->modules;
            } elseif (is_string($user->modules)) {
                $decoded = json_decode($user->modules, true);
                $userModules = is_array($decoded) ? $decoded : [];
            }
        }
        
        return in_array($module, $userModules);
    }
}

/**
 * 🔐 Verifica se o tenant atual tem acesso a uma funcionalidade do plano
 */
if (! function_exists('has_feature')) {
    function has_feature(string $featureName): bool
    {
        return app(\App\Services\FeatureAccessService::class)->hasFeature($featureName);
    }
}

/**
 * 🔐 Verifica se o tenant tem acesso a qualquer uma das funcionalidades
 */
if (! function_exists('has_any_feature')) {
    function has_any_feature(array $featureNames): bool
    {
        return app(\App\Services\FeatureAccessService::class)->hasAnyFeature($featureNames);
    }
}

/**
 * 🔐 Verifica se o tenant tem acesso a todas as funcionalidades
 */
if (! function_exists('has_all_features')) {
    function has_all_features(array $featureNames): bool
    {
        return app(\App\Services\FeatureAccessService::class)->hasAllFeatures($featureNames);
    }
}

/**
 * 🔐 Retorna todas as funcionalidades disponíveis para o tenant atual
 */
if (! function_exists('get_available_features')) {
    function get_available_features(): array
    {
        return app(\App\Services\FeatureAccessService::class)->getAvailableFeatures();
    }
}

/**
 * 🔐 Retorna o limite do plano para um tipo específico (ex: max_doctors, max_users)
 */
if (! function_exists('get_plan_limit')) {
    function get_plan_limit(string $limitType): ?int
    {
        return app(\App\Services\FeatureAccessService::class)->getPlanLimit($limitType);
    }
}

/**
 * ✂️ Trunca UUID para exibição em listagens (mostra apenas os primeiros 8 caracteres + "...")
 * Usado para economizar espaço em tabelas. Mantenha o ID completo nas views show.
 */
if (! function_exists('truncate_uuid')) {
    function truncate_uuid(?string $uuid, int $length = 8): string
    {
        if (empty($uuid)) {
            return 'N/A';
        }
        
        // Se não for um UUID (menor que 8 caracteres), retorna completo
        if (strlen($uuid) <= $length) {
            return $uuid;
        }
        
        // Retorna os primeiros caracteres + "..."
        return substr($uuid, 0, $length) . '...';
    }
}