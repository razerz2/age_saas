@extends('layouts.connect_plus.app')

@section('title', 'Agendamentos')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Agendamentos </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Agendamentos</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Agendamentos</h4>

                    <a href="{{ route('tenant.appointments.create') }}" class="btn btn-primary mb-3">
                        <i class="mdi mdi-plus"></i> Novo Agendamento
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Paciente</th>
                                    <th>Calendário</th>
                                    <th>Tipo</th>
                                    <th>Início</th>
                                    <th>Fim</th>
                                    <th>Modo</th>
                                    <th>Status</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($appointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->id }}</td>
                                        <td>{{ $appointment->patient->full_name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->calendar->name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->type->name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>{{ $appointment->ends_at ? $appointment->ends_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>
                                            @if($appointment->appointment_mode === 'online')
                                                <span class="badge bg-info">Online</span>
                                            @else
                                                <span class="badge bg-success">Presencial</span>
                                            @endif
                                        </td>
                                        <td>{{ $appointment->status_translated }}</td>
                                        <td>
                                            <a href="{{ route('tenant.appointments.show', $appointment->id) }}" class="btn btn-info btn-sm">Ver</a>
                                            <a href="{{ route('tenant.appointments.edit', $appointment->id) }}" class="btn btn-warning btn-sm">Editar</a>
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

