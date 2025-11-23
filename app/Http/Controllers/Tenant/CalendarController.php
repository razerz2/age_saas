<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Http\Requests\Tenant\StoreCalendarRequest;
use App\Http\Requests\Tenant\UpdateCalendarRequest;
use Illuminate\Support\Str;

class CalendarController extends Controller
{
    public function index()
    {
        $calendars = Calendar::with('doctor.user')
            ->orderBy('name')
            ->paginate(20);

        return view('tenant.calendars.index', compact('calendars'));
    }

    public function create()
    {
        $doctors = Doctor::with('user')->orderBy('id')->get();

        return view('tenant.calendars.create', compact('doctors'));
    }

    public function store(StoreCalendarRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        Calendar::create($data);

        return redirect()->route('tenant.calendars.index')
            ->with('success', 'Agenda criada com sucesso.');
    }

    public function show(Calendar $calendar)
    {
        $calendar->load('doctor.user');

        return view('tenant.calendars.show', compact('calendar'));
    }

    public function edit(Calendar $calendar)
    {
        $doctors = Doctor::with('user')->orderBy('id')->get();
        $calendar->load('doctor');

        return view('tenant.calendars.edit', compact('calendar', 'doctors'));
    }

    public function update(UpdateCalendarRequest $request, Calendar $calendar)
    {
        $calendar->update($request->validated());

        return redirect()->route('tenant.calendars.index')
            ->with('success', 'Agenda atualizada com sucesso.');
    }

    public function destroy(Calendar $calendar)
    {
        $calendar->delete();

        return redirect()->route('tenant.calendars.index')
            ->with('success', 'Agenda removida.');
    }
}
