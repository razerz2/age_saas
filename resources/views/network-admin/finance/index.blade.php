@extends('layouts.network-admin')

@section('title', 'Financeiro')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-cash"></i>
        </span> Indicadores Financeiros
    </h3>
    <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('network.dashboard', ['network' => app('currentNetwork')->slug]) }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Financeiro</li>
        </ul>
    </nav>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title"><i class="mdi mdi-filter-outline me-2 text-primary"></i>Filtros de Período</h4>
                <form method="GET" action="{{ route('network.finance.index') }}" class="mb-4">
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
                                <i class="mdi mdi-magnify btn-icon-prepend"></i> Atualizar Relatório
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Resumo Financeiro --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-gradient-success text-white card-img-holder shadow-sm">
                            <div class="card-body">
                                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                                <h5 class="font-weight-normal mb-3">Receitas
                                    <i class="mdi mdi-cash-plus mdi-24px float-right"></i>
                                </h5>
                                <h3>R$ {{ number_format($stats['total_revenue'] ?? 0, 2, ',', '.') }}</h3>
                                <small>Total de entradas</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-gradient-danger text-white card-img-holder shadow-sm">
                            <div class="card-body">
                                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                                <h5 class="font-weight-normal mb-3">Despesas
                                    <i class="mdi mdi-cash-minus mdi-24px float-right"></i>
                                </h5>
                                <h3>R$ {{ number_format($stats['total_expenses'] ?? 0, 2, ',', '.') }}</h3>
                                <small>Total de saídas</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-gradient-info text-white card-img-holder shadow-sm">
                            <div class="card-body">
                                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                                <h5 class="font-weight-normal mb-3">Saldo Líquido
                                    <i class="mdi mdi-scale mdi-24px float-right"></i>
                                </h5>
                                <h3>R$ {{ number_format($stats['balance'] ?? 0, 2, ',', '.') }}</h3>
                                <small>Resultado no período</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-gradient-warning text-white card-img-holder shadow-sm">
                            <div class="card-body">
                                <img src="{{ asset('connect_plus/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image">
                                <h5 class="font-weight-normal mb-3">Ticket Médio
                                    <i class="mdi mdi-trending-up mdi-24px float-right"></i>
                                </h5>
                                <h3>R$ {{ number_format($stats['average_ticket'] ?? 0, 2, ',', '.') }}</h3>
                                <small>Valor médio por atendimento</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Por Clínica --}}
                @if(!empty($stats['by_clinic']))
                <div class="card border shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title"><i class="mdi mdi-hospital-building me-2 text-primary"></i>Indicadores por Clínica</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="font-weight-bold">Clínica</th>
                                        <th class="font-weight-bold text-end">Receitas</th>
                                        <th class="font-weight-bold text-end">Despesas</th>
                                        <th class="font-weight-bold text-end">Saldo</th>
                                        <th class="font-weight-bold text-center">Cobranças</th>
                                        <th class="font-weight-bold text-center">Taxa Pagam.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['by_clinic'] as $item)
                                    <tr>
                                        <td class="font-weight-bold">{{ $item['tenant_name'] }}</td>
                                        <td class="text-end text-success">R$ {{ number_format($item['revenue'], 2, ',', '.') }}</td>
                                        <td class="text-end text-danger">R$ {{ number_format($item['expenses'], 2, ',', '.') }}</td>
                                        <td class="text-end">
                                            <span class="badge {{ $item['balance'] >= 0 ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                R$ {{ number_format($item['balance'], 2, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $item['charges_count'] }}</td>
                                        <td class="text-center">
                                            @php
                                                $rate = $item['charges_count'] > 0 ? ($item['paid_charges_count'] / $item['charges_count']) * 100 : 0;
                                            @endphp
                                            <div class="progress" style="height: 10px; width: 100px; margin: 0 auto;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $rate }}%" aria-valuenow="{{ $rate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small class="text-muted">{{ number_format($rate, 1) }}% ({{ $item['paid_charges_count'] }})</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
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
    .table thead th {
        border-top: 0;
        border-bottom-width: 1px;
    }
</style>
@endpush

