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
                            <select name="patient_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('patient_id') border-red-500 @enderror" required>
                                <option value="">Selecione um paciente</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="account" size="text-sm" class="inline mr-1" />
                                Médico <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" id="doctor_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('doctor_id') border-red-500 @enderror" required>
                                <option value="">Selecione um médico</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name_full ?? $doctor->user->name }}
                                    </option>
                                @endforeach
                            </select>
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
                            <input type="date" name="start_date" id="start_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('start_date') border-red-500 @enderror"
                                   value="{{ old('start_date', date('Y-m-d')) }}" required>
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
                        <div id="end_date_field" style="display: none;">
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
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                                <div class="flex items-end rule-button-col">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 rule-label-spacer" style="visibility: hidden;">&nbsp;</label>
                                    <button type="button" class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors rule-action-btn" id="add-rule">
                                        <x-icon name="plus" size="text-sm" class="mr-1" />
                                        Adicionar Regra
                                    </button>
                                </div>
                            </div>
                        </div>
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

@endsection

