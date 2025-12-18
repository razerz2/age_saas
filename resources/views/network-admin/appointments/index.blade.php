@extends('layouts.network-admin')

@section('title', 'Agendamentos')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-calendar-clock"></i>
        </span> Métricas de Agendamentos
    </h3>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                {{-- Filtros de período --}}
                <form method="GET" action="{{ route('network.appointments.index') }}" class="mb-4">
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

                {{-- Resumo --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-gradient-primary text-white">
                            <div class="card-body">
                                <h4 class="mb-3">Total de Agendamentos</h4>
                                <h2>{{ $stats['total'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-3">Por Status</h4>
                                <div class="row">
                                    @foreach($stats['by_status'] ?? [] as $status => $count)
                                    <div class="col-md-3">
                                        <strong>{{ ucfirst($status) }}:</strong> {{ $count }}
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Por Especialidade --}}
                @if(!empty($stats['by_specialty']))
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="mb-3">Agendamentos por Especialidade</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Especialidade</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['by_specialty'] as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td><strong>{{ $item['count'] }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Por Clínica --}}
                @if(!empty($stats['by_clinic']))
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">Agendamentos por Clínica</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Clínica</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['by_clinic'] as $item)
                                    <tr>
                                        <td>{{ $item['tenant_name'] }}</td>
                                        <td><strong>{{ $item['count'] }}</strong></td>
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

