<?php

namespace App\Http\Controllers\Tenant\PatientPortal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant\Appointment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $patientLogin = Auth::guard('patient')->user();
        $patient = $patientLogin->patient ?? null;

        if (!$patient) {
            $tenantSlug = session('tenant_slug');
            Auth::guard('patient')->logout();
            
            if ($tenantSlug) {
                return redirect()->route('patient.login', ['slug' => $tenantSlug])
                    ->withErrors(['email' => 'Paciente não encontrado.']);
            }
            
            return redirect('/')->withErrors(['email' => 'Paciente não encontrado.']);
        }

        // Estatísticas
        $totalAppointments = Appointment::where('patient_id', $patient->id)->count();
        
        $upcomingAppointments = Appointment::where('patient_id', $patient->id)
            ->where('starts_at', '>', now())
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->count();

        $unreadNotifications = 0; // Pode ser implementado depois

        // Agendamentos recentes
        $recentAppointments = Appointment::where('patient_id', $patient->id)
            ->with(['calendar.doctor.user', 'specialty', 'type'])
            ->where('starts_at', '>', now())
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        // Ajusta os dados dos agendamentos para a view
        $recentAppointments = $recentAppointments->map(function ($appointment) {
            return (object) [
                'id' => $appointment->id,
                'appointment_date' => $appointment->starts_at,
                'appointment_time' => $appointment->starts_at->format('H:i'),
                'doctor' => (object) [
                    'name' => optional($appointment->calendar)->doctor->user->name ?? 'N/A'
                ],
                'status' => $appointment->status,
            ];
        });

        return view('tenant.patient_portal.dashboard', compact(
            'totalAppointments',
            'upcomingAppointments',
            'unreadNotifications',
            'recentAppointments'
        ));
    }
}
