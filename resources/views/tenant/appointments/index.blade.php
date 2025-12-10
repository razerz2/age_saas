@extends('layouts.connect_plus.app')

@section('title', 'Agendamentos')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Agendamentos </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
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

                    {{-- Mensagens de Erro --}}
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="mdi mdi-alert-circle me-3" style="font-size: 1.5rem; flex-shrink: 0; margin-top: 0.25rem;"></i>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-2">Não é possível criar agendamentos</h5>
                                    <div class="mb-0">{!! session('error') !!}</div>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                    @endif

                    {{-- Mensagens de Sucesso --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
                            <i class="mdi mdi-check-circle me-2"></i>
                            {!! session('success') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                    @endif

                    <a href="{{ workspace_route('tenant.appointments.create') }}" class="btn btn-primary mb-3">
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
                                        <td>{{ truncate_uuid($appointment->id) }}</td>
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
                                            <a href="{{ workspace_route('tenant.appointments.show', $appointment->id) }}" class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye"></i> Ver
                                            </a>
                                            <a href="{{ workspace_route('tenant.appointments.edit', $appointment->id) }}" class="btn btn-warning btn-sm">
                                                <i class="mdi mdi-pencil"></i> Editar
                                            </a>
                                            @if (Auth::guard('tenant')->user()->role === 'admin')
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteAppointmentModal{{ $appointment->id }}">
                                                    <i class="mdi mdi-delete"></i> Excluir
                                                </button>
                                                
                                                {{-- Modal de Confirmação de Exclusão --}}
                                                <div class="modal fade" id="deleteAppointmentModal{{ $appointment->id }}" tabindex="-1" 
                                                     aria-labelledby="deleteAppointmentModalLabel{{ $appointment->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title" id="deleteAppointmentModalLabel{{ $appointment->id }}">
                                                                    <i class="mdi mdi-alert-circle me-2"></i>
                                                                    Confirmar Exclusão
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="mb-3">
                                                                    <strong>Tem certeza que deseja excluir este agendamento?</strong>
                                                                </p>
                                                                <div class="alert alert-warning mb-0">
                                                                    <i class="mdi mdi-information-outline me-2"></i>
                                                                    <strong>Atenção:</strong> Esta ação não pode ser desfeita. O agendamento será removido permanentemente.
                                                                </div>
                                                                <hr>
                                                                <div class="mb-2">
                                                                    <strong>Paciente:</strong> {{ $appointment->patient->full_name ?? 'N/A' }}
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>Data/Hora:</strong> {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>Médico:</strong> {{ $appointment->doctor->user->name_full ?? $appointment->doctor->user->name ?? 'N/A' }}
                                                                </div>
                                                                <div class="mb-0">
                                                                    <strong>Status:</strong> {{ $appointment->status_translated }}
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                    <i class="mdi mdi-close me-1"></i>
                                                                    Cancelar
                                                                </button>
                                                                <form action="{{ workspace_route('tenant.appointments.destroy', $appointment->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">
                                                                        <i class="mdi mdi-delete me-1"></i>
                                                                        Sim, Excluir
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
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

