@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Agendamento')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-calendar-clock text-primary me-2"></i>
            Detalhes do Agendamento
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.appointments.index') }}">Agendamentos</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    {{-- Header do Card --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-calendar-check text-primary me-2"></i>
                            Informações do Agendamento
                        </h4>
                        <div>
                            <a href="{{ workspace_route('tenant.appointments.edit', $appointment->id) }}" class="btn btn-warning btn-sm">
                                <i class="mdi mdi-pencil me-1"></i> Editar
                            </a>
                            <a href="{{ workspace_route('tenant.appointments.index') }}" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left me-1"></i> Voltar
                            </a>
                        </div>
                    </div>

                    {{-- Status Badge e Modo --}}
                    <div class="mb-4 d-flex gap-2">
                        @php
                            $statusBadges = [
                                'pending' => ['bg-warning', 'mdi-clock-outline'],
                                'confirmed' => ['bg-success', 'mdi-check-circle'],
                                'cancelled' => ['bg-danger', 'mdi-cancel'],
                                'completed' => ['bg-info', 'mdi-check-all'],
                            ];
                            $statusInfo = $statusBadges[$appointment->status] ?? ['bg-secondary', 'mdi-help-circle'];
                        @endphp
                        <span class="badge {{ $statusInfo[0] }} px-3 py-2">
                            <i class="mdi {{ $statusInfo[1] }} me-1"></i>
                            {{ $appointment->status_translated }}
                        </span>
                        @if($appointment->appointment_mode === 'online')
                            <span class="badge bg-info px-3 py-2">
                                <i class="mdi mdi-video-account me-1"></i>
                                Online
                            </span>
                        @else
                            <span class="badge bg-success px-3 py-2">
                                <i class="mdi mdi-hospital-building me-1"></i>
                                Presencial
                            </span>
                        @endif
                    </div>

                    {{-- Informações do Agendamento --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-identifier me-1"></i> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $appointment->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-account-heart me-1"></i> Paciente
                                </label>
                                @if($appointment->patient)
                                    <p class="mb-0 fw-semibold">
                                        <a href="{{ workspace_route('tenant.patients.show', $appointment->patient->id) }}" class="text-decoration-none">
                                            {{ $appointment->patient->full_name }}
                                        </a>
                                    </p>
                                @else
                                    <p class="mb-0 text-muted">N/A</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-doctor me-1"></i> Médico
                                </label>
                                @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                                    <p class="mb-0 fw-semibold">
                                        <a href="{{ workspace_route('tenant.doctors.show', $appointment->calendar->doctor->id) }}" class="text-decoration-none">
                                            {{ $appointment->calendar->doctor->user->name }}
                                        </a>
                                    </p>
                                @else
                                    <p class="mb-0 text-muted">N/A</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-text me-1"></i> Calendário
                                </label>
                                <p class="mb-0 fw-semibold">{{ $appointment->calendar->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-file-document-edit me-1"></i> Tipo de Consulta
                                </label>
                                <p class="mb-0 fw-semibold">{{ $appointment->type->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-medical-bag me-1"></i> Especialidade
                                </label>
                                <p class="mb-0 fw-semibold">{{ $appointment->specialty->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Data e Hora --}}
                    <h5 class="text-primary mb-3">
                        <i class="mdi mdi-clock-outline me-2"></i>
                        Data e Hora
                    </h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 bg-light">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-start me-1"></i> Início
                                </label>
                                <p class="mb-0 fw-semibold fs-6">
                                    {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 bg-light">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-end me-1"></i> Fim
                                </label>
                                <p class="mb-0 fw-semibold fs-6">
                                    {{ $appointment->ends_at ? $appointment->ends_at->format('d/m/Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                        @if($appointment->starts_at && $appointment->ends_at)
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="mdi mdi-timer me-2"></i>
                                    <strong>Duração:</strong> {{ $appointment->starts_at->diffInMinutes($appointment->ends_at) }} minutos
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Observações --}}
                    @if($appointment->notes)
                        <div class="mb-4">
                            <label class="text-muted small mb-2 d-block">
                                <i class="mdi mdi-note-text me-1"></i> Observações
                            </label>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-0">{{ $appointment->notes }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Informações Adicionais --}}
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-plus me-1"></i> Criado em
                                </label>
                                <p class="mb-0">{{ $appointment->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-edit me-1"></i> Atualizado em
                                </label>
                                <p class="mb-0">{{ $appointment->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="border-top pt-3 mt-3 d-flex gap-2">
                        @if($appointment->appointment_mode === 'online')
                            <a href="{{ workspace_route('tenant.online-appointments.show', $appointment->id) }}" class="btn btn-info">
                                <i class="mdi mdi-video-account me-2"></i>
                                Instruções Online
                            </a>
                        @endif
                        
                        @php
                            $form = \App\Models\Tenant\Form::getFormForAppointment($appointment);
                            $tenant = \App\Models\Platform\Tenant::current();
                        @endphp
                        @if($form && $tenant)
                            <a href="{{ tenant_route($tenant, 'public.form.response.create', ['form' => $form->id, 'appointment' => $appointment->id]) }}" 
                               target="_blank"
                               class="btn btn-outline-primary">
                                <i class="mdi mdi-file-document-edit me-2"></i>
                                Responder Formulário (Paciente)
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

