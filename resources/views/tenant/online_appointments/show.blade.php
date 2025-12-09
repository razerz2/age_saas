@extends('layouts.connect_plus.app')

@section('title', 'Instruções de Consulta Online')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-video-account text-primary me-2"></i>
            Instruções de Consulta Online
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.online-appointments.index') }}">Consultas Online</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Instruções</li>
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
                            <i class="mdi mdi-video-account text-primary me-2"></i>
                            Configurar Instruções
                        </h4>
                        <div>
                            <a href="{{ route('tenant.appointments.show', $appointment->id) }}" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left me-1"></i> Voltar ao Agendamento
                            </a>
                        </div>
                    </div>

                    {{-- Informações do Agendamento --}}
                    <div class="alert alert-info mb-4">
                        <h5 class="mb-2">
                            <i class="mdi mdi-calendar-clock me-2"></i>
                            Informações da Consulta
                        </h5>
                        <p class="mb-1"><strong>Paciente:</strong> {{ $appointment->patient->full_name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Data/Hora:</strong> {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                            <p class="mb-0"><strong>Médico:</strong> {{ $appointment->calendar->doctor->user->name }}</p>
                        @endif
                    </div>

                    {{-- Formulário de Instruções --}}
                    <form action="{{ route('tenant.online-appointments.save', $appointment->id) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="meeting_link" class="form-label">
                                    <i class="mdi mdi-link me-1"></i> Link da Reunião
                                </label>
                                <input type="url" 
                                       class="form-control @error('meeting_link') is-invalid @enderror" 
                                       id="meeting_link" 
                                       name="meeting_link" 
                                       value="{{ old('meeting_link', $appointment->onlineInstructions->meeting_link ?? '') }}" 
                                       placeholder="https://meet.google.com/xxx-xxxx-xxx">
                                @error('meeting_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Link da plataforma de videoconferência (Zoom, Google Meet, etc.)</small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="meeting_app" class="form-label">
                                    <i class="mdi mdi-cellphone me-1"></i> Aplicativo
                                </label>
                                <input type="text" 
                                       class="form-control @error('meeting_app') is-invalid @enderror" 
                                       id="meeting_app" 
                                       name="meeting_app" 
                                       value="{{ old('meeting_app', $appointment->onlineInstructions->meeting_app ?? '') }}" 
                                       placeholder="Zoom, Google Meet, Microsoft Teams, etc.">
                                @error('meeting_app')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="general_instructions" class="form-label">
                                    <i class="mdi mdi-information me-1"></i> Instruções Gerais
                                </label>
                                <textarea class="form-control @error('general_instructions') is-invalid @enderror" 
                                          id="general_instructions" 
                                          name="general_instructions" 
                                          rows="4" 
                                          placeholder="Instruções gerais para o paciente...">{{ old('general_instructions', $appointment->onlineInstructions->general_instructions ?? '') }}</textarea>
                                @error('general_instructions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="patient_instructions" class="form-label">
                                    <i class="mdi mdi-account-voice me-1"></i> Observações para o Paciente
                                </label>
                                <textarea class="form-control @error('patient_instructions') is-invalid @enderror" 
                                          id="patient_instructions" 
                                          name="patient_instructions" 
                                          rows="4" 
                                          placeholder="Observações específicas para o paciente...">{{ old('patient_instructions', $appointment->onlineInstructions->patient_instructions ?? '') }}</textarea>
                                @error('patient_instructions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tenant.online-appointments.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-close me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save me-1"></i> Salvar Instruções
                            </button>
                        </div>
                    </form>

                    {{-- Área de Envio --}}
                    <div class="border-top mt-4 pt-4">
                        <h5 class="mb-3">
                            <i class="mdi mdi-send me-2"></i>
                            Enviar Instruções ao Paciente
                        </h5>

                        @if(!$canSendEmail && !$canSendWhatsapp)
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert me-2"></i>
                                <strong>Atenção:</strong> Nenhum meio de envio está configurado. 
                                Configure as notificações em <a href="{{ route('tenant.settings.index') }}">Configurações</a>.
                            </div>
                        @else
                            <div class="row">
                                @if($canSendEmail)
                                    <div class="col-md-6 mb-3">
                                        <form action="{{ route('tenant.online-appointments.send-email', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100" 
                                                    @if(!$appointment->patient->email) disabled title="Paciente não possui email cadastrado" @endif>
                                                <i class="mdi mdi-email me-2"></i>
                                                Enviar por Email
                                            </button>
                                        </form>
                                        @if($appointment->onlineInstructions && $appointment->onlineInstructions->sent_by_email_at)
                                            <small class="text-muted d-block mt-2">
                                                Último envio: {{ $appointment->onlineInstructions->sent_by_email_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    </div>
                                @endif

                                @if($canSendWhatsapp)
                                    <div class="col-md-6 mb-3">
                                        <form action="{{ route('tenant.online-appointments.send-whatsapp', $appointment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100" 
                                                    @if(!$appointment->patient->phone) disabled title="Paciente não possui telefone cadastrado" @endif>
                                                <i class="mdi mdi-whatsapp me-2"></i>
                                                Enviar por WhatsApp
                                            </button>
                                        </form>
                                        @if($appointment->onlineInstructions && $appointment->onlineInstructions->sent_by_whatsapp_at)
                                            <small class="text-muted d-block mt-2">
                                                Último envio: {{ $appointment->onlineInstructions->sent_by_whatsapp_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

