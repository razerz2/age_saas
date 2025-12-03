<?php

namespace App\Http\Controllers\Tenant\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\AppleCalendarToken;
use App\Services\Tenant\AppleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AppleCalendarController extends Controller
{
    protected AppleCalendarService $appleCalendarService;

    public function __construct(AppleCalendarService $appleCalendarService)
    {
        $this->appleCalendarService = $appleCalendarService;
    }

    /**
     * Página principal de integrações Apple Calendar
     */
    public function index()
    {
        $user = Auth::guard('tenant')->user();
        
        // Verificar se a tabela apple_calendar_tokens existe
        $hasAppleCalendarTable = Schema::connection('tenant')
            ->hasTable('apple_calendar_tokens');
        
        $doctorsQuery = Doctor::with(['user' . ($hasAppleCalendarTable ? ', appleCalendarToken' : '')])
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            });

        // Aplicar filtros baseado no role
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
        
        // Se a tabela não existe, carregar relacionamento manualmente (vazio)
        if (!$hasAppleCalendarTable) {
            foreach ($doctors as $doctor) {
                $doctor->setRelation('appleCalendarToken', null);
            }
        }

        return view('tenant.integrations.apple.index', compact('doctors', 'user', 'hasAppleCalendarTable'));
    }

    /**
     * Mostra formulário para conectar com Apple Calendar
     */
    public function showConnectForm($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        
        // Verificar permissões
        $user = Auth::guard('tenant')->user();
        if ($user->role === 'doctor' && $user->doctor && $user->doctor->id !== $doctor->id) {
            abort(403, 'Você só pode conectar seu próprio calendário.');
        }

        return view('tenant.integrations.apple.connect', compact('doctor'));
    }

    /**
     * Conecta com Apple Calendar usando credenciais CalDAV
     */
    public function connect($doctorId, Request $request)
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);

            // Verificar permissões
            $user = Auth::guard('tenant')->user();
            if ($user->role === 'doctor' && $user->doctor && $user->doctor->id !== $doctor->id) {
                abort(403, 'Você só pode conectar seu próprio calendário.');
            }

            $request->validate([
                'username' => ['required', 'email'],
                'password' => ['required', 'string'],
                'server_url' => ['nullable', 'url'],
                'calendar_url' => ['nullable', 'string'],
            ]);

            // Criar ou atualizar token
            // IMPORTANTE: Para CalDAV, precisamos da senha em texto plano para autenticação
            // Em produção, considere usar senhas de app específicas do iCloud
            $token = AppleCalendarToken::updateOrCreate(
                ['doctor_id' => $doctor->id],
                [
                    'id' => Str::uuid(),
                    'username' => $request->username,
                    'password' => encrypt($request->password), // Criptografar senha usando encrypt do Laravel
                    'server_url' => $request->server_url ?: 'https://caldav.icloud.com',
                    'calendar_url' => $request->calendar_url,
                ]
            );

            // Tentar descobrir calendários se calendar_url não foi fornecido
            if (!$request->calendar_url) {
                try {
                    $calendars = $this->appleCalendarService->discoverCalendars($token);
                    if (!empty($calendars)) {
                        // Usar o primeiro calendário encontrado
                        $token->update(['calendar_url' => $calendars[0]['path']]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Não foi possível descobrir calendários automaticamente', [
                        'doctor_id' => $doctor->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Integração Apple Calendar conectada', [
                'doctor_id' => $doctor->id,
                'token_id' => $token->id,
            ]);

            return redirect()->route('tenant.integrations.apple.index')
                ->with('success', 'Integração com Apple Calendar realizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao conectar com Apple Calendar', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.integrations.apple.index')
                ->with('error', 'Erro ao conectar com Apple Calendar. Verifique suas credenciais.');
        }
    }

    /**
     * Remove a integração do Apple Calendar
     */
    public function disconnect($doctorId)
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);

            // Verificar permissões
            $user = Auth::guard('tenant')->user();
            if ($user->role === 'doctor' && $user->doctor && $user->doctor->id !== $doctor->id) {
                abort(403, 'Você só pode desconectar seu próprio calendário.');
            }

            $token = $doctor->appleCalendarToken;

            if ($token) {
                $token->delete();

                Log::info('Integração Apple Calendar removida', [
                    'doctor_id' => $doctor->id,
                ]);

                return redirect()->route('tenant.integrations.apple.index')
                    ->with('success', 'Integração com Apple Calendar removida com sucesso.');
            }

            return redirect()->route('tenant.integrations.apple.index')
                ->with('info', 'Nenhuma integração encontrada para este médico.');
        } catch (\Exception $e) {
            Log::error('Erro ao desconectar Apple Calendar', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.integrations.apple.index')
                ->with('error', 'Erro ao remover integração. Tente novamente.');
        }
    }

    /**
     * Verifica o status da integração
     */
    public function status($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $token = $doctor->appleCalendarToken;

        return response()->json([
            'connected' => $token !== null,
        ]);
    }

    /**
     * Lista eventos do Apple Calendar para um médico (API para FullCalendar)
     */
    public function getEvents($doctorId, Request $request)
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);

            $startDate = $request->get('start');
            $endDate = $request->get('end');

            $events = $this->appleCalendarService->listEvents($doctor->id, $startDate, $endDate);

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
            Log::error('Erro ao buscar eventos do Apple Calendar', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([], 500);
        }
    }
}

