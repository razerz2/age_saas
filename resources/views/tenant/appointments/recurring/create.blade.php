@extends('layouts.tailadmin.app')

@section('title', 'Criar Agendamento Recorrente')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ workspace_route('tenant.recurring-appointments.index') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">Agendamentos Recorrentes</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Novo Agendamento Recorrente</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Configure um agendamento que se repete automaticamente</p>
            </div>

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
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informações Básicas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Selecione o médico para ver os dias e horários disponíveis</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Especialidade
                            </label>
                            <select name="specialty_id" id="specialty_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('specialty_id') border-red-500 @enderror" disabled>
                                <option value="">Primeiro selecione um médico</option>
                            </select>
                            @error('specialty_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Consulta <span class="text-red-500">*</span>
                            </label>
                            <select name="appointment_type_id" id="appointment_type_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('appointment_type_id') border-red-500 @enderror" required disabled>
                                <option value="">Primeiro selecione uma especialidade</option>
                            </select>
                            @error('appointment_type_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                        @php
                            $settings = \App\Models\Tenant\TenantSetting::getAll();
                            $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
                        @endphp
                        @if($defaultMode === 'user_choice')
                            @include('tenant.appointments.partials.appointment_mode_select', ['appointment' => null])
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Data Inicial <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('start_date') border-red-500 @enderror"
                                   value="{{ old('start_date', date('Y-m-d')) }}" required>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
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
                </div>

                <!-- Seção: Regras de Recorrência -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Regras de Recorrência</h3>
                    <div id="rules-container">
                        <div class="rule-item mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dia da Semana <span class="text-red-500 rule-required-indicator">*</span></label>
                                    <select name="rules[0][weekday]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white rule-weekday" disabled>
                                        <option value="">Selecione o tipo de consulta primeiro</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Horário Disponível <span class="text-red-500 rule-required-indicator">*</span></label>
                                    <select name="rules[0][time_slot]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white rule-time-slot" disabled>
                                        <option value="">Selecione o dia da semana primeiro</option>
                                    </select>
                                    <input type="hidden" name="rules[0][start_time]" class="rule-start-time">
                                    <input type="hidden" name="rules[0][end_time]" class="rule-end-time">
                                </div>
                                <div class="flex items-end rule-button-col">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 rule-label-spacer" style="visibility: hidden;">&nbsp;</label>
                                    <button type="button" class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors rule-action-btn" id="add-rule">
                                        <i class="mdi mdi-plus mr-1"></i> Adicionar Regra
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ workspace_route('tenant.recurring-appointments.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-md text-sm font-medium transition-colors">Cancelar</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary text-white hover:bg-primary/90 text-sm font-medium rounded-md transition-colors">Criar Agendamento Recorrente</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const tenantSlug = '{{ tenant()->subdomain }}';
    let ruleIndex = 1;
    let businessHours = [];
    let doctorId = null;
    let appointmentTypeId = null;
    let startDate = null;

    // Inicializar valores ao carregar a página
    $(document).ready(function() {
        doctorId = $('#doctor_id').val();
        appointmentTypeId = $('#appointment_type_id').val();
        startDate = $('#start_date').val();
        
        // Inicializar regras como desabilitadas
        resetRules();
        
        // Inicializar o estado required da primeira regra
        updateFirstRuleRequired();
        
        // Se já tiver médico selecionado, carregar business hours e especialidades
        if (doctorId) {
            loadBusinessHours();
            loadSpecialties();
            
            // Se já tiver especialidade selecionada, carregar tipos de consulta
            const specialtyId = $('#specialty_id').val();
            if (specialtyId) {
                $('#specialty_id').trigger('change');
            }
            
            // Se já tiver tipo de consulta selecionado, atualizar regras
            if (appointmentTypeId) {
                updateAllRules();
            }
        }
    });

    // Toggle campo de término (Data Final)
    $('#end_type').on('change', function() {
        const endType = $(this).val();
        const showEndDate = endType === 'date';
        
        $('#end_date_field').toggle(showEndDate);
    }).trigger('change');

    // Carregar business hours quando médico for selecionado
    $('#doctor_id').on('change', function() {
        doctorId = $(this).val();
        
        if (!doctorId) {
            businessHours = [];
            $('#specialty_id').html('<option value="">Primeiro selecione um médico</option>').prop('disabled', true);
            $('#appointment_type_id').html('<option value="">Primeiro selecione uma especialidade</option>').prop('disabled', true);
            resetRules();
            return;
        }

        loadBusinessHours();
        loadSpecialties();
    });

    // Função para carregar business hours
    function loadBusinessHours() {
        if (!doctorId) return;

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

    // Função para carregar especialidades automaticamente quando médico for selecionado
    function loadSpecialties() {
        if (!doctorId) return;

        const $specialtySelect = $('#specialty_id');
        $specialtySelect.html('<option value="">Carregando...</option>').prop('disabled', true);

        $.ajax({
            url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/specialties`,
            method: 'GET',
            success: function(data) {
                // Carrega e preenche as especialidades automaticamente
                $specialtySelect.empty();
                
                if (data.length === 0) {
                    $specialtySelect.append('<option value="">Nenhuma especialidade cadastrada</option>');
                } else {
                    $specialtySelect.append('<option value="">Selecione uma especialidade</option>');
                    data.forEach(function(specialty) {
                        $specialtySelect.append(`<option value="${specialty.id}">${specialty.name}</option>`);
                    });
                }
                
                $specialtySelect.prop('disabled', false);
                
                // Resetar tipo de consulta quando especialidade for carregada
                $('#appointment_type_id').html('<option value="">Primeiro selecione uma especialidade</option>').prop('disabled', true);
            },
            error: function(xhr) {
                console.error('Erro ao buscar especialidades:', xhr);
                $specialtySelect.html('<option value="">Erro ao carregar especialidades</option>');
                $specialtySelect.prop('disabled', false);
            }
        });
    }

    // Carregar tipos de consulta automaticamente quando especialidade for selecionada
    $('#specialty_id').on('change', function() {
        const specialtyId = $(this).val();
        const $appointmentTypeSelect = $('#appointment_type_id');
        
        if (!specialtyId) {
            $appointmentTypeSelect.html('<option value="">Primeiro selecione uma especialidade</option>').prop('disabled', true);
            resetRules();
            return;
        }

        // Carregar tipos de consulta do médico (mesmo que não filtremos por especialidade, vamos usar o endpoint do médico)
        if (!doctorId) {
            $appointmentTypeSelect.html('<option value="">Primeiro selecione um médico</option>').prop('disabled', true);
            return;
        }

        $appointmentTypeSelect.html('<option value="">Carregando...</option>').prop('disabled', true);

        $.ajax({
            url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/appointment-types`,
            method: 'GET',
            success: function(data) {
                // Carrega e preenche os tipos de consulta automaticamente
                $appointmentTypeSelect.empty();
                
                if (data.length === 0) {
                    $appointmentTypeSelect.append('<option value="">Nenhum tipo de consulta disponível</option>');
                } else {
                    $appointmentTypeSelect.append('<option value="">Selecione um tipo</option>');
                    data.forEach(function(type) {
                        $appointmentTypeSelect.append(`<option value="${type.id}">${type.name}</option>`);
                    });
                }
                
                $appointmentTypeSelect.prop('disabled', false);
                resetRules();
            },
            error: function(xhr) {
                console.error('Erro ao buscar tipos de consulta:', xhr);
                $appointmentTypeSelect.html('<option value="">Erro ao carregar tipos de consulta</option>');
                $appointmentTypeSelect.prop('disabled', true);
            }
        });
    });

    // Quando tipo de consulta mudar, atualizar regras
    $('#appointment_type_id').on('change', function() {
        appointmentTypeId = $(this).val();
        if (appointmentTypeId) {
            // Habilitar e atualizar regras quando tipo de consulta for selecionado
            // Isso carrega automaticamente os dias da semana disponíveis
            updateAllRules();
        } else {
            // Desabilitar regras quando tipo de consulta não estiver selecionado
            resetRules();
        }
    });

    // Quando data inicial mudar, atualizar regras
    $('#start_date').on('change', function() {
        startDate = $(this).val();
        updateAllRules();
    });

    // Resetar todas as regras (desabilitar)
    function resetRules() {
        $('.rule-item').each(function() {
            const $ruleItem = $(this);
            const $weekdaySelect = $ruleItem.find('.rule-weekday');
            const $timeSlotSelect = $ruleItem.find('.rule-time-slot');
            
            $weekdaySelect.empty().append('<option value="">Selecione o tipo de consulta primeiro</option>').prop('disabled', true);
            $timeSlotSelect.empty().append('<option value="">Selecione o dia da semana primeiro</option>').prop('disabled', true);
            $ruleItem.find('.rule-start-time').val('');
            $ruleItem.find('.rule-end-time').val('');
        });
    }

    // Atualizar todas as regras com os business hours (apenas as não confirmadas)
    function updateAllRules() {
        $('.rule-item').each(function() {
            const $item = $(this);
            // Não atualizar regras confirmadas
            if (!$item.hasClass('rule-confirmed')) {
                updateRule($item);
            }
        });
    }
    
    // Atualizar apenas as outras regras (excluindo uma específica)
    function updateOtherRules(excludeRuleItem) {
        $('.rule-item').each(function() {
            const $item = $(this);
            // Não atualizar regras confirmadas ou a regra excluída
            if (!$item.hasClass('rule-confirmed') && (!excludeRuleItem || !$item.is(excludeRuleItem))) {
                updateRule($item);
            }
        });
    }

    // Obter todos os dias da semana já selecionados em regras confirmadas
    function getSelectedWeekdays(excludeRuleItem) {
        const selectedWeekdays = [];
        $('.rule-item').each(function() {
            const $item = $(this);
            // Não incluir a regra atual na verificação
            if (excludeRuleItem && $item.is(excludeRuleItem)) {
                return;
            }
            // Considerar apenas regras confirmadas ou a primeira regra (que está sendo editada)
            const isConfirmed = $item.hasClass('rule-confirmed');
            const isFirstRule = $item.is($('.rule-item').first());
            
            if (isConfirmed || isFirstRule) {
                const weekday = $item.find('.rule-weekday').val();
                if (weekday && weekday !== '') {
                    selectedWeekdays.push(weekday);
                }
            }
        });
        return selectedWeekdays;
    }

    // Atualizar uma regra específica
    function updateRule($ruleItem) {
        // Não atualizar regras confirmadas (já adicionadas)
        if ($ruleItem.hasClass('rule-confirmed')) {
            return;
        }
        
        // Atualizar valores atuais
        doctorId = $('#doctor_id').val();
        appointmentTypeId = $('#appointment_type_id').val();
        startDate = $('#start_date').val();

        const $weekdaySelect = $ruleItem.find('.rule-weekday');
        const $timeSlotSelect = $ruleItem.find('.rule-time-slot');

        // Limpar selects
        const currentSelectedWeekday = $weekdaySelect.val(); // Salvar valor atual antes de limpar
        $weekdaySelect.empty();
        $timeSlotSelect.empty();
        $timeSlotSelect.prop('disabled', true);

        // Verificar se tipo de consulta está selecionado (requisito para habilitar dia da semana)
        if (!appointmentTypeId) {
            $weekdaySelect.append('<option value="">Selecione o tipo de consulta primeiro</option>');
            $weekdaySelect.prop('disabled', true);
            return;
        }

        if (!doctorId || businessHours.length === 0) {
            $weekdaySelect.append('<option value="">Primeiro selecione um médico</option>');
            $weekdaySelect.prop('disabled', true);
            return;
        }

        // Obter dias já selecionados em outras regras (excluindo a regra atual)
        const selectedWeekdays = getSelectedWeekdays($ruleItem);

        // Carrega e preenche os dias da semana disponíveis, excluindo os já selecionados
        let availableWeekdays = [];
        businessHours.forEach(function(bh) {
            // Não incluir se já estiver selecionado em outra regra (exceto se for o mesmo valor atual)
            if (!selectedWeekdays.includes(bh.weekday_string) || bh.weekday_string === currentSelectedWeekday) {
                availableWeekdays.push(bh);
                $weekdaySelect.append(`<option value="${bh.weekday_string}">${bh.weekday_name}</option>`);
            }
        });

        $weekdaySelect.prop('disabled', false);

        // Restaurar o valor anterior se ainda estiver disponível
        if (currentSelectedWeekday && availableWeekdays.find(bh => bh.weekday_string === currentSelectedWeekday)) {
            $weekdaySelect.val(currentSelectedWeekday);
        }

        // Configurar event handlers primeiro
        setupRuleHandlers($ruleItem);
        
        // Se for a primeira regra, atualizar o estado required
        if ($ruleItem.is($('.rule-item').first())) {
            updateFirstRuleRequired();
        }
        
        // Se não houver dia selecionado e houver dias disponíveis, selecionar o primeiro automaticamente
        if (!currentSelectedWeekday && availableWeekdays.length > 0) {
            const firstWeekday = availableWeekdays[0].weekday_string;
            $weekdaySelect.val(firstWeekday);
            // Carregar os horários diretamente para o primeiro dia selecionado
            loadTimeSlotsForDay($ruleItem, firstWeekday);
        } else if (currentSelectedWeekday) {
            // Se já havia um dia selecionado, recarregar os horários
            loadTimeSlotsForDay($ruleItem, currentSelectedWeekday);
        }
    }

    // Função para carregar horários disponíveis de um dia
    function loadTimeSlotsForDay($ruleItem, weekdayString) {
        const $timeSlotSelect = $ruleItem.find('.rule-time-slot');
        const $startTimeInput = $ruleItem.find('.rule-start-time');
        const $endTimeInput = $ruleItem.find('.rule-end-time');

        $timeSlotSelect.empty();
        $timeSlotSelect.append('<option value="">Carregando...</option>');
        $timeSlotSelect.prop('disabled', true);
        $startTimeInput.val('');
        $endTimeInput.val('');

        // Atualizar valores atuais
        doctorId = $('#doctor_id').val();
        appointmentTypeId = $('#appointment_type_id').val();
        startDate = $('#start_date').val();

        console.log('Carregando horários:', { doctorId, appointmentTypeId, startDate, weekdayString });

        if (!doctorId || !appointmentTypeId || !startDate) {
            $timeSlotSelect.empty();
            let missingFields = [];
            if (!doctorId) missingFields.push('médico');
            if (!appointmentTypeId) missingFields.push('tipo de consulta');
            if (!startDate) missingFields.push('data inicial');
            $timeSlotSelect.append(`<option value="">Selecione: ${missingFields.join(', ')}</option>`);
            return;
        }

        // Buscar horários disponíveis da API
        $.ajax({
            url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/available-slots-recurring`,
            method: 'GET',
            data: {
                weekday: weekdayString,
                appointment_type_id: appointmentTypeId,
                start_date: startDate
            },
            success: function(slots) {
                console.log('Horários recebidos:', slots);
                $timeSlotSelect.empty();
                
                if (slots.length === 0) {
                    $timeSlotSelect.append('<option value="">Nenhum horário disponível</option>');
                    $timeSlotSelect.prop('disabled', true);
                    return;
                }

                $timeSlotSelect.append('<option value="">Selecione um horário</option>');
                slots.forEach(function(slot) {
                    $timeSlotSelect.append(`<option value="${slot.start}|${slot.end}" data-start="${slot.start}" data-end="${slot.end}">${slot.display}</option>`);
                });

                $timeSlotSelect.prop('disabled', false);
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

        // Quando o dia da semana mudar, carrega os horários automaticamente
        $weekdaySelect.off('change').on('change', function() {
            // Atualizar valores atuais antes de carregar
            doctorId = $('#doctor_id').val();
            appointmentTypeId = $('#appointment_type_id').val();
            startDate = $('#start_date').val();

            const weekdayString = $(this).val();
            
            // Não processar mudanças em regras confirmadas
            if ($ruleItem.hasClass('rule-confirmed')) {
                return;
            }
            
            if (weekdayString) {
                // Carrega automaticamente os horários disponíveis para o dia selecionado
                loadTimeSlotsForDay($ruleItem, weekdayString);
                
                // Atualizar apenas as outras regras não confirmadas para remover o dia selecionado das opções
                setTimeout(function() {
                    updateOtherRules($ruleItem);
                }, 100);
            } else {
                $timeSlotSelect.empty();
                $timeSlotSelect.append('<option value="">Selecione o dia</option>');
                $timeSlotSelect.prop('disabled', true);
                // Atualizar todas as outras regras não confirmadas para disponibilizar o dia removido
                setTimeout(function() {
                    updateOtherRules($ruleItem);
                }, 100);
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
        // Pegar o primeiro bloco (bloco atual)
        const $firstRule = $('.rule-item').first();
        
        // Capturar valores do bloco atual
        const currentWeekday = $firstRule.find('.rule-weekday').val();
        const currentTimeSlot = $firstRule.find('.rule-time-slot').val();
        const currentStartTime = $firstRule.find('.rule-start-time').val();
        const currentEndTime = $firstRule.find('.rule-end-time').val();
        const currentTimeSlotText = $firstRule.find('.rule-time-slot option:selected').text();
        
        // Validar se a primeira regra tem dia e horário selecionados
        if (!currentWeekday || !currentTimeSlot || !currentStartTime || !currentEndTime) {
            showAlert({ type: 'warning', title: 'Atenção', message: 'Por favor, selecione um dia da semana e um horário na primeira regra antes de adicionar outra.' });
            return;
        }
        
        // Obter dias já selecionados em regras confirmadas (para não permitir duplicação)
        const selectedWeekdays = [];
        $('.rule-item.rule-confirmed').each(function() {
            const weekday = $(this).find('.rule-weekday').val();
            if (weekday && weekday !== '') {
                selectedWeekdays.push(weekday);
            }
        });
        
        // Verificar se o dia atual já foi selecionado em outra regra confirmada
        if (selectedWeekdays.includes(currentWeekday)) {
            showAlert({ type: 'warning', title: 'Atenção', message: 'Este dia da semana já foi adicionado em outra regra. Não é possível duplicar dias.' });
            return;
        }
        
        // Obter o nome do dia da semana atual
        const currentWeekdayName = $firstRule.find('.rule-weekday option:selected').text();
        
        // Criar novo bloco com os valores do bloco atual (já com valores preenchidos)
        // Usar campos hidden para garantir que os valores sejam enviados (campos disabled não são enviados)
        const ruleHtml = `
            <div class="rule-item mb-3 p-3 border rounded rule-confirmed">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="fw-semibold mb-2">Dia da Semana <span class="text-danger">*</span></label>
                            <select class="form-control rule-weekday" disabled>
                                <option value="${currentWeekday}" selected>${currentWeekdayName}</option>
                            </select>
                            <input type="hidden" name="rules[${ruleIndex}][weekday]" value="${currentWeekday}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="fw-semibold mb-2">Horário Disponível <span class="text-danger">*</span></label>
                            <select class="form-control rule-time-slot" disabled>
                                <option value="${currentTimeSlot}" selected>${currentTimeSlotText}</option>
                            </select>
                            <input type="hidden" name="rules[${ruleIndex}][start_time]" value="${currentStartTime}">
                            <input type="hidden" name="rules[${ruleIndex}][end_time]" value="${currentEndTime}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end rule-button-col">
                        <label class="fw-semibold mb-2 rule-label-spacer" style="visibility: hidden;">&nbsp;</label>
                        <button type="button" class="rule-action-btn remove-rule inline-flex items-center justify-center gap-1 rounded-md bg-error text-white text-xs font-semibold transition hover:bg-error/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-error/50 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                            <i class="mdi mdi-delete"></i> Remover
                        </button>
                    </div>
                    <input type="hidden" name="rules[${ruleIndex}][frequency]" value="weekly">
                    <input type="hidden" name="rules[${ruleIndex}][interval]" value="1">
                </div>
            </div>
        `;
        
        // Adicionar o novo bloco ao final
        const $newRule = $(ruleHtml);
        $('#rules-container').append($newRule);
        
        // Limpar o bloco atual (primeiro bloco) para criar uma nova regra
        $firstRule.find('.rule-weekday').val('');
        $firstRule.find('.rule-time-slot').val('').prop('disabled', true);
        $firstRule.find('.rule-start-time').val('');
        $firstRule.find('.rule-end-time').val('');
        
        // Atualizar a primeira regra para refletir os dias disponíveis
        updateRule($firstRule);
        
        ruleIndex++;
        updateRemoveButtons();
        // Atualizar o estado required da primeira regra (agora não é mais obrigatória)
        updateFirstRuleRequired();
    });
    
    // Validação do formulário antes de submeter
    $('#recurring-appointment-form').on('submit', function(e) {
        // Contar regras confirmadas válidas (com campos hidden)
        let validConfirmedRules = 0;
        $('.rule-item.rule-confirmed').each(function() {
            const weekday = $(this).find('input[name*="[weekday]"]').val();
            const startTime = $(this).find('input[name*="[start_time]"]').val();
            const endTime = $(this).find('input[name*="[end_time]"]').val();
            
            if (weekday && startTime && endTime) {
                validConfirmedRules++;
            }
        });
        
        // Verificar primeira regra (não confirmada)
        const $firstRule = $('.rule-item').first();
        if (!$firstRule.hasClass('rule-confirmed')) {
            const firstWeekday = $firstRule.find('.rule-weekday').val();
            const firstStartTime = $firstRule.find('.rule-start-time').val();
            const firstEndTime = $firstRule.find('.rule-end-time').val();
            
            // Se a primeira regra está preenchida, adicionar aos contadores
            if (firstWeekday && firstStartTime && firstEndTime) {
                validConfirmedRules++;
            }
        }
        
        // Validar que há pelo menos uma regra completa
        if (validConfirmedRules === 0) {
            e.preventDefault();
            showAlert({ type: 'warning', title: 'Atenção', message: 'Por favor, adicione pelo menos uma regra de recorrência completa (dia da semana e horário).' });
            return false;
        }
        
        // Remover campos da primeira regra se estiver vazia e houver regras confirmadas
        const $firstRuleInputs = $firstRule.find('input[name*="rules[0]"], select[name*="rules[0]"]');
        if (validConfirmedRules > 0 && $firstRuleInputs.length > 0) {
            const firstWeekday = $firstRule.find('.rule-weekday').val();
            const firstStartTime = $firstRule.find('.rule-start-time').val();
            const firstEndTime = $firstRule.find('.rule-end-time').val();
            
            if (!firstWeekday || !firstStartTime || !firstEndTime) {
                // Remover o name dos campos da primeira regra para não serem enviados
                $firstRuleInputs.each(function() {
                    if ($(this).attr('name')) {
                        $(this).removeAttr('name');
                    }
                });
            }
        }
        
        return true;
    });

    // Função para atualizar o estado required da primeira regra
    function updateFirstRuleRequired() {
        const $firstRule = $('.rule-item').first();
        const $firstWeekday = $firstRule.find('.rule-weekday');
        const $firstTimeSlot = $firstRule.find('.rule-time-slot');
        const $requiredIndicators = $firstRule.find('.rule-required-indicator');
        
        // Contar regras confirmadas
        const confirmedRulesCount = $('.rule-item.rule-confirmed').length;
        
        if (confirmedRulesCount > 0) {
            // Se há regras confirmadas, primeira regra não é obrigatória
            $firstWeekday.removeAttr('required');
            $firstTimeSlot.removeAttr('required');
            $requiredIndicators.hide();
        } else {
            // Se não há regras confirmadas, primeira regra é obrigatória
            $firstWeekday.attr('required', 'required');
            $firstTimeSlot.attr('required', 'required');
            $requiredIndicators.show();
        }
    }

    // Remover regra
    $(document).on('click', '.remove-rule', function() {
        $(this).closest('.rule-item').remove();
        updateRemoveButtons();
        // Atualizar apenas as regras não confirmadas para disponibilizar o dia removido
        $('.rule-item').each(function() {
            const $item = $(this);
            if (!$item.hasClass('rule-confirmed')) {
                updateRule($item);
            }
        });
        // Atualizar o estado required da primeira regra
        updateFirstRuleRequired();
    });

    // Atualizar botões de remover e adicionar
    function updateRemoveButtons() {
        const ruleCount = $('.rule-item').length;
        // O primeiro bloco (índice 0) sempre mostra "Adicionar Regra" e nunca mostra "Remover"
        // Os demais blocos mostram "Remover" e nunca mostram "Adicionar Regra"
        $('.rule-item').each(function(index) {
            const $addBtn = $(this).find('#add-rule');
            const $removeBtn = $(this).find('.remove-rule');
            
            if (index === 0) {
                // Primeiro bloco: mostra "Adicionar Regra", oculta "Remover"
                $addBtn.show();
                $removeBtn.hide();
            } else {
                // Demais blocos: oculta "Adicionar Regra", mostra "Remover"
                $addBtn.hide();
                $removeBtn.show();
            }
        });
    }

    updateRemoveButtons();
</script>
<style>
    /* Reset e base para regras de recorrência */
    #rules-container .rule-item {
        margin-bottom: 1rem;
    }
    
    #rules-container .rule-item .row {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: flex-end !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    
    #rules-container .rule-item .col-md-4 {
        flex: 0 0 33.333333% !important;
        max-width: 33.333333% !important;
        padding-left: 8px !important;
        padding-right: 8px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-end !important;
    }
    
    #rules-container .rule-item .col-md-4:first-child {
        padding-left: 0 !important;
        padding-right: 8px !important;
    }
    
    #rules-container .rule-item .col-md-4:last-child {
        padding-right: 0 !important;
        padding-left: 8px !important;
    }
    
    #rules-container .rule-item .col-md-4:nth-child(2) {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    
    #rules-container .rule-item .form-group {
        margin-bottom: 0 !important;
        width: 100% !important;
        display: flex !important;
        flex-direction: column !important;
    }
    
    #rules-container .rule-item .form-group label {
        margin-bottom: 0.5rem !important;
        display: block !important;
        line-height: 1.2 !important;
    }
    
    #rules-container .rule-item .form-group .form-control {
        width: 100% !important;
    }
    
    /* Estilos específicos para a coluna do botão */
    #rules-container .rule-item .rule-button-col {
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        padding-left: 8px !important;
        padding-right: 0 !important;
        gap: 0 !important;
    }
    
    #rules-container .rule-item .rule-label-spacer {
        height: 1.2rem !important;
        margin-bottom: 0.5rem !important;
        display: block !important;
        padding: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    #rules-container .rule-item .rule-action-btn {
        width: 100% !important;
        margin: 0 !important;
        padding: 0.375rem 0.75rem !important;
        flex-shrink: 0 !important;
    }
</style>
@endpush

