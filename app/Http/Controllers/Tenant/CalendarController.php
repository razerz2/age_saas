<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HandlesGridRequests;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\RecurringAppointment;
use App\Http\Requests\Tenant\StoreCalendarRequest;
use App\Http\Requests\Tenant\UpdateCalendarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarController extends Controller
{
    use HasDoctorFilter;
    use HandlesGridRequests;

    public function gridData(Request $request, $slug)
    {
        $query = Calendar::with('doctor.user');

        // Filtro por médico (admin vê todos, outros filtrados)
        $this->applyDoctorFilter($query, 'doctor_id');

        $page = $this->gridPage($request);
        $perPage = $this->gridPerPage($request);

        // Busca global
        $search = $this->gridSearch($request);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('external_id', 'like', "%{$search}%")
                  ->orWhereHas('doctor.user', function ($sub) use ($search) {
                      $sub->where('name_full', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenação
        $sortable = [
            'name'        => 'name',
            'doctor'      => 'doctor_id',
            'external_id' => 'external_id',
            'created_at'  => 'created_at',
        ];

        $sort = $this->gridSort($request, $sortable, 'name', 'asc');
        $query->orderBy($sort['column'], $sort['direction']);
        if ($sort['column'] !== 'name') {
            $query->orderBy('name', 'asc');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Calendar $calendar) {

            $doctorUser = optional($calendar->doctor)->user;
            $doctorName = $doctorUser->name_full ?? $doctorUser->name ?? 'N/A';

            return [
                'name'        => e($calendar->name),
                'doctor'      => e($doctorName),
                'external_id' => e($calendar->external_id ?? '-'),
                'actions'     => view('tenant.calendars.partials.actions', compact('calendar'))->render(),
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'meta' => $this->gridMeta($paginator),
        ]);
    }

    public function index()
    {
        $user = Auth::guard('tenant')->user();
        
        $query = Calendar::with('doctor.user');
        
        // Aplicar filtro de médico (admin vê todos, médico vê só o dele, user vê os relacionados)
        $this->applyDoctorFilter($query, 'doctor_id');
        
        $calendars = $query->orderBy('name')->paginate(20);

        return view('tenant.calendars.index', compact('calendars'));
    }

    public function create()
    {
        $user = Auth::guard('tenant')->user();
        
        // Busca médicos que ainda não possuem calendário
        $doctorsQuery = Doctor::with('user')
            ->whereDoesntHave('calendars');
        
        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);
        
        $doctors = $doctorsQuery->orderBy('id')->get();
        
        // Verificar se médico já tem calendário
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor && $doctor->calendars()->exists()) {
                return redirect()->route('tenant.calendars.index', ['slug' => tenant()->subdomain])
                    ->with('error', 'Você já possui um calendário cadastrado. Cada médico pode ter apenas um calendário.');
            }
        }

        return view('tenant.calendars.create', compact('doctors'));
    }

    public function store(StoreCalendarRequest $request)
    {
        $user = Auth::guard('tenant')->user();
        $data = $request->validated();
        
        // Verifica se o médico já possui um calendário
        $doctor = Doctor::findOrFail($data['doctor_id']);
        if ($doctor->calendars()->exists()) {
            return redirect()->route('tenant.calendars.create', ['slug' => tenant()->subdomain])
                ->with('error', 'Este médico já possui um calendário cadastrado. Cada médico pode ter apenas um calendário.')
                ->withInput();
        }
        
        // Se o usuário é médico, verifica se está tentando criar para outro médico
        if ($user->role === 'doctor' && $user->doctor) {
            if ($data['doctor_id'] !== $user->doctor->id) {
                abort(403, 'Você só pode criar calendário para si mesmo.');
            }
            // Verifica se já tem calendário
            if ($user->doctor->calendars()->exists()) {
                return redirect()->route('tenant.calendars.index', ['slug' => tenant()->subdomain])
                    ->with('error', 'Você já possui um calendário cadastrado. Cada médico pode ter apenas um calendário.');
            }
        }
        
        $data['id'] = Str::uuid();
        Calendar::create($data);

        return redirect()->route('tenant.calendars.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Agenda criada com sucesso.');
    }

    public function show($slug, $id)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->load('doctor.user');
        
        $user = Auth::guard('tenant')->user();
        
        // Admin pode ver todos os calendários, outros roles têm restrições
        if ($user->role !== 'admin') {
            // Verifica permissão para visualizar o calendário
            if ($user->role === 'doctor' && $user->doctor) {
                if ($calendar->doctor_id !== $user->doctor->id) {
                    abort(403, 'Você não tem permissão para visualizar este calendário.');
                }
            } elseif ($user->role === 'user') {
                if (!$user->belongsToUser($calendar->doctor_id)) {
                    abort(403, 'Você não tem permissão para visualizar este calendário.');
                }
            }
        }

        return view('tenant.calendars.show', compact('calendar'));
    }

    public function view($slug, $id)
    {
        $calendar = Calendar::with('doctor.user')->findOrFail($id);
        $this->authorize('view', $calendar);

        return view('tenant.calendars.view', [
            'calendar' => $calendar,
            'eventsUrl' => workspace_route('tenant.calendars.events', ['id' => $calendar->id]),
            'tenantTimezone' => config('app.timezone', 'America/Sao_Paulo'),
        ]);
    }

    public function events(Request $request, $slug, $id)
    {
        $calendar = Calendar::findOrFail($id);
        $this->authorize('view', $calendar);

        if (!$request->has('start')) {
            $request->merge(['start' => Carbon::now()->startOfMonth()->toIso8601String()]);
        }

        if (!$request->has('end')) {
            $request->merge(['end' => Carbon::now()->endOfMonth()->toIso8601String()]);
        }

        $request->headers->set('Accept', 'application/json');

        return app(AppointmentController::class)->events($request, $slug, $id);
    }

    public function edit($slug, $id)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->load('doctor');
        
        $user = Auth::guard('tenant')->user();
        
        // Admin pode editar todos os calendários, outros roles têm restrições
        if ($user->role !== 'admin') {
            // Verifica permissão para editar o calendário
            if ($user->role === 'doctor' && $user->doctor) {
                if ($calendar->doctor_id !== $user->doctor->id) {
                    abort(403, 'Você não tem permissão para editar este calendário.');
                }
            } elseif ($user->role === 'user') {
                if (!$user->belongsToUser($calendar->doctor_id)) {
                    abort(403, 'Você não tem permissão para editar este calendário.');
                }
            }
        }
        
        // Busca médicos que não possuem calendário OU o médico do calendário atual (para permitir edição)
        $doctors = Doctor::with('user')
            ->where(function ($query) use ($calendar) {
                $query->whereDoesntHave('calendars')
                      ->orWhere('id', $calendar->doctor_id);
            })
            ->orderBy('id')
            ->get();

        return view('tenant.calendars.edit', compact('calendar', 'doctors'));
    }

    public function update(UpdateCalendarRequest $request, $slug, $id)
    {
        $calendar = Calendar::findOrFail($id);
        
        $user = Auth::guard('tenant')->user();
        $data = $request->validated();
        
        // Admin pode atualizar todos os calendários, outros roles têm restrições
        if ($user->role !== 'admin') {
            // Verifica permissão para atualizar o calendário
            if ($user->role === 'doctor' && $user->doctor) {
                if ($calendar->doctor_id !== $user->doctor->id) {
                    abort(403, 'Você não tem permissão para atualizar este calendário.');
                }
                // Médico não pode mudar o médico do calendário
                if ($data['doctor_id'] !== $calendar->doctor_id) {
                    return redirect()->route('tenant.calendars.edit', ['slug' => tenant()->subdomain, 'id' => $calendar->id])
                        ->with('error', 'Você não pode alterar o médico do calendário.')
                        ->withInput();
                }
            } elseif ($user->role === 'user') {
                if (!$user->belongsToUser($calendar->doctor_id)) {
                    abort(403, 'Você não tem permissão para atualizar este calendário.');
                }
            }
        }
        
        // Se está tentando mudar o médico, verifica se o novo médico já tem calendário
        if ($data['doctor_id'] !== $calendar->doctor_id) {
            $newDoctor = Doctor::findOrFail($data['doctor_id']);
            if ($newDoctor->calendars()->where('id', '!=', $calendar->id)->exists()) {
                return redirect()->route('tenant.calendars.edit', ['slug' => tenant()->subdomain, 'id' => $calendar->id])
                    ->with('error', 'Este médico já possui um calendário cadastrado. Cada médico pode ter apenas um calendário.')
                    ->withInput();
            }
        }
        
        $calendar->update($data);

        return redirect()->route('tenant.calendars.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Agenda atualizada com sucesso.');
    }

    public function destroy($slug, $id)
    {
        $calendar = Calendar::findOrFail($id);
        $this->authorize('delete', $calendar);
        
        $user = Auth::guard('tenant')->user();
        
        // Admin pode remover todos os calendários, outros roles têm restrições
        if ($user->role !== 'admin') {
            // Verifica permissão para remover o calendário
            if ($user->role === 'doctor' && $user->doctor) {
                if ($calendar->doctor_id !== $user->doctor->id) {
                    abort(403, 'Você não tem permissão para remover este calendário.');
                }
            } elseif ($user->role === 'user') {
                if (!$user->belongsToUser($calendar->doctor_id)) {
                    abort(403, 'Você não tem permissão para remover este calendário.');
                }
            }
        }

        DB::transaction(function () use ($calendar) {
            $doctorId = $calendar->doctor_id;

            // Removendo o calendário primeiro para garantir cascade dos appointments.
            $calendar->delete();

            BusinessHour::where('doctor_id', $doctorId)->delete();

            // Recorrências podem apontar para appointment_types; limpa vínculo antes de remover os tipos.
            RecurringAppointment::where('doctor_id', $doctorId)->update(['appointment_type_id' => null]);

            AppointmentType::where('doctor_id', $doctorId)->delete();
        });

        return redirect()->route('tenant.calendars.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Calendário removido com sucesso. Horários Comerciais e Tipos de Consulta vinculados também foram removidos.');
    }

    public function eventsRedirect()
    {
        $user = Auth::guard('tenant')->user();
        
        // Admin não deve acessar agendas individuais, apenas gerenciar calendários
        if ($user->role === 'admin') {
            return redirect()->route('tenant.calendars.index', ['slug' => tenant()->subdomain])
                ->with('info', 'Administradores podem gerenciar calendários, mas não possuem agendas individuais.');
        }
        
        $query = Calendar::orderBy('name');
        
        // Aplicar filtros baseado no role
        if ($user->role === 'doctor' && $user->doctor) {
            $calendar = $query->where('doctor_id', $user->doctor->id)->first();
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                $calendar = $query->whereIn('doctor_id', $allowedDoctorIds)->first();
            }
        } else {
            $calendar = $query->first();
        }

        if ($calendar) {
            return redirect()->route('tenant.calendars.view', ['slug' => tenant()->subdomain, 'id' => $calendar->id]);
        }

        return redirect()->route('tenant.calendars.index', ['slug' => tenant()->subdomain])
            ->with('info', 'Nenhum calendário encontrado. Crie um calendário primeiro.');
    }
}
