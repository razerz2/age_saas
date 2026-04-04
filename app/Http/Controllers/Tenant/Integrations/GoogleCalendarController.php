<?php

namespace App\Http\Controllers\Tenant\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\GoogleCalendarToken;
use App\Services\Tenant\GoogleCalendarService;
use Carbon\Carbon;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
        $requestedCalendarId = (string) $request->query('calendar_id', '');

        try {
            $doctor = Doctor::findOrFail($doctorId);
            $this->ensureDoctorCanInitiateAuth($doctor);
            $returnCalendarId = $this->resolveReturnCalendarId($doctor, $requestedCalendarId);

            if (!$this->hasGoogleOAuthCredentials()) {
                return $this->redirectToCalendarSync($slug, $doctor->id, $returnCalendarId)
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
                'return_calendar_id' => $returnCalendarId,
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

            return $this->redirectToCalendarSync($slug, $doctorId, $requestedCalendarId)
                ->with('error', 'Erro ao conectar com Google Calendar. Tente novamente.');
        }
    }

    public function callback(Request $request)
    {
        $stateRaw = (string) $request->get('state', '');
        $state = $this->decodeState($stateRaw);
        $tenantSlug = $state['slug'] ?? null;
        $doctorId = (string) ($state['doctor'] ?? '');
        $returnCalendarId = null;

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

            $stateNonce = $state['nonce'] ?? null;
            $stateData = $this->consumeValidStateNonce($stateNonce, $tenantSlug, $doctorId);
            if ($stateData) {
                $returnCalendarId = (string) ($stateData['return_calendar_id'] ?? '');
            }

            $error = $request->get('error');
            if ($error) {
                return $this->redirectToCalendarSync($tenantSlug, $doctorId, $returnCalendarId)
                    ->with('error', 'Erro ao autorizar acesso ao Google Calendar: ' . $error);
            }

            if (!$stateData) {
                return $this->redirectToCalendarSync($tenantSlug, $doctorId, $returnCalendarId)
                    ->with('error', 'Fluxo de autorizacao invalido ou expirado. Inicie novamente a conexao.');
            }

            $code = $request->get('code');

            if (!$code || !$doctorId) {
                return $this->redirectToCalendarSync($tenantSlug, $doctorId, $returnCalendarId)
                    ->with('error', 'Dados de autorizacao incompletos. Conecte novamente.');
            }

            $tenant = PlatformTenant::where('subdomain', $tenantSlug)->firstOrFail();
            $tenant->makeCurrent();

            $doctor = Doctor::findOrFail($doctorId);
            $returnCalendarId = $this->resolveReturnCalendarId($doctor, $returnCalendarId);

            if (!$this->hasGoogleOAuthCredentials()) {
                return $this->redirectToCalendarSync($tenantSlug, $doctorId, $returnCalendarId)
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

                return $this->redirectToCalendarSync($tenantSlug, $doctorId, $returnCalendarId)
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

            return $this->redirectToCalendarSync($tenantSlug, $doctorId, $returnCalendarId)
                ->with('success', 'Sincronização com Google Calendar conectada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Erro no callback do Google Calendar', [
                'tenant_slug' => $tenantSlug,
                'state' => $state,
                'error' => $e->getMessage(),
            ]);

            if ($tenantSlug) {
                return $this->redirectToCalendarSync($tenantSlug, $doctorId, $returnCalendarId)
                    ->with('error', 'Erro ao processar autorizacao. Tente novamente.');
            }

            return redirect()->route('login')
                ->with('error', 'Erro ao processar autorizacao. Tente novamente.');
        }
    }

    public function disconnect(string $slug, string $doctor, Request $request)
    {
        $doctorId = (string) $doctor;
        $requestedCalendarId = (string) $request->input('calendar_id', '');
        $returnContext = (string) $request->input('return_context', '');

        try {
            $doctor = Doctor::findOrFail($doctorId);
            $this->ensureDoctorCanManage($doctor);
            $returnCalendarId = $this->resolveReturnCalendarId($doctor, $requestedCalendarId);

            $token = $doctor->googleCalendarToken;

            if ($token) {
                $token->delete();

                Log::info('Integracao Google Calendar removida', [
                    'doctor_id' => $doctor->id,
                    'tenant_slug' => $slug,
                ]);

                return $this->redirectToCalendarSync($slug, $doctor->id, $returnCalendarId, $returnContext)
                    ->with('success', 'Sincronização com Google Calendar desconectada com sucesso.');
            }

            return $this->redirectToCalendarSync($slug, $doctor->id, $returnCalendarId, $returnContext)
                ->with('info', 'Nenhuma sincronização encontrada para este profissional.');
        } catch (\Throwable $e) {
            Log::error('Erro ao desconectar Google Calendar', [
                'doctor_id' => $doctorId,
                'tenant_slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return $this->redirectToCalendarSync($slug, $doctorId, $requestedCalendarId, $returnContext)
                ->with('error', 'Erro ao remover sincronização. Tente novamente.');
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

    private function consumeValidStateNonce(?string $nonce, ?string $slug, ?string $doctorId): ?array
    {
        if (!$nonce || !$slug || !$doctorId) {
            return null;
        }

        $cacheKey = $this->stateCacheKey($nonce);
        $stateData = Cache::get($cacheKey);
        Cache::forget($cacheKey);

        if (!is_array($stateData)) {
            return null;
        }

        $isValid = (string) ($stateData['slug'] ?? '') === (string) $slug
            && (string) ($stateData['doctor'] ?? '') === (string) $doctorId;

        return $isValid ? $stateData : null;
    }

    private function resolveReturnCalendarId(Doctor $doctor, ?string $calendarId): ?string
    {
        $calendarId = trim((string) $calendarId);

        if ($calendarId !== '') {
            $calendar = Calendar::query()
                ->where('id', $calendarId)
                ->where('doctor_id', $doctor->id)
                ->first();

            if ($calendar) {
                return (string) $calendar->id;
            }
        }

        $calendar = Calendar::query()
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->first();

        return $calendar ? (string) $calendar->id : null;
    }

    private function redirectToCalendarSync(
        string $slug,
        ?string $doctorId = null,
        ?string $calendarId = null,
        ?string $returnContext = null
    ): RedirectResponse
    {
        $returnContext = trim((string) $returnContext);
        if ($returnContext === 'settings') {
            return redirect()->to(route('tenant.settings.index', ['slug' => $slug]) . '#integracoes');
        }

        $resolvedCalendarId = null;
        $doctorId = trim((string) $doctorId);
        $calendarId = trim((string) $calendarId);

        if ($calendarId !== '') {
            $calendarQuery = Calendar::query()->where('id', $calendarId);
            if ($doctorId !== '') {
                $calendarQuery->where('doctor_id', $doctorId);
            }
            $calendar = $calendarQuery->first();
            if ($calendar) {
                $resolvedCalendarId = (string) $calendar->id;
            }
        }

        if (!$resolvedCalendarId && $doctorId !== '') {
            $calendar = Calendar::query()
                ->where('doctor_id', $doctorId)
                ->orderByDesc('is_active')
                ->orderByDesc('created_at')
                ->first();

            if ($calendar) {
                $resolvedCalendarId = (string) $calendar->id;
            }
        }

        if ($resolvedCalendarId) {
            return redirect()->route('tenant.agenda-settings.calendar-sync', [
                'slug' => $slug,
                'id' => $resolvedCalendarId,
            ]);
        }

        return redirect()->route('tenant.agenda-settings.index', ['slug' => $slug]);
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
