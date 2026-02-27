<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HandlesGridRequests;
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
use App\Services\Tenant\NotificationDispatcher;
use App\Services\Tenant\WaitlistService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    use HasDoctorFilter;
    use HandlesGridRequests;
    protected GoogleCalendarService $googleCalendarService;
    protected AppleCalendarService $appleCalendarService;
    protected NotificationDispatcher $notificationDispatcher;
    protected WaitlistService $waitlistService;

    public function __construct(
        GoogleCalendarService $googleCalendarService,
        AppleCalendarService $appleCalendarService,
        NotificationDispatcher $notificationDispatcher,
        WaitlistService $waitlistService
    )
    {
        $this->googleCalendarService = $googleCalendarService;
        $this->appleCalendarService = $appleCalendarService;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->waitlistService = $waitlistService;
    }

    public function gridData(Request $request, $slug)
    {
        $query = Appointment::with([
            'patient',
            'doctor.user',
            'specialty',
            'calendar',
        ]);

        // Aplicar filtro de mÃ©dico usando doctor_id diretamente
        $this->applyDoctorFilter($query, 'doctor_id');

        $page = $this->gridPage($request);
        $perPage = $this->gridPerPage($request);

        // Busca global
        $search = $this->gridSearch($request);
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

        // OrdenaÃ§Ã£o
        $sortable = [
            'starts_at' => 'starts_at',
            'date'      => 'starts_at',
            'time'      => 'starts_at',
            'patient'   => 'patient_id',
            'doctor'    => 'doctor_id',
            'mode'      => 'appointment_mode',
            'status'    => 'status',
            'status_badge' => 'status',
        ];

        $sort = $this->gridSort($request, $sortable, 'starts_at', 'desc');
        $query->orderBy($sort['column'], $sort['direction']);
        if ($sort['column'] !== 'starts_at') {
            $query->orderBy('starts_at', 'desc');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Appointment $appointment) {
            $modeLabel = match ($appointment->appointment_mode) {
                'online' => 'Online',
                'presencial' => 'Presencial',
                default => 'â€”',
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
            'meta' => $this->gridMeta($paginator),
        ]);
    }

    public function index()
    {
        $query = Appointment::with(['doctor.user', 'calendar', 'patient', 'specialty']);
        
        // Aplicar filtro de mÃ©dico usando doctor_id diretamente
        $this->applyDoctorFilter($query, 'doctor_id');

        $appointments = $query->orderBy('starts_at')->paginate(30);

        return view('tenant.appointments.index', compact('appointments'));
    }

    public function create()
    {
        // Listar mÃ©dicos ativos (com status active)
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            });

        // Aplicar filtro de mÃ©dico
        $this->applyDoctorFilter($doctorsQuery);

        // Buscar todos os mÃ©dicos primeiro para verificar configuraÃ§Ãµes
        $allDoctors = $doctorsQuery->orderBy('id')->get();
        
        // Filtrar apenas mÃ©dicos com configuraÃ§Ãµes completas
        $doctors = $allDoctors->filter(function($doctor) {
            return $doctor->hasCompleteCalendarConfiguration();
        });

        // Verificar se nÃ£o hÃ¡ mÃ©dicos cadastrados
        if ($allDoctors->isEmpty()) {
            return redirect()->route('tenant.appointments.index', ['slug' => tenant()->subdomain])
                ->with('error', 'NÃ£o hÃ¡ mÃ©dicos cadastrados no sistema. Por favor, cadastre pelo menos um mÃ©dico antes de criar agendamentos.');
        }

        // Verificar se nÃ£o hÃ¡ mÃ©dicos com configuraÃ§Ãµes completas
        if ($doctors->isEmpty()) {
            $user = Auth::guard('tenant')->user();
            $isAdmin = $user->role === 'admin';
            
            // Construir mensagem detalhada sobre o que estÃ¡ faltando
            $missingDetails = [];
            foreach ($allDoctors as $doctor) {
                $missing = $doctor->getMissingConfigurationDetails();
                if (!empty($missing)) {
                    $doctorName = $doctor->user->name_full ?? $doctor->user->name ?? 'MÃ©dico';
                    $missingDetails[] = [
                        'name' => $doctorName,
                        'missing' => $missing
                    ];
                }
            }
            
            $message = 'NÃ£o Ã© possÃ­vel criar agendamentos porque nenhum mÃ©dico possui todas as configuraÃ§Ãµes necessÃ¡rias. ';
            $message .= 'Para criar agendamentos, cada mÃ©dico precisa ter: ';
            $message .= '<ul class="mb-0 mt-2">';
            $message .= '<li><strong>CalendÃ¡rio</strong> cadastrado</li>';
            $message .= '<li><strong>HorÃ¡rios comerciais</strong> configurados</li>';
            $message .= '<li><strong>Tipos de atendimento</strong> cadastrados e ativos</li>';
            $message .= '</ul>';
            
            if ($isAdmin && !empty($missingDetails)) {
                $message .= '<div class="mt-3"><strong>Detalhes por mÃ©dico:</strong><ul class="mb-0 mt-2">';
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
            $message .= '1. Acesse <a href="' . route('tenant.doctors.index') . '" class="alert-link">MÃ©dicos</a> para verificar os mÃ©dicos cadastrados<br>';
            $message .= '2. Para cada mÃ©dico, configure:<br>';
            $message .= '&nbsp;&nbsp;&nbsp;- <a href="' . route('tenant.calendars.index') . '" class="alert-link">CalendÃ¡rio</a><br>';
            $message .= '&nbsp;&nbsp;&nbsp;- <a href="' . route('tenant.business-hours.index') . '" class="alert-link">HorÃ¡rios Comerciais</a><br>';
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

        $intentWaitlist = (string) $request->input('intent_waitlist', '0') === '1';
        if ($intentWaitlist) {
            try {
                $result = $this->waitlistService->joinWaitlist([
                    'doctor_id' => $data['doctor_id'],
                    'patient_id' => $data['patient_id'],
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'],
                ]);
            } catch (ValidationException $e) {
                $firstError = collect($e->errors())->flatten()->first() ?? 'NÃ£o foi possÃ­vel entrar na fila de espera.';

                return redirect()->back()
                    ->withInput()
                    ->withErrors($e->errors())
                    ->with('error', $firstError);
            }

            $message = $result['created']
                ? 'VocÃª entrou na fila de espera desse horÃ¡rio. Avisaremos quando a vaga estiver disponÃ­vel.'
                : 'VocÃª jÃ¡ estÃ¡ na fila de espera desse horÃ¡rio. Avisaremos quando a vaga estiver disponÃ­vel.';

            return redirect()->route('tenant.appointments.index', ['slug' => tenant()->subdomain])
                ->with('success', $message);
        }

        $data['id'] = Str::uuid();

        $confirmationEnabled = tenant_setting_bool('appointments.confirmation.enabled', false);
        $confirmationTtlMinutes = max(1, tenant_setting_int('appointments.confirmation.ttl_minutes', 30));

        $data['confirmation_token'] = $this->generateUniqueConfirmationToken();
        if ($confirmationEnabled) {
            $data['status'] = 'pending_confirmation';
            $data['confirmation_expires_at'] = now()->addMinutes($confirmationTtlMinutes);
            $data['confirmed_at'] = null;
            $data['expired_at'] = null;
            $data['canceled_at'] = null;
            $data['cancellation_reason'] = null;
        } else {
            $data['status'] = 'scheduled';
            $data['confirmed_at'] = now();
            $data['confirmation_expires_at'] = null;
            $data['expired_at'] = null;
        }
        
        // Identificar origem: se usuÃ¡rio autenticado Ã© paciente, Ã© portal; senÃ£o, Ã© interno
        if (Auth::guard('patient')->check()) {
            $data['origin'] = 'portal';
        } else {
            $data['origin'] = 'internal';
        }

        // Aplicar lÃ³gica de appointment_mode baseado na configuraÃ§Ã£o
        $mode = \App\Models\Tenant\TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        if ($mode === 'presencial') {
            $data['appointment_mode'] = 'presencial';
        } elseif ($mode === 'online') {
            $data['appointment_mode'] = 'online';
        } else { // user_choice
            $data['appointment_mode'] = $request->appointment_mode ?? 'presencial';
        }

        // Buscar o calendÃ¡rio principal do mÃ©dico automaticamente
        if (isset($data['doctor_id'])) {
            $doctor = Doctor::findOrFail($data['doctor_id']);
            
            // Validar se o mÃ©dico tem todas as configuraÃ§Ãµes necessÃ¡rias
            if (!$doctor->hasCompleteCalendarConfiguration()) {
                $missing = $doctor->getMissingConfigurationDetails();
                $missingList = implode(', ', array_map(function($item) {
                    return '<strong>' . $item . '</strong>';
                }, $missing));
                
                $doctorName = $doctor->user->name_full ?? $doctor->user->name ?? 'Este mÃ©dico';
                $message = $doctorName . ' nÃ£o possui todas as configuraÃ§Ãµes necessÃ¡rias para criar agendamentos. ';
                $message .= 'Faltam: ' . $missingList . '. ';
                $message .= 'Por favor, configure todas as opÃ§Ãµes antes de criar agendamentos.';
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', $message);
            }
            
            $calendar = $doctor->getPrimaryCalendar();
            
            if (!$calendar) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'O mÃ©dico selecionado nÃ£o possui um calendÃ¡rio cadastrado. Por favor, cadastre um calendÃ¡rio para este mÃ©dico primeiro.');
            }
            
            $data['calendar_id'] = $calendar->id;
            // Garantir que doctor_id estÃ¡ definido explicitamente
            $data['doctor_id'] = $doctor->id;
        } elseif (isset($data['calendar_id'])) {
            // Se nÃ£o tiver doctor_id mas tiver calendar_id, buscar do calendÃ¡rio
            $calendar = Calendar::findOrFail($data['calendar_id']);
            $doctor = $calendar->doctor;
            
            // Validar se o mÃ©dico tem todas as configuraÃ§Ãµes necessÃ¡rias
            if (!$doctor->hasCompleteCalendarConfiguration()) {
                $missing = $doctor->getMissingConfigurationDetails();
                $missingList = implode(', ', array_map(function($item) {
                    return '<strong>' . $item . '</strong>';
                }, $missing));
                
                $doctorName = $doctor->user->name_full ?? $doctor->user->name ?? 'Este mÃ©dico';
                $message = $doctorName . ' nÃ£o possui todas as configuraÃ§Ãµes necessÃ¡rias para criar agendamentos. ';
                $message .= 'Faltam: ' . $missingList . '. ';
                $message .= 'Por favor, configure todas as opÃ§Ãµes antes de criar agendamentos.';
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', $message);
            }
            
            $data['doctor_id'] = $calendar->doctor_id;
        }

        $appointment = Appointment::create($data);

        if ($appointment->isHold()) {
            $this->notificationDispatcher->dispatchAppointment(
                $appointment,
                'appointment.pending_confirmation',
                [
                    'event' => 'appointment_created_pending_confirmation',
                    'origin' => 'internal',
                ]
            );
        }

        // Sincronizar com Google Calendar se o mÃ©dico tiver token
        try {
            $this->googleCalendarService->syncEvent($appointment);
        } catch (\Exception $e) {
            \Log::error('Erro ao sincronizar agendamento com Google Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Para agendamentos internos: enviar link de pagamento se configurado
        // O Observer jÃ¡ cuida disso, mas garantimos aqui tambÃ©m para casos especiais
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
        
        // Buscar formulÃ¡rio ativo do mÃ©dico
        $form = \App\Models\Tenant\Form::getFormForAppointment($appointment);
        
        // Buscar resposta existente para este agendamento
        // Prioridade: 1) Resposta com appointment_id e form_id especÃ­fico, 2) Qualquer resposta com appointment_id, 3) Resposta sem appointment_id
        $formResponse = null;
        
        if ($form) {
            // Primeiro: buscar resposta especÃ­fica para este agendamento e formulÃ¡rio
            $formResponse = \App\Models\Tenant\FormResponse::findByAppointmentAndForm($appointment->id, $form->id);
        }
        
        // Se nÃ£o encontrou, buscar qualquer resposta para este agendamento
        if (!$formResponse) {
            $formResponse = \App\Models\Tenant\FormResponse::where('appointment_id', $appointment->id)
                ->whereNotNull('appointment_id')
                ->orderBy('submitted_at', 'desc')
                ->first();
        }
        
        // Fallback: buscar resposta sem appointment_id (caso nÃ£o tenha sido salvo)
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
        
        // Listar mÃ©dicos ativos (com status active)
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            });

        // Aplicar filtro de mÃ©dico
        $this->applyDoctorFilter($doctorsQuery);

        // Buscar todos os mÃ©dicos primeiro para verificar configuraÃ§Ãµes
        $allDoctors = $doctorsQuery->orderBy('id')->get();
        
        // Filtrar apenas mÃ©dicos com configuraÃ§Ãµes completas
        // Mas incluir o mÃ©dico do agendamento atual mesmo que nÃ£o tenha todas as configuraÃ§Ãµes
        // (para nÃ£o impedir a ediÃ§Ã£o de agendamentos existentes)
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

        // Buscar o calendÃ¡rio principal do mÃ©dico automaticamente
        if (isset($data['doctor_id'])) {
            $doctor = Doctor::findOrFail($data['doctor_id']);
            
            // Validar se o mÃ©dico tem todas as configuraÃ§Ãµes necessÃ¡rias (apenas se mudou de mÃ©dico)
            if ($appointment->doctor_id !== $doctor->id && !$doctor->hasCompleteCalendarConfiguration()) {
                $missing = $doctor->getMissingConfigurationDetails();
                $missingList = implode(', ', array_map(function($item) {
                    return '<strong>' . $item . '</strong>';
                }, $missing));
                
                $doctorName = $doctor->user->name_full ?? $doctor->user->name ?? 'Este mÃ©dico';
                $message = $doctorName . ' nÃ£o possui todas as configuraÃ§Ãµes necessÃ¡rias para criar agendamentos. ';
                $message .= 'Faltam: ' . $missingList . '. ';
                $message .= 'Por favor, configure todas as opÃ§Ãµes antes de alterar o agendamento para este mÃ©dico.';
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', $message);
            }
            
            $calendar = $doctor->getPrimaryCalendar();
            
            if (!$calendar) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'O mÃ©dico selecionado nÃ£o possui um calendÃ¡rio cadastrado. Por favor, cadastre um calendÃ¡rio para este mÃ©dico primeiro.');
            }
            
            $data['calendar_id'] = $calendar->id;
            // Garantir que doctor_id estÃ¡ definido explicitamente
            $data['doctor_id'] = $doctor->id;
        }

        // Aplicar lÃ³gica de appointment_mode baseado na configuraÃ§Ã£o
        $mode = \App\Models\Tenant\TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        if ($mode === 'presencial') {
            $data['appointment_mode'] = 'presencial';
        } elseif ($mode === 'online') {
            $data['appointment_mode'] = 'online';
        } else { // user_choice
            $data['appointment_mode'] = $request->appointment_mode ?? $appointment->appointment_mode ?? 'presencial';
        }

        $appointment->update($data);

        // Sincronizar com Google Calendar se o mÃ©dico tiver token
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
            abort(403, 'VocÃª nÃ£o tem permissÃ£o para excluir agendamentos.');
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

    public function confirm(Request $request, $slug, $appointment)
    {
        $appointment = Appointment::findOrFail($appointment);

        if (!$appointment->isHold()) {
            return redirect()->back()->with('error', 'Apenas agendamentos pendentes podem ser confirmados.');
        }

        if (!$appointment->confirmation_expires_at || now()->gte($appointment->confirmation_expires_at)) {
            $appointment->update([
                'status' => 'expired',
                'expired_at' => now(),
                'confirmation_expires_at' => null,
            ]);

            $this->notificationDispatcher->dispatchAppointment(
                $appointment,
                'appointment.expired',
                ['event' => 'appointment_expired_on_confirm_attempt']
            );

            $this->waitlistService->onSlotReleased(
                $appointment->doctor_id,
                $appointment->starts_at,
                $appointment->ends_at
            );

            return redirect()->back()->with('error', 'O prazo de confirmacao deste agendamento expirou.');
        }

        $appointment->update([
            'status' => 'scheduled',
            'confirmed_at' => now(),
            'confirmation_expires_at' => null,
            'expired_at' => null,
            'canceled_at' => null,
            'cancellation_reason' => null,
        ]);

        $this->notificationDispatcher->dispatchAppointment(
            $appointment,
            'appointment.confirmed',
            ['event' => 'appointment_confirmed']
        );

        return redirect()->back()->with('success', 'Agendamento confirmado com sucesso.');
    }

    public function cancel(Request $request, $slug, $appointment)
    {
        $appointment = Appointment::findOrFail($appointment);
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if (in_array($appointment->status, ['canceled', 'cancelled'], true)) {
            return redirect()->back()->with('info', 'Este agendamento ja esta cancelado.');
        }

        $appointment->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'cancellation_reason' => $validated['reason'] ?? null,
            'confirmation_expires_at' => null,
            'expired_at' => null,
        ]);

        $this->notificationDispatcher->dispatchAppointment(
            $appointment,
            'appointment.canceled',
            ['event' => 'appointment_canceled']
        );

        $this->waitlistService->onSlotReleased(
            $appointment->doctor_id,
            $appointment->starts_at,
            $appointment->ends_at
        );

        return redirect()->back()->with('success', 'Agendamento cancelado com sucesso.');
    }

    public function events(Request $request, $slug, $id)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->load('doctor.user');
        
        // Se for uma requisiÃ§Ã£o AJAX, esperando JSON, ou se tiver parÃ¢metros de query do FullCalendar (start/end), retorna JSON
        $isFullCalendarRequest = $request->has('start') || $request->has('end') || 
                                 $request->ajax() || $request->wantsJson() || $request->expectsJson() ||
                                 str_contains($request->header('Accept', ''), 'application/json');
        
        if ($isFullCalendarRequest) {
            $user = Auth::guard('tenant')->user();
            
            // Query base para buscar os agendamentos do calendÃ¡rio
            $query = Appointment::where('calendar_id', $calendar->id)
                ->with(['patient', 'type', 'specialty']);
            
            // Admin pode ver todos os eventos, outros roles tÃªm restriÃ§Ãµes
            if ($user->role !== 'admin') {
                // Verifica permissÃ£o para visualizar os eventos
                if ($user->role === 'doctor' && $user->doctor) {
                    // MÃ©dico sÃ³ pode ver eventos do seu prÃ³prio calendÃ¡rio
                    if ($calendar->doctor_id !== $user->doctor->id) {
                        return response()->json([]);
                    }
                } elseif ($user->role === 'user') {
                    // UsuÃ¡rio comum precisa ter permissÃ£o para ver o mÃ©dico
                    if (!$user->belongsToUser($calendar->doctor_id)) {
                        return response()->json([]);
                    }
                }
            }
            
            // Obter parÃ¢metros de data do FullCalendar (se disponÃ­veis)
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
            
            // Adicionar agendamentos recorrentes que ainda nÃ£o foram gerados
            $recurringEvents = $this->getRecurringAppointmentEvents($calendar->doctor_id, $startDate, $endDate);
            $events = $events->merge($recurringEvents);
            
            return response()->json($events);
        }
        
        // Retorna a view para acesso direto - mas primeiro verifica permissÃµes
        $user = Auth::guard('tenant')->user();
        
        // Admin pode ver todos os calendÃ¡rios, outros roles tÃªm restriÃ§Ãµes
        if ($user->role !== 'admin') {
            // Verifica permissÃ£o para visualizar o calendÃ¡rio
            if ($user->role === 'doctor' && $user->doctor) {
                // MÃ©dico sÃ³ pode ver seu prÃ³prio calendÃ¡rio
                if ($calendar->doctor_id !== $user->doctor->id) {
                    abort(403, 'VocÃª nÃ£o tem permissÃ£o para visualizar este calendÃ¡rio.');
                }
            } elseif ($user->role === 'user') {
                // UsuÃ¡rio comum precisa ter permissÃ£o para ver o mÃ©dico
                if (!$user->belongsToUser($calendar->doctor_id)) {
                    abort(403, 'VocÃª nÃ£o tem permissÃ£o para visualizar este calendÃ¡rio.');
                }
            } else {
                // UsuÃ¡rio nÃ£o autenticado ou role invÃ¡lido
                abort(403, 'VocÃª precisa estar autenticado para visualizar este calendÃ¡rio.');
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
     * Gera eventos de agendamentos recorrentes para exibiÃ§Ã£o no calendÃ¡rio
     */
    private function getRecurringAppointmentEvents($doctorId, Carbon $startDate, Carbon $endDate): \Illuminate\Support\Collection
    {
        $events = collect();

        // Buscar recorrÃªncias ativas do mÃ©dico
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

            // Processar cada regra da recorrÃªncia
            foreach ($recurring->rules as $rule) {
                $weekdayNumber = $rule->getWeekdayNumber();
                
                // Gerar eventos para cada ocorrÃªncia no perÃ­odo
                $currentDate = $startDate->copy();
                
                // Encontrar primeira ocorrÃªncia do dia da semana no perÃ­odo
                $daysUntilWeekday = ($weekdayNumber - $currentDate->dayOfWeek + 7) % 7;
                if ($daysUntilWeekday > 0) {
                    $currentDate->addDays($daysUntilWeekday);
                }
                
                // Garantir que a data nÃ£o seja anterior Ã  data inicial da recorrÃªncia
                if ($currentDate->lt($recurring->start_date)) {
                    $currentDate = $recurring->start_date->copy();
                    $daysToAdd = ($weekdayNumber - $currentDate->dayOfWeek + 7) % 7;
                    if ($daysToAdd > 0) {
                        $currentDate->addDays($daysToAdd);
                    }
                }

                // Gerar eventos atÃ© o final do perÃ­odo ou atÃ© a data final da recorrÃªncia
                while ($currentDate->lte($endDate)) {
                    // Verificar limites da recorrÃªncia
                    if ($recurring->end_type === 'date' && $recurring->end_date && $currentDate->gt($recurring->end_date)) {
                        break;
                    }

                    if ($recurring->end_type === 'total_sessions' && $recurring->total_sessions) {
                        $generatedCount = $recurring->getGeneratedSessionsCount();
                        if ($generatedCount >= $recurring->total_sessions) {
                            break;
                        }
                    }

                    // Verificar se jÃ¡ existe um agendamento gerado para esta data e horÃ¡rio
                    $startDateTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $rule->start_time);
                    $endDateTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $rule->end_time);
                    
                    $existingAppointment = Appointment::where('recurring_appointment_id', $recurring->id)
                        ->whereDate('starts_at', $currentDate->format('Y-m-d'))
                        ->whereTime('starts_at', $rule->start_time)
                        ->whereTime('ends_at', $rule->end_time)
                        ->first();

                    // Se nÃ£o existe, criar evento virtual para exibiÃ§Ã£o
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

                    // AvanÃ§ar para a prÃ³xima semana
                    $currentDate->addWeek();
                }
            }
        }

        return $events;
    }

    /**
     * API: Buscar calendÃ¡rios por mÃ©dico
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
     * API: Buscar tipos de consulta por mÃ©dico
     */
    public function getAppointmentTypesByDoctor($slug, $doctorId)
    {
        try {
            if (!$this->findAccessibleDoctor($doctorId)) {
                return response()->json([]);
            }

            // Retorna apenas os tipos de consulta do mÃ©dico especÃ­fico
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
            \Log::error('Erro ao buscar tipos de consulta por mÃ©dico', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage()
            ]);
            
            // Retornar array vazio em caso de erro
            return response()->json([]);
        }
    }

    /**
     * API: Buscar especialidades por mÃ©dico
     */
    public function getSpecialtiesByDoctor($slug, $doctorId)
    {
        try {
            $doctor = $this->findAccessibleDoctor($doctorId);
            if (!$doctor) {
                return response()->json([]);
            }
            
            \Log::info('Buscando especialidades para mÃ©dico', [
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
            \Log::error('Erro ao buscar especialidades por mÃ©dico', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retornar array vazio em caso de erro
            return response()->json([]);
        }
    }

    /**
     * API: Buscar horÃ¡rios disponÃ­veis para um mÃ©dico em uma data especÃ­fica
     */
    public function getAvailableSlots(Request $request, $slug, $doctorId)
    {
        $request->validate([
            'date' => 'required|date',
            'appointment_type_id' => 'nullable|exists:tenant.appointment_types,id',
        ]);

        if (!$this->findAccessibleDoctor($doctorId)) {
            return response()->json([
                'slots' => [],
                'available' => [],
            ]);
        }

        $date = Carbon::parse($request->date, 'America/Campo_Grande')->startOfDay();
        $todayCampoGrande = Carbon::now('America/Campo_Grande')->startOfDay();
        if ($date->lt($todayCampoGrande)) {
            return response()->json([
                'message' => 'NÃ£o Ã© possÃ­vel buscar horÃ¡rios para uma data passada. Selecione hoje ou uma data futura.',
            ], 422);
        }

        $weekday = $date->dayOfWeek; // 0 = Domingo, 6 = SÃ¡bado

        $businessHours = BusinessHour::where('doctor_id', $doctorId)
            ->where('weekday', $weekday)
            ->orderBy('start_time')
            ->get();

        if ($businessHours->isEmpty()) {
            return response()->json([
                'slots' => [],
                'available' => [],
            ]);
        }

        $calendars = Calendar::where('doctor_id', $doctorId)->pluck('id');

        $existingAppointments = Appointment::whereIn('calendar_id', $calendars)
            ->whereDate('starts_at', $date->format('Y-m-d'))
            ->whereIn('status', ['scheduled', 'rescheduled', 'pending_confirmation'])
            ->get(['id', 'starts_at', 'ends_at', 'status', 'confirmation_expires_at']);

        $duration = 30;
        if ($request->appointment_type_id) {
            $appointmentType = AppointmentType::find($request->appointment_type_id);
            if ($appointmentType) {
                $duration = $appointmentType->duration_min;
            }
        }

        $slots = [];
        $availableSlots = [];

        foreach ($businessHours as $businessHour) {
            $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->start_time);
            $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->end_time);

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

                $slotPayload = [
                    'label' => $slotStart->format('H:i'),
                    'starts_at' => $slotStart->format('Y-m-d H:i:s'),
                    'ends_at' => $slotEnd->format('Y-m-d H:i:s'),
                    'status' => 'FREE',
                ];

                $isInBreak = false;
                if ($breakStartTime && $breakEndTime) {
                    $isInBreak = ($slotStart->lt($breakEndTime) && $slotEnd->gt($breakStartTime));
                }

                if ($isInBreak) {
                    $slotPayload['status'] = 'DISABLED';
                    $slotPayload['reason'] = 'BREAK';
                    $slots[] = $slotPayload;
                    $currentSlot->addMinutes($duration);
                    continue;
                }

                if ($this->isSlotBlockedByRecurring($doctorId, $date, $slotStart, $slotEnd)) {
                    $slotPayload['status'] = 'DISABLED';
                    $slotPayload['reason'] = 'RECURRING_BLOCK';
                    $slots[] = $slotPayload;
                    $currentSlot->addMinutes($duration);
                    continue;
                }

                $holdAppointment = $existingAppointments->first(function ($appointment) use ($slotStart, $slotEnd) {
                    if ($appointment->status !== 'pending_confirmation') {
                        return false;
                    }

                    $apptStart = Carbon::parse($appointment->starts_at);
                    $apptEnd = Carbon::parse($appointment->ends_at);
                    return $slotStart->lt($apptEnd) && $slotEnd->gt($apptStart);
                });

                if ($holdAppointment) {
                    $slotPayload['status'] = 'HOLD';
                    $slotPayload['appointment_id'] = $holdAppointment->id;
                    if ($holdAppointment->confirmation_expires_at) {
                        $slotPayload['hold_expires_at'] = $holdAppointment->confirmation_expires_at->format('Y-m-d H:i:s');
                    }
                } else {
                    $busyAppointment = $existingAppointments->first(function ($appointment) use ($slotStart, $slotEnd) {
                        if (!in_array($appointment->status, ['scheduled', 'rescheduled'], true)) {
                            return false;
                        }

                        $apptStart = Carbon::parse($appointment->starts_at);
                        $apptEnd = Carbon::parse($appointment->ends_at);
                        return $slotStart->lt($apptEnd) && $slotEnd->gt($apptStart);
                    });

                    if ($busyAppointment) {
                        $slotPayload['status'] = 'BUSY';
                        $slotPayload['appointment_id'] = $busyAppointment->id;
                    }
                }

                if ($slotPayload['status'] === 'FREE') {
                    $availableSlots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'datetime_start' => $slotStart->toIso8601String(),
                        'datetime_end' => $slotEnd->toIso8601String(),
                    ];
                }

                $slots[] = $slotPayload;
                $currentSlot->addMinutes($duration);
            }
        }

        return response()->json([
            'slots' => $slots,
            'available' => $availableSlots,
        ]);
    }

    /**
     * Verifica se um slot estÃ¡ bloqueado por uma recorrÃªncia ativa
     */
    private function isSlotBlockedByRecurring($doctorId, Carbon $date, Carbon $slotStart, Carbon $slotEnd): bool
    {
        $weekday = $date->dayOfWeek;
        $weekdayString = RecurringAppointmentRule::weekdayFromNumber($weekday);
        $slotStartTime = $slotStart->format('H:i');
        $slotEndTime = $slotEnd->format('H:i');

        // Buscar recorrÃªncias ativas do mÃ©dico que tÃªm regras para este dia da semana
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

        // Verificar se alguma recorrÃªncia ainda estÃ¡ dentro dos limites e bloqueia o slot
        foreach ($recurringAppointments as $recurring) {
            if (!$recurring->isActive()) {
                continue;
            }

            // Verificar se ainda nÃ£o atingiu o limite de sessÃµes
            if ($recurring->end_type === 'total_sessions' && $recurring->total_sessions) {
                $generatedCount = $recurring->getGeneratedSessionsCount();
                if ($generatedCount >= $recurring->total_sessions) {
                    continue; // JÃ¡ atingiu o limite
                }
            }

            // Verificar se a data estÃ¡ dentro do perÃ­odo vÃ¡lido
            if ($recurring->end_type === 'date' && $recurring->end_date && $date->gt($recurring->end_date)) {
                continue; // Data fora do perÃ­odo
            }

            // Verificar se alguma regra desta recorrÃªncia bloqueia o slot (verifica sobreposiÃ§Ã£o)
            foreach ($recurring->rules as $rule) {
                if ($rule->weekday !== $weekdayString) {
                    continue;
                }

                // Converter horÃ¡rios para Carbon para comparaÃ§Ã£o precisa
                // Normalizar o formato do horÃ¡rio (pode vir como "HH:MM:SS" ou "HH:MM")
                $ruleStartTime = $rule->start_time;
                $ruleEndTime = $rule->end_time;
                
                // Normalizar para formato HH:MM:SS se necessÃ¡rio
                if (strlen($ruleStartTime) === 5) {
                    $ruleStartTime .= ':00';
                }
                if (strlen($ruleEndTime) === 5) {
                    $ruleEndTime .= ':00';
                }
                
                // Criar objetos Carbon com a data e horÃ¡rio, zerando segundos e microsegundos para comparaÃ§Ã£o precisa
                try {
                    $ruleStart = Carbon::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d') . ' ' . $ruleStartTime)->startOfMinute();
                    $ruleEnd = Carbon::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d') . ' ' . $ruleEndTime)->startOfMinute();
                } catch (\Exception $e) {
                    // Fallback: usar parse se createFromFormat falhar
                    $ruleStart = Carbon::parse($date->format('Y-m-d') . ' ' . $ruleStartTime)->startOfMinute();
                    $ruleEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $ruleEndTime)->startOfMinute();
                }
                
                // Garantir que os slots tambÃ©m estÃ£o normalizados
                $normalizedSlotStart = $slotStart->copy()->startOfMinute();
                $normalizedSlotEnd = $slotEnd->copy()->startOfMinute();

                // Verifica sobreposiÃ§Ã£o de intervalos de tempo:
                // Dois intervalos [a, b) e [c, d) se sobrepÃµem se: a < d && b > c
                // Onde o fim do intervalo Ã© exclusivo (um agendamento 08:00-09:00 termina em 09:00:00, mas nÃ£o inclui 09:00:00)
                // EntÃ£o o prÃ³ximo slot pode comeÃ§ar exatamente em 09:00:00
                // IMPORTANTE: Se a regra termina Ã s 09:00:00 e o slot comeÃ§a Ã s 09:00:00, NÃƒO hÃ¡ sobreposiÃ§Ã£o
                // Por isso usamos > (maior que) e nÃ£o >= (maior ou igual)
                // 
                // Exemplo:
                // - Regra: 08:00:00 - 09:00:00
                // - Slot: 09:00:00 - 10:00:00
                // - VerificaÃ§Ã£o: 08:00:00 < 10:00:00 (true) && 09:00:00 > 09:00:00 (false) = false (nÃ£o bloqueia) âœ“
                $hasOverlap = $ruleStart->lt($normalizedSlotEnd) && $ruleEnd->gt($normalizedSlotStart);
                
                if ($hasOverlap) {
                    return true; // HÃ¡ sobreposiÃ§Ã£o, o slot estÃ¡ bloqueado
                }
            }
        }

        return false;
    }

    /**
     * API: Buscar dias trabalhados (business hours) do mÃ©dico
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
                2 => 'TerÃ§a-feira',
                3 => 'Quarta-feira',
                4 => 'Quinta-feira',
                5 => 'Sexta-feira',
                6 => 'SÃ¡bado',
            ];

            // Agrupar por weekday
            $grouped = $businessHours->groupBy('weekday');
            
            // Criar array com todos os dias da semana (mesmo que nÃ£o tenham horÃ¡rios)
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
            \Log::error('Erro ao buscar dias trabalhados do mÃ©dico', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erro ao buscar dias trabalhados do mÃ©dico: ' . $e->getMessage(),
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
     * API: Buscar mÃƒÂ©dicos por texto (nome/registro/especialidade)
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
                $name = $doctor->user?->name_full ?: $doctor->user?->name ?: 'MÃƒÂ©dico';
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

    private function generateUniqueConfirmationToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Appointment::where('confirmation_token', $token)->exists());

        return $token;
    }

    private function findAccessibleDoctor(string $doctorId): ?Doctor
    {
        $query = Doctor::with('user');
        $this->applyDoctorFilter($query);

        return $query->where('id', $doctorId)->first();
    }
}

