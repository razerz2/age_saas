@extends('layouts.connect_plus.app')

@section('title', 'Horários Comerciais')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Horários Comerciais </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Horários Comerciais</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Horários Comerciais</h4>

                    <a href="{{ workspace_route('tenant.business-hours.create') }}" class="btn btn-primary mb-3">
                        + Novo
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Médico</th>
                                    <th>Dia da Semana</th>
                                    <th>Horário Início</th>
                                    <th>Horário Fim</th>
                                    <th>Intervalo</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($businessHours as $businessHour)
                                    <tr>
                                        <td>{{ truncate_uuid($businessHour->id) }}</td>
                                        <td>{{ $businessHour->doctor->user->name ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                                            @endphp
                                            {{ $days[$businessHour->weekday] ?? $businessHour->weekday }}
                                        </td>
                                        <td>{{ $businessHour->start_time }}</td>
                                        <td>{{ $businessHour->end_time }}</td>
                                        <td>
                                            @if($businessHour->break_start_time && $businessHour->break_end_time)
                                                <span class="badge bg-info">
                                                    <i class="mdi mdi-pause-circle me-1"></i>
                                                    {{ $businessHour->break_start_time }} - {{ $businessHour->break_end_time }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ workspace_route('tenant.business-hours.show', $businessHour->id) }}" class="btn btn-info btn-sm">Ver</a>
                                            <a href="{{ workspace_route('tenant.business-hours.edit', $businessHour->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#datatable-list').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
            }
        });
    });
</script>
@endpush

