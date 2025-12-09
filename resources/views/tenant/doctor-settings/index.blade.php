@extends('layouts.connect_plus.app')

@section('title', 'Configurações')

@section('content')

    <div class="page-header">
        <h3 class="page-title">Configurações</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Configurações</li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="mdi mdi-account-doctor text-primary me-2"></i>
                        {{ $doctor->user->display_name ?? $doctor->user->name }}
                    </h4>

                    {{-- Abas --}}
                    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">
                                <i class="mdi mdi-calendar-month me-1"></i>
                                Calendário
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="hours-tab" data-bs-toggle="tab" data-bs-target="#hours" type="button" role="tab">
                                <i class="mdi mdi-clock-outline me-1"></i>
                                Horários
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="types-tab" data-bs-toggle="tab" data-bs-target="#types" type="button" role="tab">
                                <i class="mdi mdi-clipboard-pulse me-1"></i>
                                Tipos de Atendimento
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="settingsTabsContent">
                        {{-- Aba Calendário --}}
                        <div class="tab-pane fade show active" id="calendar" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">
                                        <i class="mdi mdi-calendar-month text-primary me-2"></i>
                                        Calendário
                                    </h5>

                                    @if($calendar)
                                        <form action="{{ route('tenant.doctor-settings.update-calendar') }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">
                                                        <i class="mdi mdi-tag me-1"></i>
                                                        Nome <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                           name="name" value="{{ old('name', $calendar->name) }}" 
                                                           placeholder="Ex: Calendário Principal" required>
                                                    @error('name')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">
                                                        <i class="mdi mdi-link me-1"></i>
                                                        ID Externo
                                                    </label>
                                                    <input type="text" class="form-control @error('external_id') is-invalid @enderror" 
                                                           name="external_id" value="{{ old('external_id', $calendar->external_id) }}" 
                                                           placeholder="ID do calendário em sistema externo (opcional)">
                                                    <small class="form-text text-muted">ID usado para sincronização com calendários externos</small>
                                                    @error('external_id')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-end mt-4">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="mdi mdi-content-save me-1"></i>
                                                    Salvar Calendário
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <form action="{{ route('tenant.doctor-settings.update-calendar') }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="alert alert-info">
                                                <i class="mdi mdi-information-outline me-2"></i>
                                                Nenhum calendário cadastrado. Preencha os dados abaixo para criar um.
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">
                                                        <i class="mdi mdi-tag me-1"></i>
                                                        Nome <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                           name="name" value="{{ old('name') }}" 
                                                           placeholder="Ex: Calendário Principal" required>
                                                    @error('name')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">
                                                        <i class="mdi mdi-link me-1"></i>
                                                        ID Externo
                                                    </label>
                                                    <input type="text" class="form-control @error('external_id') is-invalid @enderror" 
                                                           name="external_id" value="{{ old('external_id') }}" 
                                                           placeholder="ID do calendário em sistema externo (opcional)">
                                                    <small class="form-text text-muted">ID usado para sincronização com calendários externos</small>
                                                    @error('external_id')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-end mt-4">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="mdi mdi-plus me-1"></i>
                                                    Criar Calendário
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Aba Horários --}}
                        <div class="tab-pane fade" id="hours" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="card-title mb-0">
                                            <i class="mdi mdi-clock-outline text-primary me-2"></i>
                                            Horários de Atendimento
                                        </h5>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHourModal">
                                            <i class="mdi mdi-plus me-1"></i>
                                            Novo Horário
                                        </button>
                                    </div>

                                    @if($businessHours->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Dia da Semana</th>
                                                        <th>Horário Início</th>
                                                        <th>Horário Fim</th>
                                                        <th>Intervalo</th>
                                                        <th style="width: 120px;">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                                                    @endphp
                                                    @foreach($businessHours as $hour)
                                                        <tr>
                                                            <td>{{ $days[$hour->weekday] ?? $hour->weekday }}</td>
                                                            <td>{{ $hour->start_time }}</td>
                                                            <td>{{ $hour->end_time }}</td>
                                                            <td>
                                                                @if($hour->break_start_time && $hour->break_end_time)
                                                                    <span class="badge bg-info">
                                                                        <i class="mdi mdi-pause-circle me-1"></i>
                                                                        Intervalo: {{ $hour->break_start_time }} - {{ $hour->break_end_time }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-warning" 
                                                                        onclick="editHour('{{ $hour->id }}', '{{ $hour->weekday }}', '{{ $hour->start_time }}', '{{ $hour->end_time }}', '{{ $hour->break_start_time }}', '{{ $hour->break_end_time }}')">
                                                                    <i class="mdi mdi-pencil"></i>
                                                                </button>
                                                                <form action="{{ route('tenant.doctor-settings.destroy-business-hour', $hour->id) }}" 
                                                                      method="POST" class="d-inline" 
                                                                      onsubmit="return confirm('Tem certeza que deseja remover este horário?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="mdi mdi-information-outline me-2"></i>
                                            Nenhum horário cadastrado. Clique em "Novo Horário" para adicionar.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Aba Tipos --}}
                        <div class="tab-pane fade" id="types" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="card-title mb-0">
                                            <i class="mdi mdi-clipboard-pulse text-primary me-2"></i>
                                            Tipos de Atendimento
                                        </h5>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                                            <i class="mdi mdi-plus me-1"></i>
                                            Novo Tipo
                                        </button>
                                    </div>

                                    @if($appointmentTypes->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Nome</th>
                                                        <th>Duração (min)</th>
                                                        <th>Status</th>
                                                        <th style="width: 120px;">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($appointmentTypes as $type)
                                                        <tr>
                                                            <td>{{ $type->name }}</td>
                                                            <td>{{ $type->duration_min ?? 'N/A' }}</td>
                                                            <td>
                                                                @if ($type->is_active)
                                                                    <span class="badge bg-success">Ativo</span>
                                                                @else
                                                                    <span class="badge bg-danger">Inativo</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-warning" 
                                                                        onclick="editType('{{ $type->id }}', '{{ $type->name }}', '{{ $type->duration_min }}', '{{ $type->is_active }}')">
                                                                    <i class="mdi mdi-pencil"></i>
                                                                </button>
                                                                <form action="{{ route('tenant.doctor-settings.destroy-appointment-type', $type->id) }}" 
                                                                      method="POST" class="d-inline" 
                                                                      onsubmit="return confirm('Tem certeza que deseja remover este tipo de atendimento?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="mdi mdi-information-outline me-2"></i>
                                            Nenhum tipo de atendimento cadastrado. Clique em "Novo Tipo" para adicionar.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Adicionar Horário --}}
    <div class="modal fade" id="addHourModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('tenant.doctor-settings.store-business-hour') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Horário de Atendimento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="mdi mdi-calendar-week me-1"></i>
                                Dias da Semana <span class="text-danger">*</span>
                            </label>
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
                            <div class="mt-2">
                                <button type="button" id="add-weekday-btn" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-plus me-1"></i> Adicionar Dia
                                </button>
                                <button type="button" id="clear-weekdays-btn" class="btn btn-sm btn-outline-secondary">
                                    <i class="mdi mdi-delete-sweep me-1"></i> Limpar
                                </button>
                            </div>
                            <div id="selected-weekdays" class="border rounded p-3 bg-light mt-2" style="min-height: 60px;">
                                <p class="text-muted mb-0">
                                    <i class="mdi mdi-information-outline me-1"></i>
                                    Nenhum dia selecionado
                                </p>
                            </div>
                            <div id="weekdays-inputs"></div>
                            @error('weekdays')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row business-hours-form-layout">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="mdi mdi-clock-start me-1"></i>
                                        Horário Início <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                           name="start_time" value="{{ old('start_time') }}" required>
                                    <small class="form-text text-muted" style="visibility: hidden;">Opcional</small>
                                    @error('start_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="mdi mdi-clock-end me-1"></i>
                                        Horário Fim <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                           name="end_time" value="{{ old('end_time') }}" required>
                                    <small class="form-text text-muted" style="visibility: hidden;">Opcional</small>
                                    @error('end_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="mdi mdi-pause-circle-outline me-1"></i>
                                        Início do Intervalo
                                    </label>
                                    <input type="time" class="form-control @error('break_start_time') is-invalid @enderror" 
                                           name="break_start_time" value="{{ old('break_start_time') }}">
                                    <small class="form-text text-muted">Opcional</small>
                                    @error('break_start_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="mdi mdi-pause-circle me-1"></i>
                                        Fim do Intervalo
                                    </label>
                                    <input type="time" class="form-control @error('break_end_time') is-invalid @enderror" 
                                           name="break_end_time" value="{{ old('break_end_time') }}">
                                    <small class="form-text text-muted">Opcional</small>
                                    @error('break_end_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Editar Horário --}}
    <div class="modal fade" id="editHourModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editHourForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Horário de Atendimento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="mdi mdi-calendar-week me-1"></i>
                                Dia da Semana <span class="text-danger">*</span>
                            </label>
                            <select name="weekday" class="form-control" required>
                                <option value="0">Domingo</option>
                                <option value="1">Segunda-feira</option>
                                <option value="2">Terça-feira</option>
                                <option value="3">Quarta-feira</option>
                                <option value="4">Quinta-feira</option>
                                <option value="5">Sexta-feira</option>
                                <option value="6">Sábado</option>
                            </select>
                        </div>
                        <div class="row business-hours-form-layout">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="mdi mdi-clock-start me-1"></i>
                                        Horário Início <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control" name="start_time" required>
                                    <small class="form-text text-muted" style="visibility: hidden;">Opcional</small>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="mdi mdi-clock-end me-1"></i>
                                        Horário Fim <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control" name="end_time" required>
                                    <small class="form-text text-muted" style="visibility: hidden;">Opcional</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="mdi mdi-pause-circle-outline me-1"></i>
                                        Início do Intervalo
                                    </label>
                                    <input type="time" class="form-control" name="break_start_time">
                                    <small class="form-text text-muted">Opcional</small>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="mdi mdi-pause-circle me-1"></i>
                                        Fim do Intervalo
                                    </label>
                                    <input type="time" class="form-control" name="break_end_time">
                                    <small class="form-text text-muted">Opcional</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Adicionar Tipo --}}
    <div class="modal fade" id="addTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('tenant.doctor-settings.store-appointment-type') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Tipo de Atendimento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="mdi mdi-tag me-1"></i>
                                Nome <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" 
                                   placeholder="Ex: Consulta Médica, Retorno, etc." required>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="mdi mdi-clock-outline me-1"></i>
                                    Duração (minutos) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('duration_min') is-invalid @enderror" 
                                       name="duration_min" value="{{ old('duration_min', 30) }}" 
                                       min="1" placeholder="30" required>
                                @error('duration_min')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="mdi mdi-eye me-1"></i>
                                    Status
                                </label>
                                <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inativo</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Editar Tipo --}}
    <div class="modal fade" id="editTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editTypeForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Tipo de Atendimento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="mdi mdi-tag me-1"></i>
                                Nome <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="mdi mdi-clock-outline me-1"></i>
                                    Duração (minutos) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="duration_min" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="mdi mdi-eye me-1"></i>
                                    Status
                                </label>
                                <select name="is_active" class="form-select">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('styles')
<link href="{{ asset('css/tenant-business-hours.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Gerenciamento de dias da semana no modal de horário
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
        
        function updateWeekdaysDisplay() {
            const container = $('#selected-weekdays');
            container.empty();
            
            if (selectedWeekdays.length === 0) {
                container.html('<p class="text-muted mb-0"><i class="mdi mdi-information-outline me-1"></i>Nenhum dia selecionado</p>');
                return;
            }
            
            selectedWeekdays.forEach(function(weekday) {
                const weekdayStr = String(weekday);
                const name = weekdayNames[weekday] || 'Dia ' + weekday;
                const badge = $('<span>')
                    .addClass('badge bg-primary me-2 mb-2 weekday-badge')
                    .attr('data-id', weekdayStr)
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
            
            const inputsContainer = $('#weekdays-inputs');
            inputsContainer.empty();
            selectedWeekdays.forEach(function(weekday) {
                inputsContainer.append($('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'weekdays[]')
                    .val(parseInt(weekday))
                );
            });
        }
        
        $('#add-weekday-btn').on('click', function() {
            const select = $('#weekday-select');
            const weekday = select.val();
            
            if (weekday === '') {
                alert('Por favor, selecione um dia da semana');
                return;
            }
            
            // Converter para string para comparação consistente
            const weekdayStr = String(weekday);
            if (selectedWeekdays.some(function(id) { return String(id) === weekdayStr; })) {
                alert('Este dia já foi adicionado');
                return;
            }
            
            selectedWeekdays.push(parseInt(weekday));
            updateWeekdaysDisplay();
            select.val('');
        });
        
        // Usar delegação de eventos no container para garantir que funcione
        $('#selected-weekdays').on('click', '.weekday-badge .btn-close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const badge = $(this).closest('.weekday-badge');
            const weekday = String(badge.attr('data-id'));
            
            selectedWeekdays = selectedWeekdays.filter(function(id) {
                return String(id) !== weekday;
            });
            
            updateWeekdaysDisplay();
        });
        
        $('#clear-weekdays-btn').on('click', function() {
            if (selectedWeekdays.length === 0) {
                return;
            }
            
            if (confirm('Deseja remover todos os dias selecionados?')) {
                selectedWeekdays = [];
                updateWeekdaysDisplay();
            }
        });
        
        // Validação antes de submeter o formulário
        $('#addHourModal form').on('submit', function(e) {
            if (selectedWeekdays.length === 0) {
                e.preventDefault();
                alert('Por favor, selecione pelo menos um dia da semana.');
                return false;
            }
            
            const startTime = $(this).find('input[name="start_time"]').val();
            const endTime = $(this).find('input[name="end_time"]').val();
            
            if (!startTime || !endTime) {
                e.preventDefault();
                alert('Por favor, preencha os horários de início e fim.');
                return false;
            }
            
            // Garantir que os weekdays estão como inteiros
            const inputsContainer = $('#weekdays-inputs');
            inputsContainer.empty();
            selectedWeekdays.forEach(function(weekday) {
                inputsContainer.append($('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'weekdays[]')
                    .val(parseInt(weekday))
                );
            });
        });
        
        // Limpar ao fechar o modal
        $('#addHourModal').on('hidden.bs.modal', function () {
            selectedWeekdays = [];
            updateWeekdaysDisplay();
            $('#addHourModal form')[0].reset();
        });
        
        // Função para editar horário
        window.editHour = function(id, weekday, startTime, endTime, breakStartTime, breakEndTime) {
            const form = $('#editHourForm');
            form.attr('action', '{{ route("tenant.doctor-settings.update-business-hour", ":id") }}'.replace(':id', id));
            form.find('select[name="weekday"]').val(weekday);
            form.find('input[name="start_time"]').val(startTime);
            form.find('input[name="end_time"]').val(endTime);
            form.find('input[name="break_start_time"]').val(breakStartTime || '');
            form.find('input[name="break_end_time"]').val(breakEndTime || '');
            $('#editHourModal').modal('show');
        };
        
        // Função para editar tipo
        window.editType = function(id, name, durationMin, isActive) {
            const form = $('#editTypeForm');
            form.attr('action', '{{ route("tenant.doctor-settings.update-appointment-type", ":id") }}'.replace(':id', id));
            form.find('input[name="name"]').val(name);
            form.find('input[name="duration_min"]').val(durationMin);
            form.find('select[name="is_active"]').val(isActive ? '1' : '0');
            $('#editTypeModal').modal('show');
        };
    });
</script>
@endpush

@endsection

