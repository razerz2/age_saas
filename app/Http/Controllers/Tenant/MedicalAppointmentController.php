<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormResponse;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MedicalAppointmentController extends Controller
{
    /**
     * Exibe tela inicial com seleção de data e médico (se necessário)
     */
    public function index()
    {
        $user = Auth::guard('tenant')->user();
        $doctors = collect();

        // Admin: pode selecionar qualquer médico
        if ($user->role === 'admin') {
            $doctors = Doctor::with('user')
                ->whereHas('user', function($query) {
                    $query->where('status', 'active');
                })
                ->orderBy('id')
                ->get();
        }
        // Usuário comum: pode selecionar apenas médicos relacionados
        elseif ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                $doctors = Doctor::with('user')
                    ->whereIn('id', $allowedDoctorIds)
                    ->whereHas('user', function($query) {
                        $query->where('status', 'active');
                    })
                    ->orderBy('id')
                    ->get();
            }
        }
        // Médico: não precisa selecionar (já tem seu médico)

        return view('tenant.medical_appointments.index', compact('doctors'));
    }

    /**
     * Recebe a data escolhida e redireciona para a sessão de atendimento
     */
    public function start(Request $request)
    {
        $user = Auth::guard('tenant')->user();
        
        $rules = [
            'date' => 'required|date',
        ];

        // Admin e usuário comum precisam selecionar médico (se tiverem médicos disponíveis)
        if ($user->role === 'admin' || $user->role === 'user') {
            // Verificar se há médicos disponíveis
            if ($user->role === 'admin') {
                $hasDoctors = Doctor::whereHas('user', function($query) {
                    $query->where('status', 'active');
                })->exists();
            } else {
                $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
                $hasDoctors = !empty($allowedDoctorIds);
            }
            
            if ($hasDoctors) {
                $rules['doctor_ids'] = 'required|array|min:1';
                $rules['doctor_ids.*'] = 'required|exists:tenant.doctors,id';
            }
        }

        $request->validate($rules);

        $date = Carbon::parse($request->date)->format('Y-m-d');
        
        // Validar permissão do usuário comum
        if ($user->role === 'user' && $request->doctor_ids) {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            $invalidDoctorIds = array_diff($request->doctor_ids, $allowedDoctorIds);
            
            if (!empty($invalidDoctorIds)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Você não tem permissão para acessar um ou mais médicos selecionados.');
            }
        }

        // Para médico, usar o doctor_id do próprio usuário
        $doctorIds = $request->doctor_ids ?? ($user->doctor ? [$user->doctor->id] : []);

        // Construir URL com múltiplos doctor_ids
        $url = route('tenant.medical-appointments.session', ['date' => $date]);
        if (!empty($doctorIds)) {
            // Adicionar cada doctor_id como parâmetro separado na query string
            $queryParams = [];
            foreach ($doctorIds as $doctorId) {
                $queryParams[] = 'doctor_ids[]=' . urlencode($doctorId);
            }
            $url .= '?' . implode('&', $queryParams);
        }

        return redirect($url);
    }

    /**
     * Exibe a tela de atendimento do dia
     */
    public function session($date, Request $request)
    {
        // Garantir que o tenant está ativo e a conexão configurada
        $this->ensureTenantConnection();

        $user = Auth::guard('tenant')->user();
        $dateCarbon = Carbon::parse($date);
        
        // Obter doctor_ids da query string (pode ser array ou string única)
        $doctorIdsParam = $request->get('doctor_ids', []);
        if (!is_array($doctorIdsParam)) {
            // Se for string única, converter para array
            $doctorIdsParam = $doctorIdsParam ? [$doctorIdsParam] : [];
        }
        $doctorIds = array_filter($doctorIdsParam); // Remove valores vazios

        // Buscar agendamentos do dia
        $query = Appointment::with(['calendar.doctor.user', 'patient', 'type', 'specialty'])
            ->forDay($dateCarbon)
            ->whereIn('status', ['scheduled', 'confirmed', 'arrived', 'in_service']);

        // Aplicar filtros baseado no role e permissões
        if ($user->role === 'doctor') {
            // Buscar o doctor diretamente do banco para garantir que existe
            $doctor = Doctor::where('user_id', $user->id)->first();
            
            if ($doctor) {
                $doctorIds = [$doctor->id];
                Log::info('Filtrando agendamentos para médico', [
                    'user_id' => $user->id,
                    'doctor_ids' => $doctorIds,
                ]);
                
                // Filtrar diretamente pelo doctor_id do calendar
                $query->whereHas('calendar', function($q) use ($doctorIds) {
                    $q->whereIn('doctor_id', $doctorIds);
                });
            } else {
                // Se o usuário tem role doctor mas não tem vínculo com médico, não mostra nada
                Log::warning('Usuário com role doctor mas sem registro na tabela doctors', [
                    'user_id' => $user->id,
                ]);
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'admin') {
            // Admin: filtrar por médicos selecionados se fornecidos
            if (!empty($doctorIds)) {
                Log::info('Filtrando agendamentos para admin por médicos específicos', [
                    'user_id' => $user->id,
                    'doctor_ids' => $doctorIds,
                ]);
                
                $query->whereHas('calendar', function($q) use ($doctorIds) {
                    $q->whereIn('doctor_id', $doctorIds);
                });
            }
            // Se não fornecido, admin vê tudo (sem filtro adicional)
        } elseif ($user->role === 'user') {
            // Usuário comum: filtrar por médicos selecionados e validar permissão
            if (!empty($doctorIds)) {
                $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
                $invalidDoctorIds = array_diff($doctorIds, $allowedDoctorIds);
                
                if (!empty($invalidDoctorIds)) {
                    // Sem permissão para um ou mais médicos
                    Log::warning('Usuário comum tentando acessar médicos sem permissão', [
                        'user_id' => $user->id,
                        'doctor_ids' => $doctorIds,
                        'invalid_ids' => $invalidDoctorIds,
                    ]);
                    abort(403, 'Você não tem permissão para acessar um ou mais médicos selecionados.');
                }
                
                Log::info('Filtrando agendamentos para usuário comum por médicos específicos', [
                    'user_id' => $user->id,
                    'doctor_ids' => $doctorIds,
                ]);
                
                $query->whereHas('calendar', function($q) use ($doctorIds) {
                    $q->whereIn('doctor_id', $doctorIds);
                });
            } else {
                // Se não fornecido, usar lista de médicos permitidos
                $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
                if (!empty($allowedDoctorIds)) {
                    Log::info('Filtrando agendamentos para usuário comum', [
                        'user_id' => $user->id,
                        'allowed_doctor_ids' => $allowedDoctorIds
                    ]);
                    
                    $query->whereHas('calendar', function($q) use ($allowedDoctorIds) {
                        $q->whereIn('doctor_id', $allowedDoctorIds);
                    });
                } else {
                    // Se não tem médicos permitidos, não mostra nada
                    Log::info('Usuário comum sem médicos permitidos', [
                        'user_id' => $user->id
                    ]);
                    $query->whereRaw('1 = 0');
                }
            }
        }

        // Ordenar por horário de início (starts_at)
        $appointments = $query->orderBy('starts_at', 'asc')->get();

        Log::info('Agendamentos encontrados', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'doctor_ids' => $doctorIds,
            'count' => $appointments->count(),
        ]);

        return view('tenant.medical_appointments.session', compact('appointments', 'date', 'doctorIds'));
    }

    /**
     * Retorna detalhes de um agendamento via AJAX
     */
    public function details($appointment)
    {
        try {
            // Garantir que o tenant está ativo e a conexão configurada
            $this->ensureTenantConnection();

            $user = Auth::guard('tenant')->user();

            if (!$user) {
                abort(401, 'Usuário não autenticado.');
            }

            // Se $appointment não for uma instância de Appointment, buscar pelo ID
            if (!$appointment instanceof Appointment) {
                $appointment = Appointment::findOrFail($appointment);
            }

            // Carregar relacionamentos necessários primeiro
            $appointment->load(['calendar.doctor.user', 'patient', 'type', 'specialty']);

            // Verificar se o agendamento tem calendar
            if (!$appointment->calendar) {
                Log::warning('Agendamento sem calendário', ['appointment_id' => $appointment->id]);
                abort(404, 'Calendário não encontrado para este agendamento.');
            }

            // Verificar se o calendar tem doctor
            if (!$appointment->calendar->doctor) {
                Log::warning('Calendário sem médico', [
                    'appointment_id' => $appointment->id,
                    'calendar_id' => $appointment->calendar->id
                ]);
                abort(404, 'Médico não encontrado para este calendário.');
            }

            // Verificar permissão de acesso
            // Admin pode ver tudo
            if ($user->role !== 'admin') {
                $hasPermission = false;
                
                if ($user->role === 'doctor') {
                    // Carregar relacionamento doctor do usuário se necessário
                    if (!$user->relationLoaded('doctor')) {
                        $user->load('doctor');
                    }
                    
                    if ($user->doctor && (string)$appointment->calendar->doctor_id === (string)$user->doctor->id) {
                        $hasPermission = true;
                    } else {
                        Log::warning('Acesso negado: médico não autorizado', [
                            'user_id' => $user->id,
                            'user_doctor_id' => $user->doctor->id ?? null,
                            'appointment_doctor_id' => $appointment->calendar->doctor_id ?? null,
                            'comparison' => [
                                'doctor_id_type' => gettype($appointment->calendar->doctor_id),
                                'user_doctor_id_type' => gettype($user->doctor->id ?? null),
                                'equal' => $user->doctor ? ((string)$appointment->calendar->doctor_id === (string)$user->doctor->id) : false
                            ]
                        ]);
                    }
                } elseif ($user->role === 'user') {
                    $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
                    // Converter para string para comparação
                    $allowedDoctorIdsStr = array_map('strval', $allowedDoctorIds);
                    $appointmentDoctorIdStr = (string)($appointment->calendar->doctor_id ?? '');
                    
                    if (!empty($allowedDoctorIdsStr) && in_array($appointmentDoctorIdStr, $allowedDoctorIdsStr)) {
                        $hasPermission = true;
                    } else {
                        Log::warning('Acesso negado: usuário comum sem permissão para o médico', [
                            'user_id' => $user->id,
                            'allowed_doctor_ids' => $allowedDoctorIds,
                            'allowed_doctor_ids_str' => $allowedDoctorIdsStr,
                            'appointment_doctor_id' => $appointment->calendar->doctor_id ?? null,
                            'appointment_doctor_id_str' => $appointmentDoctorIdStr,
                            'in_array_result' => in_array($appointmentDoctorIdStr, $allowedDoctorIdsStr)
                        ]);
                    }
                } else {
                    // Role desconhecido
                    Log::warning('Acesso negado: role desconhecido', [
                        'user_id' => $user->id,
                        'role' => $user->role
                    ]);
                }
                
                if (!$hasPermission) {
                    if (request()->ajax() || request()->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Você não tem permissão para visualizar este agendamento.'
                        ], 403);
                    }
                    abort(403, 'Você não tem permissão para visualizar este agendamento.');
                }
            }

            // Verificar se existe formulário ativo para este agendamento
            $form = Form::getFormForAppointment($appointment);
            $formResponse = null;
            
            if ($form) {
                // Buscar resposta do formulário para este agendamento
                $formResponse = FormResponse::where('form_id', $form->id)
                    ->where('appointment_id', $appointment->id)
                    ->where('patient_id', $appointment->patient_id)
                    ->where('status', 'submitted')
                    ->first();
            }

            return view('tenant.medical_appointments.partials.details', compact('appointment', 'form', 'formResponse'));
        } catch (\Exception $e) {
            // Log do erro para debug
            \Log::error('Erro ao carregar detalhes do agendamento', [
                'appointment_id' => $appointment->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retornar uma view de erro amigável
            if (request()->ajax() || request()->wantsJson()) {
                return response()->view('tenant.medical_appointments.partials.error', [
                    'message' => 'Erro ao carregar detalhes do agendamento: ' . $e->getMessage()
                ], 500);
            }

            abort(500, 'Erro ao carregar detalhes do agendamento.');
        }
    }

    /**
     * Atualiza o status do agendamento
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        // Garantir que o tenant está ativo e a conexão configurada
        $this->ensureTenantConnection();

        $user = Auth::guard('tenant')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.'
            ], 401);
        }

        // Verificar permissão
        $this->checkPermission($user, $appointment);

        $request->validate([
            'status' => 'required|in:scheduled,arrived,in_service,completed,cancelled',
        ]);

        $appointment->update([
            'status' => $request->status,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso.',
                'appointment' => $appointment->load(['calendar.doctor.user', 'patient', 'type', 'specialty']),
            ]);
        }

        return redirect()->back()->with('success', 'Status atualizado com sucesso.');
    }

    /**
     * Conclui o atendimento e redireciona para o próximo
     */
    public function complete(Appointment $appointment)
    {
        // Garantir que o tenant está ativo e a conexão configurada
        $this->ensureTenantConnection();

        $user = Auth::guard('tenant')->user();

        // Verificar permissão
        $this->checkPermission($user, $appointment);

        // Marcar como concluído
        $appointment->update([
            'status' => 'completed',
        ]);

        // Buscar próximo agendamento do dia
        $date = $appointment->starts_at->format('Y-m-d');

        $query = Appointment::with(['calendar.doctor.user', 'patient', 'type', 'specialty'])
            ->forDay(Carbon::parse($date))
            ->whereIn('status', ['scheduled', 'confirmed', 'arrived', 'in_service'])
            ->where('starts_at', '>', $appointment->starts_at);

        // Aplicar mesmos filtros de permissão
        if ($user->role === 'doctor' && $user->doctor) {
            $query->whereHas('calendar', function($q) use ($user) {
                $q->where('doctor_id', $user->doctor->id);
            });
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                $query->whereHas('calendar', function($q) use ($allowedDoctorIds) {
                    $q->whereIn('doctor_id', $allowedDoctorIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $nextAppointment = $query->orderBy('starts_at', 'asc')->first();

        if ($nextAppointment) {
            return redirect()
                ->route('tenant.medical-appointments.session', ['date' => $date])
                ->with('selected_appointment', $nextAppointment->id)
                ->with('success', 'Atendimento concluído. Próximo agendamento selecionado.');
        }

        return redirect()
            ->route('tenant.medical-appointments.session', ['date' => $date])
            ->with('info', 'Atendimento concluído. Não há mais agendamentos para hoje.');
    }

    /**
     * Garante que a conexão do tenant está configurada
     */
    private function ensureTenantConnection()
    {
        // Se o tenant já está ativo, verificar se a conexão está configurada
        $currentTenant = Tenant::current();
        
        if ($currentTenant) {
            // Verificar se a conexão está configurada corretamente
            $connectionConfig = config('database.connections.tenant');
            
            if (empty($connectionConfig['password']) && !empty($currentTenant->db_password)) {
                // Reconfigurar a conexão se a senha não estiver configurada
                Config::set('database.connections.tenant.host', $currentTenant->db_host ?? env('DB_TENANT_HOST', '127.0.0.1'));
                Config::set('database.connections.tenant.port', $currentTenant->db_port ?? env('DB_TENANT_PORT', '5432'));
                Config::set('database.connections.tenant.database', $currentTenant->db_name);
                Config::set('database.connections.tenant.username', $currentTenant->db_username);
                Config::set('database.connections.tenant.password', $currentTenant->db_password ?? '');
                
                DB::purge('tenant');
                DB::reconnect('tenant');
            }
        } else {
            // Tentar ativar o tenant a partir do usuário autenticado
            $user = Auth::guard('tenant')->user();
            if ($user && $user->tenant) {
                $user->tenant->makeCurrent();
            } else {
                // Tentar ativar a partir da sessão
                $slug = session('tenant_slug');
                if ($slug) {
                    $tenant = Tenant::where('subdomain', $slug)->first();
                    if ($tenant) {
                        $tenant->makeCurrent();
                    }
                }
            }
        }
    }

    /**
     * Verifica se o usuário tem permissão para acessar o agendamento
     */
    private function checkPermission($user, Appointment $appointment)
    {
        // Garantir que o relacionamento calendar está carregado
        if (!$appointment->relationLoaded('calendar')) {
            $appointment->load('calendar');
        }

        // Verificar se o calendar existe
        if (!$appointment->calendar) {
            Log::warning('Agendamento sem calendário na verificação de permissão', [
                'appointment_id' => $appointment->id
            ]);
            abort(404, 'Calendário não encontrado para este agendamento.');
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'doctor') {
            // Carregar relacionamento doctor do usuário se necessário
            if (!$user->relationLoaded('doctor')) {
                $user->load('doctor');
            }
            
            if ($user->doctor && $appointment->calendar->doctor_id !== $user->doctor->id) {
                Log::warning('Acesso negado: médico não autorizado', [
                    'user_id' => $user->id,
                    'user_doctor_id' => $user->doctor->id ?? null,
                    'appointment_doctor_id' => $appointment->calendar->doctor_id
                ]);
                abort(403, 'Você não tem permissão para acessar este agendamento.');
            } elseif (!$user->doctor) {
                Log::warning('Acesso negado: usuário com role doctor mas sem vínculo com médico', [
                    'user_id' => $user->id
                ]);
                abort(403, 'Você não tem permissão para acessar este agendamento.');
            }
            return true;
        }

        if ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (empty($allowedDoctorIds) || !in_array($appointment->calendar->doctor_id, $allowedDoctorIds)) {
                Log::warning('Acesso negado: usuário comum sem permissão para o médico', [
                    'user_id' => $user->id,
                    'allowed_doctor_ids' => $allowedDoctorIds,
                    'appointment_doctor_id' => $appointment->calendar->doctor_id
                ]);
                abort(403, 'Você não tem permissão para acessar este agendamento.');
            }
            return true;
        }

        Log::warning('Acesso negado: role desconhecido', [
            'user_id' => $user->id,
            'role' => $user->role
        ]);
        abort(403, 'Você não tem permissão para acessar este agendamento.');
    }

    /**
     * Retorna a resposta do formulário para visualização no modal
     */
    public function getFormResponse($appointmentId)
    {
        try {
            // Garantir que o tenant está ativo e a conexão configurada
            $this->ensureTenantConnection();

            $user = Auth::guard('tenant')->user();
            $appointment = Appointment::findOrFail($appointmentId);

            // Verificar permissão
            $this->checkPermission($user, $appointment);

            // Buscar formulário ativo
            $form = Form::getFormForAppointment($appointment);
            
            if (!$form) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum formulário encontrado para este agendamento.'
                ], 404);
            }

            // Buscar resposta do formulário
            $formResponse = FormResponse::where('form_id', $form->id)
                ->where('appointment_id', $appointment->id)
                ->where('patient_id', $appointment->patient_id)
                ->where('status', 'submitted')
                ->with([
                    'form' => function($query) {
                        $query->with(['sections' => function($q) {
                            $q->orderBy('position');
                            $q->with(['questions' => function($q2) {
                                $q2->orderBy('position');
                                $q2->with('options');
                            }]);
                        }]);
                    },
                    'answers.question',
                    'patient'
                ])
                ->first();

            if (!$formResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resposta do formulário não encontrada.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'html' => view('tenant.medical_appointments.partials.form-response-modal', compact('formResponse'))->render()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar resposta do formulário', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar resposta do formulário: ' . $e->getMessage()
            ], 500);
        }
    }
}

