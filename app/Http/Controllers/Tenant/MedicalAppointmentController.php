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
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MedicalAppointmentController extends Controller
{
    private ?bool $hasQueuePositionColumn = null;
    private ?bool $hasQueueUpdatedAtColumn = null;

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
        $url = workspace_route('tenant.medical-appointments.session', ['date' => $date]);
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
    public function session($slug, $date, Request $request)
    {
        $this->ensureTenantConnection();

        $user = Auth::guard('tenant')->user();
        $dateCarbon = Carbon::parse($date);
        $doctorIds = $this->normalizeDoctorIdsFromRequest($request);

        $query = Appointment::with(['calendar.doctor.user', 'patient', 'type', 'specialty'])
            ->forDay($dateCarbon)
            ->whereIn('status', $this->dayQueueStatuses());

        $this->applyRoleDoctorFilter($query, $user, $doctorIds);

        if ($this->hasQueuePositionColumn()) {
            $query
                ->orderByRaw('CASE WHEN queue_position IS NULL THEN 1 ELSE 0 END')
                ->orderBy('queue_position', 'asc');
        }

        $appointments = $query
            ->orderBy('starts_at', 'asc')
            ->get();

        return view('tenant.medical_appointments.session', compact('appointments', 'date', 'doctorIds'));
    }

    /**
     * Retorna detalhes de um agendamento via AJAX
     */
    public function details($slug, $appointmentId)
    {
        try {
            // Garantir que o tenant está ativo e a conexão configurada
            $this->ensureTenantConnection();

            $user = Auth::guard('tenant')->user();

            if (!$user) {
                abort(401, 'Usuário não autenticado.');
            }

            // Se $appointment não for uma instância de Appointment, buscar pelo ID
            $appointment = $this->resolveAppointmentOrFail($slug, $appointmentId);

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
                'tenant_id' => tenant()?->id,
                'workspace_slug' => $slug,
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retornar uma view de erro amigável
            if (request()->ajax() || request()->wantsJson()) {
                if (
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ||
                    $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
                ) {
                    return response()->view('tenant.medical_appointments.partials.error', [
                        'message' => 'Agendamento nao encontrado para este workspace.'
                    ], 404);
                }

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
    public function updateStatusGet(Request $request, $slug, $appointmentId)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'ok' => false,
                'message' => 'Metodo GET nao permitido para alteracao de status. Use POST.',
            ], 405);
        }

        return redirect(workspace_route('tenant.medical-appointments.index'))
            ->with('error', 'Metodo invalido para alterar status. Tente novamente pela tela de atendimento.');
    }

    /**
     * Atualiza o status do agendamento
     */
    public function updateStatus(Request $request, $slug, $appointmentId)
    {
        $this->ensureTenantConnection();
        $user = Auth::guard('tenant')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'ok' => false,
                'message' => 'Usuario nao autenticado.',
            ], 401);
        }

        try {
            if (!Str::isUuid($appointmentId)) {
                return response()->json([
                    'success' => false,
                    'ok' => false,
                    'message' => 'Identificador de agendamento invalido.',
                ], 422);
            }

            $appointment = Appointment::with(['calendar.doctor.user', 'patient', 'type', 'specialty'])->find($appointmentId);
            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'ok' => false,
                    'message' => 'Agendamento nao encontrado.',
                ], 404);
            }

            $this->checkPermission($user, $appointment);

            $normalizedStatus = $this->normalizeIncomingMedicalStatus((string) $request->input('status', ''));
            if ($normalizedStatus === null) {
                return response()->json([
                    'success' => false,
                    'ok' => false,
                    'message' => 'Status invalido para alteracao.',
                    'errors' => [
                        'status' => ['Status invalido para alteracao.'],
                    ],
                ], 422);
            }

            $request->merge([
                'status' => $normalizedStatus,
            ]);

            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:arrived,in_service,completed,no_show,canceled,rescheduled',
                'note' => 'nullable|string|max:2000',
                'reschedule_at' => 'nullable|date',
                'current_date' => 'nullable|date_format:Y-m-d',
            ]);

            $validator->after(function ($validator) use ($request) {
                $status = (string) $request->input('status');
                $note = trim((string) $request->input('note', ''));
                $statusNeedsNote = in_array($status, ['no_show', 'canceled', 'rescheduled'], true);

                if ($statusNeedsNote && $note === '') {
                    $validator->errors()->add('note', 'Informe o motivo para o status selecionado.');
                }

                if ($status === 'rescheduled' && !$request->filled('reschedule_at')) {
                    $validator->errors()->add('reschedule_at', 'Informe a nova data e hora da remarcacao.');
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'ok' => false,
                    'message' => $validator->errors()->first() ?: 'Dados invalidos.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $targetStatus = (string) $request->input('status');
            $note = trim((string) $request->input('note', ''));
            $rescheduleAtRaw = $request->input('reschedule_at');

            $updateData = [
                'status' => $targetStatus,
            ];

            if ($targetStatus === 'canceled') {
                $updateData['canceled_at'] = now();
                $updateData['cancellation_reason'] = $note;
            } elseif ($targetStatus === 'no_show') {
                $updateData['canceled_at'] = null;
                $updateData['cancellation_reason'] = $note;
            } else {
                $updateData['canceled_at'] = null;
                $updateData['cancellation_reason'] = null;
            }

            if ($targetStatus === 'rescheduled') {
                $rescheduleAt = Carbon::parse((string) $rescheduleAtRaw);
                $durationMinutes = max(
                    5,
                    (int) (
                        $appointment->starts_at && $appointment->ends_at
                            ? $appointment->starts_at->diffInMinutes($appointment->ends_at)
                            : ($appointment->type->duration_min ?? 30)
                    )
                );

                $updateData['starts_at'] = $rescheduleAt;
                $updateData['ends_at'] = $rescheduleAt->copy()->addMinutes($durationMinutes);
                if ($this->hasQueuePositionColumn()) {
                    $updateData['queue_position'] = null;
                }
                if ($this->hasQueueUpdatedAtColumn()) {
                    $updateData['queue_updated_at'] = now();
                }
                $updateData['cancellation_reason'] = $note;
            }

            if ($note !== '') {
                $noteLine = sprintf('[%s] %s: %s', now()->format('d/m/Y H:i'), strtoupper($targetStatus), $note);
                $existingNotes = trim((string) ($appointment->notes ?? ''));
                $updateData['notes'] = $existingNotes === '' ? $noteLine : $existingNotes . PHP_EOL . $noteLine;
            }

            $appointment->update($updateData);
            $appointment->refresh()->load(['calendar.doctor.user', 'patient', 'type', 'specialty']);

            $currentDate = (string) $request->input('current_date', '');
            $removeFromDay = false;

            if ($currentDate !== '') {
                $removeFromDay = $appointment->starts_at?->format('Y-m-d') !== $currentDate
                    || !in_array($appointment->status, $this->dayQueueStatuses(), true);
            }

            return response()->json([
                'success' => true,
                'ok' => true,
                'message' => 'Status atualizado com sucesso.',
                'status' => $appointment->status,
                'label' => $appointment->status_translated,
                'remove_from_day' => $removeFromDay,
                'appointment' => $this->serializeAppointmentForSession($appointment),
            ]);
        } catch (HttpException $e) {
            return response()->json([
                'success' => false,
                'ok' => false,
                'message' => $e->getMessage() ?: 'Operacao nao permitida.',
            ], $e->getStatusCode());
        } catch (QueryException $e) {
            $traceId = (string) Str::uuid();
            $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());
            $constraintName = (string) ($e->errorInfo[2] ?? '');

            Log::error('Erro de banco em updateStatus de atendimento medico', [
                'trace_id' => $traceId,
                'tenant_id' => tenant()?->id,
                'workspace_slug' => $slug,
                'appointment_id' => $appointmentId,
                'user_id' => $user->id,
                'payload' => $request->all(),
                'sql_state' => $sqlState,
                'constraint' => $constraintName,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($sqlState === '23514' && str_contains($constraintName, 'appointments_status_check')) {
                return response()->json([
                    'success' => false,
                    'ok' => false,
                    'message' => 'Status nao permitido pela configuracao atual do banco para este tenant.',
                    'errors' => [
                        'status' => ['Status nao permitido para este tenant. Atualize as migrations de status.'],
                    ],
                    'trace_id' => $traceId,
                ], 422);
            }

            return response()->json([
                'success' => false,
                'ok' => false,
                'message' => 'Erro interno ao atualizar status.',
                'trace_id' => $traceId,
            ], 500);
        } catch (\Throwable $e) {
            $traceId = (string) Str::uuid();

            Log::error('Erro em updateStatus de atendimento medico', [
                'trace_id' => $traceId,
                'tenant_id' => tenant()?->id,
                'workspace_slug' => $slug,
                'appointment_id' => $appointmentId,
                'user_id' => $user->id,
                'payload' => $request->all(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'ok' => false,
                'message' => 'Erro interno ao atualizar status.',
                'trace_id' => $traceId,
            ], 500);
        }
    }

    /**
     * Persiste a ordem manual da fila do dia.
     */
    public function reorderQueue(Request $request, $slug, $date)
    {
        $this->ensureTenantConnection();
        $user = Auth::guard('tenant')->user();

        if (!$this->hasQueuePositionColumn()) {
            return response()->json([
                'success' => false,
                'ok' => false,
                'message' => 'Ordem manual indisponivel para este tenant. Execute as migrations do tenant e tente novamente.',
            ], 422);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'ok' => false,
                'message' => 'Usuario nao autenticado.',
            ], 401);
        }

        try {
            $validator = Validator::make($request->all(), [
                'ordered_ids' => 'required|array|min:1',
                'ordered_ids.*' => 'required|uuid',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'ok' => false,
                    'message' => $validator->errors()->first() ?: 'Ordem invalida.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $orderedIds = array_values(array_unique(array_map('strval', (array) $request->input('ordered_ids', []))));
            $doctorIds = $this->normalizeDoctorIdsFromRequest($request);

            $query = Appointment::query()
                ->forDay(Carbon::parse($date))
                ->whereIn('status', $this->dayQueueStatuses());

            $this->applyRoleDoctorFilter($query, $user, $doctorIds);

            $matchedIds = (clone $query)
                ->whereIn('id', $orderedIds)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            if (count($matchedIds) !== count($orderedIds)) {
                $invalidIds = array_values(array_diff($orderedIds, $matchedIds));

                return response()->json([
                    'success' => false,
                    'ok' => false,
                    'message' => 'A lista possui agendamentos invalidos para o dia/tenant.',
                    'errors' => [
                        'ordered_ids' => $invalidIds,
                    ],
                ], 422);
            }

            $hasQueueUpdatedAtColumn = $this->hasQueueUpdatedAtColumn();

            DB::connection('tenant')->transaction(function () use ($orderedIds, $hasQueueUpdatedAtColumn) {
                foreach ($orderedIds as $index => $appointmentId) {
                    $updateData = [
                        'queue_position' => $index + 1,
                    ];

                    if ($hasQueueUpdatedAtColumn) {
                        $updateData['queue_updated_at'] = now();
                    }

                    Appointment::where('id', $appointmentId)->update($updateData);
                }
            });

            return response()->json([
                'success' => true,
                'ok' => true,
                'message' => 'Ordem da fila atualizada.',
                'ordered_ids' => $orderedIds,
            ]);
        } catch (HttpException $e) {
            return response()->json([
                'success' => false,
                'ok' => false,
                'message' => $e->getMessage() ?: 'Operacao nao permitida.',
            ], $e->getStatusCode());
        } catch (\Throwable $e) {
            Log::error('Erro ao reordenar fila de atendimento', [
                'tenant_id' => tenant()?->id,
                'workspace_slug' => $slug,
                'date' => $date,
                'payload' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'ok' => false,
                'message' => 'Erro interno ao reordenar fila.',
            ], 500);
        }
    }
    /**
     * Conclui o atendimento e redireciona para o próximo
     */
    public function complete($slug, $appointmentId)
    {
        // Garantir que o tenant está ativo e a conexão configurada
        $this->ensureTenantConnection();

        $user = Auth::guard('tenant')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario nao autenticado.'
            ], 401);
        }

        if (!Str::isUuid($appointmentId)) {
            Log::warning('UUID invalido recebido em complete de atendimento medico', [
                'tenant_id' => tenant()?->id,
                'workspace_slug' => $slug,
                'appointment_id_received' => $appointmentId,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Identificador de agendamento invalido.'
            ], 422);
        }

        $appointment = Appointment::find($appointmentId);
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Agendamento nao encontrado.'
            ], 404);
        }

        // Verificar permissao
        $this->checkPermission($user, $appointment);

        // Marcar como concluído
        $appointment->update([
            'status' => 'completed',
        ]);

        // Buscar próximo agendamento do dia
        $date = $appointment->starts_at->format('Y-m-d');

        $query = Appointment::with(['calendar.doctor.user', 'patient', 'type', 'specialty'])
            ->forDay(Carbon::parse($date))
            ->whereIn('status', $this->dayQueueStatuses())
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
                ->to(workspace_route('tenant.medical-appointments.session', ['date' => $date]))
                ->with('selected_appointment', $nextAppointment->id)
                ->with('success', 'Atendimento concluído. Próximo agendamento selecionado.');
        }

        return redirect()
            ->to(workspace_route('tenant.medical-appointments.session', ['date' => $date]))
            ->with('info', 'Atendimento concluído. Não há mais agendamentos para hoje.');
    }
    /**
     * @return array<int, string>
     */
    private function dayQueueStatuses(): array
    {
        return ['scheduled', 'confirmed', 'arrived', 'in_service', 'rescheduled'];
    }

    private function normalizeIncomingMedicalStatus(string $input): ?string
    {
        $normalized = mb_strtolower(trim($input), 'UTF-8');
        $normalized = str_replace(
            ['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç', '-', ' '],
            ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c', '_', '_'],
            $normalized
        );

        $map = [
            'arrived' => 'arrived',
            'chegou' => 'arrived',
            'in_service' => 'in_service',
            'em_atendimento' => 'in_service',
            'completed' => 'completed',
            'concluido' => 'completed',
            'finalizado' => 'completed',
            'no_show' => 'no_show',
            'nao_compareceu' => 'no_show',
            'canceled' => 'canceled',
            'cancelado' => 'canceled',
            'cancelled' => 'canceled',
            'rescheduled' => 'rescheduled',
            'remarcado' => 'rescheduled',
        ];

        return $map[$normalized] ?? null;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeDoctorIdsFromRequest(Request $request): array
    {
        $doctorIdsParam = $request->input('doctor_ids', []);
        if (!is_array($doctorIdsParam)) {
            $doctorIdsParam = $doctorIdsParam ? [$doctorIdsParam] : [];
        }

        return array_values(array_filter(array_map('strval', $doctorIdsParam)));
    }

    /**
     * Aplica filtro de acesso por role na query de atendimento do dia.
     *
     * @param array<int, string> $doctorIds
     */
    private function applyRoleDoctorFilter(Builder $query, $user, array &$doctorIds): void
    {
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if (!$doctor) {
                $query->whereRaw('1 = 0');
                $doctorIds = [];
                return;
            }

            $doctorIds = [(string) $doctor->id];

            $query->whereHas('calendar', function ($calendarQuery) use ($doctorIds) {
                $calendarQuery->whereIn('doctor_id', $doctorIds);
            });
            return;
        }

        if ($user->role === 'admin') {
            if (!empty($doctorIds)) {
                $query->whereHas('calendar', function ($calendarQuery) use ($doctorIds) {
                    $calendarQuery->whereIn('doctor_id', $doctorIds);
                });
            }
            return;
        }

        if ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->map(fn ($id) => (string) $id)->all();

            if (empty($allowedDoctorIds)) {
                $query->whereRaw('1 = 0');
                $doctorIds = [];
                return;
            }

            if (!empty($doctorIds)) {
                $invalidDoctorIds = array_diff($doctorIds, $allowedDoctorIds);
                if (!empty($invalidDoctorIds)) {
                    abort(403, 'VocÃª nÃ£o tem permissÃ£o para acessar um ou mais mÃ©dicos selecionados.');
                }

                $query->whereHas('calendar', function ($calendarQuery) use ($doctorIds) {
                    $calendarQuery->whereIn('doctor_id', $doctorIds);
                });
                return;
            }

            $doctorIds = $allowedDoctorIds;
            $query->whereHas('calendar', function ($calendarQuery) use ($allowedDoctorIds) {
                $calendarQuery->whereIn('doctor_id', $allowedDoctorIds);
            });
            return;
        }

        abort(403, 'VocÃª nÃ£o tem permissÃ£o para acessar atendimentos.');
    }

    /**
     * Serializa dados essenciais para atualizar a fila sem reload.
     */
    private function serializeAppointmentForSession(Appointment $appointment): array
    {
        $doctorUser = optional(optional($appointment->calendar)->doctor)->user;

        return [
            'id' => (string) $appointment->id,
            'status' => (string) $appointment->status,
            'status_label' => (string) $appointment->status_translated,
            'starts_at_iso' => $appointment->starts_at?->toIso8601String(),
            'starts_at_time' => $appointment->starts_at?->format('H:i'),
            'starts_at_display' => $appointment->starts_at?->format('d/m/Y H:i'),
            'patient_name' => (string) ($appointment->patient->full_name ?? 'N/A'),
            'doctor_name' => (string) ($doctorUser->name_full ?? $doctorUser->name ?? 'N/A'),
            'type_name' => (string) ($appointment->type->name ?? 'Tipo nÃ£o informado'),
            'queue_position' => $appointment->queue_position,
        ];
    }

    private function hasQueuePositionColumn(): bool
    {
        if ($this->hasQueuePositionColumn !== null) {
            return $this->hasQueuePositionColumn;
        }

        try {
            $this->hasQueuePositionColumn = Schema::connection('tenant')->hasColumn('appointments', 'queue_position');
        } catch (\Throwable $e) {
            Log::warning('Falha ao verificar coluna queue_position', [
                'tenant_id' => tenant()?->id,
                'error' => $e->getMessage(),
            ]);
            $this->hasQueuePositionColumn = false;
        }

        return $this->hasQueuePositionColumn;
    }

    private function hasQueueUpdatedAtColumn(): bool
    {
        if ($this->hasQueueUpdatedAtColumn !== null) {
            return $this->hasQueueUpdatedAtColumn;
        }

        if (!$this->hasQueuePositionColumn()) {
            $this->hasQueueUpdatedAtColumn = false;
            return false;
        }

        try {
            $this->hasQueueUpdatedAtColumn = Schema::connection('tenant')->hasColumn('appointments', 'queue_updated_at');
        } catch (\Throwable $e) {
            Log::warning('Falha ao verificar coluna queue_updated_at', [
                'tenant_id' => tenant()?->id,
                'error' => $e->getMessage(),
            ]);
            $this->hasQueueUpdatedAtColumn = false;
        }

        return $this->hasQueueUpdatedAtColumn;
    }

    /**
     * Garante que a conexao do tenant esta configurada.
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
     * Resolve appointment by UUID and fail safely when id is invalid.
     */
    private function resolveAppointmentOrFail($slug, $appointmentId): Appointment
    {
        if (!Str::isUuid($appointmentId)) {
            Log::warning('UUID invalido recebido em atendimento medico', [
                'tenant_id' => tenant()?->id,
                'workspace_slug' => $slug,
                'appointment_id_received' => $appointmentId,
                'user_id' => Auth::guard('tenant')->id(),
            ]);

            abort(404, 'Agendamento nao encontrado para este workspace.');
        }

        return Appointment::findOrFail($appointmentId);
    }

    /**
     * Retorna a resposta do formulario para visualizacao no modal.
     */
    public function getFormResponse($slug, $appointmentId)
    {
        try {
            // Garantir que o tenant está ativo e a conexão configurada
            $this->ensureTenantConnection();

            $user = Auth::guard('tenant')->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario nao autenticado.'
                ], 401);
            }

            $appointment = $this->resolveAppointmentOrFail($slug, $appointmentId);

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
            Log::error('Erro ao buscar resposta do formulario', [
                'tenant_id' => tenant()?->id,
                'workspace_slug' => $slug,
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage()
            ]);

            if (
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agendamento nao encontrado para este workspace.'
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar resposta do formulario: ' . $e->getMessage()
            ], 500);
        }
    }
}

