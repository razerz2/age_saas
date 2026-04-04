@extends('layouts.tailadmin.app')

@php
    $professionalLabelService = app(\App\Services\Tenant\ProfessionalLabelService::class);
    $professionalSingular = $professionalLabelService->singular();
    $professionalPlural = $professionalLabelService->plural();
    $professionalRegistration = $professionalLabelService->registration();
    $professionalSingularLower = function_exists('mb_strtolower') ? mb_strtolower($professionalSingular, 'UTF-8') : strtolower($professionalSingular);
    $professionalPluralLower = function_exists('mb_strtolower') ? mb_strtolower($professionalPlural, 'UTF-8') : strtolower($professionalPlural);
@endphp

@section('title', 'Editar Agendamento')
@section('page', 'appointments')

@section('content')
    <div id="appointments-config" class="hidden"
        data-professional-singular="{{ $professionalSingular }}"
        data-professional-singular-lower="{{ $professionalSingularLower }}"
        data-professional-plural="{{ $professionalPlural }}"
        data-professional-plural-lower="{{ $professionalPluralLower }}"
        data-professional-registration="{{ $professionalRegistration }}"></div>
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="mb-2 text-2xl font-semibold text-gray-900 dark:text-white">Editar Agendamento</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <x-icon name="home-outline" class="mr-2 h-5 w-5" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="h-6 w-6 text-gray-400" />
                                <a href="{{ workspace_route('tenant.appointments.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white md:ml-2">Agendamentos</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="h-6 w-6 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <x-help-button module="appointments" />
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 p-6 dark:border-gray-700">
            <div class="flex items-center">
                <div>
                    <h2 class="flex items-center text-xl font-semibold text-gray-900 dark:text-white">
                        <x-icon name="calendar-edit-outline" class="mr-2 h-6 w-6 text-blue-600" />
                        Editar Agendamento
                    </h2>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">Atualize as informações do agendamento abaixo</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <form
                dusk="appointment-form"
                class="space-y-6"
                action="{{ workspace_route('tenant.appointments.update', $appointment->id) }}"
                method="POST"
                data-appointment-edit="true"
                data-current-appointment-type-id="{{ old('appointment_type', $appointment->appointment_type) }}"
                data-current-specialty-id="{{ old('specialty_id', $appointment->specialty_id) }}"
                data-initial-date="{{ $appointment->starts_at ? $appointment->starts_at->format('Y-m-d') : '' }}"
                data-initial-time-start="{{ $appointment->starts_at ? $appointment->starts_at->format('H:i') : '' }}"
                data-initial-time-end="{{ $appointment->ends_at ? $appointment->ends_at->format('H:i') : '' }}"
                data-initial-starts-at="{{ $appointment->starts_at ? $appointment->starts_at->format('Y-m-d H:i:s') : '' }}"
                data-initial-ends-at="{{ $appointment->ends_at ? $appointment->ends_at->format('Y-m-d H:i:s') : '' }}"
            >
                @csrf
                @method('PUT')

                <div class="space-y-6">
                        <section class="rounded-xl border border-gray-200 p-5 dark:border-gray-700">
                            <h3 class="mb-4 flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <x-icon name="information-outline" class="mr-2 h-5 w-5 text-blue-600" />
                                Informações Básicas
                            </h3>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $professionalSingular }} <span class="text-red-500">*</span>
                                    </label>
                                    @php
                                        $selectedDoctorId = old('doctor_id', $appointment->doctor_id);
                                        $selectedDoctor = $doctors->firstWhere('id', $selectedDoctorId);
                                        $selectedDoctorName = $selectedDoctor ? ($selectedDoctor->user->name_full ?? $selectedDoctor->user->name) : '';
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <input dusk="appointment-doctor-id" type="hidden" name="doctor_id" id="doctor_id" value="{{ $selectedDoctorId }}" data-selected-name="{{ $selectedDoctorName }}" required>
                                        <input dusk="appointment-doctor-name" type="text" id="doctor_name" class="form-control @error('doctor_id') is-invalid @enderror" value="{{ $selectedDoctorName }}" placeholder="Selecione um {{ $professionalSingularLower }}" readonly>
                                        <x-tailadmin-button dusk="appointment-search-doctor-button" type="button" variant="secondary" size="sm" class="js-open-entity-search" data-entity-type="doctors" data-search-url="{{ workspace_route('tenant.appointments.api.search-doctors') }}" data-hidden-input-id="doctor_id" data-display-input-id="doctor_name" data-modal-title="Buscar {{ $professionalSingularLower }}">
                                            Buscar
                                        </x-tailadmin-button>
                                    </div>
                                    @error('doctor_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="mt-1 block text-xs text-gray-500 dark:text-gray-400">O calendário do {{ $professionalSingularLower }} será selecionado automaticamente</small>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Paciente <span class="text-red-500">*</span>
                                    </label>
                                    @php
                                        $selectedPatientId = old('patient_id', $appointment->patient_id);
                                        $selectedPatient = $patients->firstWhere('id', $selectedPatientId);
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <input dusk="appointment-patient-id" type="hidden" name="patient_id" id="patient_id" value="{{ $selectedPatientId }}" required>
                                        <input dusk="appointment-patient-name" type="text" id="patient_name" class="form-control @error('patient_id') is-invalid @enderror" value="{{ $selectedPatient?->full_name ?? '' }}" placeholder="Selecione um paciente" readonly>
                                        <x-tailadmin-button dusk="appointment-search-patient-button" type="button" variant="secondary" size="sm" class="js-open-entity-search" data-entity-type="patients" data-search-url="{{ workspace_route('tenant.appointments.api.search-patients') }}" data-hidden-input-id="patient_id" data-display-input-id="patient_name" data-modal-title="Buscar paciente">
                                            Buscar
                                        </x-tailadmin-button>
                                    </div>
                                    @error('patient_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div data-appointment-type-wrapper class="hidden">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Consulta</label>
                                    <select name="appointment_type" id="appointment_type" class="form-control @error('appointment_type') is-invalid @enderror" disabled>
                                        <option value="">Primeiro selecione um {{ $professionalSingularLower }}</option>
                                    </select>
                                    @error('appointment_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Especialidade</label>
                                    <select name="specialty_id" id="specialty_id" class="form-control @error('specialty_id') is-invalid @enderror" disabled>
                                        <option value="">Primeiro selecione um {{ $professionalSingularLower }}</option>
                                    </select>
                                    @error('specialty_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <input type="hidden" name="calendar_id" id="calendar_id" value="{{ old('calendar_id', $appointment->calendar_id) }}">
                        </section>

                        <section class="rounded-xl border border-gray-200 p-5 dark:border-gray-700">
                            <h3 class="mb-4 flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <x-icon name="clock-outline" class="mr-2 h-5 w-5 text-blue-600" />
                                Data e Horário
                            </h3>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Data <span class="text-red-500">*</span>
                                    </label>
                                    <div class="appointment-date-field-group flex gap-2">
                                        <input
                                            dusk="appointment-date"
                                            type="date"
                                            id="appointment_date"
                                            class="form-control @error('appointment_date') is-invalid @enderror @error('starts_at') is-invalid @enderror"
                                            name="appointment_date"
                                            value="{{ old('appointment_date', $appointment->starts_at ? $appointment->starts_at->format('Y-m-d') : '') }}"
                                            min="{{ \Carbon\Carbon::now('America/Campo_Grande')->toDateString() }}"
                                            required
                                        >
                                        <x-tailadmin-button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            class="appointment-date-picker-trigger px-2 py-2"
                                            data-action="open-date-picker"
                                            aria-label="Abrir calendário"
                                            title="Abrir calendário"
                                        >
                                            <i class="mdi mdi-calendar"></i>
                                        </x-tailadmin-button>
                                        <x-tailadmin-button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            id="btn-show-business-hours"
                                            class="whitespace-nowrap"
                                            title="Ver dias trabalhados do {{ $professionalSingularLower }}"
                                        >
                                            <i class="mdi mdi-calendar-clock mr-1"></i>
                                            Ver dias trabalhados
                                        </x-tailadmin-button>
                                    </div>
                                    @error('appointment_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('starts_at')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Horário Disponível <span class="text-red-500">*</span>
                                    </label>
                                    <select dusk="appointment-time" name="appointment_time" id="appointment_time" class="form-control @error('appointment_time') is-invalid @enderror" required>
                                        <option value="">Carregando horários...</option>
                                    </select>
                                    @error('appointment_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="mt-1 block text-xs text-gray-500 dark:text-gray-400">Horários disponíveis baseados nas configurações do {{ $professionalSingularLower }}</small>
                                </div>
                            </div>

                            <input type="hidden" name="starts_at" id="starts_at" value="{{ old('starts_at', $appointment->starts_at ? $appointment->starts_at->format('Y-m-d H:i:s') : '') }}">
                            <input type="hidden" name="ends_at" id="ends_at" value="{{ old('ends_at', $appointment->ends_at ? $appointment->ends_at->format('Y-m-d H:i:s') : '') }}">
                            <input type="hidden" name="intent_waitlist" id="intent_waitlist" value="{{ old('intent_waitlist', 0) }}">

                            <div id="slot_waitlist_alert" class="mt-4 hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200" role="alert" aria-hidden="true">
                                <span id="slot_waitlist_alert_message">
                                    Você escolheu um horário já reservado. Você será encaminhado para a fila de espera e receberá uma notificação com link se a vaga ficar disponível.
                                </span>
                            </div>
                        </section>

                        <section class="rounded-xl border border-gray-200 p-5 dark:border-gray-700">
                            <h3 class="mb-4 flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <x-icon name="information-outline" class="mr-2 h-5 w-5 text-blue-600" />
                                Status e Observações
                            </h3>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Status <span class="text-red-500">*</span>
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

                                @php
                                    $settings = \App\Models\Tenant\TenantSetting::getAll();
                                    $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
                                @endphp
                                @if($defaultMode === 'user_choice')
                                    @include('tenant.appointments.partials.appointment_mode_select', ['appointment' => $appointment])
                                @endif
                            </div>

                            <div class="mt-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
                                <textarea dusk="appointment-notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="4" placeholder="Digite observações sobre o agendamento (opcional)">{{ old('notes', $appointment->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </section>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-3 dark:border-gray-700">
                            <x-tailadmin-button
                                variant="secondary"
                                size="md"
                                href="{{ workspace_route('tenant.appointments.index') }}"
                                class="border-gray-300 bg-transparent text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5"
                            >
                                <i class="mdi mdi-arrow-left"></i>
                                Cancelar
                            </x-tailadmin-button>
                            <x-tailadmin-button dusk="appointment-submit-button" type="submit" variant="primary" size="lg">
                                <i class="mdi mdi-content-save"></i>
                                Atualizar Agendamento
                            </x-tailadmin-button>
                        </div>
                </div>
            </form>
        </div>
    </div>

    <div id="businessHoursModal" class="fixed inset-0 hidden" style="z-index:2147483646;" aria-hidden="true">
        <button type="button" class="js-close-business-hours-modal absolute inset-0 bg-black/50" aria-label="Fechar modal"></button>
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center p-4" style="z-index:2147483647;">
            <div class="pointer-events-auto relative w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Dias trabalhados do {{ $professionalSingularLower }}
                    </h3>
                    <button type="button" class="js-close-business-hours-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Fechar modal">
                        <x-icon name="close" class="h-6 w-6" />
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto px-6 py-5">
                    <div id="business-hours-loading" class="hidden py-3 text-sm text-gray-600 dark:text-gray-300">
                        Carregando informações...
                    </div>

                    <div id="business-hours-content" class="hidden space-y-3">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            <span class="font-medium">{{ $professionalSingular }}:</span>
                            <span id="business-hours-doctor-name">-</span>
                        </p>
                        <div id="business-hours-list" class="space-y-3"></div>
                        <div id="business-hours-empty" class="hidden rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-200">
                            Nenhum dia trabalhado configurado para este {{ $professionalSingularLower }}.
                        </div>
                    </div>

                    <div id="business-hours-error" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
                        <span id="business-hours-error-message">Erro ao carregar informações.</span>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <x-tailadmin-button type="button" variant="secondary" size="sm" class="js-close-business-hours-modal">
                        Fechar
                    </x-tailadmin-button>
                </div>
            </div>
        </div>
    </div>

    <div id="entitySearchModal" dusk="entity-search-modal" class="entity-search-modal hidden" data-entity-search-modal>
        <div class="entity-search-modal__backdrop" data-entity-search-backdrop></div>
        <div class="entity-search-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="entity-search-modal-title">
            <div class="entity-search-modal__header">
                <h3 id="entity-search-modal-title" class="text-lg font-semibold text-gray-900 dark:text-white" data-entity-search-title>Buscar</h3>
                <button type="button" class="js-close-entity-search-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Fechar modal de busca">
                    <x-icon name="close" class="h-6 w-6" />
                </button>
            </div>
            <div class="entity-search-modal__body">
                <input dusk="entity-search-input" type="text" class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Digite para buscar..." data-entity-search-input>
                <div class="entity-search-modal__results-wrap mt-3 rounded-md border border-gray-200 dark:border-gray-700">
                    <div class="p-3 text-sm text-gray-500 dark:text-gray-400" data-entity-search-empty>Digite para buscar.</div>
                    <div class="hidden p-3 text-sm text-gray-500 dark:text-gray-400" data-entity-search-loading>Buscando...</div>
                    <ul class="hidden" data-entity-search-results></ul>
                </div>
            </div>
            <div class="entity-search-modal__footer">
                <button type="button" class="btn btn-outline js-cancel-entity-search">Cancelar</button>
                <button dusk="entity-search-confirm-button" type="button" class="btn btn-primary js-confirm-entity-search" data-entity-search-confirm disabled>Selecionar</button>
            </div>
        </div>
    </div>
@endsection
