@extends('layouts.tailadmin.app')

@section('title', 'Editar Agendamento Recorrente')

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
                <p class="text-sm text-gray-500 dark:text-gray-400">Atualize as informações do agendamento recorrente</p>
            </div>

            <form class="space-y-8" action="{{ workspace_route('tenant.recurring-appointments.update', ['id' => $recurringAppointment->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Seção: Informações Básicas -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informações Básicas</h3>
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
                                Médico <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" id="doctor_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('doctor_id') border-red-500 @enderror" required>
                                <option value="">Selecione um médico</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id', $recurringAppointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name_full ?? $doctor->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Selecione o médico para ver os dias e horários disponíveis</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Consulta
                            </label>
                            <select name="appointment_type_id" id="appointment_type_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('appointment_type_id') border-red-500 @enderror" disabled>
                                <option value="">Primeiro selecione um médico</option>
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
                            <input type="date" name="start_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('start_date') border-red-500 @enderror"
                                   value="{{ old('start_date', $recurringAppointment->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seção: Tipo de Término -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Tipo de Término</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Término <span class="text-red-500">*</span>
                            </label>
                            <select name="end_type" id="end_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('end_type') border-red-500 @enderror" required>
                                <option value="none" {{ old('end_type', $recurringAppointment->end_type) == 'none' ? 'selected' : '' }}>Sem limite (infinito)</option>
                                <option value="total_sessions" {{ old('end_type', $recurringAppointment->end_type) == 'total_sessions' ? 'selected' : '' }}>Total de sessões</option>
                                <option value="date" {{ old('end_type', $recurringAppointment->end_type) == 'date' ? 'selected' : '' }}>Data final</option>
                            </select>
                            @error('end_type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div id="total_sessions_field" style="display: {{ old('end_type', $recurringAppointment->end_type) == 'total_sessions' ? 'block' : 'none' }};">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Total de Sessões <span class="text-red-500">*</span>
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

                <!-- Seção: Regras de Recorrência -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Regras de Recorrência</h3>
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
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Horário Disponível <span class="text-red-500">*</span></label>
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

@push('scripts')
<script>
    const tenantSlug = '{{ tenant()->subdomain }}';
    let ruleIndex = {{ count($recurringAppointment->rules) }};
    let businessHours = [];
    let doctorId = null;
    let appointmentTypeId = null;
    let startDate = null;
    let recurringAppointmentId = '{{ $recurringAppointment->id }}';

    // Toggle campos de término
    $('#end_type').on('change', function() {
        const endType = $(this).val();
        $('#total_sessions_field').toggle(endType === 'total_sessions');
        $('#end_date_field').toggle(endType === 'date');
    });

    // Carregar tipos de consulta quando médico for selecionado
    function loadAppointmentTypes(doctorId) {
        const $appointmentTypeSelect = $('#appointment_type_id');
        const currentAppointmentTypeId = '{{ old("appointment_type_id", $recurringAppointment->appointment_type_id) }}';

        if (!doctorId) {
            $appointmentTypeSelect.html('<option value="">Primeiro selecione um médico</option>').prop('disabled', true);
            return;
        }

        $appointmentTypeSelect.html('<option value="">Carregando...</option>').prop('disabled', true);

        $.ajax({
            url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/appointment-types`,
            method: 'GET',
            success: function(data) {
                $appointmentTypeSelect.empty();
                
                if (!data || data.length === 0) {
                    $appointmentTypeSelect.append('<option value="">Nenhum tipo de consulta disponível</option>');
                    $appointmentTypeSelect.prop('disabled', true);
                    return;
                }

                $appointmentTypeSelect.append('<option value="">Selecione um tipo</option>');
                
                let currentTypeFound = false;
                data.forEach(function(type) {
                    const selected = (currentAppointmentTypeId && type.id === currentAppointmentTypeId) ? 'selected' : '';
                    if (selected) currentTypeFound = true;
                    $appointmentTypeSelect.append(`<option value="${type.id}" ${selected}>${type.name} (${type.duration_min} min)</option>`);
                });
                
                $appointmentTypeSelect.prop('disabled', false);
                
                // Se o tipo atual não foi encontrado, limpar seleção
                if (currentAppointmentTypeId && !currentTypeFound) {
                    $appointmentTypeSelect.val('');
                    appointmentTypeId = null;
                } else if (currentTypeFound) {
                    appointmentTypeId = currentAppointmentTypeId;
                }
            },
            error: function(xhr) {
                console.error('Erro ao buscar tipos de consulta:', xhr);
                $appointmentTypeSelect.html('<option value="">Erro ao carregar tipos de consulta</option>');
            }
        });
    }

    // Carregar business hours quando médico for selecionado ou ao carregar a página
    function loadBusinessHours() {
        doctorId = $('#doctor_id').val();
        
        if (!doctorId) {
            businessHours = [];
            updateAllRules();
            return;
        }

        // Buscar business hours do médico
        $.ajax({
            url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/business-hours`,
            method: 'GET',
            success: function(data) {
                businessHours = data;
                updateAllRules();
            },
            error: function(xhr) {
                console.error('Erro ao buscar horários do médico:', xhr);
                showAlert({ type: 'error', title: 'Erro', message: 'Erro ao carregar horários do médico. Por favor, tente novamente.' });
            }
        });
    }

    // Quando tipo de consulta mudar, atualizar regras
    $('#appointment_type_id').on('change', function() {
        appointmentTypeId = $(this).val();
        updateAllRules();
    });

    // Quando data inicial mudar, atualizar regras
    $('#start_date').on('change', function() {
        startDate = $(this).val();
        updateAllRules();
    });

    // Carregar ao mudar médico
    $('#doctor_id').on('change', function() {
        const selectedDoctorId = $(this).val();
        loadAppointmentTypes(selectedDoctorId);
        loadBusinessHours();
    });

    // Carregar ao carregar a página se já tiver médico selecionado
    $(document).ready(function() {
        doctorId = $('#doctor_id').val();
        appointmentTypeId = $('#appointment_type_id').val();
        startDate = $('#start_date').val();
        
        if (doctorId) {
            loadAppointmentTypes(doctorId);
            loadBusinessHours();
        }
    });

    // Atualizar todas as regras com os business hours
    function updateAllRules() {
        $('.rule-item').each(function() {
            updateRule($(this));
        });
    }

    // Atualizar uma regra específica
    function updateRule($ruleItem) {
        const $weekdaySelect = $ruleItem.find('.rule-weekday');
        const $timeSlotSelect = $ruleItem.find('.rule-time-slot');
        const selectedWeekday = $weekdaySelect.data('selected');
        const selectedTimeSlot = $timeSlotSelect.data('selected');

        // Limpar selects
        $weekdaySelect.empty();
        $timeSlotSelect.empty();
        $timeSlotSelect.prop('disabled', true);

        if (!doctorId || businessHours.length === 0) {
            $weekdaySelect.append('<option value="">Primeiro selecione um médico</option>');
            $weekdaySelect.prop('disabled', true);
            return;
        }

        // Preencher dias da semana disponíveis
        businessHours.forEach(function(bh) {
            const selected = selectedWeekday === bh.weekday_string ? 'selected' : '';
            $weekdaySelect.append(`<option value="${bh.weekday_string}" ${selected}>${bh.weekday_name}</option>`);
        });

        $weekdaySelect.prop('disabled', false);

        // Configurar event handlers
        setupRuleHandlers($ruleItem);

        // Se já tiver um dia selecionado, carregar horários
        if (selectedWeekday) {
            loadTimeSlotsForDay($ruleItem, selectedWeekday, selectedTimeSlot);
        }
    }

    // Função para carregar horários disponíveis de um dia
    function loadTimeSlotsForDay($ruleItem, weekdayString, selectedTimeSlot = null) {
        const $timeSlotSelect = $ruleItem.find('.rule-time-slot');
        const $startTimeInput = $ruleItem.find('.rule-start-time');
        const $endTimeInput = $ruleItem.find('.rule-end-time');

        $timeSlotSelect.empty();
        $timeSlotSelect.append('<option value="">Carregando...</option>');
        $timeSlotSelect.prop('disabled', true);

        if (!doctorId || !appointmentTypeId || !startDate) {
            $timeSlotSelect.empty();
            $timeSlotSelect.append('<option value="">Selecione médico, tipo de consulta e data inicial</option>');
            return;
        }

        // Buscar horários disponíveis da API
        $.ajax({
            url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/available-slots-recurring`,
            method: 'GET',
            data: {
                weekday: weekdayString,
                appointment_type_id: appointmentTypeId,
                start_date: startDate,
                recurring_appointment_id: recurringAppointmentId
            },
            success: function(slots) {
                $timeSlotSelect.empty();
                
                if (slots.length === 0) {
                    $timeSlotSelect.append('<option value="">Nenhum horário disponível</option>');
                    $timeSlotSelect.prop('disabled', true);
                    return;
                }

                $timeSlotSelect.append('<option value="">Selecione um horário</option>');
                slots.forEach(function(slot) {
                    const timeSlotValue = `${slot.start}|${slot.end}`;
                    const selected = selectedTimeSlot === timeSlotValue ? 'selected' : '';
                    $timeSlotSelect.append(`<option value="${timeSlotValue}" data-start="${slot.start}" data-end="${slot.end}" ${selected}>${slot.display}</option>`);
                });

                $timeSlotSelect.prop('disabled', false);

                // Se já tiver um horário selecionado, atualizar campos hidden
                if (selectedTimeSlot) {
                    const selectedOption = $timeSlotSelect.find(`option[value="${selectedTimeSlot}"]`);
                    if (selectedOption.length) {
                        $startTimeInput.val(selectedOption.data('start'));
                        $endTimeInput.val(selectedOption.data('end'));
                    }
                }
            },
            error: function(xhr) {
                console.error('Erro ao buscar horários disponíveis:', xhr);
                $timeSlotSelect.empty();
                $timeSlotSelect.append('<option value="">Erro ao carregar horários</option>');
                $timeSlotSelect.prop('disabled', true);
            }
        });
    }

    // Configurar event handlers para uma regra
    function setupRuleHandlers($ruleItem) {
        const $weekdaySelect = $ruleItem.find('.rule-weekday');
        const $timeSlotSelect = $ruleItem.find('.rule-time-slot');
        const $startTimeInput = $ruleItem.find('.rule-start-time');
        const $endTimeInput = $ruleItem.find('.rule-end-time');

        // Quando o dia da semana mudar, atualizar horários
        $weekdaySelect.off('change').on('change', function() {
            const weekdayString = $(this).val();
            if (weekdayString) {
                loadTimeSlotsForDay($ruleItem, weekdayString);
            } else {
                $timeSlotSelect.empty();
                $timeSlotSelect.append('<option value="">Selecione o dia</option>');
                $timeSlotSelect.prop('disabled', true);
            }
        });

        // Quando horário mudar, atualizar campos hidden
        $timeSlotSelect.off('change').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const startTime = selectedOption.data('start');
            const endTime = selectedOption.data('end');
            
            if (startTime && endTime) {
                $startTimeInput.val(startTime);
                $endTimeInput.val(endTime);
            } else {
                $startTimeInput.val('');
                $endTimeInput.val('');
            }
        });
    }

    // Adicionar regra
    $('#add-rule').on('click', function() {
        // Pegar o último bloco (bloco atual que está sendo preenchido)
        const $lastRule = $('.rule-item').last();
        
        // Capturar valores do bloco atual
        const currentWeekday = $lastRule.find('.rule-weekday').val();
        const currentTimeSlot = $lastRule.find('.rule-time-slot').val();
        const currentStartTime = $lastRule.find('.rule-start-time').val();
        const currentEndTime = $lastRule.find('.rule-end-time').val();
        
        // Criar novo bloco com os valores do bloco atual
        const ruleHtml = `
            <div class="rule-item mb-3 p-3 border rounded">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="fw-semibold">Dia da Semana <span class="text-danger">*</span></label>
                            <select name="rules[${ruleIndex}][weekday]" class="form-control rule-weekday" required>
                                ${businessHours.length > 0 ? businessHours.map(bh => `<option value="${bh.weekday_string}" ${bh.weekday_string === currentWeekday ? 'selected' : ''}>${bh.weekday_name}</option>`).join('') : '<option value="">Primeiro selecione um médico</option>'}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="fw-semibold">Horário Disponível <span class="text-danger">*</span></label>
                            <select name="rules[${ruleIndex}][time_slot]" class="form-control rule-time-slot" required>
                                <option value="">Selecione o dia e tipo de consulta</option>
                            </select>
                            <input type="hidden" name="rules[${ruleIndex}][start_time]" class="rule-start-time" value="${currentStartTime || ''}">
                            <input type="hidden" name="rules[${ruleIndex}][end_time]" class="rule-end-time" value="${currentEndTime || ''}">
                        </div>
                    </div>
                    <input type="hidden" name="rules[${ruleIndex}][frequency]" value="weekly">
                    <input type="hidden" name="rules[${ruleIndex}][interval]" value="1">
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-group w-100">
                            <label class="fw-semibold">&nbsp;</label>
                            <button type="button" class="remove-rule w-100 inline-flex items-center justify-center gap-1 rounded-md bg-error text-white text-sm font-semibold transition hover:bg-error/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-error/50 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                                <i class="mdi mdi-delete"></i> Remover
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Adicionar o novo bloco ao final
        const $newRule = $(ruleHtml);
        $('#rules-container').append($newRule);
        
        // Configurar o novo bloco e carregar horários se necessário
        setupRuleHandlers($newRule);
        
        // Se o novo bloco tiver dia da semana selecionado, carregar horários
        if (currentWeekday) {
            const $newTimeSlotSelect = $newRule.find('.rule-time-slot');
            
            // Carregar horários para o dia selecionado
            loadTimeSlotsForDay($newRule, currentWeekday, currentTimeSlot);
        } else {
            updateRule($newRule);
        }
        
        // Limpar o bloco atual (último bloco)
        $lastRule.find('.rule-weekday').val('');
        $lastRule.find('.rule-time-slot').val('').prop('disabled', true);
        $lastRule.find('.rule-start-time').val('');
        $lastRule.find('.rule-end-time').val('');
        
        ruleIndex++;
        updateRemoveButtons();
    });

    // Remover regra
    $(document).on('click', '.remove-rule', function() {
        $(this).closest('.rule-item').remove();
        updateRemoveButtons();
    });

    // Atualizar botões de remover
    function updateRemoveButtons() {
        const ruleCount = $('.rule-item').length;
        // O primeiro bloco (índice 0) nunca mostra o botão remover se houver apenas uma regra
        // Os demais blocos sempre mostram o botão remover
        $('.rule-item').each(function(index) {
            const $removeBtn = $(this).find('.remove-rule');
            if (index === 0 && ruleCount === 1) {
                // Primeiro bloco oculto se houver apenas uma regra
                $removeBtn.hide();
            } else if (index === 0 && ruleCount > 1) {
                // Primeiro bloco mostra se houver mais de uma regra
                $removeBtn.show();
            } else {
                // Demais blocos sempre mostram
                $removeBtn.show();
            }
        });
    }
</script>
@endpush

