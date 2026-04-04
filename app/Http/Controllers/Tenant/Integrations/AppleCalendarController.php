<?php

namespace App\Http\Controllers\Tenant\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AppleCalendarToken;
use App\Models\Tenant\Doctor;
use App\Services\Tenant\AppleCalendarService;
use Illuminate\Http\Request;
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

    public function showConnectForm(string $slug, string $doctor)
    {
        $doctorId = (string) $doctor;
        $doctor = Doctor::findOrFail($doctorId);
        $this->ensureDoctorCanInitiateAuth($doctor);

        if (!$this->hasAppleCalendarTable()) {
            return redirect()->route('tenant.integrations.apple.index', ['slug' => $slug])
                ->with('error', 'A estrutura do Apple Calendar nao esta disponivel neste tenant.');
        }

        return view('tenant.integrations.apple.connect', compact('doctor'));
    }

    public function connect(string $slug, string $doctor, Request $request)
    {
        $doctorId = (string) $doctor;

        try {
            if (!$this->hasAppleCalendarTable()) {
                return redirect()->route('tenant.integrations.apple.index', ['slug' => $slug])
                    ->with('error', 'A estrutura do Apple Calendar nao esta disponivel neste tenant.');
            }

            $doctor = Doctor::findOrFail($doctorId);
            $this->ensureDoctorCanInitiateAuth($doctor);

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

            return redirect()->route('tenant.integrations.apple.index', ['slug' => $slug])
                ->with('success', 'Integracao com Apple Calendar realizada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Erro ao conectar com Apple Calendar', [
                'doctor_id' => $doctorId,
                'tenant_slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.integrations.apple.index', ['slug' => $slug])
                ->with('error', 'Erro ao conectar com Apple Calendar. Verifique as credenciais.');
        }
    }

    public function disconnect(string $slug, string $doctor)
    {
        $doctorId = (string) $doctor;

        try {
            if (!$this->hasAppleCalendarTable()) {
                return redirect()->route('tenant.integrations.apple.index', ['slug' => $slug])
                    ->with('error', 'A estrutura do Apple Calendar nao esta disponivel neste tenant.');
            }

            $doctor = Doctor::findOrFail($doctorId);
            $this->ensureDoctorCanManage($doctor);

            $token = $doctor->appleCalendarToken;

            if ($token) {
                $token->delete();

                Log::info('Integracao Apple Calendar removida', [
                    'doctor_id' => $doctor->id,
                    'tenant_slug' => $slug,
                ]);

                return redirect()->route('tenant.integrations.apple.index', ['slug' => $slug])
                    ->with('success', 'Integracao com Apple Calendar removida com sucesso.');
            }

            return redirect()->route('tenant.integrations.apple.index', ['slug' => $slug])
                ->with('info', 'Nenhuma integracao encontrada para este medico.');
        } catch (\Throwable $e) {
            Log::error('Erro ao desconectar Apple Calendar', [
                'doctor_id' => $doctorId,
                'tenant_slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.integrations.apple.index', ['slug' => $slug])
                ->with('error', 'Erro ao remover integracao. Tente novamente.');
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
