@extends('layouts.tailadmin.app')

@section('title', 'Editar Agendamento Recorrente')
@section('page', 'appointments')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Editar Agendamento Recorrente</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.recurring-appointments.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Agendamentos Recorrentes</a>
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
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Editar Agendamento Recorrente</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Atualize as informaÃ§Ãµes do agendamento recorrente</p>
            </div>

            <form class="space-y-8"
                action="{{ workspace_route('tenant.recurring-appointments.update', ['id' => $recurringAppointment->id]) }}"
                method="POST"
                data-recurring-edit="true"
                data-recurring-id="{{ $recurringAppointment->id }}"
                data-current-appointment-type-id="{{ old('appointment_type_id', $recurringAppointment->appointment_type_id) }}">
                @csrf
                @method('PUT')

                <!-- SeÃ§Ã£o: InformaÃ§Ãµes BÃ¡sicas -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">InformaÃ§Ãµes BÃ¡sicas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Paciente <span class="text-red-500">*</span>
                            </label>
                            <select name="patient_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('patient_id') border-red-500 @enderror" required>
                                <option value="">Selecione um paciente</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id', $recurringAppointment->patient_id) == $patient->id ? 'selected' : '' }}>
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
                                MÃ©dico <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" id="doctor_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('doctor_id') border-red-500 @enderror" required>
                                <option value="">Selecione um mÃ©dico</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id', $recurringAppointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name_full ?? $doctor->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Selecione o mÃ©dico para ver os dias e horÃ¡rios disponÃ­veis</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Consulta
                            </label>
                            <select name="appointment_type_id" id="appointment_type_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('appointment_type_id') border-red-500 @enderror" disabled>
                                <option value="">Primeiro selecione um mÃ©dico</option>
                            </select>
                            @error('appointment_type_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Data Inicial <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="start_date" name="start_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('start_date') border-red-500 @enderror"
                                   value="{{ old('start_date', $recurringAppointment->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- SeÃ§Ã£o: Tipo de TÃ©rmino -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Tipo de TÃ©rmino</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                TÃ©rmino <span class="text-red-500">*</span>
                            </label>
                            <select name="end_type" id="end_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('end_type') border-red-500 @enderror" required>
                                <option value="none" {{ old('end_type', $recurringAppointment->end_type) == 'none' ? 'selected' : '' }}>Sem limite (infinito)</option>
                                <option value="total_sessions" {{ old('end_type', $recurringAppointment->end_type) == 'total_sessions' ? 'selected' : '' }}>Total de sessÃµes</option>
                                <option value="date" {{ old('end_type', $recurringAppointment->end_type) == 'date' ? 'selected' : '' }}>Data final</option>
                            </select>
                            @error('end_type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div id="total_sessions_field" style="display: {{ old('end_type', $recurringAppointment->end_type) == 'total_sessions' ? 'block' : 'none' }};">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Total de SessÃµes <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="total_sessions" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('total_sessions') border-red-500 @enderror"
                                   value="{{ old('total_sessions', $recurringAppointment->total_sessions) }}" min="1">
                            @error('total_sessions')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div id="end_date_field" style="display: {{ old('end_type', $recurringAppointment->end_type) == 'date' ? 'block' : 'none' }};">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Data Final <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="end_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('end_date') border-red-500 @enderror"
                                   value="{{ old('end_date', $recurringAppointment->end_date ? $recurringAppointment->end_date->format('Y-m-d') : '') }}">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        @php
                            $settings = \App\Models\Tenant\TenantSetting::getAll();
                            $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
                        @endphp
                        @if($defaultMode === 'user_choice')
                            @include('tenant.appointments.partials.appointment_mode_select', ['appointment' => $recurringAppointment])
                        @endif
                    </div>
                </div>

                <!-- SeÃ§Ã£o: Regras de RecorrÃªncia -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Regras de RecorrÃªncia</h3>
                    <div id="rules-container">
                        @foreach($recurringAppointment->rules as $index => $rule)
                            <div class="rule-item mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dia da Semana <span class="text-red-500">*</span></label>
                                        <select name="rules[{{ $index }}][weekday]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white rule-weekday" required data-selected="{{ $rule->weekday }}">
                                            <option value="">Carregando...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">HorÃ¡rio DisponÃ­vel <span class="text-red-500">*</span></label>
                                        <select name="rules[{{ $index }}][time_slot]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white rule-time-slot" required data-selected="{{ $rule->start_time }}|{{ $rule->end_time }}">
                                            <option value="">Carregando...</option>
                                        </select>
                                        <input type="hidden" name="rules[{{ $index }}][start_time]" class="rule-start-time" value="{{ $rule->start_time }}">
                                        <input type="hidden" name="rules[{{ $index }}][end_time]" class="rule-end-time" value="{{ $rule->end_time }}">
                                    </div>
                                    <input type="hidden" name="rules[{{ $index }}][frequency]" value="{{ $rule->frequency ?? 'weekly' }}">
                                    <input type="hidden" name="rules[{{ $index }}][interval]" value="{{ $rule->interval ?? 1 }}">
                                    <div class="flex items-end">
                                        <div class="w-full">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">&nbsp;</label>
                                            <button type="button" class="inline-flex items-center justify-center w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors remove-rule" {{ count($recurringAppointment->rules) <= 1 ? 'style="display: none;"' : '' }}>
                                                <i class="mdi mdi-delete mr-1"></i> Remover
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors" id="add-rule">
                        <i class="mdi mdi-plus mr-1"></i> Adicionar Regra
                    </button>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ workspace_route('tenant.recurring-appointments.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-md text-sm font-medium transition-colors">Cancelar</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary text-white hover:bg-primary/90 text-sm font-medium rounded-md transition-colors">Atualizar Agendamento Recorrente</button>
                </div>
            </form>
        </div>
    </div>

@endsection

