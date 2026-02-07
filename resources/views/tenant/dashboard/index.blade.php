@extends('layouts.connect_plus.app')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tenant-dashboard.css') }}">
@endpush

@section('content')

<div class="dashboard-container">
<div class="row">
    {{-- 🔹 Cards Estatísticos Principais --}}
    
    {{-- Total de Pacientes --}}
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-primary stat-card-compact">
            <div class="card-body p-2 stat-card-body-compact">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-primary me-2 icon-wrapper-compact">
                            <i class="mdi mdi-account-multiple icon-3d text-white icon-3d-compact"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label-compact mb-1">Total de Pacientes</div>
                            <div class="stat-number-compact">{{ number_format($stats['patients']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['patients']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge-compact {{ $stats['patients']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $stats['patients']['variation'] > 0 ? '↑' : '↓' }} {{ abs($stats['patients']['variation']) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Médicos Cadastrados --}}
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-info stat-card-compact">
            <div class="card-body p-2 stat-card-body-compact">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-info me-2 icon-wrapper-compact">
                            <i class="mdi mdi-doctor icon-3d text-white icon-3d-compact"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label-compact mb-1">Médicos Cadastrados</div>
                            <div class="stat-number-compact">{{ number_format($stats['doctors']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['doctors']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge-compact {{ $stats['doctors']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $stats['doctors']['variation'] > 0 ? '↑' : '↓' }} {{ abs($stats['doctors']['variation']) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Agendamentos do Dia --}}
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-success stat-card-compact">
            <div class="card-body p-2 stat-card-body-compact">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-success me-2 icon-wrapper-compact">
                            <i class="mdi mdi-calendar-today icon-3d text-white icon-3d-compact"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label-compact mb-1">Agendamentos do Dia</div>
                            <div class="stat-number-compact">{{ number_format($stats['today']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['today']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge-compact {{ $stats['today']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $stats['today']['variation'] > 0 ? '↑' : '↓' }} {{ abs($stats['today']['variation']) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Agendamentos da Semana --}}
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-blue stat-card-compact">
            <div class="card-body p-2 stat-card-body-compact">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-blue me-2 icon-wrapper-compact">
                            <i class="mdi mdi-calendar-week icon-3d text-white icon-3d-compact"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label-compact mb-1">Agendamentos da Semana</div>
                            <div class="stat-number-compact">{{ number_format($stats['week']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['week']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge-compact {{ $stats['week']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $stats['week']['variation'] > 0 ? '↑' : '↓' }} {{ abs($stats['week']['variation']) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Agendamentos do Mês --}}
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 grid-margin stretch-card">
        <div class="card card-rounded stat-card shadow-lg border-0 card-bg-gradient-indigo stat-card-compact">
            <div class="card-body p-2 stat-card-body-compact">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="icon-wrapper icon-gradient-indigo me-2 icon-wrapper-compact">
                            <i class="mdi mdi-calendar-month icon-3d text-white icon-3d-compact"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-label-compact mb-1">Agendamentos do Mês</div>
                            <div class="stat-number-compact">{{ number_format($stats['month']['total']) }}</div>
                        </div>
                    </div>
                    @if($stats['month']['variation'] != 0)
                    <div class="ms-2">
                        <span class="badge variation-badge-compact {{ $stats['month']['variation'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
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
<div class="row mt-3">
    <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded chart-card shadow-lg border-0">
            <div class="card-body">
                <h4 class="card-title">Agendamentos nos Últimos 12 Meses</h4>
                @if(count($chartLast12Months) > 0)
                    <canvas id="appointmentsLineChart" style="max-height: 250px;"></canvas>
                @else
                    <div class="text-center empty-state" style="min-height: 250px; display: flex; align-items: center; justify-content: center;">
                        <div>
                            <div class="empty-state-icon mb-3">
                                <i class="mdi mdi-chart-line mdi-48px text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">Nenhum agendamento encontrado</h5>
                            <p class="text-muted small">Não há dados de agendamentos nos últimos 12 meses</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 🔹 Gráfico de Pizza e Tabela de Próximos Agendamentos --}}
<div class="row mt-3 dashboard-cards-row">
    {{-- Gráfico de Pizza - Distribuição por Especialidade --}}
    <div class="col-xl-6 col-lg-6 col-md-12 mb-4 mb-xl-0 dashboard-card-col">
        <div class="card card-rounded chart-card shadow-lg border-0 h-100">
            <div class="card-body d-flex flex-column">
                <h4 class="card-title mb-3">Distribuição por Especialidade</h4>
                <div class="flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 250px;">
                    @if(count($chartBySpecialty) > 0)
                        <canvas id="specialtyPieChart" style="max-width: 100%; max-height: 100%;"></canvas>
                    @else
                        <div class="text-center empty-state">
                            <div class="empty-state-icon mb-3">
                                <i class="mdi mdi-chart-pie mdi-48px text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">Nenhuma especialidade cadastrada</h5>
                            <p class="text-muted small">Cadastre especialidades para ver este gráfico</p>
                            <a href="{{ workspace_route('tenant.specialties.create') }}" class="btn btn-sm btn-primary mt-2">
                                <i class="mdi mdi-plus me-1"></i> Cadastrar Especialidade
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela - Próximos Agendamentos --}}
    <div class="col-xl-6 col-lg-6 col-md-12 dashboard-card-col">
        <div class="card card-rounded table-card shadow-lg border-0 h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="card-title mb-0">Próximos Agendamentos</h4>
                    <a href="{{ workspace_route('tenant.appointments.index') }}" class="btn btn-sm btn-primary">
                        Ver todos
                    </a>
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-hover mb-0">
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
<div class="row mt-3">
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
</div>

@push('scripts')
<script>
    // 🔹 Gráfico de Linha - Agendamentos últimos 12 meses
    const ctxLine = document.getElementById('appointmentsLineChart');
    if (ctxLine) {
        const chartData = @json(array_column($chartLast12Months, 'total'));
        const chartLabels = @json(array_column($chartLast12Months, 'short'));
        const currentMonth = new Date().getMonth();
        
        // Identificar índice do mês atual
        const currentMonthIndex = chartLabels.findIndex(label => {
            const monthNames = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            return monthNames[currentMonth] === label;
        });
        
        // Cores para pontos (destaque para mês atual)
        const pointColors = chartLabels.map((_, index) => 
            index === currentMonthIndex ? '#ff6b6b' : '#0d6efd'
        );
        const pointSizes = chartLabels.map((_, index) => 
            index === currentMonthIndex ? 8 : 4
        );
        
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Agendamentos',
                    data: chartData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: pointSizes,
                    pointBackgroundColor: pointColors,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: pointColors,
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#1f2937',
                        bodyColor: '#1f2937',
                        borderColor: '#e5e7eb',
                        borderWidth: 2,
                        padding: 16,
                        displayColors: false,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            title: function(context) {
                                const monthNames = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                                                   'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                                const monthIndex = chartLabels.indexOf(context[0].label);
                                return monthNames[monthIndex] + (monthIndex === currentMonthIndex ? ' (Mês Atual)' : '');
                            },
                            label: function(context) {
                                const value = context.parsed.y;
                                const total = chartData.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return [
                                    `Total de agendamentos: ${value}`,
                                    `Percentual: ${percentage}%`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.08)',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 1,
                            color: '#6b7280',
                            font: {
                                size: 11,
                                weight: '500'
                            },
                            padding: 8
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 11,
                                weight: '500'
                            },
                            padding: 8
                        }
                    }
                }
            }
        });
    }

    // 🔹 Gráfico de Donut - Distribuição por Especialidade
    const ctxPie = document.getElementById('specialtyPieChart');
    if (ctxPie) {
        const specialtyData = @json($chartBySpecialty);
        const labels = specialtyData.map(item => item.label);
        const values = specialtyData.map(item => item.value);
        
        // Paleta de cores moderna e harmoniosa
        const colors = [
            '#0d6efd', '#5b6fe0', '#8893ff', '#c9ceff',
            '#6c757d', '#adb5bd', '#dee2e6', '#e9ecef',
            '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4'
        ];

        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    const dataset = data.datasets[0];
                                    const total = dataset.data.reduce((a, b) => a + b, 0);
                                    return data.labels.map((label, i) => {
                                        const value = dataset.data[i];
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return {
                                            text: `${label} (${percentage}%)`,
                                            fillStyle: dataset.backgroundColor[i],
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#1f2937',
                        bodyColor: '#1f2937',
                        borderColor: '#e5e7eb',
                        borderWidth: 2,
                        padding: 16,
                        displayColors: true,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return [
                                    `Agendamentos: ${context.parsed}`,
                                    `Percentual: ${percentage}%`
                                ];
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: false,
                    duration: 800,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
</script>
@endpush

@endsection

