@extends('layouts.tailadmin.app')

@section('title', 'Atendimento - ' . \Carbon\Carbon::parse($date)->format('d/m/Y'))
@section('page', 'medical_appointments')

@section('content')

    @php
        $initialAppointmentId = session('selected_appointment') ?? ($appointments && $appointments->count() > 0 ? $appointments->first()->id : null);
    @endphp

    <div id="medical-appointments-config"
         data-details-url-template="{{ workspace_route('tenant.medical-appointments.details', ['appointment' => '__ID__']) }}"
         data-update-status-url-template="{{ workspace_route('tenant.medical-appointments.update-status', ['appointment' => '__ID__']) }}"
         data-complete-url-template="{{ workspace_route('tenant.medical-appointments.complete', ['appointment' => '__ID__']) }}"
         data-form-response-url-template="{{ workspace_route('tenant.medical-appointments.form-response', ['appointment' => '__ID__']) }}"
         data-csrf="{{ csrf_token() }}"
         data-initial-id="{{ $initialAppointmentId }}"></div>

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
                               data-appointment-id="{{ $appointment->id }}">
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
                        <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.medical-appointments.index') }}"
                            class="w-full justify-center border-primary text-primary bg-transparent hover:bg-primary/10 dark:border-primary/40 dark:text-primary dark:hover:bg-primary/20">
                            <i class="mdi mdi-arrow-left"></i>
                            Voltar para Seleção
                        </x-tailadmin-button>
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
                    <x-tailadmin-button type="button" variant="secondary" size="sm" data-bs-dismiss="modal">
                        <i class="mdi mdi-close"></i>
                        Fechar
                    </x-tailadmin-button>
                </div>
            </div>
        </div>
    </div>

@endsection


