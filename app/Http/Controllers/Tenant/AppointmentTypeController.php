<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HandlesGridRequests;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\Doctor;
use App\Http\Requests\Tenant\StoreAppointmentTypeRequest;
use App\Http\Requests\Tenant\UpdateAppointmentTypeRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentTypeController extends Controller
{
    use HasDoctorFilter;
    use HandlesGridRequests;
    public function index(Request $request)
    {
        $query = AppointmentType::with(['doctor.user']);

        // Aplicar filtro de médico
        $this->applyDoctorFilter($query, 'doctor_id');

        // Filtro opcional por médico (apenas se admin)
        $user = Auth::guard('tenant')->user();
        if ($user->role === 'admin' && $request->has('doctor_id') && $request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointmentTypes = $query->orderBy('name')->paginate(20)->withQueryString();

        // Buscar médicos para o filtro
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($q) {
                $q->where('status', 'active');
            });

        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);

        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.appointment-types.index', compact('appointmentTypes', 'doctors'));
    }

    public function create()
    {
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->whereDoesntHave('appointmentTypes');

        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);

        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.appointment-types.create', compact('doctors'));
    }

    public function store(StoreAppointmentTypeRequest $request)
    {
        $user = Auth::guard('tenant')->user();
        
        // Determinar qual médico será usado
        $doctor = null;
        
        if ($user->role === 'doctor' && $user->doctor) {
            $doctor = $user->doctor;
        } elseif ($user->role === 'user') {
            $allowedDoctors = $user->allowedDoctors()->get();
            if ($allowedDoctors->count() === 1) {
                $doctor = $allowedDoctors->first();
            } elseif ($request->has('doctor_id')) {
                // Se houver múltiplos médicos, usar o doctor_id do request (admin ou usuário com múltiplos médicos)
                $doctor = Doctor::find($request->doctor_id);
            } else {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }
        } elseif ($user->role === 'admin' && $request->has('doctor_id')) {
            // Admin pode especificar o médico
            $doctor = Doctor::find($request->doctor_id);
        }
        
        if (!$doctor) {
            return redirect()->back()->with('error', 'Médico não encontrado.');
        }
        
        $data = $request->validated();
        $data['id'] = Str::uuid();
        $data['doctor_id'] = $doctor->id;

        AppointmentType::create($data);

        return redirect()->route('tenant.appointment-types.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Tipo de atendimento criado com sucesso.');
    }

    public function show($slug, $id)
    {
        $appointmentType = AppointmentType::with(['doctor.user'])->findOrFail($id);
        return view('tenant.appointment-types.show', compact('appointmentType'));
    }

    public function edit($slug, $id)
    {
        $appointmentType = AppointmentType::with(['doctor.user'])->findOrFail($id);

        // Listar médicos ativos (com status active) que ainda não possuem tipo de consulta
        // ou o médico atual deste tipo de consulta (para permitir edição)
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->where(function($query) use ($appointmentType) {
                $query->whereDoesntHave('appointmentTypes')
                      ->orWhere('id', $appointmentType->doctor_id);
            });
        
        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);
        
        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.appointment-types.edit', compact('appointmentType', 'doctors'));
    }

    public function update(UpdateAppointmentTypeRequest $request, $slug, $id)
    {
        $appointmentType = AppointmentType::findOrFail($id);
        
        // Verificar permissões
        $user = Auth::guard('tenant')->user();
        $allowedDoctorIds = $this->getAllowedDoctorIds();
        
        if ($user->role !== 'admin' && !in_array($appointmentType->doctor_id, $allowedDoctorIds)) {
            abort(403, 'Você não tem permissão para atualizar este tipo de atendimento.');
        }
        
        $appointmentType->update($request->validated());

        return redirect()->route('tenant.appointment-types.index', ['slug' => $slug])
            ->with('success', 'Tipo de atendimento atualizado com sucesso.');
    }

    public function gridData(Request $request, $slug)
    {
        $query = AppointmentType::with('doctor.user');

        $this->applyDoctorFilter($query, 'doctor_id');

        $page = $this->gridPage($request);
        $perPage = $this->gridPerPage($request);

        // Busca global
        $search = $this->gridSearch($request);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('doctor.user', function ($sub) use ($search) {
                      $sub->where('name_full', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenação whitelist
        $sortable = [
            'name'         => 'name',
            'doctor'       => 'doctor_id',
            'duration_min' => 'duration_min',
            'price'        => 'price',
            'color'        => 'color',
            'created_at'   => 'created_at',
        ];

        $sort = $this->gridSort($request, $sortable, 'name', 'asc');
        $query->orderBy($sort['column'], $sort['direction']);
        if ($sort['column'] !== 'name') {
            $query->orderBy('name', 'asc');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (AppointmentType $type) {

            $doctorUser = optional($type->doctor)->user;
            $doctorName = $doctorUser->name_full ?? $doctorUser->name ?? 'N/A';

            return [
                'name'         => e($type->name),
                'doctor'       => e($doctorName),
                'duration_min' => e($type->duration_min) . ' min',
                'price'        => isset($type->price)
                                    ? 'R$ ' . number_format($type->price, 2, ',', '.')
                                    : '-',
                'color'        => $type->color
                                    ? '<span class="inline-block w-4 h-4 rounded-full" style="background:' . e($type->color) . '"></span>'
                                    : '-',
                'actions'      => view('tenant.appointment-types.partials.actions', compact('type'))->render(),
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'meta' => $this->gridMeta($paginator),
        ]);
    }

    public function destroy($slug, $id)
    {
        $appointmentType = AppointmentType::findOrFail($id);
        
        // Verificar permissões
        $user = Auth::guard('tenant')->user();
        $allowedDoctorIds = $this->getAllowedDoctorIds();
        
        if ($user->role !== 'admin' && !in_array($appointmentType->doctor_id, $allowedDoctorIds)) {
            abort(403, 'Você não tem permissão para remover este tipo de atendimento.');
        }
        
        $appointmentType->delete();

        return redirect()->route('tenant.appointment-types.index', ['slug' => $slug])
            ->with('success', 'Tipo removido.');
    }
}
