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
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <x-icon name="home-outline" class="w-5 h-5 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <a href="{{ workspace_route('tenant.appointments.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white md:ml-2">Agendamentos</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
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
                        <x-icon name="calendar-edit-outline" class="w-6 h-6 mr-2 text-blue-600" />
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
                        <x-icon name="information-outline" class="w-5 h-5 mr-2 text-blue-600" />
                        Informações Básicas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-doctor me-1"></i>
                                            Médico <span class="text-danger">*</span>
                                        </label>
                                        @php
                                            $selectedDoctorId = old('doctor_id', $appointment->doctor_id);
                                            $selectedDoctor = $doctors->firstWhere('id', $selectedDoctorId);
                                            $selectedDoctorName = $selectedDoctor ? ($selectedDoctor->user->name_full ?? $selectedDoctor->user->name) : '';
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <input type="hidden" name="doctor_id" id="doctor_id" value="{{ $selectedDoctorId }}" data-selected-name="{{ $selectedDoctorName }}" required>
                                            <input type="text" id="doctor_name" class="form-control @error('doctor_id') is-invalid @enderror" value="{{ $selectedDoctorName }}" placeholder="Selecione um mÃ©dico" readonly>
                                            <x-tailadmin-button type="button" variant="secondary" size="sm" class="js-open-entity-search" data-entity-type="doctors" data-search-url="{{ workspace_route('tenant.appointments.api.search-doctors') }}" data-hidden-input-id="doctor_id" data-display-input-id="doctor_name" data-modal-title="Buscar mÃ©dico">
                                                Buscar
                                            </x-tailadmin-button>
                                        </div>
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
                                        @php
                                            $selectedPatientId = old('patient_id', $appointment->patient_id);
                                            $selectedPatient = $patients->firstWhere('id', $selectedPatientId);
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <input type="hidden" name="patient_id" id="patient_id" value="{{ $selectedPatientId }}" required>
                                            <input type="text" id="patient_name" class="form-control @error('patient_id') is-invalid @enderror" value="{{ $selectedPatient?->full_name ?? '' }}" placeholder="Selecione um paciente" readonly>
                                            <x-tailadmin-button type="button" variant="secondary" size="sm" class="js-open-entity-search" data-entity-type="patients" data-search-url="{{ workspace_route('tenant.appointments.api.search-patients') }}" data-hidden-input-id="patient_id" data-display-input-id="patient_name" data-modal-title="Buscar paciente">
                                                Buscar
                                            </x-tailadmin-button>
                                        </div>
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
                                            <div class="flex gap-2 appointment-date-field-group">
                                                <input type="date" id="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror @error('starts_at') is-invalid @enderror" 
                                                       name="appointment_date" value="{{ old('appointment_date', $appointment->starts_at ? $appointment->starts_at->format('Y-m-d') : '') }}" 
                                                       min="{{ \Carbon\Carbon::now('America/Campo_Grande')->toDateString() }}" required>
                                                <x-tailadmin-button
                                                    type="button"
                                                    variant="secondary"
                                                    size="sm"
                                                    class="px-2 py-2 appointment-date-picker-trigger"
                                                    data-action="open-date-picker"
                                                    aria-label="Abrir calendário"
                                                    title="Abrir calendário"
                                                >
                                                    <i class="mdi mdi-calendar"></i>
                                                </x-tailadmin-button>
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
                            <input type="hidden" name="intent_waitlist" id="intent_waitlist" value="{{ old('intent_waitlist', 0) }}">
                            <div id="slot_waitlist_alert" class="alert alert-warning mt-3" role="alert" style="display: none;" aria-hidden="true">
                                <span id="slot_waitlist_alert_message">
                                    Você escolheu um horário já reservado. Você será encaminhado para a fila de espera e receberá uma notificação com link se a vaga ficar disponível.
                                </span>
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

    <div id="entitySearchModal" class="entity-search-modal hidden" data-entity-search-modal>
        <div class="entity-search-modal__backdrop" data-entity-search-backdrop></div>
        <div class="entity-search-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="entity-search-modal-title">
            <div class="entity-search-modal__header">
                <h3 id="entity-search-modal-title" class="text-lg font-semibold text-gray-900 dark:text-white" data-entity-search-title>Buscar</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 js-close-entity-search-modal" aria-label="Fechar modal de busca">
                    <x-icon name="close" class="w-6 h-6" />
                </button>
            </div>
            <div class="entity-search-modal__body">
                <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Digite para buscar..." data-entity-search-input>
                <div class="entity-search-modal__results-wrap border border-gray-200 dark:border-gray-700 rounded-md mt-3">
                    <div class="p-3 text-sm text-gray-500 dark:text-gray-400" data-entity-search-empty>Digite para buscar.</div>
                    <div class="hidden p-3 text-sm text-gray-500 dark:text-gray-400" data-entity-search-loading>Buscando...</div>
                    <ul class="hidden" data-entity-search-results></ul>
                </div>
            </div>
            <div class="entity-search-modal__footer">
                <button type="button" class="btn btn-outline js-cancel-entity-search">Cancelar</button>
                <button type="button" class="btn btn-primary js-confirm-entity-search" data-entity-search-confirm disabled>Selecionar</button>
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
