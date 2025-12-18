@extends('layouts.network-admin')

@section('title', 'Agendamentos')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-calendar-clock"></i>
        </span> Métricas de Agendamentos
    </h3>
    <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('network.dashboard', ['network' => app('currentNetwork')->slug]) }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Agendamentos</li>
        </ul>
    </nav>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title"><i class="mdi mdi-filter-outline me-2 text-primary"></i>Filtros de Período</h4>
                <form method="GET" action="{{ route('network.appointments.index') }}" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold">Data Início</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="form-control border-primary-light">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold">Data Fim</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="form-control border-primary-light">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-gradient-primary w-100 btn-icon-text">
                                <i class="mdi mdi-magnify btn-icon-prepend"></i> Atualizar Dados
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Resumo --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-gradient-primary text-white card-img-holder shadow-sm">
                            <div class="card-body">
                                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                                <h4 class="font-weight-normal mb-3">Total Geral
                                    <i class="mdi mdi-chart-line mdi-24px float-right"></i>
                                </h4>
                                <h2 class="mb-2">{{ $stats['total'] ?? 0 }}</h2>
                                <small>No período selecionado</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card border shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title text-muted mb-4">Distribuição por Status</h4>
                                <div class="row">
                                    @foreach($stats['by_status'] ?? [] as $status => $count)
                                    <div class="col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="badge badge-outline-{{ $status == 'confirmado' ? 'success' : ($status == 'cancelado' ? 'danger' : 'info') }} me-2" style="width: 10px; height: 10px; padding: 0; border-radius: 50%;"></div>
                                            <div>
                                                <p class="mb-0 text-muted small">{{ ucfirst($status) }}</p>
                                                <h5 class="mb-0 font-weight-bold">{{ $count }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Por Especialidade --}}
                    @if(!empty($stats['by_specialty']))
                    <div class="col-md-6 grid-margin stretch-card">
                        <div class="card border shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title"><i class="mdi mdi-bookmark-outline me-2 text-info"></i>Por Especialidade</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th class="font-weight-bold">Especialidade</th>
                                                <th class="font-weight-bold text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stats['by_specialty'] as $item)
                                            <tr>
                                                <td>{{ $item['name'] }}</td>
                                                <td class="text-end"><span class="badge bg-gradient-info">{{ $item['count'] }}</span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Por Clínica --}}
                    @if(!empty($stats['by_clinic']))
                    <div class="col-md-6 grid-margin stretch-card">
                        <div class="card border shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title"><i class="mdi mdi-hospital-building me-2 text-success"></i>Por Clínica</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th class="font-weight-bold">Clínica</th>
                                                <th class="font-weight-bold text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stats['by_clinic'] as $item)
                                            <tr>
                                                <td>{{ $item['tenant_name'] }}</td>
                                                <td class="text-end"><span class="badge bg-gradient-success">{{ $item['count'] }}</span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-primary-light {
        border-color: #ebedf2;
    }
    .btn-gradient-primary {
        background: linear-gradient(to right, #da8cff, #9a55ff);
        border: 0;
        color: white;
    }
    .badge-outline-success { border: 2px solid #1bcfb4; background: #1bcfb4; }
    .badge-outline-danger { border: 2px solid #fe7c96; background: #fe7c96; }
    .badge-outline-info { border: 2px solid #25d5f2; background: #25d5f2; }
</style>
@endpush

