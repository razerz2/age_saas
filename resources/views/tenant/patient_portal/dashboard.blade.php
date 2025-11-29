@extends('tenant.patient_portal.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-view-dashboard"></i>
        </span>
        Dashboard
    </h3>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-4 stretch-card grid-margin">
        <div class="card bg-gradient-danger card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                <h4 class="font-weight-normal mb-3">Meus Agendamentos
                    <i class="mdi mdi-calendar-clock mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">{{ $totalAppointments ?? 0 }}</h2>
                <h6 class="card-text">Total de agendamentos</h6>
            </div>
        </div>
    </div>
    <div class="col-md-4 stretch-card grid-margin">
        <div class="card bg-gradient-info card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                <h4 class="font-weight-normal mb-3">Próximos Agendamentos
                    <i class="mdi mdi-calendar-check mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">{{ $upcomingAppointments ?? 0 }}</h2>
                <h6 class="card-text">Agendamentos futuros</h6>
            </div>
        </div>
    </div>
    <div class="col-md-4 stretch-card grid-margin">
        <div class="card bg-gradient-success card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                <h4 class="font-weight-normal mb-3">Notificações
                    <i class="mdi mdi-bell-outline mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">{{ $unreadNotifications ?? 0 }}</h2>
                <h6 class="card-text">Não lidas</h6>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Próximos Agendamentos</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Médico</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAppointments ?? [] as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_date->format('d/m/Y') }}</td>
                                    <td>{{ $appointment->appointment_time }}</td>
                                    <td>{{ $appointment->doctor->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $appointment->status === 'confirmed' ? 'success' : ($appointment->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('patient.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-primary">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum agendamento encontrado</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

