<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Patient;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\RecurringAppointment;
use App\Models\Tenant\RecurringAppointmentRule;
use App\Http\Requests\Tenant\StoreAppointmentRequest;
use App\Http\Requests\Tenant\UpdateAppointmentRequest;
use App\Services\Tenant\GoogleCalendarService;
use App\Services\Tenant\AppleCalendarService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    use HasDoctorFilter;
    protected GoogleCalendarService $googleCalendarService;
    protected AppleCalendarService $appleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService, AppleCalendarService $appleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
        $this->appleCalendarService = $appleCalendarService;
    }

    public function gridData(Request $request, $slug)
    {
        $query = Appointment::with([
            'patient',
            'doctor.user',
            'specialty',
            'calendar',
        ]);

        // Aplicar filtro de médico usando doctor_id diretamente
        $this->applyDoctorFilter($query, 'doctor_id');

        $page  = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));

        // Busca global
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($sub) use ($search) {
                    $sub->where('full_name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('doctor.user', function ($sub) use ($search) {
                    $sub->where('name_full', 'like', '%' . $search . '%');
                })
                ->orWhereHas('specialty', function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%');
                })
                ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        // Ordenação
        $sortable = [
            'starts_at' => 'starts_at',
            'patient'   => 'patient_id',
            'doctor'    => 'doctor_id',
            'mode'      => 'appointment_mode',
            'status'    => 'status',
        ];

        $sortField = (string) $request->input('sort', 'starts_at');
        $sortDir   = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (isset($sortable[$sortField])) {
            $query->orderBy($sortable[$sortField], $sortDir);
        } else {
            $query->orderBy('starts_at', 'desc');
        }

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Appointment $appointment) {
            $modeLabel = match ($appointment->appointment_mode) {
                'online' => 'Online',
                'presencial' => 'Presencial',
                default => '—',
            };

            return [
                'date'         => $appointment->starts_at?->format('d/m/Y'),
                'time'         => $appointment->starts_at?->format('H:i'),
                'patient'      => e($appointment->patient->full_name ?? '-'),
                'doctor'       => e(optional(optional($appointment->doctor)->user)->name_full ?? '-'),
                'specialty'    => e(optional($appointment->specialty)->name ?? '-'),
                'mode'         => $modeLabel,
                'status_badge' => view('tenant.appointments.partials.status', compact('appointment'))->render(),
                'actions'      => view('tenant.appointments.partials.actions', compact('appointment'))->render(),
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function index()
    {
        $query = Appointment::with(['doctor.user', 'calendar', 'patient', 'specialty']);
        
        // Aplicar filtro de médico usando doctor_id diretamente
        $this->applyDoctorFilter($query, 'doctor_id');

        $appointments = $query->orderBy('starts_at')->paginate(30);

        return view('tenant.appointments.index', compact('appointments'));
    }

    public function create()
    {
        // Listar médicos ativos (com status active)
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            });

        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);

        // Buscar todos os médicos primeiro para verificar configurações
        $allDoctors = $doctorsQuery->orderBy('id')->get();
        
        // Filtrar apenas médicos com configurações completas
        $doctors = $allDoctors->filter(function($doctor) {
            return $doctor->hasCompleteCalendarConfiguration();
        });

        // Verificar se não há médicos cadastrados
        if ($allDoctors->isEmpty()) {
            return redirect()->route('tenant.appointments.index', ['slug' => tenant()->subdomain])
                ->with('error', 'Não há médicos cadastrados no sistema. Por favor, cadastre pelo menos um médico antes de criar agendamentos.');
        }

        // Verificar se não há médicos com configurações completas
        if ($doctors->isEmpty()) {
            $user = Auth::guard('tenant')->user();
            $isAdmin = $user->role === 'admin';
            
            // Construir mensagem detalhada sobre o que está faltando
            $missingDetails = [];
            foreach ($allDoctors as $doctor) {
                $missing = $doctor->getMissingConfigurationDetails();
                if (!empty($missing)) {
                    $doctorName = $doctor->user->name_full ?? $doctor->user->name ?? 'Médico';
                    $missingDetails[] = [
                        'name' => $doctorName,
                        'missing' => $missing
                    ];
                }
            }
            
            $message = 'Não é possível criar agendamentos porque nenhum médico possui todas as configurações necessárias. ';
            $message .= 'Para criar agendamentos, cada médico precisa ter: ';
            $message .= '<ul class="mb-0 mt-2">';
            $message .= '<li><strong>Calendário</strong> cadastrado</li>';
            $message .= '<li><strong>Horários comerciais</strong> configurados</li>';
            $message .= '<li><strong>Tipos de atendimento</strong> cadastrados e ativos</li>';
            $message .= '</ul>';
            
            if ($isAdmin && !empty($missingDetails)) {
                $message .= '<div class="mt-3"><strong>Detalhes por médico:</strong><ul class="mb-0 mt-2">';
                foreach ($missingDetails as $detail) {
                    $missingList = implode(', ', array_map(function($item) {
                        return '<strong>' . $item . '</strong>';
                    }, $detail['missing']));
                    $message .= '<li>' . $detail['name'] . ': falta ' . $missingList . '</li>';
                }
                $message .= '</ul></div>';
            }
            
            $message .= '<div class="mt-3">';
            $message .= '<strong>O que fazer:</strong><br>';
            $message .= '1. Acesse <a href="' . route('tenant.doctors.index') . '" class="alert-link">Médicos</a> para verificar os médicos cadastrados<br>';
            $message .= '2. Para cada médico, configure:<br>';
            $message .= '&nbsp;&nbsp;&nbsp;- <a href="' . route('tenant.calendars.index') . '" class="alert-link">Calendário</a><br>';
            $message .= '&nbsp;&nbsp;&nbsp;- <a href="' . route('tenant.business-hours.index') . '" class="alert-link">Horários Comerciais</a><br>';
            $message .= '&nbsp;&nbsp;&nbsp;- <a href="' . route('tenant.appointment-types.index') . '" class="alert-link">Tipos de Atendimento</a>';
            $message .= '</div>';
            
            return redirect()->route('tenant.appointments.index', ['slug' => tenant()->subdomain])
                ->with('error', $message);
        }

        $patients = Patient::orderBy('full_name')->get();

        return view('tenant.appointments.create', compact(
            'doctors',
            'patients'
        ));
    }

    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();
        
        // Sempre definir status como "scheduled" ao criar um novo agendamento
        $data['status'] = 'scheduled';
        
        // Identificar origem: se usuário autenticado é paciente, é portal; senão, é interno
        if (Auth::guard('patient')->check()) {
            $data['origin'] = 'portal';
        } else {
            $data['origin'] = 'internal';
        }

        // Aplicar lógica de appointment_mode baseado na configuração
        $mode = \App\Models\Tenant\TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        if ($mode === 'presencial') {
            $data['appointment_mode'] = 'presencial';
        } elseif ($mode === 'online') {
            $data['appointment_mode'] = 'online';
        } else { // user_choice
            $data['appointment_mode'] = $request->appointment_mode ?? 'presencial';
        }

        // Buscar o calendário principal do médico automaticamente
        if (isset($data['doctor_id'])) {
            $doctor = Doctor::findOrFail($data['doctor_id']);
            
            // Validar se o médico tem todas as configurações necessárias
            if (!$doctor->hasCompleteCalendarConfiguration()) {
                $missing = $doctor->getMissingConfigurationDetails();
                $missingList = implode(', ', array_map(function($item) {
                    return '<strong>' . $item . '</strong>';
                }, $missing));
                
                $doctorName = $doctor->user->name_full ?? $doctor->user->name ?? 'Este médico';
                $message = $doctorName . ' não possui todas as configurações necessárias para criar agendamentos. ';
                $message .= 'Faltam: ' . $missingList . '. ';
                $message .= 'Por favor, configure todas as opções antes de criar agendamentos.';
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', $message);
            }
            
            $calendar = $doctor->getPrimaryCalendar();
            
            if (!$calendar) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'O médico selecionado não possui um calendário cadastrado. Por favor, cadastre um calendário para este médico primeiro.');
            }
            
            $data['calendar_id'] = $calendar->id;
            // Garantir que doctor_id está definido explicitamente
            $data['doctor_id'] = $doctor->id;
        } elseif (isset($data['calendar_id'])) {
            // Se não tiver doctor_id mas tiver calendar_id, buscar do calendário
            $calendar = Calendar::findOrFail($data['calendar_id']);
            $doctor = $calendar->doctor;
            
            // Validar se o médico tem todas as configurações necessárias
            if (!$doctor->hasCompleteCalendarConfiguration()) {
                $missing = $doctor->getMissingConfigurationDetails();
                $missingList = implode(', ', array_map(function($item) {
                    return '<strong>' . $item . '</strong>';
                }, $missing));
                
                $doctorName = $doctor->user->name_full ?? $doctor->user->name ?? 'Este médico';
                $message = $doctorName . ' não possui todas as configurações necessárias para criar agendamentos. ';
                $message .= 'Faltam: ' . $missingList . '. ';
                $message .= 'Por favor, configure todas as opções antes de criar agendamentos.';
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', $message);
            }
            
            $data['doctor_id'] = $calendar->doctor_id;
        }

        $appointment = Appointment::create($data);

        // Sincronizar com Google Calendar se o médico tiver token
        try {
            $this->googleCalendarService->syncEvent($appointment);
        } catch (\Exception $e) {
            \Log::error('Erro ao sincronizar agendamento com Google Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Para agendamentos internos: enviar link de pagamento se configurado
        // O Observer já cuida disso, mas garantimos aqui também para casos especiais
        if (
            tenant_setting('finance.enabled') === 'true' &&
            tenant_setting('finance.charge_on_internal_appointment') === 'true' &&
            tenant_setting('finance.auto_send_payment_link') === 'true'
        ) {
            $redirectService = app(\App\Services\Finance\FinanceRedirectService::class);
            $charge = $redirectService->getPendingCharge($appointment);
            
            if ($charge && $redirectService->shouldSendPaymentLink($charge)) {
                \App\Services\TenantNotificationService::sendPaymentLink($charge);
            }
        }

        return redirect()->route('tenant.appointments.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Agendamento criado com sucesso.');
    }

    public function show($slug, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->load(['calendar.doctor.user', 'patient', 'type', 'specialty']);
        
        // Buscar formulário ativo do médico
        $form = \App\Models\Tenant\Form::getFormForAppointment($appointment);
        
        // Buscar resposta existente para este agendamento
        // Prioridade: 1) Resposta com appointment_id e form_id específico, 2) Qualquer resposta com appointment_id, 3) Resposta sem appointment_id
        $formResponse = null;
        
        if ($form) {
            // Primeiro: buscar resposta específica para este agendamento e formulário
            $formResponse = \App\Models\Tenant\FormResponse::findByAppointmentAndForm($appointment->id, $form->id);
        }
        
        // Se não encontrou, buscar qualquer resposta para este agendamento
        if (!$formResponse) {
            $formResponse = \App\Models\Tenant\FormResponse::where('appointment_id', $appointment->id)
                ->whereNotNull('appointment_id')
                ->orderBy('submitted_at', 'desc')
                ->first();
        }
        
        // Fallback: buscar resposta sem appointment_id (caso não tenha sido salvo)
        if (!$formResponse && $form && $appointment->patient_id) {
            $formResponse = \App\Models\Tenant\FormResponse::where('form_id', $form->id)
                ->where('patient_id', $appointment->patient_id)
                ->whereNull('appointment_id')
                ->orderBy('submitted_at', 'desc')
                ->first();
        }
        
        return view('tenant.appointments.show', compact('appointment', 'form', 'formResponse'));
    }

    public function edit($slug, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Listar médicos ativos (com status active)
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            });

        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);

        // Buscar todos os médicos primeiro para verificar configurações
        $allDoctors = $doctorsQuery->orderBy('id')->get();
        
        // Filtrar apenas médicos com configurações completas
        // Mas incluir o médico do agendamento atual mesmo que não tenha todas as configurações
        // (para não impedir a edição de agendamentos existentes)
        $doctors = $allDoctors->filter(function($doctor) use ($appointment) {
            return $doctor->hasCompleteCalendarConfiguration() || $doctor->id === $appointment->doctor_id;
        });
        
        $patients = Patient::orderBy('full_name')->get();

        $appointment->load(['doctor.user', 'calendar', 'patient', 'specialty', 'type']);

        return view('tenant.appointments.edit', compact(
            'appointment',
            'doctors',
            'patients'
        ));
    }

    public function update(UpdateAppointmentRequest $request, $slug, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $data = $request->validated();

        // Buscar o calendário principal do médico automaticamente
        if (isset($data['doctor_id'])) {
            $doctor = Doctor::findOrFail($data['doctor_id']);
            
            // Validar se o médico tem todas as configurações necessárias (apenas se mudou de médico)
            if ($appointment->doctor_id !== $doctor->id && !$doctor->hasCompleteCalendarConfiguration()) {
                $missing = $doctor->getMissingConfigurationDetails();
                $missingList = implode(', ', array_map(function($item) {
                    return '<strong>' . $item . '</strong>';
                }, $missing));
                
                $doctorName = $doctor->user->name_full ?? $doctor->user->name ?? 'Este médico';
                $message = $doctorName . ' não possui todas as configurações necessárias para criar agendamentos. ';
                $message .= 'Faltam: ' . $missingList . '. ';
                $message .= 'Por favor, configure todas as opções antes de alterar o agendamento para este médico.';
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', $message);
            }
            
            $calendar = $doctor->getPrimaryCalendar();
            
            if (!$calendar) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'O médico selecionado não possui um calendário cadastrado. Por favor, cadastre um calendário para este médico primeiro.');
            }
            
            $data['calendar_id'] = $calendar->id;
            // Garantir que doctor_id está definido explicitamente
            $data['doctor_id'] = $doctor->id;
        }

        // Aplicar lógica de appointment_mode baseado na configuração
        $mode = \App\Models\Tenant\TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        if ($mode === 'presencial') {
            $data['appointment_mode'] = 'presencial';
        } elseif ($mode === 'online') {
            $data['appointment_mode'] = 'online';
        } else { // user_choice
            $data['appointment_mode'] = $request->appointment_mode ?? $appointment->appointment_mode ?? 'presencial';
        }

        $appointment->update($data);

        // Sincronizar com Google Calendar se o médico tiver token
        try {
            $this->googleCalendarService->syncEvent($appointment);
        } catch (\Exception $e) {
            \Log::error('Erro ao sincronizar agendamento com Google Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('tenant.appointments.index', ['slug' => $slug])
            ->with('success', 'Agendamento atualizado com sucesso.');
    }

    public function destroy($slug, $id)
    {
        $user = Auth::guard('tenant')->user();
        
        // Apenas admin pode excluir agendamentos
        if ($user->role !== 'admin') {
            abort(403, 'Você não tem permissão para excluir agendamentos.');
        }
        
        $appointment = Appointment::findOrFail($id);

        // Remover do Google Calendar se existir
        try {
            $this->googleCalendarService->deleteEvent($appointment);
        } catch (\Exception $e) {
            \Log::error('Erro ao remover agendamento do Google Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Remover do Apple Calendar se existir
        try {
            $this->appleCalendarService->deleteEvent($appointment);
        } catch (\Exception $e) {
            \Log::error('Erro ao remover agendamento do Apple Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }

        $appointment->delete();

        return redirect()->route('tenant.appointments.index', ['slug' => $slug])
            ->with('success', 'Agendamento removido.');
    }

    public function events(Request $request, $slug, $id)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->load('doctor.user');
        
        // Se for uma requisição AJAX, esperando JSON, ou se tiver parâmetros de query do FullCalendar (start/end), retorna JSON
        $isFullCalendarRequest = $request->has('start') || $request->has('end') || 
                                 $request->ajax() || $request->wantsJson() || $request->expectsJson() ||
                                 str_contains($request->header('Accept', ''), 'application/json');
        
        if ($isFullCalendarRequest) {
            $user = Auth::guard('tenant')->user();
            
            // Query base para buscar os agendamentos do calendário
            $query = Appointment::where('calendar_id', $calendar->id)
                ->with(['patient', 'type', 'specialty']);
            
            // Admin pode ver todos os eventos, outros roles têm restrições
            if ($user->role !== 'admin') {
                // Verifica permissão para visualizar os eventos
                if ($user->role === 'doctor' && $user->doctor) {
                    // Médico só pode ver eventos do seu próprio calendário
                    if ($calendar->doctor_id !== $user->doctor->id) {
                        return response()->json([]);
                    }
                } elseif ($user->role === 'user') {
                    // Usuário comum precisa ter permissão para ver o médico
                    if (!$user->belongsToUser($calendar->doctor_id)) {
                        return response()->json([]);
                    }
                }
            }
            
            // Obter parâmetros de data do FullCalendar (se disponíveis)
            $startDate = $request->has('start') ? Carbon::parse($request->start) : Carbon::now()->startOfMonth();
            $endDate = $request->has('end') ? Carbon::parse($request->end) : Carbon::now()->endOfMonth();
            
            $appointments = $query->get();
            
            // Formata os eventos para o FullCalendar
            $events = $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'title' => $appointment->patient ? $appointment->patient->full_name : 'Sem paciente',
                    'start' => $appointment->starts_at->toIso8601String(),
                    'end' => $appointment->ends_at ? $appointment->ends_at->toIso8601String() : null,
                    'backgroundColor' => $this->getEventColor($appointment->status ?? 'scheduled'),
                    'borderColor' => $this->getEventColor($appointment->status ?? 'scheduled'),
                    'extendedProps' => [
                        'patient' => $appointment->patient ? $appointment->patient->full_name : null,
                        'type' => $appointment->type ? $appointment->type->name : null,
                        'specialty' => $appointment->specialty ? $appointment->specialty->name : null,
                        'status' => $appointment->status ?? 'scheduled',
                        'notes' => $appointment->notes ?? null,
                        'is_recurring' => false,
                    ]
                ];
            });
            
            // Adicionar agendamentos recorrentes que ainda não foram gerados
            $recurringEvents = $this->getRecurringAppointmentEvents($calendar->doctor_id, $startDate, $endDate);
            $events = $events->merge($recurringEvents);
            
            return response()->json($events);
        }
        
        // Retorna a view para acesso direto - mas primeiro verifica permissões
        $user = Auth::guard('tenant')->user();
        
        // Admin pode ver todos os calendários, outros roles têm restrições
        if ($user->role !== 'admin') {
            // Verifica permissão para visualizar o calendário
            if ($user->role === 'doctor' && $user->doctor) {
                // Médico só pode ver seu próprio calendário
                if ($calendar->doctor_id !== $user->doctor->id) {
                    abort(403, 'Você não tem permissão para visualizar este calendário.');
                }
            } elseif ($user->role === 'user') {
                // Usuário comum precisa ter permissão para ver o médico
                if (!$user->belongsToUser($calendar->doctor_id)) {
                    abort(403, 'Você não tem permissão para visualizar este calendário.');
                }
            } else {
                // Usuário não autenticado ou role inválido
                abort(403, 'Você precisa estar autenticado para visualizar este calendário.');
            }
        }
        
        return view('tenant.calendars.events', compact('calendar'));
    }
    
    /**
     * Retorna a cor do evento baseado no status
     */
    private function getEventColor($status)
    {
        return match($status) {
            'completed' => '#28a745',
            'cancelled' => '#dc3545',
            'confirmed' => '#007bff',
            'pending' => '#ffc107',
            default => '#6c757d',
        };
    }

    /**
     * Gera eventos de agendamentos recorrentes para exibição no calendário
     */
    private function getRecurringAppointmentEvents($doctorId, Carbon $startDate, Carbon $endDate): \Illuminate\Support\Collection
    {
        $events = collect();

        // Buscar recorrências ativas do médico
        $recurringAppointments = RecurringAppointment::where('doctor_id', $doctorId)
            ->where('active', true)
            ->where('start_date', '<=', $endDate->format('Y-m-d'))
            ->where(function($query) use ($startDate) {
                $query->where('end_type', 'none')
                      ->orWhere(function($q) use ($startDate) {
                          $q->where('end_type', 'date')
                            ->where('end_date', '>=', $startDate->format('Y-m-d'));
                      })
                      ->orWhere(function($q) {
                          $q->where('end_type', 'total_sessions');
                      });
            })
            ->with(['rules', 'patient', 'appointmentType'])
            ->get();

        foreach ($recurringAppointments as $recurring) {
            if (!$recurring->isActive()) {
                continue;
            }

            // Processar cada regra da recorrência
            foreach ($recurring->rules as $rule) {
                $weekdayNumber = $rule->getWeekdayNumber();
                
                // Gerar eventos para cada ocorrência no período
                $currentDate = $startDate->copy();
                
                // Encontrar primeira ocorrência do dia da semana no período
                $daysUntilWeekday = ($weekdayNumber - $currentDate->dayOfWeek + 7) % 7;
                if ($daysUntilWeekday > 0) {
                    $currentDate->addDays($daysUntilWeekday);
                }
                
                // Garantir que a data não seja anterior à data inicial da recorrência
                if ($currentDate->lt($recurring->start_date)) {
                    $currentDate = $recurring->start_date->copy();
                    $daysToAdd = ($weekdayNumber - $currentDate->dayOfWeek + 7) % 7;
                    if ($daysToAdd > 0) {
                        $currentDate->addDays($daysToAdd);
                    }
                }

                // Gerar eventos até o final do período ou até a data final da recorrência
                while ($currentDate->lte($endDate)) {
                    // Verificar limites da recorrência
                    if ($recurring->end_type === 'date' && $recurring->end_date && $currentDate->gt($recurring->end_date)) {
                        break;
                    }

                    if ($recurring->end_type === 'total_sessions' && $recurring->total_sessions) {
                        $generatedCount = $recurring->getGeneratedSessionsCount();
                        if ($generatedCount >= $recurring->total_sessions) {
                            break;
                        }
                    }

                    // Verificar se já existe um agendamento gerado para esta data e horário
                    $startDateTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $rule->start_time);
                    $endDateTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $rule->end_time);
                    
                    $existingAppointment = Appointment::where('recurring_appointment_id', $recurring->id)
                        ->whereDate('starts_at', $currentDate->format('Y-m-d'))
                        ->whereTime('starts_at', $rule->start_time)
                        ->whereTime('ends_at', $rule->end_time)
                        ->first();

                    // Se não existe, criar evento virtual para exibição
                    if (!$existingAppointment) {
                        $events->push([
                            'id' => 'recurring_' . $recurring->id . '_' . $currentDate->format('Y-m-d') . '_' . $rule->id,
                            'title' => $recurring->patient ? $recurring->patient->full_name : 'Agendamento Recorrente',
                            'start' => $startDateTime->toIso8601String(),
                            'end' => $endDateTime->toIso8601String(),
                            'backgroundColor' => '#17a2b8', // Cor diferente para agendamentos recorrentes
                            'borderColor' => '#17a2b8',
                            'extendedProps' => [
                                'patient' => $recurring->patient ? $recurring->patient->full_name : null,
                                'type' => $recurring->appointmentType ? $recurring->appointmentType->name : null,
                                'specialty' => null,
                                'status' => 'scheduled',
                                'notes' => 'Agendamento recorrente',
                                'is_recurring' => true,
                                'recurring_appointment_id' => $recurring->id,
                            ]
                        ]);
                    }

                    // Avançar para a próxima semana
                    $currentDate->addWeek();
                }
            }
        }

        return $events;
    }

    /**
     * API: Buscar calendários por médico
     */
    public function getCalendarsByDoctor($slug, $doctorId)
    {
        if (!$this->findAccessibleDoctor($doctorId)) {
            return response()->json([]);
        }

        $calendars = Calendar::where('doctor_id', $doctorId)
            ->orderBy('name')
            ->get()
            ->map(function($calendar) {
                return [
                    'id' => $calendar->id,
                    'name' => $calendar->name,
                ];
            });

        return response()->json($calendars);
    }

    /**
     * API: Buscar tipos de consulta por médico
     */
    public function getAppointmentTypesByDoctor($slug, $doctorId)
    {
        try {
            if (!$this->findAccessibleDoctor($doctorId)) {
                return response()->json([]);
            }

            // Retorna apenas os tipos de consulta do médico específico
            $types = AppointmentType::where('doctor_id', $doctorId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'duration_min' => $type->duration_min,
                    ];
                });

            return response()->json($types);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar tipos de consulta por médico', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage()
            ]);
            
            // Retornar array vazio em caso de erro
            return response()->json([]);
        }
    }

    /**
     * API: Buscar especialidades por médico
     */
    public function getSpecialtiesByDoctor($slug, $doctorId)
    {
        try {
            $doctor = $this->findAccessibleDoctor($doctorId);
            if (!$doctor) {
                return response()->json([]);
            }
            
            \Log::info('Buscando especialidades para médico', [
                'doctor_id' => $doctorId,
                'doctor_name' => $doctor->user->name_full ?? $doctor->user->name ?? 'N/A'
            ]);
            
            $specialties = $doctor->specialties()
                ->orderBy('name')
                ->get();
            
            \Log::info('Especialidades encontradas', [
                'doctor_id' => $doctorId,
                'count' => $specialties->count(),
                'specialties' => $specialties->pluck('name')->toArray()
            ]);
            
            $result = $specialties->map(function($specialty) {
                return [
                    'id' => $specialty->id,
                    'name' => $specialty->name,
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar especialidades por médico', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retornar array vazio em caso de erro
            return response()->json([]);
        }
    }

    /**
     * API: Buscar horários disponíveis para um médico em uma data específica
     */
    public function getAvailableSlots(Request $request, $slug, $doctorId)
    {
        $request->validate([
            'date' => 'required|date',
            'appointment_type_id' => 'nullable|exists:tenant.appointment_types,id',
        ]);

        if (!$this->findAccessibleDoctor($doctorId)) {
            return response()->json([]);
        }

        $date = Carbon::parse($request->date, 'America/Campo_Grande')->startOfDay();
        $todayCampoGrande = Carbon::now('America/Campo_Grande')->startOfDay();
        if ($date->lt($todayCampoGrande)) {
            return response()->json([
                'message' => 'Não é possível buscar horários para uma data passada. Selecione hoje ou uma data futura.',
            ], 422);
        }

        $weekday = $date->dayOfWeek; // 0 = Domingo, 6 = Sábado
        
        // Buscar horários comerciais do médico para o dia da semana
        $businessHours = BusinessHour::where('doctor_id', $doctorId)
            ->where('weekday', $weekday)
            ->orderBy('start_time')
            ->get();

        if ($businessHours->isEmpty()) {
            return response()->json([]);
        }

        // Buscar calendários do médico
        $calendars = Calendar::where('doctor_id', $doctorId)->pluck('id');
        
        // Buscar agendamentos existentes para o dia (apenas scheduled e rescheduled)
        // Cancelados e não comparecidos não ocupam horário
        $existingAppointments = Appointment::whereIn('calendar_id', $calendars)
            ->whereDate('starts_at', $date->format('Y-m-d'))
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->get();

        // Duração padrão (30 minutos) ou do tipo de consulta
        $duration = 30; // minutos padrão
        if ($request->appointment_type_id) {
            $appointmentType = AppointmentType::find($request->appointment_type_id);
            if ($appointmentType) {
                $duration = $appointmentType->duration_min;
            }
        }

        $availableSlots = [];

        foreach ($businessHours as $businessHour) {
            $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->start_time);
            $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->end_time);

            // Verificar se há intervalo configurado
            $breakStartTime = null;
            $breakEndTime = null;
            if ($businessHour->break_start_time && $businessHour->break_end_time) {
                $breakStartTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->break_start_time);
                $breakEndTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->break_end_time);
            }

            $currentSlot = $startTime->copy();

            while ($currentSlot->copy()->addMinutes($duration)->lte($endTime)) {
                $slotStart = $currentSlot->copy();
                $slotEnd = $currentSlot->copy()->addMinutes($duration);
                
                // Verificar se o slot está dentro do intervalo (se houver)
                $isInBreak = false;
                if ($breakStartTime && $breakEndTime) {
                    // Verifica se o slot se sobrepõe ao intervalo
                    $isInBreak = ($slotStart->lt($breakEndTime) && $slotEnd->gt($breakStartTime));
                }
                
                if ($isInBreak) {
                    // Pular este slot pois está no intervalo
                    $currentSlot->addMinutes($duration);
                    continue;
                }

                // Verificar se o slot não conflita com agendamentos existentes
                // Considera apenas agendamentos com status 'scheduled' ou 'rescheduled'
                $hasConflict = $existingAppointments->filter(function($appointment) use ($slotStart, $slotEnd) {
                    $apptStart = Carbon::parse($appointment->starts_at);
                    $apptEnd = Carbon::parse($appointment->ends_at);
                    
                    // Verifica sobreposição: 
                    // - Slot começa antes do agendamento terminar
                    // - Slot termina depois do agendamento começar
                    // Isso cobre todos os casos: sobreposição parcial, slot dentro do agendamento, agendamento dentro do slot
                    return $slotStart->lt($apptEnd) && $slotEnd->gt($apptStart);
                })->isNotEmpty();

                // Verificar se o slot está bloqueado por uma recorrência ativa
                if (!$hasConflict) {
                    $hasConflict = $this->isSlotBlockedByRecurring($doctorId, $date, $slotStart, $slotEnd);
                }

                if (!$hasConflict) {
                    $availableSlots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'datetime_start' => $slotStart->toIso8601String(),
                        'datetime_end' => $slotEnd->toIso8601String(),
                    ];
                }

                $currentSlot->addMinutes($duration);
            }
        }

        return response()->json($availableSlots);
    }

    /**
     * Verifica se um slot está bloqueado por uma recorrência ativa
     */
    private function isSlotBlockedByRecurring($doctorId, Carbon $date, Carbon $slotStart, Carbon $slotEnd): bool
    {
        $weekday = $date->dayOfWeek;
        $weekdayString = RecurringAppointmentRule::weekdayFromNumber($weekday);
        $slotStartTime = $slotStart->format('H:i');
        $slotEndTime = $slotEnd->format('H:i');

        // Buscar recorrências ativas do médico que têm regras para este dia da semana
        $recurringAppointments = RecurringAppointment::where('doctor_id', $doctorId)
            ->where('active', true)
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where(function($query) use ($date) {
                $query->where('end_type', 'none')
                      ->orWhere(function($q) use ($date) {
                          $q->where('end_type', 'date')
                            ->where('end_date', '>=', $date->format('Y-m-d'));
                      })
                      ->orWhere(function($q) {
                          $q->where('end_type', 'total_sessions');
                      });
            })
            ->whereHas('rules', function($q) use ($weekdayString) {
                $q->where('weekday', $weekdayString);
            })
            ->with('rules')
            ->get();

        if ($recurringAppointments->isEmpty()) {
            return false;
        }

        // Verificar se alguma recorrência ainda está dentro dos limites e bloqueia o slot
        foreach ($recurringAppointments as $recurring) {
            if (!$recurring->isActive()) {
                continue;
            }

            // Verificar se ainda não atingiu o limite de sessões
            if ($recurring->end_type === 'total_sessions' && $recurring->total_sessions) {
                $generatedCount = $recurring->getGeneratedSessionsCount();
                if ($generatedCount >= $recurring->total_sessions) {
                    continue; // Já atingiu o limite
                }
            }

            // Verificar se a data está dentro do período válido
            if ($recurring->end_type === 'date' && $recurring->end_date && $date->gt($recurring->end_date)) {
                continue; // Data fora do período
            }

            // Verificar se alguma regra desta recorrência bloqueia o slot (verifica sobreposição)
            foreach ($recurring->rules as $rule) {
                if ($rule->weekday !== $weekdayString) {
                    continue;
                }

                // Converter horários para Carbon para comparação precisa
                // Normalizar o formato do horário (pode vir como "HH:MM:SS" ou "HH:MM")
                $ruleStartTime = $rule->start_time;
                $ruleEndTime = $rule->end_time;
                
                // Normalizar para formato HH:MM:SS se necessário
                if (strlen($ruleStartTime) === 5) {
                    $ruleStartTime .= ':00';
                }
                if (strlen($ruleEndTime) === 5) {
                    $ruleEndTime .= ':00';
                }
                
                // Criar objetos Carbon com a data e horário, zerando segundos e microsegundos para comparação precisa
                try {
                    $ruleStart = Carbon::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d') . ' ' . $ruleStartTime)->startOfMinute();
                    $ruleEnd = Carbon::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d') . ' ' . $ruleEndTime)->startOfMinute();
                } catch (\Exception $e) {
                    // Fallback: usar parse se createFromFormat falhar
                    $ruleStart = Carbon::parse($date->format('Y-m-d') . ' ' . $ruleStartTime)->startOfMinute();
                    $ruleEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $ruleEndTime)->startOfMinute();
                }
                
                // Garantir que os slots também estão normalizados
                $normalizedSlotStart = $slotStart->copy()->startOfMinute();
                $normalizedSlotEnd = $slotEnd->copy()->startOfMinute();

                // Verifica sobreposição de intervalos de tempo:
                // Dois intervalos [a, b) e [c, d) se sobrepõem se: a < d && b > c
                // Onde o fim do intervalo é exclusivo (um agendamento 08:00-09:00 termina em 09:00:00, mas não inclui 09:00:00)
                // Então o próximo slot pode começar exatamente em 09:00:00
                // IMPORTANTE: Se a regra termina às 09:00:00 e o slot começa às 09:00:00, NÃO há sobreposição
                // Por isso usamos > (maior que) e não >= (maior ou igual)
                // 
                // Exemplo:
                // - Regra: 08:00:00 - 09:00:00
                // - Slot: 09:00:00 - 10:00:00
                // - Verificação: 08:00:00 < 10:00:00 (true) && 09:00:00 > 09:00:00 (false) = false (não bloqueia) ✓
                $hasOverlap = $ruleStart->lt($normalizedSlotEnd) && $ruleEnd->gt($normalizedSlotStart);
                
                if ($hasOverlap) {
                    return true; // Há sobreposição, o slot está bloqueado
                }
            }
        }

        return false;
    }

    /**
     * API: Buscar dias trabalhados (business hours) do médico
     */
    public function getBusinessHoursByDoctor($slug, $doctorId)
    {
        try {
            $doctor = $this->findAccessibleDoctor($doctorId);
            if (!$doctor) {
                return response()->json([]);
            }
            
            $businessHours = BusinessHour::where('doctor_id', $doctorId)
                ->orderBy('weekday')
                ->orderBy('start_time')
                ->get();

            // Mapear weekday para nome do dia
            $weekdayNames = [
                0 => 'Domingo',
                1 => 'Segunda-feira',
                2 => 'Terça-feira',
                3 => 'Quarta-feira',
                4 => 'Quinta-feira',
                5 => 'Sexta-feira',
                6 => 'Sábado',
            ];

            // Agrupar por weekday
            $grouped = $businessHours->groupBy('weekday');
            
            // Criar array com todos os dias da semana (mesmo que não tenham horários)
            $result = [
                'doctor' => [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name_full ?? $doctor->user->name ?? 'N/A',
                ],
                'business_hours' => []
            ];

            // Processar cada dia da semana
            foreach ($grouped as $weekday => $hours) {
                $result['business_hours'][] = [
                    'weekday' => (int)$weekday,
                    'weekday_name' => $weekdayNames[$weekday] ?? 'N/A',
                    'hours' => $hours->map(function($h) {
                        return [
                            'start_time' => substr($h->start_time, 0, 5), // Formato HH:MM
                            'end_time' => substr($h->end_time, 0, 5),
                            'break_start_time' => $h->break_start_time ? substr($h->break_start_time, 0, 5) : null,
                            'break_end_time' => $h->break_end_time ? substr($h->break_end_time, 0, 5) : null,
                        ];
                    })->values()->toArray(),
                ];
            }

            // Ordenar por weekday
            usort($result['business_hours'], function($a, $b) {
                return $a['weekday'] <=> $b['weekday'];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar dias trabalhados do médico', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erro ao buscar dias trabalhados do médico: ' . $e->getMessage(),
                'doctor' => null,
                'business_hours' => []
            ], 500);
        }
    }

    /**
     * API: Buscar pacientes por texto (nome/email/telefone/CPF)
     */
    public function searchPatients(Request $request, $slug)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $queryText = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 10);

        $patientsQuery = Patient::query()->orderBy('full_name');

        if ($queryText !== '') {
            $patientsQuery->where(function ($query) use ($queryText) {
                $query->where('full_name', 'like', "%{$queryText}%")
                    ->orWhere('email', 'like', "%{$queryText}%")
                    ->orWhere('phone', 'like', "%{$queryText}%")
                    ->orWhere('cpf', 'like', "%{$queryText}%");
            });
        }

        $patients = $patientsQuery
            ->limit($limit)
            ->get(['id', 'full_name', 'email', 'phone', 'cpf'])
            ->map(function (Patient $patient) {
                $secondary = $patient->email ?: ($patient->phone ?: $patient->cpf);

                return [
                    'id' => $patient->id,
                    'name' => $patient->full_name,
                    'secondary' => $secondary,
                ];
            });

        return response()->json(['data' => $patients]);
    }

    /**
     * API: Buscar mÃ©dicos por texto (nome/registro/especialidade)
     */
    public function searchDoctors(Request $request, $slug)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $queryText = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 10);

        $doctorsQuery = Doctor::query()
            ->with(['user:id,name,name_full,status', 'specialties:id,name'])
            ->whereHas('user', function ($query) {
                $query->where('status', 'active');
            })
            ->whereHas('calendars')
            ->whereHas('businessHours')
            ->whereHas('appointmentTypes', function ($query) {
                $query->where('is_active', true);
            });

        $this->applyDoctorFilter($doctorsQuery);

        if ($queryText !== '') {
            $doctorsQuery->where(function ($query) use ($queryText) {
                $query->whereHas('user', function ($subQuery) use ($queryText) {
                    $subQuery->where('name_full', 'like', "%{$queryText}%")
                        ->orWhere('name', 'like', "%{$queryText}%");
                })
                ->orWhere('registration_value', 'like', "%{$queryText}%")
                ->orWhere('crm_number', 'like', "%{$queryText}%")
                ->orWhereHas('specialties', function ($subQuery) use ($queryText) {
                    $subQuery->where('name', 'like', "%{$queryText}%");
                });
            });
        }

        $doctors = $doctorsQuery
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(function (Doctor $doctor) {
                $name = $doctor->user?->name_full ?: $doctor->user?->name ?: 'MÃ©dico';
                $registration = $doctor->registration_value ?: $doctor->crm_number;
                $specialty = $doctor->specialties->first()?->name;
                $secondary = $registration ?: $specialty;

                return [
                    'id' => $doctor->id,
                    'name' => $name,
                    'secondary' => $secondary,
                ];
            });

        return response()->json(['data' => $doctors]);
    }

    private function findAccessibleDoctor(string $doctorId): ?Doctor
    {
        $query = Doctor::with('user');
        $this->applyDoctorFilter($query);

        return $query->where('id', $doctorId)->first();
    }
}
