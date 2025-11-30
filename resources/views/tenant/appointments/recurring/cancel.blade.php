@extends('layouts.connect_plus.app')

@section('title', 'Cancelar Agendamento Recorrente')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Cancelar Agendamento Recorrente </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.recurring-appointments.index') }}">Agendamentos Recorrentes</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Cancelar</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="mdi mdi-alert-circle text-warning me-2"></i>
                        Confirmar Cancelamento
                    </h4>

                    <div class="alert alert-warning">
                        <strong>Atenção!</strong> Ao cancelar este agendamento recorrente:
                        <ul class="mb-0 mt-2">
                            <li>Não serão geradas novas sessões</li>
                            <li>Os horários bloqueados serão liberados</li>
                            <li>As sessões já geradas não serão afetadas</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h5>Informações do Agendamento:</h5>
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
                                <th>Sessões Geradas:</th>
                                <td>{{ $recurringAppointment->getGeneratedSessionsCount() }}</td>
                            </tr>
                        </table>
                    </div>

                    <form action="{{ route('tenant.recurring-appointments.destroy', $recurringAppointment->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('tenant.recurring-appointments.show', $recurringAppointment->id) }}" class="btn btn-light me-2">
                                Voltar
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="mdi mdi-cancel"></i> Confirmar Cancelamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

