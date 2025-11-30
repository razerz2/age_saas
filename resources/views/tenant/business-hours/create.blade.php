@extends('layouts.connect_plus.app')

@section('title', 'Criar Horário Comercial')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Horário Comercial </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.business-hours.index') }}">Horários Comerciais</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar</li>
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
                                <i class="mdi mdi-clock-plus text-primary me-2"></i>
                                Novo Horário Comercial
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para criar um novo horário comercial</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ route('tenant.business-hours.store') }}" method="POST">
                        @csrf

                        {{-- Seção: Informações do Horário --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações do Horário
                            </h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-doctor me-1"></i>
                                            Médico <span class="text-danger">*</span>
                                        </label>
                                        <select name="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                            <option value="">Selecione um médico</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name ?? 'N/A' }}</option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Seção: Dias da Semana --}}
                            <div class="mb-4">
                                <h5 class="mb-3 text-primary">
                                    <i class="mdi mdi-calendar-week me-2"></i>
                                    Dias da Semana
                                </h5>
                                <div class="form-group">
                                    <label class="fw-semibold mb-2">Selecione os dias da semana de atendimento</label>
                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <select id="weekday-select" class="form-control @error('weekdays') is-invalid @enderror">
                                                <option value="">Selecione um dia da semana</option>
                                                <option value="0" data-name="Domingo">Domingo</option>
                                                <option value="1" data-name="Segunda-feira">Segunda-feira</option>
                                                <option value="2" data-name="Terça-feira">Terça-feira</option>
                                                <option value="3" data-name="Quarta-feira">Quarta-feira</option>
                                                <option value="4" data-name="Quinta-feira">Quinta-feira</option>
                                                <option value="5" data-name="Sexta-feira">Sexta-feira</option>
                                                <option value="6" data-name="Sábado">Sábado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex gap-2">
                                                <button type="button" id="add-weekday-btn" class="btn btn-primary flex-fill">
                                                    <i class="mdi mdi-plus me-1"></i> Adicionar
                                                </button>
                                                <button type="button" id="clear-weekdays-btn" class="btn btn-outline-secondary">
                                                    <i class="mdi mdi-delete-sweep"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Área para exibir dias selecionados --}}
                                    <div id="selected-weekdays" class="border rounded p-3 bg-light" style="min-height: 60px;">
                                        @if(old('weekdays'))
                                            @php
                                                $weekdayNames = [
                                                    0 => 'Domingo',
                                                    1 => 'Segunda-feira',
                                                    2 => 'Terça-feira',
                                                    3 => 'Quarta-feira',
                                                    4 => 'Quinta-feira',
                                                    5 => 'Sexta-feira',
                                                    6 => 'Sábado'
                                                ];
                                            @endphp
                                            @foreach(old('weekdays') as $weekday)
                                                <span class="badge bg-primary me-2 mb-2 weekday-badge" data-id="{{ $weekday }}" 
                                                      style="font-size: 13px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px;">
                                                    <i class="mdi mdi-calendar-week"></i>
                                                    {{ $weekdayNames[$weekday] ?? 'Dia ' . $weekday }}
                                                    <button type="button" class="btn-close btn-close-white ms-1" 
                                                            style="font-size: 10px; opacity: 0.8;" 
                                                            aria-label="Remover"></button>
                                                </span>
                                            @endforeach
                                        @else
                                            <p class="text-muted mb-0">
                                                <i class="mdi mdi-information-outline me-1"></i>
                                                Nenhum dia selecionado
                                            </p>
                                        @endif
                                    </div>
                                    
                                    {{-- Campos hidden para enviar os IDs (serão criados dinamicamente pelo JavaScript) --}}
                                    <div id="weekdays-inputs"></div>
                                    
                                    @error('weekdays')
                                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                    @enderror
                                    @error('weekdays.*')
                                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-clock-start me-1"></i>
                                            Horário Início <span class="text-danger">*</span>
                                        </label>
                                        <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                               name="start_time" value="{{ old('start_time') }}" required>
                                        @error('start_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-clock-end me-1"></i>
                                            Horário Fim <span class="text-danger">*</span>
                                        </label>
                                        <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                               name="end_time" value="{{ old('end_time') }}" required>
                                        @error('end_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.business-hours.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Horário Comercial
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tenant-business-hours.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let selectedWeekdays = [];
        
        const weekdayNames = {
            0: 'Domingo',
            1: 'Segunda-feira',
            2: 'Terça-feira',
            3: 'Quarta-feira',
            4: 'Quinta-feira',
            5: 'Sexta-feira',
            6: 'Sábado'
        };
        
        // Carregar dias já selecionados (do old)
        function loadSelectedWeekdays() {
            @if(old('weekdays'))
                selectedWeekdays = @json(old('weekdays'));
            @endif
            updateWeekdaysDisplay();
        }
        
        // Atualizar exibição dos dias selecionados
        function updateWeekdaysDisplay() {
            const container = $('#selected-weekdays');
            container.empty();
            
            if (selectedWeekdays.length === 0) {
                container.html('<p class="text-muted mb-0"><i class="mdi mdi-information-outline me-1"></i>Nenhum dia selecionado</p>');
                return;
            }
            
            selectedWeekdays.forEach(function(weekday) {
                const name = weekdayNames[weekday] || 'Dia ' + weekday;
                const badge = $('<span>')
                    .addClass('badge bg-primary me-2 mb-2 weekday-badge')
                    .attr('data-id', weekday)
                    .css({
                        'font-size': '13px', 
                        'padding': '8px 14px', 
                        'display': 'inline-flex', 
                        'align-items': 'center', 
                        'gap': '6px'
                    })
                    .html('<i class="mdi mdi-calendar-week"></i>' + name + '<button type="button" class="btn-close btn-close-white ms-1" style="font-size: 10px; opacity: 0.8;" aria-label="Remover"></button>');
                container.append(badge);
            });
            
            // Atualizar campos hidden
            const inputsContainer = $('#weekdays-inputs');
            inputsContainer.empty();
            selectedWeekdays.forEach(function(weekday) {
                inputsContainer.append($('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'weekdays[]')
                    .val(weekday)
                );
            });
        }
        
        // Adicionar dia
        $('#add-weekday-btn').on('click', function() {
            const select = $('#weekday-select');
            const weekday = select.val();
            
            if (weekday === '') {
                alert('Por favor, selecione um dia da semana');
                return;
            }
            
            // Verificar se já foi adicionado
            if (selectedWeekdays.includes(weekday)) {
                alert('Este dia já foi adicionado');
                return;
            }
            
            selectedWeekdays.push(weekday);
            updateWeekdaysDisplay();
            select.val(''); // Limpar seleção
        });
        
        // Remover dia (delegation para elementos dinâmicos)
        $(document).on('click', '.weekday-badge .btn-close', function(e) {
            e.preventDefault();
            const badge = $(this).closest('.weekday-badge');
            const weekday = badge.data('id');
            
            selectedWeekdays = selectedWeekdays.filter(function(id) {
                return id !== weekday;
            });
            
            updateWeekdaysDisplay();
        });
        
        // Limpar todos os dias
        $('#clear-weekdays-btn').on('click', function() {
            if (selectedWeekdays.length === 0) {
                return;
            }
            
            if (confirm('Deseja remover todos os dias selecionados?')) {
                selectedWeekdays = [];
                updateWeekdaysDisplay();
            }
        });
        
        // Permitir adicionar com Enter no select
        $('#weekday-select').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#add-weekday-btn').click();
            }
        });
        
        // Inicializar
        loadSelectedWeekdays();
    });
</script>
@endpush

@endsection

