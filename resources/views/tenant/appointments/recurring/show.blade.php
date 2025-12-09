@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Agendamento Recorrente')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes do Agendamento Recorrente </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.recurring-appointments.index') }}">Agendamentos Recorrentes</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="mdi mdi-calendar-text text-primary me-2"></i>
                                Detalhes do Agendamento Recorrente
                            </h4>
                        </div>
                        <div>
                            <a href="{{ workspace_route('tenant.recurring-appointments.edit', ['id' => $recurringAppointment->id]) }}" class="btn btn-warning">
                                <i class="mdi mdi-pencil"></i> Editar
                            </a>
                            @if($recurringAppointment->active)
                                <a href="{{ workspace_route('tenant.recurring-appointments.cancel', ['id' => $recurringAppointment->id]) }}" class="btn btn-danger">
                                    <i class="mdi mdi-cancel"></i> Cancelar
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Informações Básicas</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Paciente:</th>
                                    <td>{{ $recurringAppointment->patient->full_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Médico:</th>
                                    <td>{{ $recurringAppointment->doctor->user->name_full ?? $recurringAppointment->doctor->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Tipo de Consulta:</th>
                                    <td>{{ $recurringAppointment->appointmentType->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Data Inicial:</th>
                                    <td>{{ $recurringAppointment->start_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Tipo de Término:</th>
                                    <td>
                                        @if($recurringAppointment->end_type === 'none')
                                            <span class="badge bg-info">Sem limite (infinito)</span>
                                        @elseif($recurringAppointment->end_type === 'total_sessions')
                                            <span class="badge bg-warning">{{ $recurringAppointment->total_sessions }} sessões</span>
                                        @elseif($recurringAppointment->end_type === 'date')
                                            {{ $recurringAppointment->end_date->format('d/m/Y') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($recurringAppointment->active)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-danger">Cancelado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sessões Geradas:</th>
                                    <td>{{ $recurringAppointment->getGeneratedSessionsCount() }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="mb-3">Regras de Recorrência</h5>
                            <div class="list-group">
                                @foreach($recurringAppointment->rules as $rule)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ ucfirst($rule->weekday) }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $rule->start_time }} - {{ $rule->end_time }}
                                                </small>
                                                <br>
                                                <small>
                                                    Frequência: 
                                                    @if($rule->frequency === 'weekly') Semanal
                                                    @elseif($rule->frequency === 'biweekly') Quinzenal
                                                    @elseif($rule->frequency === 'monthly') Mensal
                                                    @endif
                                                    @if($rule->interval > 1)
                                                        (Intervalo: {{ $rule->interval }})
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($recurringAppointment->appointments->count() > 0)
                        <div class="mt-4">
                            <h5 class="mb-3">Sessões Geradas</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data/Hora Início</th>
                                            <th>Data/Hora Fim</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recurringAppointment->appointments as $appointment)
                                            <tr>
                                                <td>{{ $appointment->starts_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $appointment->ends_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $appointment->status_translated }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

@endsection

