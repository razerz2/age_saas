<?php

namespace App\Http\Controllers\Tenant\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AppleCalendarToken;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Services\Tenant\AppleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AppleCalendarController extends Controller
{
    protected AppleCalendarService $appleCalendarService;

    public function __construct(AppleCalendarService $appleCalendarService)
    {
        $this->appleCalendarService = $appleCalendarService;
    }

    public function index(string $slug)
    {
        $user = Auth::guard('tenant')->user();

        if ($user->role === 'doctor' && !$user->relationLoaded('doctor')) {
            $user->load('doctor');
        }

        $hasAppleCalendarTable = $this->hasAppleCalendarTable();

        $relations = ['user'];
        if ($hasAppleCalendarTable) {
            $relations[] = 'appleCalendarToken';
        }

        $doctorsQuery = Doctor::with($relations)
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

        if (!$hasAppleCalendarTable) {
            foreach ($doctors as $doctor) {
                $doctor->setRelation('appleCalendarToken', null);
            }
        }

        return view('tenant.integrations.apple.index', compact('doctors', 'user', 'hasAppleCalendarTable'));
    }

    public function showConnectForm(string $slug, string $doctor, Request $request)
    {
        $doctorId = (string) $doctor;
        $doctor = Doctor::findOrFail($doctorId);
        $this->ensureDoctorCanInitiateAuth($doctor);
        $returnCalendarId = $this->resolveReturnCalendarId($doctor, (string) $request->query('calendar_id', ''));

        if (!$this->hasAppleCalendarTable()) {
            return $this->redirectAfterAction($slug, $doctor->id, $returnCalendarId)
                ->with('error', 'A estrutura do Apple Calendar nao esta disponivel neste tenant.');
        }

        return view('tenant.integrations.apple.connect', compact('doctor', 'returnCalendarId'));
    }

    public function connect(string $slug, string $doctor, Request $request)
    {
        $doctorId = (string) $doctor;
        $requestedCalendarId = (string) $request->input('calendar_id', '');

        try {
            if (!$this->hasAppleCalendarTable()) {
                return $this->redirectAfterAction($slug, $doctorId, $requestedCalendarId)
                    ->with('error', 'A estrutura do Apple Calendar nao esta disponivel neste tenant.');
            }

            $doctor = Doctor::findOrFail($doctorId);
            $this->ensureDoctorCanInitiateAuth($doctor);
            $returnCalendarId = $this->resolveReturnCalendarId($doctor, $requestedCalendarId);

            $request->validate([
                'username' => ['required', 'email'],
                'password' => ['required', 'string'],
                'server_url' => ['nullable', 'url'],
                'calendar_url' => ['nullable', 'string'],
            ]);

            $token = AppleCalendarToken::updateOrCreate(
                ['doctor_id' => $doctor->id],
                [
                    'id' => (string) Str::uuid(),
                    'username' => (string) $request->username,
                    'password' => encrypt((string) $request->password),
                    'server_url' => (string) ($request->server_url ?: 'https://caldav.icloud.com'),
                    'calendar_url' => $request->filled('calendar_url') ? (string) $request->calendar_url : null,
                ]
            );

            if (!$request->filled('calendar_url')) {
                try {
                    $calendars = $this->appleCalendarService->discoverCalendars($token);
                    if (!empty($calendars) && isset($calendars[0]['path'])) {
                        $token->update(['calendar_url' => (string) $calendars[0]['path']]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Nao foi possivel descobrir calendarios automaticamente', [
                        'doctor_id' => $doctor->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Integracao Apple Calendar conectada', [
                'doctor_id' => $doctor->id,
                'token_id' => $token->id,
                'tenant_slug' => $slug,
            ]);

            return $this->redirectAfterAction($slug, $doctor->id, $returnCalendarId)
                ->with('success', 'Sincronização com Apple Calendar conectada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Erro ao conectar com Apple Calendar', [
                'doctor_id' => $doctorId,
                'tenant_slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return $this->redirectAfterAction($slug, $doctorId, $requestedCalendarId)
                ->with('error', 'Erro ao conectar com Apple Calendar. Verifique as credenciais.');
        }
    }

    public function disconnect(string $slug, string $doctor, Request $request)
    {
        $doctorId = (string) $doctor;
        $requestedCalendarId = (string) $request->input('calendar_id', '');
        $returnContext = (string) $request->input('return_context', '');

        try {
            if (!$this->hasAppleCalendarTable()) {
                return $this->redirectAfterAction($slug, $doctorId, $requestedCalendarId, $returnContext)
                    ->with('error', 'A estrutura do Apple Calendar nao esta disponivel neste tenant.');
            }

            $doctor = Doctor::findOrFail($doctorId);
            $this->ensureDoctorCanManage($doctor);
            $returnCalendarId = $this->resolveReturnCalendarId($doctor, $requestedCalendarId);

            $token = $doctor->appleCalendarToken;

            if ($token) {
                $token->delete();

                Log::info('Integracao Apple Calendar removida', [
                    'doctor_id' => $doctor->id,
                    'tenant_slug' => $slug,
                ]);

                return $this->redirectAfterAction($slug, $doctor->id, $returnCalendarId, $returnContext)
                    ->with('success', 'Sincronização com Apple Calendar desconectada com sucesso.');
            }

            return $this->redirectAfterAction($slug, $doctor->id, $returnCalendarId, $returnContext)
                ->with('info', 'Nenhuma sincronização encontrada para este profissional.');
        } catch (\Throwable $e) {
            Log::error('Erro ao desconectar Apple Calendar', [
                'doctor_id' => $doctorId,
                'tenant_slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return $this->redirectAfterAction($slug, $doctorId, $requestedCalendarId, $returnContext)
                ->with('error', 'Erro ao remover sincronização. Tente novamente.');
        }
    }

    public function status(string $slug, string $doctor)
    {
        $doctorId = (string) $doctor;

        if (!$this->hasAppleCalendarTable()) {
            return response()->json([
                'connected' => false,
                'available' => false,
            ]);
        }

        $doctor = Doctor::findOrFail($doctorId);
        $this->ensureDoctorCanView($doctor);

        return response()->json([
            'connected' => $doctor->appleCalendarToken !== null,
            'available' => true,
        ]);
    }

    public function getEvents(string $slug, string $doctor, Request $request)
    {
        $doctorId = (string) $doctor;

        try {
            if (!$this->hasAppleCalendarTable()) {
                return response()->json([], 200);
            }

            $doctor = Doctor::findOrFail($doctorId);
            $this->ensureDoctorCanView($doctor);

            $startDate = $request->get('start');
            $endDate = $request->get('end');

            $events = $this->appleCalendarService->listEvents($doctor->id, $startDate, $endDate);

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
            Log::error('Erro ao buscar eventos do Apple Calendar', [
                'doctor_id' => $doctorId,
                'tenant_slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return response()->json([], 500);
        }
    }

    private function hasAppleCalendarTable(): bool
    {
        return Schema::connection('tenant')->hasTable('apple_calendar_tokens');
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

    private function redirectAfterAction(
        string $slug,
        ?string $doctorId = null,
        ?string $calendarId = null,
        ?string $returnContext = null
    ): RedirectResponse {
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
