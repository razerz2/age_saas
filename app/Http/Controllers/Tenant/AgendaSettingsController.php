<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Http\Requests\Tenant\StoreAgendaSettingsRequest;
use App\Http\Requests\Tenant\UpdateAgendaSettingsRequest;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\RecurringAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AgendaSettingsController extends Controller
{
    use HasDoctorFilter;

    public function index(Request $request)
    {
        $query = Calendar::query()
            ->with('doctor.user')
            ->select('calendars.*')
            ->selectSub(
                BusinessHour::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('business_hours.doctor_id', 'calendars.doctor_id'),
                'business_hours_count'
            )
            ->selectSub(
                AppointmentType::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('appointment_types.doctor_id', 'calendars.doctor_id'),
                'appointment_types_count'
            )
            ->selectSub(
                AppointmentType::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('appointment_types.doctor_id', 'calendars.doctor_id')
                    ->where('is_active', true),
                'appointment_types_active_count'
            );

        $this->applyDoctorFilter($query, 'doctor_id');

        $search = trim((string) $request->get('search', ''));
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

        $calendars = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('tenant.agenda-settings.index', compact('calendars', 'search'));
    }

    public function create()
    {
        $doctorsQuery = Doctor::with('user')->whereDoesntHave('calendars');
        $this->applyDoctorFilter($doctorsQuery);
        $doctors = $doctorsQuery->orderBy('id')->get();

        if ($doctors->isEmpty()) {
            return redirect()->route('tenant.agenda-settings.index', ['slug' => tenant()->subdomain])
                ->with('error', 'Todos os profissionais disponíveis já possuem agenda cadastrada.');
        }

        return view('tenant.agenda-settings.create', compact('doctors'));
    }

    public function store(StoreAgendaSettingsRequest $request)
    {
        $data = $request->validated();

        $doctor = $this->assertDoctorAccess((string) $data['doctor_id']);
        if ($doctor->calendars()->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Este profissional já possui agenda cadastrada.');
        }

        $this->validateSingleFormConsistency($data['business_hours'], $data['appointment_types']);

        $calendar = DB::connection('tenant')->transaction(function () use ($doctor, $data) {
            $calendar = Calendar::create([
                'id' => Str::uuid(),
                'doctor_id' => $doctor->id,
                'name' => $data['name'],
                'external_id' => $data['external_id'] ?? null,
                'is_active' => (string) $data['is_active'] === '1',
            ]);

            $this->replaceBusinessHours($doctor->id, $data['business_hours']);
            $this->syncAppointmentTypes($doctor->id, $data['appointment_types']);

            return $calendar;
        });

        return redirect()->route('tenant.agenda-settings.show', [
            'slug' => tenant()->subdomain,
            'id' => $calendar->id,
        ])->with('success', 'Agenda criada com sucesso.');
    }

    public function show($slug, $id)
    {
        $calendar = $this->findAccessibleCalendar($id);
        $businessHours = $calendar->doctor->businessHours()->orderBy('weekday')->orderBy('start_time')->get();
        $appointmentTypes = $calendar->doctor->appointmentTypes()->orderByDesc('is_active')->orderBy('name')->get();

        return view('tenant.agenda-settings.show', compact('calendar', 'businessHours', 'appointmentTypes'));
    }

    public function edit($slug, $id)
    {
        $calendar = $this->findAccessibleCalendar($id);

        return view('tenant.agenda-settings.edit', compact('calendar'));
    }

    public function update(UpdateAgendaSettingsRequest $request, $slug, $id)
    {
        $calendar = $this->findAccessibleCalendar($id);
        $data = $request->validated();

        if ((string) $data['doctor_id'] !== (string) $calendar->doctor_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Não é permitido alterar o profissional de uma agenda existente.');
        }

        $this->validateSingleFormConsistency($data['business_hours'], $data['appointment_types']);

        DB::connection('tenant')->transaction(function () use ($calendar, $data) {
            $calendar->update([
                'name' => $data['name'],
                'external_id' => $data['external_id'] ?? null,
                'is_active' => (string) $data['is_active'] === '1',
            ]);

            $this->replaceBusinessHours($calendar->doctor_id, $data['business_hours']);
            $this->syncAppointmentTypes($calendar->doctor_id, $data['appointment_types']);
        });

        return redirect()->route('tenant.agenda-settings.show', [
            'slug' => tenant()->subdomain,
            'id' => $calendar->id,
        ])->with('success', 'Agenda atualizada com sucesso.');
    }

    public function toggleStatus($slug, $id)
    {
        $calendar = $this->findAccessibleCalendar($id);
        $calendar->is_active = !$calendar->is_active;
        $calendar->save();

        $message = $calendar->is_active
            ? 'Agenda ativada com sucesso.'
            : 'Agenda desativada com sucesso.';

        return redirect()->route('tenant.agenda-settings.index', ['slug' => tenant()->subdomain])
            ->with('success', $message);
    }

    public function destroy($slug, $id)
    {
        $calendar = $this->findAccessibleCalendar($id);

        DB::connection('tenant')->transaction(function () use ($calendar) {
            $doctorId = $calendar->doctor_id;

            $calendar->delete();
            BusinessHour::where('doctor_id', $doctorId)->delete();
            RecurringAppointment::where('doctor_id', $doctorId)->update(['appointment_type_id' => null]);
            AppointmentType::where('doctor_id', $doctorId)->delete();
        });

        return redirect()->route('tenant.agenda-settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Agenda excluída com sucesso.');
    }

    private function findAccessibleCalendar(string $calendarId): Calendar
    {
        $query = Calendar::with('doctor.user');
        $this->applyDoctorFilter($query, 'doctor_id');

        $calendar = $query->where('id', $calendarId)->first();
        if (!$calendar) {
            abort(404, 'Agenda não encontrada.');
        }

        return $calendar;
    }

    private function assertDoctorAccess(string $doctorId): Doctor
    {
        $query = Doctor::with('user');
        $this->applyDoctorFilter($query);
        $doctor = $query->where('id', $doctorId)->first();

        if (!$doctor) {
            abort(403, 'Você não tem permissão para gerenciar este profissional.');
        }

        return $doctor;
    }

    private function validateSingleFormConsistency(array $businessHours, array $appointmentTypes): void
    {
        $validator = Validator::make(
            [
                'business_hours' => $businessHours,
                'appointment_types' => $appointmentTypes,
            ],
            []
        );

        $validator->after(function ($validator) use ($businessHours, $appointmentTypes) {
            $seenHourKeys = [];
            foreach ($businessHours as $index => $hour) {
                $start = (string) ($hour['start_time'] ?? '');
                $end = (string) ($hour['end_time'] ?? '');
                $breakStart = (string) ($hour['break_start_time'] ?? '');
                $breakEnd = (string) ($hour['break_end_time'] ?? '');

                if ($start >= $end) {
                    $validator->errors()->add("business_hours.{$index}.end_time", 'O horário final deve ser maior que o horário inicial.');
                }

                if (($breakStart !== '' && $breakEnd === '') || ($breakStart === '' && $breakEnd !== '')) {
                    $validator->errors()->add("business_hours.{$index}.break_end_time", 'Preencha início e fim do intervalo.');
                }

                if ($breakStart !== '' && $breakEnd !== '') {
                    if ($breakStart >= $breakEnd) {
                        $validator->errors()->add("business_hours.{$index}.break_end_time", 'O fim do intervalo deve ser maior que o início.');
                    }
                    if ($breakStart <= $start || $breakEnd >= $end) {
                        $validator->errors()->add("business_hours.{$index}.break_start_time", 'O intervalo precisa estar dentro do horário de atendimento.');
                    }
                }

                $key = implode('|', [
                    (string) ($hour['weekday'] ?? ''),
                    $start,
                    $end,
                ]);
                if (in_array($key, $seenHourKeys, true)) {
                    $validator->errors()->add("business_hours.{$index}.weekday", 'Existem horários duplicados no formulário.');
                }
                $seenHourKeys[] = $key;
            }

            $seenTypeNames = [];
            foreach ($appointmentTypes as $index => $type) {
                $name = mb_strtolower(trim((string) ($type['name'] ?? '')));
                if ($name === '') {
                    continue;
                }
                if (in_array($name, $seenTypeNames, true)) {
                    $validator->errors()->add("appointment_types.{$index}.name", 'Existem tipos de atendimento duplicados no formulário.');
                }
                $seenTypeNames[] = $name;
            }
        });

        $validator->validate();
    }

    private function replaceBusinessHours(string $doctorId, array $rows): void
    {
        BusinessHour::where('doctor_id', $doctorId)->delete();

        foreach ($rows as $row) {
            BusinessHour::create([
                'id' => Str::uuid(),
                'doctor_id' => $doctorId,
                'weekday' => (int) $row['weekday'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'break_start_time' => $row['break_start_time'] ?: null,
                'break_end_time' => $row['break_end_time'] ?: null,
            ]);
        }
    }

    private function syncAppointmentTypes(string $doctorId, array $rows): void
    {
        $existing = AppointmentType::where('doctor_id', $doctorId)->get()->keyBy('id');
        $keptIds = [];

        foreach ($rows as $row) {
            $typeId = $row['id'] ?? null;
            $payload = [
                'name' => $row['name'],
                'duration_min' => (int) $row['duration_min'],
                'is_active' => (string) $row['is_active'] === '1',
            ];

            if ($typeId && !$existing->has($typeId)) {
                throw ValidationException::withMessages([
                    'appointment_types' => 'Um dos tipos enviados não pertence ao profissional da agenda.',
                ]);
            }

            if ($typeId && $existing->has($typeId)) {
                $type = $existing->get($typeId);
                $type->update($payload);
                $keptIds[] = $type->id;
                continue;
            }

            $newType = AppointmentType::create([
                'id' => Str::uuid(),
                'doctor_id' => $doctorId,
                'name' => $payload['name'],
                'duration_min' => $payload['duration_min'],
                'is_active' => $payload['is_active'],
            ]);
            $keptIds[] = $newType->id;
        }

        AppointmentType::where('doctor_id', $doctorId)
            ->whereNotIn('id', $keptIds)
            ->update(['is_active' => false]);
    }
}
