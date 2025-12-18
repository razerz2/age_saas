@extends('layouts.network-admin')

@section('title', 'Dashboard')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2 shadow-sm">
            <i class="mdi mdi-view-dashboard"></i>
        </span> Dashboard de Rede
    </h3>
    <nav aria-label="breadcrumb">
        <ul class="breadcrumb mb-0">
            <li class="breadcrumb-item active" aria-current="page">
                <span class="text-muted">Visão Geral</span>
                <i class="mdi mdi-chart-box-outline icon-sm text-primary align-middle ms-2"></i>
            </li>
        </ul>
    </nav>
</div>

<div class="row">
    {{-- Total de Clínicas --}}
    <div class="col-md-3 stretch-card grid-margin">
        <div class="card bg-premium-red card-img-holder text-white shadow hover-card">
            <div class="card-body py-4">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                <div class="d-flex justify-content-between">
                    <h4 class="font-weight-normal mb-3">Total de Clínicas</h4>
                    <i class="mdi mdi-hospital-building mdi-36px opacity-5"></i>
                </div>
                <h2 class="mb-2 font-weight-bold">{{ $totalClinics }}</h2>
                <p class="card-text small mb-0"><i class="mdi mdi-check-circle me-1"></i> Clínicas integradas</p>
            </div>
        </div>
    </div>

    {{-- Total de Médicos --}}
    <div class="col-md-3 stretch-card grid-margin">
        <div class="card bg-premium-blue card-img-holder text-white shadow hover-card">
            <div class="card-body py-4">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                <div class="d-flex justify-content-between">
                    <h4 class="font-weight-normal mb-3">Total de Médicos</h4>
                    <i class="mdi mdi-doctor mdi-36px opacity-5"></i>
                </div>
                <h2 class="mb-2 font-weight-bold">{{ $totalDoctors }}</h2>
                <p class="card-text small mb-0"><i class="mdi mdi-account-group me-1"></i> Profissionais ativos</p>
            </div>
        </div>
    </div>

    {{-- Agendamentos do Mês --}}
    <div class="col-md-3 stretch-card grid-margin">
        <div class="card bg-premium-green card-img-holder text-white shadow hover-card">
            <div class="card-body py-4">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                <div class="d-flex justify-content-between">
                    <h4 class="font-weight-normal mb-3">Agendamentos</h4>
                    <i class="mdi mdi-calendar-check mdi-36px opacity-5"></i>
                </div>
                <h2 class="mb-2 font-weight-bold">{{ $appointmentsThisMonth }}</h2>
                <p class="card-text small mb-0"><i class="mdi mdi-clock-outline me-1"></i> Competência atual</p>
            </div>
        </div>
    </div>

    {{-- Crescimento --}}
    <div class="col-md-3 stretch-card grid-margin">
        <div class="card bg-premium-yellow card-img-holder text-white shadow hover-card">
            <div class="card-body py-4">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                <div class="d-flex justify-content-between">
                    <h4 class="font-weight-normal mb-3">Crescimento</h4>
                    <i class="mdi mdi-trending-up mdi-36px opacity-5"></i>
                </div>
                <h2 class="mb-2 font-weight-bold">
                    {{ $growthRate >= 0 ? '+' : '' }}{{ $growthRate }}%
                </h2>
                <p class="card-text small mb-0"><i class="mdi mdi-arrow-up-bold me-1"></i> Comparado ao mês anterior</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Evolução por Clínica</h4>
                    <div class="badge badge-outline-primary">Top Performance</div>
                </div>
                <div class="chart-container" style="position: relative; height:350px;">
                    <canvas id="clinicChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title mb-4">Agendamentos por Especialidade</h4>
                <div class="chart-container" style="position: relative; height:300px;">
                    <canvas id="specialtyChart"></canvas>
                </div>
                <div id="specialty-legend" class="mt-4"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hover-card {
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
        cursor: pointer;
    }
    .hover-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 14px 28px rgba(0,0,0,0.15), 0 10px 10px rgba(0,0,0,0.10) !important;
    }
    .opacity-5 {
        opacity: 0.5;
    }
    .bg-premium-red {
        background: linear-gradient(135deg, #ff5f6d 0%, #ffc371 100%) !important;
    }
    .bg-premium-blue {
        background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%) !important;
    }
    .bg-premium-green {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
    }
    .bg-premium-yellow {
        background: linear-gradient(135deg, #f09819 0%, #edde5d 100%) !important;
    }
    .card-title {
        font-weight: 700;
        color: #343a40;
        font-size: 1.1rem;
    }
    .badge-outline-primary {
        color: #b66dff;
        border: 1px solid #b66dff;
    }
</style>
@endpush

@push('styles')
<style>
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.12) !important;
    }
    .card-title {
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    .bg-gradient-primary {
        background: linear-gradient(to right, #da8cff, #9a55ff) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico por especialidade
    const specialtyCtx = document.getElementById('specialtyChart').getContext('2d');
    new Chart(specialtyCtx, {
        type: 'doughnut',
        data: {
            labels: @json(collect($statsBySpecialty)->pluck('name')->toArray()),
            datasets: [{
                data: @json(collect($statsBySpecialty)->pluck('count')->toArray()),
                backgroundColor: ['#b66dff', '#2193b0', '#11998e', '#f09819', '#ff5f6d'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            cutout: '75%',
            plugins: {
                legend: {
                    display: false
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico por clínica
    const clinicCtx = document.getElementById('clinicChart').getContext('2d');
    new Chart(clinicCtx, {
        type: 'bar',
        data: {
            labels: @json(collect($statsByClinic)->pluck('tenant_name')->toArray()),
            datasets: [{
                label: 'Total de Agendamentos',
                data: @json(collect($statsByClinic)->pluck('count')->toArray()),
                backgroundColor: 'rgba(182, 109, 255, 0.8)',
                borderColor: '#b66dff',
                borderWidth: 1,
                borderRadius: 5,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endpush

