@extends('layouts.network-admin')

@section('title', 'Financeiro')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-cash"></i>
        </span> Indicadores Financeiros
    </h3>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                {{-- Filtros de período --}}
                <form method="GET" action="{{ route('network.finance.index') }}" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Data Início</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data Fim</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </div>
                </form>

                {{-- Resumo Financeiro --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-gradient-success text-white">
                            <div class="card-body">
                                <h5 class="mb-3">Receitas</h5>
                                <h3>R$ {{ number_format($stats['total_revenue'] ?? 0, 2, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-gradient-danger text-white">
                            <div class="card-body">
                                <h5 class="mb-3">Despesas</h5>
                                <h3>R$ {{ number_format($stats['total_expenses'] ?? 0, 2, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-gradient-info text-white">
                            <div class="card-body">
                                <h5 class="mb-3">Saldo</h5>
                                <h3>R$ {{ number_format($stats['balance'] ?? 0, 2, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-gradient-warning text-white">
                            <div class="card-body">
                                <h5 class="mb-3">Ticket Médio</h5>
                                <h3>R$ {{ number_format($stats['average_ticket'] ?? 0, 2, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Por Clínica --}}
                @if(!empty($stats['by_clinic']))
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">Indicadores por Clínica</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Clínica</th>
                                        <th>Receitas</th>
                                        <th>Despesas</th>
                                        <th>Saldo</th>
                                        <th>Cobranças</th>
                                        <th>Pagas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['by_clinic'] as $item)
                                    <tr>
                                        <td>{{ $item['tenant_name'] }}</td>
                                        <td>R$ {{ number_format($item['revenue'], 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($item['expenses'], 2, ',', '.') }}</td>
                                        <td>
                                            <strong class="{{ $item['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                R$ {{ number_format($item['balance'], 2, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td>{{ $item['charges_count'] }}</td>
                                        <td>{{ $item['paid_charges_count'] }}</td>
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

