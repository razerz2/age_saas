@extends('layouts.connect_plus.app')

@section('title', 'Consultas Online')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Consultas Online </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Consultas Online</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Consultas Online</h4>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Médico</th>
                                    <th>Data/Hora</th>
                                    <th>Status</th>
                                    <th>Instruções Enviadas</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($appointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->patient->full_name ?? 'N/A' }}</td>
                                        <td>
                                            @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                                                {{ $appointment->calendar->doctor->user->name }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-info">Online</span>
                                            <span class="badge bg-{{ $appointment->status === 'scheduled' ? 'success' : ($appointment->status === 'canceled' ? 'danger' : 'warning') }}">
                                                {{ $appointment->status_translated }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($appointment->onlineInstructions)
                                                @if($appointment->onlineInstructions->sent_by_email_at)
                                                    <span class="badge bg-success" title="Enviado por email em {{ $appointment->onlineInstructions->sent_by_email_at->format('d/m/Y H:i') }}">
                                                        <i class="mdi mdi-email"></i> Email
                                                    </span>
                                                @endif
                                                @if($appointment->onlineInstructions->sent_by_whatsapp_at)
                                                    <span class="badge bg-success" title="Enviado por WhatsApp em {{ $appointment->onlineInstructions->sent_by_whatsapp_at->format('d/m/Y H:i') }}">
                                                        <i class="mdi mdi-whatsapp"></i> WhatsApp
                                                    </span>
                                                @endif
                                                @if(!$appointment->onlineInstructions->sent_by_email_at && !$appointment->onlineInstructions->sent_by_whatsapp_at)
                                                    <span class="badge bg-warning">Não enviado</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Sem instruções</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('tenant.online-appointments.show', $appointment->id) }}" class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye"></i> Gerenciar
                                            </a>
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
            },
            "order": [[2, "desc"]]
        });
    });
</script>
@endpush

