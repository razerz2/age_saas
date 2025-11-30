<?php

namespace App\Http\Controllers\Tenant\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\GoogleCalendarToken;
use App\Services\Tenant\GoogleCalendarService;
use Google_Client;
use Google_Service_Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleCalendarController extends Controller
{
    protected GoogleCalendarService $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Redireciona para o Google OAuth
     */
    public function connect($doctorId, Request $request)
    {
        try {
            // Busca o doctor manualmente após garantir que o tenant está ativo
            $doctor = Doctor::findOrFail($doctorId);

            // Verifica se as credenciais do Google estão configuradas
            $clientId = config('services.google.client_id');
            $clientSecret = config('services.google.client_secret');
            
            if (empty($clientId) || empty($clientSecret)) {
                Log::error('Credenciais do Google não configuradas', [
                    'doctor_id' => $doctorId,
                ]);
                
                return redirect()->route('tenant.integrations.google.index')
                    ->with('error', 'Credenciais do Google não configuradas. Entre em contato com o administrador.');
            }

            // Obtém o tenant atual
            $tenant = \App\Models\Platform\Tenant::current();
            if (!$tenant) {
                throw new \Exception('Não foi possível determinar o tenant para o callback do Google OAuth');
            }

            // Redirect global único (não usa tenant na URL)
            $redirectUri = route('google.callback');
            
            // 🔍 DIAGNÓSTICO: Log para verificar qual redirect está sendo gerado
            // Compare este valor com o cadastrado no Google Cloud Console
            Log::info('🔍 DIAGNÓSTICO REDIRECT URI - Google Calendar OAuth', [
                'redirect_uri_gerado' => $redirectUri,
                'app_url_config' => config('app.url'),
                'app_url_env' => env('APP_URL'),
                'url_esperada_ngrok' => 'https://5946f73d7978.ngrok-free.app/google/callback',
                'sao_iguais' => $redirectUri === 'https://5946f73d7978.ngrok-free.app/google/callback',
                'diferenca' => $redirectUri !== 'https://5946f73d7978.ngrok-free.app/google/callback' 
                    ? '⚠️ URLs DIFERENTES! Verifique APP_URL no .env' 
                    : '✅ URLs iguais',
            ]);
            
            // 🔍 TEMPORÁRIO: Descomente a linha abaixo para ver o redirect no navegador
            // REMOVER/COMENTAR APÓS CORRIGIR O APP_URL
            // dd(['redirect_uri' => $redirectUri, 'app_url' => config('app.url')]);

            // State: JSON com tenant + doctor para recuperar no callback
            $state = json_encode([
                'tenant' => $tenant->subdomain,
                'doctor' => $doctor->id,
            ]);

            $client = new Google_Client();
            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);
            $client->setRedirectUri($redirectUri);
            $client->setAccessType('offline');
            $client->setPrompt('consent');
            $client->addScope([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ]);

            // Passa o state com tenant e doctor
            $client->setState($state);

            $authUrl = $client->createAuthUrl();

            Log::info('Redirecionando para Google OAuth', [
                'doctor_id' => $doctor->id,
                'tenant_slug' => $tenant->subdomain,
                'redirect_uri' => $redirectUri,
            ]);

            return redirect()->away($authUrl);
        } catch (\Exception $e) {
            Log::error('Erro ao iniciar conexão com Google Calendar', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('tenant.integrations.google.index')
                ->with('error', 'Erro ao conectar com Google Calendar. Tente novamente.');
        }
    }

    /**
     * Callback do Google OAuth (rota global)
     */
    public function callback(Request $request)
    {
        try {
            $code = $request->get('code');
            $error = $request->get('error');
            $stateRaw = $request->get('state');

            if ($error) {
                Log::error('Erro no callback do Google OAuth', [
                    'error' => $error,
                    'error_description' => $request->get('error_description'),
                ]);
                
                // Tenta redirecionar para o tenant correto se o state estiver presente
                $tenantSlug = null;
                if ($stateRaw) {
                    $state = json_decode($stateRaw, true);
                    $tenantSlug = $state['tenant'] ?? null;
                }
                
                if ($tenantSlug) {
                    // Inicializa o tenant antes de redirecionar
                    $tenant = \App\Models\Platform\Tenant::where('subdomain', $tenantSlug)->first();
                    if ($tenant) {
                        $tenant->makeCurrent();
                    }
                    return redirect()->route('tenant.integrations.google.index')
                        ->with('error', 'Erro ao autorizar acesso ao Google Calendar: ' . $error);
                }
                
                // Fallback: redireciona para home
                return redirect()->route('login')
                    ->with('error', 'Erro ao autorizar acesso ao Google Calendar: ' . $error);
            }

            if (!$code) {
                Log::error('Código de autorização não recebido no callback do Google OAuth');
                return redirect()->route('login')
                    ->with('error', 'Código de autorização não recebido.');
            }

            // Recupera o state (JSON com tenant e doctor)
            if (!$stateRaw) {
                Log::error('State OAuth não recebido no callback do Google OAuth');
                return redirect()->route('login')
                    ->with('error', 'Estado OAuth não recebido. Tente conectar novamente.');
            }

            $state = json_decode($stateRaw, true);
            if (!$state || !isset($state['tenant']) || !isset($state['doctor'])) {
                Log::error('State OAuth inválido no callback do Google OAuth', [
                    'state_raw' => $stateRaw,
                ]);
                return redirect()->route('login')
                    ->with('error', 'Estado OAuth inválido. Tente conectar novamente.');
            }

            $tenantSlug = $state['tenant'];
            $doctorId = $state['doctor'];

            // Inicializa o tenant correto
            $tenant = \App\Models\Platform\Tenant::where('subdomain', $tenantSlug)->firstOrFail();
            $tenant->makeCurrent();

            Log::info('Tenant inicializado no callback do Google OAuth', [
                'tenant_slug' => $tenantSlug,
                'tenant_id' => $tenant->id,
            ]);

            // Busca o doctor no banco do tenant
            $doctor = Doctor::findOrFail($doctorId);

            // Redirect global (mesma usada no connect)
            $redirectUri = route('google.callback');

            // Troca o código por tokens
            $client = new Google_Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri($redirectUri);

            $accessToken = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($accessToken['error'])) {
                Log::error('Erro ao obter token do Google', [
                    'error' => $accessToken['error_description'] ?? $accessToken['error'],
                    'tenant_slug' => $tenantSlug,
                    'doctor_id' => $doctorId,
                ]);

                return redirect()->route('tenant.integrations.google.index')
                    ->with('error', 'Erro ao obter token de acesso do Google.');
            }

            // Calcula a data de expiração
            $expiresAt = null;
            if (isset($accessToken['expires_in'])) {
                $expiresAt = Carbon::now()->addSeconds($accessToken['expires_in']);
            }

            // Salva ou atualiza o token vinculado ao médico no banco do tenant correto
            $token = GoogleCalendarToken::updateOrCreate(
                ['doctor_id' => $doctor->id],
                [
                    'id' => Str::uuid(),
                    'access_token' => $accessToken,
                    'refresh_token' => $accessToken['refresh_token'] ?? null,
                    'expires_at' => $expiresAt,
                ]
            );

            Log::info('Token do Google Calendar salvo', [
                'tenant_slug' => $tenantSlug,
                'doctor_id' => $doctor->id,
                'token_id' => $token->id,
            ]);

            // Redireciona para a página de integrações do tenant
            return redirect()->route('tenant.integrations.google.index')
                ->with('success', 'Integração com Google Calendar realizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro no callback do Google Calendar', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'state' => $request->get('state'),
            ]);

            // Tenta redirecionar para o tenant correto se possível
            $tenantSlug = null;
            $stateRaw = $request->get('state');
            if ($stateRaw) {
                $state = json_decode($stateRaw, true);
                $tenantSlug = $state['tenant'] ?? null;
            }
            
            if ($tenantSlug) {
                // Inicializa o tenant antes de redirecionar
                $tenant = \App\Models\Platform\Tenant::where('subdomain', $tenantSlug)->first();
                if ($tenant) {
                    $tenant->makeCurrent();
                }
                return redirect()->route('tenant.integrations.google.index')
                    ->with('error', 'Erro ao processar autorização. Tente novamente.');
            }

            return redirect()->route('login')
                ->with('error', 'Erro ao processar autorização. Tente novamente.');
        }
    }

    /**
     * Remove a integração do Google Calendar
     */
    public function disconnect($doctorId)
    {
        try {
            // Busca o doctor manualmente após garantir que o tenant está ativo
            $doctor = Doctor::findOrFail($doctorId);

            $token = $doctor->googleCalendarToken;

            if ($token) {
                $token->delete();

                Log::info('Integração Google Calendar removida', [
                    'doctor_id' => $doctor->id,
                ]);

                return redirect()->route('tenant.integrations.google.index')
                    ->with('success', 'Integração com Google Calendar removida com sucesso.');
            }

            return redirect()->route('tenant.integrations.google.index')
                ->with('info', 'Nenhuma integração encontrada para este médico.');
        } catch (\Exception $e) {
            Log::error('Erro ao desconectar Google Calendar', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.integrations.google.index')
                ->with('error', 'Erro ao remover integração. Tente novamente.');
        }
    }

    /**
     * Verifica o status da integração
     */
    public function status($doctorId)
    {
        // Busca o doctor manualmente após garantir que o tenant está ativo
        $doctor = Doctor::findOrFail($doctorId);

        $token = $doctor->googleCalendarToken;

        return response()->json([
            'connected' => $token !== null,
            'expired' => $token ? $token->isExpired() : false,
            'expires_at' => $token && $token->expires_at ? $token->expires_at->toIso8601String() : null,
        ]);
    }

    /**
     * Lista eventos do Google Calendar para um médico (API para FullCalendar)
     */
    public function getEvents($doctorId, Request $request)
    {
        try {
            // Busca o doctor manualmente após garantir que o tenant está ativo
            $doctor = Doctor::findOrFail($doctorId);

            $startDate = $request->get('start');
            $endDate = $request->get('end');

            $events = $this->googleCalendarService->listEvents($doctor->id, $startDate, $endDate);

            // Formata para FullCalendar
            $formattedEvents = array_map(function ($event) {
                return [
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'start' => $event['start'],
                    'end' => $event['end'],
                    'description' => $event['description'] ?? null,
                ];
            }, $events);

            return response()->json($formattedEvents);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar eventos do Google Calendar', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([], 500);
        }
    }

    /**
     * Página principal de integrações Google Calendar
     */
    public function index()
    {
        $doctors = Doctor::with(['user', 'googleCalendarToken'])
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->orderBy('id')
            ->get();

        return view('tenant.integrations.google.index', compact('doctors'));
    }
}

