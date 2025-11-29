@extends('layouts.connect_plus.app')

@section('title', 'Editar Agendamento')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Editar Agendamento </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.appointments.index') }}">Agendamentos</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="mdi mdi-calendar-edit text-primary me-2"></i>
                                Editar Agendamento
                            </h4>
                            <p class="card-description mb-0 text-muted">Atualize as informações do agendamento abaixo</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ route('tenant.appointments.update', $appointment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Seção: Informações Básicas --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações Básicas
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-calendar me-1"></i>
                                            Calendário <span class="text-danger">*</span>
                                        </label>
                                        <select name="calendar_id" class="form-control @error('calendar_id') is-invalid @enderror" required>
                                            <option value="">Selecione um calendário</option>
                                            @foreach($calendars as $calendar)
                                                <option value="{{ $calendar->id }}" {{ old('calendar_id', $appointment->calendar_id) == $calendar->id ? 'selected' : '' }}>{{ $calendar->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('calendar_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-account me-1"></i>
                                            Paciente <span class="text-danger">*</span>
                                        </label>
                                        <select name="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                            <option value="">Selecione um paciente</option>
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>{{ $patient->full_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('patient_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-calendar-clock me-1"></i>
                                            Tipo de Consulta
                                        </label>
                                        <select name="appointment_type" class="form-control @error('appointment_type') is-invalid @enderror">
                                            <option value="">Selecione um tipo</option>
                                            @foreach($appointmentTypes as $appointmentType)
                                                <option value="{{ $appointmentType->id }}" {{ old('appointment_type', $appointment->appointment_type) == $appointmentType->id ? 'selected' : '' }}>{{ $appointmentType->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('appointment_type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-stethoscope me-1"></i>
                                            Especialidade
                                        </label>
                                        <select name="specialty_id" class="form-control @error('specialty_id') is-invalid @enderror">
                                            <option value="">Selecione uma especialidade</option>
                                            @foreach($specialties as $specialty)
                                                <option value="{{ $specialty->id }}" {{ old('specialty_id', $appointment->specialty_id) == $specialty->id ? 'selected' : '' }}>{{ $specialty->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('specialty_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Data e Hora --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-clock-outline me-2"></i>
                                Data e Horário
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-calendar-start me-1"></i>
                                            Data e Hora de Início <span class="text-danger">*</span>
                                        </label>
                                        <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror" 
                                               name="starts_at" value="{{ old('starts_at', $appointment->starts_at ? $appointment->starts_at->format('Y-m-d\TH:i') : '') }}" required>
                                        @error('starts_at')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-calendar-end me-1"></i>
                                            Data e Hora de Fim <span class="text-danger">*</span>
                                        </label>
                                        <input type="datetime-local" class="form-control @error('ends_at') is-invalid @enderror" 
                                               name="ends_at" value="{{ old('ends_at', $appointment->ends_at ? $appointment->ends_at->format('Y-m-d\TH:i') : '') }}" required>
                                        @error('ends_at')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Status e Observações --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information me-2"></i>
                                Status e Observações
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-toggle-switch me-1"></i>
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                            <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>Agendado</option>
                                            <option value="rescheduled" {{ old('status', $appointment->status) == 'rescheduled' ? 'selected' : '' }}>Reagendado</option>
                                            <option value="canceled" {{ old('status', $appointment->status) == 'canceled' ? 'selected' : '' }}>Cancelado</option>
                                            <option value="attended" {{ old('status', $appointment->status) == 'attended' ? 'selected' : '' }}>Atendido</option>
                                            <option value="no_show" {{ old('status', $appointment->status) == 'no_show' ? 'selected' : '' }}>Não Compareceu</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-note-text me-1"></i>
                                            Observações
                                        </label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                  name="notes" rows="4" 
                                                  placeholder="Digite observações sobre o agendamento (opcional)">{{ old('notes', $appointment->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.appointments.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Atualizar Agendamento
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
<style>
    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }
    .card-title {
        font-weight: 600;
    }
    h5.text-primary {
        font-weight: 600;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    .btn-lg {
        padding: 0.75rem 2rem;
        font-weight: 600;
    }
</style>
@endpush

@endsection

