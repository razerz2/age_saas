@extends('layouts.connect_plus.app')

@section('title', 'Integração Google Calendar')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-google text-primary me-2"></i>
            Integração Google Calendar
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.integrations.index') }}">Integrações</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Google Calendar</li>
            </ol>
        </nav>
    </div>

    {{-- Alertas --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="mdi mdi-information me-1"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Card Informativo --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="mdi mdi-information-outline text-info me-2"></i>
                        Como Funciona a Integração
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Sincronização Automática:</strong> Todos os agendamentos são sincronizados automaticamente com o Google Calendar do médico
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Criação:</strong> Ao criar um agendamento, o evento é criado no Google Calendar
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Edição:</strong> Ao editar um agendamento, o evento é atualizado no Google Calendar
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Cancelamento:</strong> Ao cancelar um agendamento, o evento é removido do Google Calendar
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Agendamentos Recorrentes:</strong> São sincronizados como eventos recorrentes (RRULE) no Google Calendar
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Conta Individual:</strong> Cada médico conecta sua própria conta do Google Calendar
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Renovação Automática:</strong> Tokens são renovados automaticamente quando necessário
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Áreas Sincronizadas:</strong> Funciona para agendamentos criados em qualquer área do sistema (administrativa, pública, portal do paciente)
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="mdi mdi-google text-primary me-2"></i>
                        Integração Google Calendar por Médico
                    </h4>

                    <p class="text-muted mb-4">
                        Cada médico pode conectar sua própria conta do Google Calendar. 
                        Os agendamentos serão sincronizados automaticamente com o calendário do médico conectado.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Médico</th>
                                    <th>Status</th>
                                    <th>Última Atualização</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($doctors as $doctor)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="mdi mdi-account-circle text-primary" style="font-size: 2rem;"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $doctor->user->name_full ?? $doctor->user->name }}</strong>
                                                    @if ($doctor->crm_number)
                                                        <br>
                                                        <small class="text-muted">CRM: {{ $doctor->crm_number }}/{{ $doctor->crm_state }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($doctor->googleCalendarToken)
                                                <div>
                                                    <span class="badge bg-success">
                                                        <i class="mdi mdi-check-circle me-1"></i>
                                                        Conectado
                                                    </span>
                                                    @if ($doctor->googleCalendarToken->isExpired())
                                                        <br>
                                                        <small class="text-warning mt-1 d-inline-block">
                                                            <i class="mdi mdi-alert me-1"></i>
                                                            Token expirado (será renovado automaticamente)
                                                        </small>
                                                    @else
                                                        <br>
                                                        <small class="text-success mt-1 d-inline-block">
                                                            <i class="mdi mdi-sync me-1"></i>
                                                            Sincronização ativa
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="mdi mdi-close-circle me-1"></i>
                                                    Desconectado
                                                </span>
                                                <br>
                                                <small class="text-muted mt-1 d-inline-block">
                                                    Clique em "Conectar Google" para ativar
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($doctor->googleCalendarToken && $doctor->googleCalendarToken->updated_at)
                                                {{ $doctor->googleCalendarToken->updated_at->format('d/m/Y H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($user->role === 'admin' || ($user->role === 'doctor' && $user->doctor && $user->doctor->id === $doctor->id))
                                                {{-- Admin e médico (para si mesmo) podem conectar/desconectar --}}
                                                @if ($doctor->googleCalendarToken)
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-info btn-sm" 
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="top" 
                                                                title="Status da integração">
                                                            <i class="mdi mdi-information-outline"></i>
                                                        </button>
                                                        <form action="{{ workspace_route('tenant.integrations.google.disconnect', ['doctor' => $doctor->id]) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Tem certeza que deseja desconectar a integração do Google Calendar para este médico?\n\nOs eventos já criados no Google Calendar não serão removidos automaticamente.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="mdi mdi-link-variant-off me-1"></i>
                                                                Desconectar
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <a href="{{ workspace_route('tenant.integrations.google.connect', ['doctor' => $doctor->id]) }}" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="mdi mdi-google me-1"></i>
                                                        Conectar Google
                                                    </a>
                                                @endif
                                            @else
                                                {{-- Usuário comum: apenas visualiza status --}}
                                                <span class="text-muted">
                                                    <i class="mdi mdi-eye me-1"></i>
                                                    Apenas visualização
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="mdi mdi-information-outline me-2"></i>
                                            Nenhum médico cadastrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <a href="{{ workspace_route('tenant.integrations.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i>
                            Voltar para Integrações
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializa tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

