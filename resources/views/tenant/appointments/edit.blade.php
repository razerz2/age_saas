@extends('layouts.tailadmin.app')

@section('title', 'Editar Agendamento')
@section('page', 'appointments')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Editar Agendamento</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.appointments.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Agendamentos</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <x-help-button module="appointments" />
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Agendamento
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Atualize as informações do agendamento abaixo</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <form class="space-y-8"
                action="{{ workspace_route('tenant.appointments.update', $appointment->id) }}"
                method="POST"
                data-appointment-edit="true"
                data-current-appointment-type-id="{{ old('appointment_type', $appointment->appointment_type) }}"
                data-current-specialty-id="{{ old('specialty_id', $appointment->specialty_id) }}"
                data-initial-date="{{ $appointment->starts_at ? $appointment->starts_at->format('Y-m-d') : '' }}"
                data-initial-time-start="{{ $appointment->starts_at ? $appointment->starts_at->format('H:i') : '' }}"
                data-initial-time-end="{{ $appointment->ends_at ? $appointment->ends_at->format('H:i') : '' }}"
                data-initial-starts-at="{{ $appointment->starts_at ? $appointment->starts_at->format('Y-m-d H:i:s') : '' }}"
                data-initial-ends-at="{{ $appointment->ends_at ? $appointment->ends_at->format('Y-m-d H:i:s') : '' }}">
                @csrf
                @method('PUT')

                <!-- Seção: Informações Básicas -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informações Básicas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-doctor me-1"></i>
                                            Médico <span class="text-danger">*</span>
                                        </label>
                                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                            <option value="">Selecione um médico</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->user->name_full ?? $doctor->user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">O calendário do médico será selecionado automaticamente</small>
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
                                        <select name="appointment_type" id="appointment_type" class="form-control @error('appointment_type') is-invalid @enderror" disabled>
                                            <option value="">Primeiro selecione um médico</option>
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
                                        <select name="specialty_id" id="specialty_id" class="form-control @error('specialty_id') is-invalid @enderror" disabled>
                                            <option value="">Primeiro selecione um médico</option>
                                        </select>
                                        @error('specialty_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="calendar_id" id="calendar_id" value="{{ old('calendar_id', $appointment->calendar_id) }}">
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
                                            Data <span class="text-danger">*</span>
                                        </label>
                                            <div class="flex gap-2">
                                                <input type="date" id="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror @error('starts_at') is-invalid @enderror" 
                                                       name="appointment_date" value="{{ old('appointment_date', $appointment->starts_at ? $appointment->starts_at->format('Y-m-d') : '') }}" 
                                                       min="{{ date('Y-m-d') }}" required>
                                                <x-tailadmin-button type="button" variant="secondary" size="sm" id="btn-show-business-hours"
                                                    class="px-2 py-2" data-bs-toggle="modal" data-bs-target="#businessHoursModal"
                                                    title="Ver dias trabalhados do médico">
                                                    <i class="mdi mdi-calendar-clock"></i>
                                                </x-tailadmin-button>
                                            </div>
                                        @error('appointment_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('starts_at')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-clock-outline me-1"></i>
                                            Horário Disponível <span class="text-danger">*</span>
                                        </label>
                                        <select name="appointment_time" id="appointment_time" class="form-control @error('appointment_time') is-invalid @enderror" required>
                                            <option value="">Carregando horários...</option>
                                        </select>
                                        @error('appointment_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Horários disponíveis baseados nas configurações do médico</small>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="starts_at" id="starts_at" value="{{ old('starts_at', $appointment->starts_at ? $appointment->starts_at->format('Y-m-d H:i:s') : '') }}">
                            <input type="hidden" name="ends_at" id="ends_at" value="{{ old('ends_at', $appointment->ends_at ? $appointment->ends_at->format('Y-m-d H:i:s') : '') }}">
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
                                @php
                                    $settings = \App\Models\Tenant\TenantSetting::getAll();
                                    $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
                                @endphp
                                @if($defaultMode === 'user_choice')
                                    @include('tenant.appointments.partials.appointment_mode_select', ['appointment' => $appointment])
                                @endif
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
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t">
                            <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.appointments.index') }}"
                                class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                <i class="mdi mdi-arrow-left"></i>
                                Cancelar
                            </x-tailadmin-button>
                            <x-tailadmin-button type="submit" variant="primary" size="lg">
                                <i class="mdi mdi-content-save"></i>
                                Atualizar Agendamento
                            </x-tailadmin-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Dias Trabalhados --}}
    <div class="modal fade" id="businessHoursModal" tabindex="-1" aria-labelledby="businessHoursModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="businessHoursModalLabel">
                        <i class="mdi mdi-calendar-clock me-2"></i>
                        Dias Trabalhados do Médico
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div id="business-hours-loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-2 text-muted">Carregando informações...</p>
                    </div>
                    <div id="business-hours-content" class="d-none">
                        <div class="mb-3">
                            <strong>Médico:</strong> <span id="business-hours-doctor-name">-</span>
                        </div>
                        <div id="business-hours-list">
                            <!-- Conteúdo será preenchido via JavaScript -->
                        </div>
                        <div id="business-hours-empty" class="alert alert-info d-none">
                            <i class="mdi mdi-information-outline me-2"></i>
                            Nenhum dia trabalhado configurado para este médico.
                        </div>
                    </div>
                    <div id="business-hours-error" class="alert alert-danger d-none">
                        <i class="mdi mdi-alert-circle me-2"></i>
                        <span id="business-hours-error-message">Erro ao carregar informações.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <x-tailadmin-button type="button" variant="secondary" size="sm" data-bs-dismiss="modal">
                        Fechar
                    </x-tailadmin-button>
                </div>
            </div>
        </div>
    </div>

@endsection
