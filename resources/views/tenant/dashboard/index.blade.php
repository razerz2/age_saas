@extends('layouts.connect_plus.app')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tenant-dashboard.css') }}">
@endpush

@section('content')

<div class="row">
    {{-- 🔹 Cards Estatísticos Principais --}}
    
    {{-- Total de Pacientes --}}
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-primary">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-primary me-3">
                            <i class="mdi mdi-account-multiple icon-3d text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label mb-2">Total de Pacientes</div>
                            <div class="stat-number">{{ number_format($stats['patients']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['patients']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge {{ $stats['patients']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $stats['patients']['variation'] > 0 ? '↑' : '↓' }} {{ abs($stats['patients']['variation']) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Médicos Cadastrados --}}
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-info">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-info me-3">
                            <i class="mdi mdi-doctor icon-3d text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label mb-2">Médicos Cadastrados</div>
                            <div class="stat-number">{{ number_format($stats['doctors']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['doctors']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge {{ $stats['doctors']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $stats['doctors']['variation'] > 0 ? '↑' : '↓' }} {{ abs($stats['doctors']['variation']) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Especialidades --}}
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-warning">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-warning me-3">
                            <i class="mdi mdi-medical-bag icon-3d text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label mb-2">Especialidades</div>
                            <div class="stat-number">{{ number_format($stats['specialties']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['specialties']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge {{ $stats['specialties']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $stats['specialties']['variation'] > 0 ? '↑' : '↓' }} {{ abs($stats['specialties']['variation']) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Agendamentos do Dia --}}
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-success">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-success me-3">
                            <i class="mdi mdi-calendar-today icon-3d text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label mb-2">Agendamentos do Dia</div>
                            <div class="stat-number">{{ number_format($stats['today']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['today']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge {{ $stats['today']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $stats['today']['variation'] > 0 ? '↑' : '↓' }} {{ abs($stats['today']['variation']) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Agendamentos da Semana --}}
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-blue">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-blue me-3">
                            <i class="mdi mdi-calendar-week icon-3d text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label mb-2">Agendamentos da Semana</div>
                            <div class="stat-number">{{ number_format($stats['week']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['week']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge {{ $stats['week']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $stats['week']['variation'] > 0 ? '↑' : '↓' }} {{ abs($stats['week']['variation']) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Agendamentos do Mês --}}
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-indigo">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-indigo me-3">
                            <i class="mdi mdi-calendar-month icon-3d text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label mb-2">Agendamentos do Mês</div>
                            <div class="stat-number">{{ number_format($stats['month']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['month']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge {{ $stats['month']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $stats['month']['variation'] > 0 ? '↑' : '↓' }} {{ abs($stats['month']['variation']) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 🔹 Gráfico de Linha - Agendamentos últimos 12 meses --}}
<div class="row mt-4">
    <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded chart-card shadow-lg border-0">
            <div class="card-body">
                <h4 class="card-title">Agendamentos nos Últimos 12 Meses</h4>
                <canvas id="appointmentsLineChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- 🔹 Gráfico de Pizza e Tabela de Próximos Agendamentos --}}
<div class="row mt-4">
    {{-- Gráfico de Pizza - Distribuição por Especialidade --}}
    <div class="col-xl-6 col-lg-6 col-md-12 grid-margin stretch-card">
        <div class="card card-rounded chart-card shadow-lg border-0">
            <div class="card-body">
                <h4 class="card-title">Distribuição por Especialidade</h4>
                <canvas id="specialtyPieChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Tabela - Próximos Agendamentos --}}
    <div class="col-xl-6 col-lg-6 col-md-12 grid-margin stretch-card">
        <div class="card card-rounded table-card shadow-lg border-0">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title mb-0">Próximos Agendamentos</h4>
                    <a href="{{ workspace_route('tenant.appointments.index') }}" class="btn btn-sm btn-primary">
                        Ver todos
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Tipo</th>
                                <th>Data/Hora</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointmentsNext as $appointment)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <div class="avatar-circle">
                                                {{ strtoupper(substr($appointment->patient->full_name ?? 'N/A', 0, 2)) }}
                                            </div>
                                        </div>
                                        <span class="fw-medium">{{ $appointment->patient->full_name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $appointment->calendar->doctor->user->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $appointment->type->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $appointment->starts_at->format('d/m/Y') }}<br>
                                        <strong>{{ $appointment->starts_at->format('H:i') }}</strong>
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'scheduled' => 'success',
                                            'rescheduled' => 'warning',
                                            'canceled' => 'danger',
                                            'attended' => 'primary',
                                            'no_show' => 'secondary'
                                        ];
                                        $color = $statusColors[$appointment->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ $appointment->status_translated }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Nenhum agendamento nas próximas 24 horas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 🔹 Consultórios Ativos Hoje --}}
<div class="row mt-4">
    <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded shadow-lg border-0 consultorios-section">
            <div class="card-body">
                <h4 class="card-title">Consultórios Ativos Hoje</h4>
                <div class="row">
                    @forelse($activeDoctorsToday as $doctor)
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card doctor-widget-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-3">
                                        <div class="doctor-widget-icon">
                                            <i class="mdi mdi-doctor mdi-24px"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6>{{ $doctor['doctor'] }}</h6>
                                        <small>{{ $doctor['specialty'] }}</small>
                                    </div>
                                </div>
                                <div class="doctor-widget-info">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Atendimentos:</span>
                                        <span class="fw-bold">{{ $doctor['count'] }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Horários:</span>
                                        <span class="fw-medium">{{ $doctor['times'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <p class="text-center text-muted py-4">Nenhum consultório ativo hoje.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // 🔹 Gráfico de Linha - Agendamentos últimos 12 meses
    const ctxLine = document.getElementById('appointmentsLineChart');
    if (ctxLine) {
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: @json(array_column($chartLast12Months, 'short')),
                datasets: [{
                    label: 'Agendamentos',
                    data: @json(array_column($chartLast12Months, 'total')),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#ffffff',
                        titleColor: '#1f2937',
                        bodyColor: '#1f2937',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // 🔹 Gráfico de Pizza - Distribuição por Especialidade
    const ctxPie = document.getElementById('specialtyPieChart');
    if (ctxPie) {
        const specialtyData = @json($chartBySpecialty);
        const labels = specialtyData.map(item => item.label);
        const values = specialtyData.map(item => item.value);
        
        // Paleta de cores harmoniosa
        const colors = [
            '#0d6efd', '#5b6fe0', '#8893ff', '#c9ceff',
            '#6c757d', '#adb5bd', '#dee2e6', '#e9ecef'
        ];

        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#ffffff',
                        titleColor: '#1f2937',
                        bodyColor: '#1f2937',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush

@endsection

