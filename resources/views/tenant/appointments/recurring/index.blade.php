@extends('layouts.connect_plus.app')

@section('title', 'Agendamentos Recorrentes')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Agendamentos Recorrentes </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Agendamentos Recorrentes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Agendamentos Recorrentes</h4>

                    <a href="{{ workspace_route('tenant.recurring-appointments.create') }}" class="btn btn-primary mb-3">
                        <i class="mdi mdi-plus"></i> Novo Agendamento Recorrente
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Médico</th>
                                    <th>Tipo</th>
                                    <th>Data Inicial</th>
                                    <th>Término</th>
                                    <th>Regras</th>
                                    <th>Status</th>
                                    <th>Sessões Geradas</th>
                                    <th style="width: 200px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($recurringAppointments as $recurring)
                                    <tr>
                                        <td>{{ $recurring->patient->full_name ?? 'N/A' }}</td>
                                        <td>{{ $recurring->doctor->user->name_full ?? $recurring->doctor->user->name ?? 'N/A' }}</td>
                                        <td>{{ $recurring->appointmentType->name ?? 'N/A' }}</td>
                                        <td>{{ $recurring->start_date->format('d/m/Y') }}</td>
                                        <td>
                                            @if($recurring->end_type === 'none')
                                                <span class="badge bg-info">Sem limite</span>
                                            @elseif($recurring->end_type === 'total_sessions')
                                                <span class="badge bg-warning">{{ $recurring->total_sessions }} sessões</span>
                                            @elseif($recurring->end_type === 'date')
                                                {{ $recurring->end_date->format('d/m/Y') }}
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($recurring->rules as $rule)
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($rule->weekday) }} {{ $rule->start_time }}-{{ $rule->end_time }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($recurring->active)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-danger">Cancelado</span>
                                            @endif
                                        </td>
                                        <td>{{ $recurring->getGeneratedSessionsCount() }}</td>
                                        <td>
                                            <a href="{{ workspace_route('tenant.recurring-appointments.show', ['id' => $recurring->id]) }}" class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye"></i> Ver
                                            </a>
                                            <a href="{{ workspace_route('tenant.recurring-appointments.edit', ['id' => $recurring->id]) }}" class="btn btn-warning btn-sm">
                                                <i class="mdi mdi-pencil"></i> Editar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $recurringAppointments->links() }}
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

