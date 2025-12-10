@extends('layouts.connect_plus.app')

@section('title', 'Atendimento - ' . \Carbon\Carbon::parse($date)->format('d/m/Y'))

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-account-heart text-primary me-2"></i>
            Atendimento Médico
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.medical-appointments.index') }}">Atendimento</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                </li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="mdi mdi-information me-1"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        {{-- Coluna Esquerda: Lista de Agendamentos --}}
        <div class="col-lg-4 col-md-5">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-calendar-clock me-2"></i>
                        Agendamentos do Dia
                    </h5>
                </div>
                <div class="card-body p-0" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <div class="list-group list-group-flush" id="appointments-list">
                        @forelse($appointments as $appointment)
                            @php
                                $isLate = $appointment->starts_at < now() && $appointment->status !== 'completed';
                                $isSelected = session('selected_appointment') === $appointment->id;
                            @endphp
                            <a href="#" 
                               class="list-group-item list-group-item-action appointment-item {{ $isSelected ? 'active-item' : '' }} {{ $isLate ? 'bg-danger-subtle' : '' }}"
                               data-appointment-id="{{ $appointment->id }}"
                               onclick="loadAppointmentDetails('{{ $appointment->id }}'); return false;">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">
                                            {{ $appointment->starts_at->format('H:i') }} — {{ $appointment->patient->full_name ?? 'N/A' }}
                                        </h6>
                                        <p class="mb-1 text-muted small">
                                            <strong>Consulta:</strong> {{ $appointment->type->name ?? 'N/A' }}
                                        </p>
                                        <p class="mb-0">
                                            @php
                                                $statusClasses = [
                                                    'scheduled' => 'badge-primary',
                                                    'confirmed' => 'badge-info',
                                                    'arrived' => 'badge-warning',
                                                    'in_service' => 'badge-success',
                                                    'completed' => 'badge-secondary',
                                                    'cancelled' => 'badge-danger',
                                                ];
                                                $statusClass = $statusClasses[$appointment->status] ?? 'badge-secondary';
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                {{ $appointment->status_translated }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                <i class="mdi mdi-calendar-remove" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Nenhum agendamento para este dia</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ workspace_route('tenant.medical-appointments.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="mdi mdi-arrow-left me-2"></i>
                        Voltar para Seleção
                    </a>
                </div>
            </div>
        </div>

        {{-- Coluna Direita: Detalhes do Atendimento --}}
        <div class="col-lg-8 col-md-7">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-account-details me-2"></i>
                        Detalhes do Atendimento
                    </h5>
                </div>
                <div class="card-body" id="appointment-details" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-information-outline" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0">Selecione um agendamento da lista para ver os detalhes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Visualizar Formulário Respondido --}}
    <div class="modal fade" id="form-response-modal" tabindex="-1" aria-labelledby="form-response-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="form-response-modal-label">
                        <i class="mdi mdi-file-document-check me-2"></i>
                        Formulário Respondido
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body" id="form-response-modal-body">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-2 text-muted">Carregando formulário...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Carregar detalhes do primeiro agendamento se houver um selecionado
    @if(session('selected_appointment'))
        loadAppointmentDetails('{{ session('selected_appointment') }}');
    @elseif($appointments && $appointments->count() > 0)
        loadAppointmentDetails('{{ $appointments->first()->id }}');
    @endif

    function loadAppointmentDetails(appointmentId) {
        // Remover classe active de todos os itens
        document.querySelectorAll('.appointment-item').forEach(item => {
            item.classList.remove('active-item');
        });

        // Adicionar classe active ao item clicado
        const clickedItem = document.querySelector(`[data-appointment-id="${appointmentId}"]`);
        if (clickedItem) {
            clickedItem.classList.add('active-item');
        }

        // Carregar detalhes via AJAX
        fetch(`{{ workspace_route('tenant.medical-appointments.details', ['appointment' => '__ID__']) }}`.replace('__ID__', appointmentId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html, application/json',
            },
            credentials: 'same-origin'
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            
            // Se for JSON (erro), tratar como JSON
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'Erro ao carregar detalhes');
                }
                return data.html || data;
            }
            
            // Se não for OK, tratar como erro
            if (!response.ok) {
                const text = await response.text();
                
                // Se for HTML (página de erro), extrair mensagem
                if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                    // Tentar extrair mensagem do HTML
                    const match = text.match(/403|Forbidden|não tem permissão/i);
                    if (match) {
                        throw new Error('Você não tem permissão para visualizar este agendamento.');
                    }
                    throw new Error('Erro ao carregar detalhes. Verifique se você tem permissão para visualizar este agendamento.');
                }
                
                // Se for texto simples, usar como mensagem
                throw new Error(text || 'Erro ao carregar detalhes');
            }
            
            return response.text();
        })
        .then(html => {
            document.getElementById('appointment-details').innerHTML = html;
        })
        .catch(error => {
            console.error('Erro ao carregar detalhes:', error);
            let errorMessage = 'Erro ao carregar detalhes do agendamento.';
            
            // Extrair mensagem de erro mais específica
            if (error.message) {
                if (error.message.includes('permissão') || error.message.includes('403') || error.message.includes('Forbidden')) {
                    errorMessage = 'Você não tem permissão para visualizar este agendamento.';
                } else if (error.message.includes('404') || error.message.includes('não encontrado')) {
                    errorMessage = 'Agendamento não encontrado.';
                } else if (!error.message.includes('<!DOCTYPE') && !error.message.includes('<html')) {
                    errorMessage = error.message;
                }
            }
            
            document.getElementById('appointment-details').innerHTML = `
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-circle me-1"></i>
                    ${errorMessage}
                </div>
            `;
        });
    }

    function updateStatus(appointmentId, status) {
        if (!confirm('Tem certeza que deseja alterar o status?')) {
            return;
        }

        const formData = new FormData();
        formData.append('status', status);
        formData.append('_token', '{{ csrf_token() }}');

        fetch(`{{ workspace_route('tenant.medical-appointments.update-status', ['appointment' => '__ID__']) }}`.replace('__ID__', appointmentId), {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recarregar a página para atualizar a lista
                window.location.reload();
            } else {
                alert('Erro ao atualizar status: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao atualizar status');
        });
    }

    function completeAppointment(appointmentId) {
        if (!confirm('Tem certeza que deseja finalizar este atendimento?')) {
            return;
        }

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');

        fetch(`{{ workspace_route('tenant.medical-appointments.complete', ['appointment' => '__ID__']) }}`.replace('__ID__', appointmentId), {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                return response.json();
            }
        })
        .then(data => {
            if (data && !data.success) {
                alert('Erro ao finalizar atendimento: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao finalizar atendimento');
        });
    }

    function viewFormResponse(appointmentId) {
        // Mostrar loading no modal
        const modalBody = document.getElementById('form-response-modal-body');
        modalBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2 text-muted">Carregando formulário...</p>
            </div>
        `;

        // Abrir modal
        const modal = new bootstrap.Modal(document.getElementById('form-response-modal'));
        modal.show();

        // Buscar resposta do formulário
        fetch(`{{ workspace_route('tenant.medical-appointments.form-response', ['appointment' => '__ID__']) }}`.replace('__ID__', appointmentId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalBody.innerHTML = data.html;
            } else {
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert-circle me-1"></i>
                        ${data.message || 'Erro ao carregar formulário.'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-circle me-1"></i>
                    Erro ao carregar formulário. Tente novamente.
                </div>
            `;
        });
    }
</script>

<style>
    .active-item {
        background-color: #e3f2fd !important;
        border-left: 4px solid #2196F3 !important;
        font-weight: 600;
    }

    .bg-danger-subtle {
        background-color: #ffebee !important;
    }

    .appointment-item:hover {
        background-color: #f5f5f5;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .appointment-item {
        transition: all 0.2s ease;
    }

    /* Garantir que as colunas fiquem lado a lado */
    @media (min-width: 768px) {
        .row.g-3 {
            display: flex;
            flex-wrap: nowrap;
        }
    }

    /* Scrollbar customizada para a lista de agendamentos */
    .card-body::-webkit-scrollbar {
        width: 6px;
    }

    .card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .card-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .card-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endpush

