@extends('layouts.network-admin')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-view-dashboard"></i>
        </span> Dashboard
    </h3>
</div>

<div class="row">
    {{-- Total de Clínicas --}}
    <div class="col-md-3 stretch-card grid-margin">
        <div class="card bg-gradient-danger card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                <h4 class="font-weight-normal mb-3">Total de Clínicas
                    <i class="mdi mdi-hospital-building mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">{{ $totalClinics }}</h2>
            </div>
        </div>
    </div>

    {{-- Total de Médicos --}}
    <div class="col-md-3 stretch-card grid-margin">
        <div class="card bg-gradient-info card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                <h4 class="font-weight-normal mb-3">Total de Médicos
                    <i class="mdi mdi-doctor mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">{{ $totalDoctors }}</h2>
            </div>
        </div>
    </div>

    {{-- Agendamentos do Mês --}}
    <div class="col-md-3 stretch-card grid-margin">
        <div class="card bg-gradient-success card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                <h4 class="font-weight-normal mb-3">Agendamentos (Este Mês)
                    <i class="mdi mdi-calendar-clock mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">{{ $appointmentsThisMonth }}</h2>
            </div>
        </div>
    </div>

    {{-- Crescimento --}}
    <div class="col-md-3 stretch-card grid-margin">
        <div class="card bg-gradient-warning card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                <h4 class="font-weight-normal mb-3">Crescimento Mensal
                    <i class="mdi mdi-chart-line mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">
                    {{ $growthRate >= 0 ? '+' : '' }}{{ $growthRate }}%
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Agendamentos por Especialidade</h4>
                <canvas id="specialtyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Agendamentos por Clínica</h4>
                <canvas id="clinicChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

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
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            }]
        }
    });

    // Gráfico por clínica
    const clinicCtx = document.getElementById('clinicChart').getContext('2d');
    new Chart(clinicCtx, {
        type: 'bar',
        data: {
            labels: @json(collect($statsByClinic)->pluck('tenant_name')->toArray()),
            datasets: [{
                label: 'Agendamentos',
                data: @json(collect($statsByClinic)->pluck('count')->toArray()),
                backgroundColor: '#36A2EB'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush

