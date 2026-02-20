@extends('layouts.tailadmin.app')

@section('title', 'Criar Agendamento Recorrente')
@section('page', 'recurring-appointments')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            <x-icon name="home-outline" size="text-base" />
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <a href="{{ workspace_route('tenant.recurring-appointments.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Agendamentos Recorrentes</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="appointments" />
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-icon name="calendar-month-outline" size="text-lg" class="mr-2 text-blue-600" />
                    Novo Agendamento Recorrente
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Configure um agendamento que se repete automaticamente</p>
            </div>
        </div>

        <div class="p-6">
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-md p-4 mb-6">
                    <strong>Erro de Validação:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-8" id="recurring-appointment-form" action="{{ workspace_route('tenant.recurring-appointments.store') }}" method="POST">
                @csrf

                <!-- Seção: Informações Básicas -->
                <div class="pb-8 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="information-outline" size="text-base" class="mr-2 text-blue-600" />
                        Informações Básicas
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="account-outline" size="text-sm" class="inline mr-1" />
                                Paciente <span class="text-red-500">*</span>
                            </label>
                            @php
                                $selectedPatientId = old('patient_id');
                                $selectedPatient = $selectedPatientId ? $patients->firstWhere('id', $selectedPatientId) : null;
                            @endphp
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="patient_id" id="patient_id" value="{{ $selectedPatientId }}" required>
                                <input type="text" id="patient_name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-50 dark:bg-gray-700 dark:text-white @error('patient_id') border-red-500 @enderror" value="{{ $selectedPatient?->full_name ?? '' }}" placeholder="Selecione um paciente" readonly>
                                <button type="button" class="btn btn-outline js-open-entity-search" data-entity-type="patients" data-search-url="{{ workspace_route('tenant.appointments.api.search-patients') }}" data-hidden-input-id="patient_id" data-display-input-id="patient_name" data-modal-title="Buscar paciente">
                                    Buscar
                                </button>
                            </div>
                            @error('patient_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="account" size="text-sm" class="inline mr-1" />
                                Médico <span class="text-red-500">*</span>
                            </label>
                            @php
                                $selectedDoctorId = old('doctor_id');
                                $selectedDoctor = $selectedDoctorId ? $doctors->firstWhere('id', $selectedDoctorId) : null;
                                $selectedDoctorName = $selectedDoctor ? ($selectedDoctor->user->name_full ?? $selectedDoctor->user->name) : '';
                            @endphp
                            <div class="flex items-center gap-2 mb-2">
                                <input type="hidden" name="doctor_id" id="doctor_id" data-initial-value="{{ $selectedDoctorId }}" value="{{ $selectedDoctorId }}" data-selected-name="{{ $selectedDoctorName }}" required>
                                <input type="text" id="doctor_name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-50 dark:bg-gray-700 dark:text-white @error('doctor_id') border-red-500 @enderror" value="{{ $selectedDoctorName }}" placeholder="Selecione um médico" readonly>
                                <button type="button" class="btn btn-outline js-open-entity-search" data-entity-type="doctors" data-search-url="{{ workspace_route('tenant.appointments.api.search-doctors') }}" data-hidden-input-id="doctor_id" data-display-input-id="doctor_name" data-modal-title="Buscar médico">
                                    Buscar
                                </button>
                            </div>
                            @error('doctor_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Selecione o médico para carregar especialidades e dias disponíveis.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="check-bold" size="text-sm" class="inline mr-1" />
                                Especialidade
                            </label>
                            <select name="specialty_id" id="specialty_id" data-initial-value="{{ old('specialty_id') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('specialty_id') border-red-500 @enderror" disabled>
                                <option value="">Primeiro selecione um médico</option>
                            </select>
                            @error('specialty_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        @php
                            $settings = \App\Models\Tenant\TenantSetting::getAll();
                            $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
                        @endphp
                        @if($defaultMode === 'user_choice')
                            <div>
                                @include('tenant.appointments.partials.appointment_mode_select', ['appointment' => null])
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="calendar-month-outline" size="text-sm" class="inline mr-1" />
                                Data Inicial <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2 recurring-date-field-group">
                                <input type="date" name="start_date" id="start_date" class="native-date w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('start_date') border-red-500 @enderror"
                                       value="{{ old('start_date') }}" min="{{ \Carbon\Carbon::now('America/Campo_Grande')->toDateString() }}" required disabled>
                                <button type="button" class="btn btn-outline recurring-date-picker-trigger" data-action="open-date-picker" aria-label="Abrir calendário" title="Abrir calendário">
                                    <x-icon name="calendar-month-outline" size="text-sm" />
                                </button>
                            </div>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="check-bold" size="text-sm" class="inline mr-1" />
                                Tipo de Término <span class="text-red-500">*</span>
                            </label>
                            <select name="end_type" id="end_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('end_type') border-red-500 @enderror" required>
                                <option value="none" {{ old('end_type', 'none') == 'none' ? 'selected' : '' }}>Sem limite (infinito)</option>
                                <option value="date" {{ old('end_type') == 'date' ? 'selected' : '' }}>Data final</option>
                            </select>
                            @error('end_type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div id="end_date_field" class="hidden recurring-end-date-field">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Data Final <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="end_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('end_date') border-red-500 @enderror"
                                   value="{{ old('end_date') }}">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <input type="hidden" name="appointment_type_id" id="appointment_type_id" value="{{ old('appointment_type_id') }}">
                </div>

                <!-- Seção: Regras de Recorrência -->
                <div class="pt-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="calendar-month-outline" size="text-base" class="mr-2 text-blue-600" />
                        Regras de Recorrência
                    </h3>
                    <div id="rules-container">
                        <div class="rule-item mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Dia da Semana <span class="text-red-500 rule-required-indicator">*</span>
                                    </label>
                                    <select name="rules[0][weekday]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white rule-weekday" required disabled>
                                        <option value="">Selecione um médico primeiro</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Horário Disponível <span class="text-red-500 rule-required-indicator">*</span>
                                    </label>
                                    <select name="rules[0][time_slot]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white rule-time-slot" required disabled>
                                        <option value="">Selecione um dia da semana primeiro</option>
                                    </select>
                                    <input type="hidden" name="rules[0][start_time]" class="rule-start-time" value="{{ old('rules.0.start_time') }}">
                                    <input type="hidden" name="rules[0][end_time]" class="rule-end-time" value="{{ old('rules.0.end_time') }}">
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" class="btn btn-primary inline-flex items-center gap-2" id="add-rule" aria-label="Adicionar regra de recorrência">
                            <x-icon name="plus" size="text-sm" />
                            Adicionar Regra
                        </button>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ workspace_route('tenant.recurring-appointments.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" size="text-sm" class="mr-2" />
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="inbox-arrow-down-outline" size="text-base" class="mr-2" />
                        Criar Agendamento Recorrente
                    </button>
                </div>
            </form>
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

@endsection

