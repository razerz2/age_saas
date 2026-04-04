<?php

namespace App\Http\Controllers\Tenant\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\GoogleCalendarToken;
use App\Services\Tenant\GoogleCalendarService;
use Carbon\Carbon;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleCalendarController extends Controller
{
    protected GoogleCalendarService $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    public function index(string $slug)
    {
        $user = Auth::guard('tenant')->user();
        $doctorsQuery = Doctor::with(['user', 'googleCalendarToken'])
            ->whereHas('user', function ($query) {
                $query->where('status', 'active');
            });

        if ($user->role === 'doctor' && $user->doctor) {
            $doctorsQuery->where('id', $user->doctor->id);
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                $doctorsQuery->whereIn('id', $allowedDoctorIds);
            } else {
                $doctorsQuery->whereRaw('1 = 0');
            }
        }

        $doctors = $doctorsQuery->orderBy('id')->get();
        $hasGoogleCredentials = $this->hasGoogleOAuthCredentials();

        return view('tenant.integrations.google.index', compact('doctors', 'user', 'hasGoogleCredentials'));
    }

    public function connect(string $slug, string $doctor, Request $request)
    {
        $doctorId = (string) $doctor;

        try {
            $doctor = Doctor::findOrFail($doctorId);
            $this->ensureDoctorCanInitiateAuth($doctor);

            if (!$this->hasGoogleOAuthCredentials()) {
                return redirect()->route('tenant.integrations.google.index', ['slug' => $slug])
                    ->with('error', 'Credenciais globais do Google nao configuradas na Platform (com fallback para ambiente).');
            }

            $tenant = PlatformTenant::current();
            if (!$tenant) {
                $tenant = PlatformTenant::where('subdomain', $slug)->first();
            }
            if (!$tenant) {
                throw new \RuntimeException('Tenant nao encontrado para iniciar OAuth do Google Calendar.');
            }

            $googleOAuthConfig = google_oauth_config();
            $redirectUri = (string) ($googleOAuthConfig['redirect_uri'] ?? route('google.callback'));
            $stateNonce = (string) Str::uuid();
            Cache::put($this->stateCacheKey($stateNonce), [
                'slug' => $tenant->subdomain,
                'doctor' => (string) $doctor->id,
                'initiator_user_id' => (string) (Auth::guard('tenant')->id() ?? ''),
            ], now()->addMinutes(15));

            $state = json_encode([
                'slug' => $tenant->subdomain,
                'doctor' => $doctor->id,
                'nonce' => $stateNonce,
            ]);

            $client = new Google_Client();
            $client->setClientId((string) ($googleOAuthConfig['client_id'] ?? ''));
            $client->setClientSecret((string) ($googleOAuthConfig['client_secret'] ?? ''));
            $client->setRedirectUri($redirectUri);
            $client->setAccessType('offline');
            $client->setPrompt('consent');
            $client->addScope([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events',
            ]);
            $client->setState((string) $state);

            $authUrl = $client->createAuthUrl();

            Log::info('Redirecionando para Google OAuth', [
                'doctor_id' => $doctor->id,
                'tenant_slug' => $tenant->subdomain,
                'redirect_uri' => $redirectUri,
            ]);

            return redirect()->away($authUrl);
        } catch (\Throwable $e) {
            Log::error('Erro ao iniciar conexao com Google Calendar', [
                'doctor_id' => $doctorId,
                'tenant_slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.integrations.google.index', ['slug' => $slug])
                ->with('error', 'Erro ao conectar com Google Calendar. Tente novamente.');
        }
    }

    public function callback(Request $request)
    {
        $stateRaw = (string) $request->get('state', '');
        $state = $this->decodeState($stateRaw);
        $tenantSlug = $state['slug'] ?? null;

        try {
            if ($tenantSlug) {
                $tenant = PlatformTenant::where('subdomain', $tenantSlug)->first();
                if ($tenant) {
                    $tenant->makeCurrent();
                }
            }

            if (!$tenantSlug) {
                return redirect()->route('login')
                    ->with('error', 'Estado OAuth invalido. Conecte novamente.');
            }

            $error = $request->get('error');
            if ($error) {
                return redirect()->route('tenant.integrations.google.index', ['slug' => $tenantSlug])
                    ->with('error', 'Erro ao autorizar acesso ao Google Calendar: ' . $error);
            }

            $code = $request->get('code');
            $doctorId = $state['doctor'] ?? null;
            $stateNonce = $state['nonce'] ?? null;

            if (!$this->isValidStateNonce($stateNonce, $tenantSlug, $doctorId)) {
                return redirect()->route('tenant.integrations.google.index', ['slug' => $tenantSlug])
                    ->with('error', 'Fluxo de autorizacao invalido ou expirado. Inicie novamente a conexao.');
            }

            if (!$code || !$doctorId) {
                return redirect()->route('tenant.integrations.google.index', ['slug' => $tenantSlug])
                    ->with('error', 'Dados de autorizacao incompletos. Conecte novamente.');
            }

            $tenant = PlatformTenant::where('subdomain', $tenantSlug)->firstOrFail();
            $tenant->makeCurrent();

            $doctor = Doctor::findOrFail($doctorId);

            if (!$this->hasGoogleOAuthCredentials()) {
                return redirect()->route('tenant.integrations.google.index', ['slug' => $tenantSlug])
                    ->with('error', 'Credenciais globais do Google nao configuradas na Platform (com fallback para ambiente).');
            }

            $googleOAuthConfig = google_oauth_config();
            $client = new Google_Client();
            $client->setClientId((string) ($googleOAuthConfig['client_id'] ?? ''));
            $client->setClientSecret((string) ($googleOAuthConfig['client_secret'] ?? ''));
            $client->setRedirectUri((string) ($googleOAuthConfig['redirect_uri'] ?? route('google.callback')));

            $accessToken = $client->fetchAccessTokenWithAuthCode((string) $code);

            if (isset($accessToken['error'])) {
                $reason = $accessToken['error_description'] ?? $accessToken['error'];
                Log::error('Erro ao obter token do Google', [
                    'tenant_slug' => $tenantSlug,
                    'doctor_id' => $doctorId,
                    'error' => $reason,
                ]);

                return redirect()->route('tenant.integrations.google.index', ['slug' => $tenantSlug])
                    ->with('error', 'Erro ao obter token de acesso do Google.');
            }

            $expiresAt = null;
            if (isset($accessToken['expires_in'])) {
                $expiresAt = Carbon::now()->addSeconds((int) $accessToken['expires_in']);
            }

            GoogleCalendarToken::updateOrCreate(
                ['doctor_id' => $doctor->id],
                [
                    'id' => (string) Str::uuid(),
                    'access_token' => $accessToken,
                    'refresh_token' => $accessToken['refresh_token'] ?? null,
                    'expires_at' => $expiresAt,
                ]
            );

            return redirect()->route('tenant.integrations.google.index', ['slug' => $tenantSlug])
                ->with('success', 'Integracao com Google Calendar realizada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Erro no callback do Google Calendar', [
                'tenant_slug' => $tenantSlug,
                'state' => $state,
                'error' => $e->getMessage(),
            ]);

            if ($tenantSlug) {
                return redirect()->route('tenant.integrations.google.index', ['slug' => $tenantSlug])
                    ->with('error', 'Erro ao processar autorizacao. Tente novamente.');
            }

            return redirect()->route('login')
                ->with('error', 'Erro ao processar autorizacao. Tente novamente.');
        }
    }

    public function disconnect(string $slug, string $doctor)
    {
        $doctorId = (string) $doctor;

        try {
            $doctor = Doctor::findOrFail($doctorId);
            $this->ensureDoctorCanManage($doctor);

            $token = $doctor->googleCalendarToken;

            if ($token) {
                $token->delete();

                Log::info('Integracao Google Calendar removida', [
                    'doctor_id' => $doctor->id,
                    'tenant_slug' => $slug,
                ]);

                return redirect()->route('tenant.integrations.google.index', ['slug' => $slug])
                    ->with('success', 'Integracao com Google Calendar removida com sucesso.');
            }

            return redirect()->route('tenant.integrations.google.index', ['slug' => $slug])
                ->with('info', 'Nenhuma integracao encontrada para este medico.');
        } catch (\Throwable $e) {
            Log::error('Erro ao desconectar Google Calendar', [
                'doctor_id' => $doctorId,
                'tenant_slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.integrations.google.index', ['slug' => $slug])
                ->with('error', 'Erro ao remover integracao. Tente novamente.');
        }
    }

    public function status(string $slug, string $doctor)
    {
        $doctorId = (string) $doctor;
        $doctor = Doctor::findOrFail($doctorId);
        $this->ensureDoctorCanView($doctor);

        $token = $doctor->googleCalendarToken;

        return response()->json([
            'connected' => $token !== null,
            'expired' => $token ? $token->isExpired() : false,
            'expires_at' => $token && $token->expires_at ? $token->expires_at->toIso8601String() : null,
        ]);
    }

    public function getEvents(string $slug, string $doctor, Request $request)
    {
        $doctorId = (string) $doctor;

        try {
            $doctor = Doctor::findOrFail($doctorId);
            $this->ensureDoctorCanView($doctor);

            $startDate = $request->get('start');
            $endDate = $request->get('end');

            $events = $this->googleCalendarService->listEvents($doctor->id, $startDate, $endDate);

            $formattedEvents = array_map(function (array $event): array {
                return [
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'start' => $event['start'],
                    'end' => $event['end'],
                    'description' => $event['description'] ?? null,
                ];
            }, $events);

            return response()->json($formattedEvents);
        } catch (\Throwable $e) {
            Log::error('Erro ao buscar eventos do Google Calendar', [
                'doctor_id' => $doctorId,
                'tenant_slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return response()->json([], 500);
        }
    }

    /**
     * @return array{slug?: string, doctor?: string}|array<string, mixed>
     */
    private function decodeState(string $rawState): array
    {
        if (trim($rawState) === '') {
            return [];
        }

        $decoded = json_decode($rawState, true);
        if (!is_array($decoded)) {
            return [];
        }

        if (!isset($decoded['slug']) && isset($decoded['tenant'])) {
            $decoded['slug'] = $decoded['tenant'];
        }

        return $decoded;
    }

    private function hasGoogleOAuthCredentials(?array $oauthConfig = null): bool
    {
        $oauthConfig = $oauthConfig ?? google_oauth_config();

        return trim((string) ($oauthConfig['client_id'] ?? '')) !== ''
            && trim((string) ($oauthConfig['client_secret'] ?? '')) !== '';
    }

    private function stateCacheKey(string $nonce): string
    {
        return 'google_oauth_state:' . $nonce;
    }

    private function isValidStateNonce(?string $nonce, ?string $slug, ?string $doctorId): bool
    {
        if (!$nonce || !$slug || !$doctorId) {
            return false;
        }

        $cacheKey = $this->stateCacheKey($nonce);
        $stateData = Cache::get($cacheKey);
        Cache::forget($cacheKey);

        if (!is_array($stateData)) {
            return false;
        }

        return (string) ($stateData['slug'] ?? '') === (string) $slug
            && (string) ($stateData['doctor'] ?? '') === (string) $doctorId;
    }

    private function ensureDoctorCanInitiateAuth(Doctor $doctor): void
    {
        $user = Auth::guard('tenant')->user();
        if (!$user) {
            abort(403);
        }

        if ($user->role === 'doctor' && $user->doctor && (string) $user->doctor->id === (string) $doctor->id) {
            return;
        }

        abort(403, 'A autenticacao deve ser iniciada pelo proprio profissional.');
    }

    private function ensureDoctorCanManage(Doctor $doctor): void
    {
        $user = Auth::guard('tenant')->user();
        if (!$user) {
            abort(403);
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'doctor' && $user->doctor && (string) $user->doctor->id === (string) $doctor->id) {
            return;
        }

        abort(403, 'Voce so pode gerenciar seu proprio calendario.');
    }

    private function ensureDoctorCanView(Doctor $doctor): void
    {
        $user = Auth::guard('tenant')->user();
        if (!$user) {
            abort(403);
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'doctor' && $user->doctor && (string) $user->doctor->id === (string) $doctor->id) {
            return;
        }

        if ($user->role === 'user') {
            $allowed = $user->allowedDoctors()->where('doctors.id', $doctor->id)->exists();
            if ($allowed) {
                return;
            }
        }

        abort(403, 'Voce nao possui permissao para visualizar este calendario.');
    }
}
