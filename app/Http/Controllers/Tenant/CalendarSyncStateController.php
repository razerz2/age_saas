<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CalendarSyncState;
use App\Models\Tenant\Appointment;

use App\Http\Requests\Tenant\CalendarSync\StoreCalendarSyncStateRequest;
use App\Http\Requests\Tenant\CalendarSync\UpdateCalendarSyncStateRequest;

use Illuminate\Support\Str;

class CalendarSyncStateController extends Controller
{
    public function index()
    {
        $syncs = CalendarSyncState::with(['appointment.patient', 'appointment.calendar.doctor.user'])
            ->orderBy('last_sync_at', 'desc')
            ->paginate(20);

        return view('tenant.calendar_sync.index', compact('syncs'));
    }

    public function create()
    {
        $appointments = Appointment::with(['patient', 'calendar.doctor'])
            ->orderBy('starts_at', 'desc')
            ->get();

        return view('tenant.calendar_sync.create', compact('appointments'));
    }

    public function store(StoreCalendarSyncStateRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        CalendarSyncState::create($data);

        return redirect()->route('tenant.calendar-sync.index')
            ->with('success', 'Estado de sincronização criado com sucesso.');
    }

    public function show(CalendarSyncState $calendarSyncState)
    {
        $calendarSyncState->load([
            'appointment.patient',
            'appointment.calendar.doctor.user'
        ]);

        return view('tenant.calendar_sync.show', compact('calendarSyncState'));
    }

    public function edit(CalendarSyncState $calendarSyncState)
    {
        $calendarSyncState->load(['appointment']);

        $appointments = Appointment::orderBy('starts_at', 'desc')->get();

        return view('tenant.calendar_sync.edit', compact('calendarSyncState', 'appointments'));
    }

    public function update(UpdateCalendarSyncStateRequest $request, CalendarSyncState $calendarSyncState)
    {
        $calendarSyncState->update($request->validated());

        return redirect()->route('tenant.calendar-sync.index')
            ->with('success', 'Estado de sincronização atualizado.');
    }

    public function destroy(CalendarSyncState $calendarSyncState)
    {
        $calendarSyncState->delete();

        return redirect()->route('tenant.calendar-sync.index')
            ->with('success', 'Estado de sincronização removido.');
    }
}
