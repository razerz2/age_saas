@extends('layouts.connect_plus.app')

@section('title', 'Editar Agendamento Recorrente')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Editar Agendamento Recorrente </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.recurring-appointments.index') }}">Agendamentos Recorrentes</a>
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
                                Editar Agendamento Recorrente
                            </h4>
                            <p class="card-description mb-0 text-muted">Atualize as informações do agendamento recorrente</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ route('tenant.recurring-appointments.update', $recurringAppointment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Seção: Informações Básicas --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações Básicas
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-account me-1"></i>
                                            Paciente <span class="text-danger">*</span>
                                        </label>
                                        <select name="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                            <option value="">Selecione um paciente</option>
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" {{ old('patient_id', $recurringAppointment->patient_id) == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('patient_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-doctor me-1"></i>
                                            Médico <span class="text-danger">*</span>
                                        </label>
                                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                            <option value="">Selecione um médico</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $recurringAppointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->user->name_full ?? $doctor->user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Selecione o médico para ver os dias e horários disponíveis</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-calendar-clock me-1"></i>
                                            Tipo de Consulta
                                        </label>
                                        <select name="appointment_type_id" id="appointment_type_id" class="form-control @error('appointment_type_id') is-invalid @enderror" disabled>
                                            <option value="">Primeiro selecione um médico</option>
                                        </select>
                                        @error('appointment_type_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-calendar-start me-1"></i>
                                            Data Inicial <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                               value="{{ old('start_date', $recurringAppointment->start_date->format('Y-m-d')) }}" required>
                                        @error('start_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Tipo de Término --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-calendar-end me-2"></i>
                                Tipo de Término
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-toggle-switch me-1"></i>
                                            Término <span class="text-danger">*</span>
                                        </label>
                                        <select name="end_type" id="end_type" class="form-control @error('end_type') is-invalid @enderror" required>
                                            <option value="none" {{ old('end_type', $recurringAppointment->end_type) == 'none' ? 'selected' : '' }}>Sem limite (infinito)</option>
                                            <option value="total_sessions" {{ old('end_type', $recurringAppointment->end_type) == 'total_sessions' ? 'selected' : '' }}>Total de sessões</option>
                                            <option value="date" {{ old('end_type', $recurringAppointment->end_type) == 'date' ? 'selected' : '' }}>Data final</option>
                                        </select>
                                        @error('end_type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4" id="total_sessions_field" style="display: {{ old('end_type', $recurringAppointment->end_type) == 'total_sessions' ? 'block' : 'none' }};">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-numeric me-1"></i>
                                            Total de Sessões <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="total_sessions" class="form-control @error('total_sessions') is-invalid @enderror" 
                                               value="{{ old('total_sessions', $recurringAppointment->total_sessions) }}" min="1">
                                        @error('total_sessions')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4" id="end_date_field" style="display: {{ old('end_type', $recurringAppointment->end_type) == 'date' ? 'block' : 'none' }};">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-calendar-end me-1"></i>
                                            Data Final <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                                               value="{{ old('end_date', $recurringAppointment->end_date ? $recurringAppointment->end_date->format('Y-m-d') : '') }}">
                                        @error('end_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Regras de Recorrência --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-calendar-repeat me-2"></i>
                                Regras de Recorrência
                            </h5>
                            <div id="rules-container">
                                @foreach($recurringAppointment->rules as $index => $rule)
                                    <div class="rule-item mb-3 p-3 border rounded">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="fw-semibold">Dia da Semana <span class="text-danger">*</span></label>
                                                    <select name="rules[{{ $index }}][weekday]" class="form-control rule-weekday" required data-selected="{{ $rule->weekday }}">
                                                        <option value="">Carregando...</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="fw-semibold">Horário Disponível <span class="text-danger">*</span></label>
                                                    <select name="rules[{{ $index }}][time_slot]" class="form-control rule-time-slot" required data-selected="{{ $rule->start_time }}|{{ $rule->end_time }}">
                                                        <option value="">Carregando...</option>
                                                    </select>
                                                    <input type="hidden" name="rules[{{ $index }}][start_time]" class="rule-start-time" value="{{ $rule->start_time }}">
                                                    <input type="hidden" name="rules[{{ $index }}][end_time]" class="rule-end-time" value="{{ $rule->end_time }}">
                                                </div>
                                            </div>
                                            <input type="hidden" name="rules[{{ $index }}][frequency]" value="{{ $rule->frequency ?? 'weekly' }}">
                                            <input type="hidden" name="rules[{{ $index }}][interval]" value="{{ $rule->interval ?? 1 }}">
                                            <div class="col-md-4 d-flex align-items-end">
                                                <div class="form-group w-100">
                                                    <label class="fw-semibold">&nbsp;</label>
                                                    <button type="button" class="btn btn-danger w-100 remove-rule" {{ count($recurringAppointment->rules) <= 1 ? 'style="display: none;"' : '' }}>
                                                        <i class="mdi mdi-delete"></i> Remover
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-success" id="add-rule">
                                <i class="mdi mdi-plus"></i> Adicionar Regra
                            </button>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('tenant.recurring-appointments.index') }}" class="btn btn-light me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Atualizar Agendamento Recorrente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
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
            url: `/tenant/api/doctors/${doctorId}/appointment-types`,
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
            url: `/tenant/api/doctors/${doctorId}/business-hours`,
            method: 'GET',
            success: function(data) {
                businessHours = data;
                updateAllRules();
            },
            error: function(xhr) {
                console.error('Erro ao buscar horários do médico:', xhr);
                alert('Erro ao carregar horários do médico. Por favor, tente novamente.');
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
            url: `/tenant/api/doctors/${doctorId}/available-slots-recurring`,
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
                            <button type="button" class="btn btn-danger w-100 remove-rule">
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

