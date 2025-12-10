@extends('layouts.connect_plus.app')

@section('title', 'Editar Agendamento')

@section('content')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="page-title mb-0"> Editar Agendamento </h3>
            <x-help-button module="appointments" />
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.appointments.index') }}">Agendamentos</a>
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
                                Editar Agendamento
                            </h4>
                            <p class="card-description mb-0 text-muted">Atualize as informações do agendamento abaixo</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ workspace_route('tenant.appointments.update', $appointment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Seção: Informações Básicas --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações Básicas
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-doctor me-1"></i>
                                            Médico <span class="text-danger">*</span>
                                        </label>
                                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                            <option value="">Selecione um médico</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->user->name_full ?? $doctor->user->name }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                        <select name="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                            <option value="">Selecione um paciente</option>
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>{{ $patient->full_name }}</option>
                                            @endforeach
                                        </select>
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
                                        <div class="d-flex gap-2">
                                            <input type="date" id="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror @error('starts_at') is-invalid @enderror" 
                                                   name="appointment_date" value="{{ old('appointment_date', $appointment->starts_at ? $appointment->starts_at->format('Y-m-d') : '') }}" 
                                                   min="{{ date('Y-m-d') }}" required>
                                            <button type="button" class="btn btn-info btn-sm" id="btn-show-business-hours" 
                                                    data-bs-toggle="modal" data-bs-target="#businessHoursModal" 
                                                    title="Ver dias trabalhados do médico">
                                                <i class="mdi mdi-calendar-clock"></i>
                                            </button>
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
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ workspace_route('tenant.appointments.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Atualizar Agendamento
                            </button>
                        </div>
                    </form>

                </div>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
    const tenantSlug = '{{ tenant()->subdomain }}';
document.addEventListener('DOMContentLoaded', function() {
    const doctorSelect = document.getElementById('doctor_id');
    const calendarIdInput = document.getElementById('calendar_id');
    const appointmentTypeSelect = document.getElementById('appointment_type');
    const specialtySelect = document.getElementById('specialty_id');
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const startsAtInput = document.getElementById('starts_at');
    const endsAtInput = document.getElementById('ends_at');
    const currentAppointmentTypeId = '{{ old("appointment_type", $appointment->appointment_type) }}';
    const currentSpecialtyId = '{{ old("specialty_id", $appointment->specialty_id) }}';
    const currentDoctorId = '{{ old("doctor_id", $appointment->doctor_id) }}';
    
    // Valores iniciais do agendamento
    const initialStartsAt = '{{ $appointment->starts_at ? $appointment->starts_at->format("Y-m-d H:i:s") : "" }}';
    const initialEndsAt = '{{ $appointment->ends_at ? $appointment->ends_at->format("Y-m-d H:i:s") : "" }}';
    const initialDate = '{{ $appointment->starts_at ? $appointment->starts_at->format("Y-m-d") : "" }}';
    const initialTimeStart = '{{ $appointment->starts_at ? $appointment->starts_at->format("H:i") : "" }}';
    const initialTimeEnd = '{{ $appointment->ends_at ? $appointment->ends_at->format("H:i") : "" }}';

    // Carregar informações quando médico mudar
    doctorSelect.addEventListener('change', function() {
        const doctorId = this.value;
        
        if (!doctorId) {
            resetDependentFields();
            return;
        }

        // Carregar calendário, tipos de consulta e especialidades
        loadCalendar(doctorId);
        loadAppointmentTypes(doctorId);
        loadSpecialties(doctorId);
        
        // Recarregar horários se já houver data selecionada
        if (dateInput.value) {
            loadAvailableSlots(doctorId);
        }
    });

    // Carregar dias trabalhados quando o modal for aberto
    const businessHoursModal = document.getElementById('businessHoursModal');
    businessHoursModal.addEventListener('show.bs.modal', function() {
        const doctorId = doctorSelect.value;
        
        // Resetar estado do modal
        const loadingEl = document.getElementById('business-hours-loading');
        const contentEl = document.getElementById('business-hours-content');
        const errorEl = document.getElementById('business-hours-error');
        const emptyEl = document.getElementById('business-hours-empty');
        
        if (loadingEl) {
            loadingEl.classList.remove('d-none');
            loadingEl.style.display = 'block';
        }
        if (contentEl) {
            contentEl.classList.add('d-none');
            contentEl.style.display = 'none';
        }
        if (errorEl) {
            errorEl.classList.add('d-none');
            errorEl.style.display = 'none';
        }
        if (emptyEl) {
            emptyEl.classList.add('d-none');
            emptyEl.style.display = 'none';
        }
        
        if (doctorId) {
            loadBusinessHours(doctorId);
        } else {
            if (loadingEl) loadingEl.style.display = 'none';
            if (errorEl) {
                errorEl.style.display = 'block';
                document.getElementById('business-hours-error-message').textContent = 'Por favor, selecione um médico primeiro.';
            }
        }
    });

    function loadBusinessHours(doctorId) {
        const loadingEl = document.getElementById('business-hours-loading');
        const contentEl = document.getElementById('business-hours-content');
        const errorEl = document.getElementById('business-hours-error');
        const emptyEl = document.getElementById('business-hours-empty');
        const listEl = document.getElementById('business-hours-list');
        const doctorNameEl = document.getElementById('business-hours-doctor-name');

        // Mostrar loading e esconder outros
        if (loadingEl) {
            loadingEl.classList.remove('d-none');
            loadingEl.style.display = 'block';
        }
        if (contentEl) {
            contentEl.classList.add('d-none');
            contentEl.style.display = 'none';
        }
        if (errorEl) {
            errorEl.classList.add('d-none');
            errorEl.style.display = 'none';
        }
        if (emptyEl) {
            emptyEl.classList.add('d-none');
            emptyEl.style.display = 'none';
        }

        fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/business-hours`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (loadingEl) loadingEl.style.display = 'none';

                // Verificar se os dados são um array direto ou um objeto
                let businessHoursArray = null;
                let doctorInfo = null;

                if (Array.isArray(data)) {
                    // Se for um array direto, usar ele
                    businessHoursArray = data;
                    // Buscar o nome do médico do select
                    const selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
                    const doctorName = selectedOption ? selectedOption.textContent.trim() : 'N/A';
                    doctorInfo = { name: doctorName };
                } else if (data && typeof data === 'object') {
                    if (data.error) {
                        if (loadingEl) {
                            loadingEl.classList.add('d-none');
                            loadingEl.style.display = 'none';
                        }
                        if (errorEl) {
                            errorEl.classList.remove('d-none');
                            errorEl.style.display = 'block';
                            document.getElementById('business-hours-error-message').textContent = data.error;
                        }
                        return;
                    }
                    // Se for um objeto, extrair business_hours
                    businessHoursArray = data.business_hours;
                    doctorInfo = data.doctor;
                } else {
                    if (errorEl) {
                        errorEl.classList.remove('d-none');
                        errorEl.style.display = 'block';
                        document.getElementById('business-hours-error-message').textContent = 'Formato de dados inválido recebido da API.';
                    }
                    return;
                }

                if (!businessHoursArray || businessHoursArray.length === 0) {
                    if (loadingEl) {
                        loadingEl.classList.add('d-none');
                        loadingEl.style.display = 'none';
                    }
                    if (emptyEl) {
                        emptyEl.classList.remove('d-none');
                        emptyEl.style.display = 'block';
                    }
                    return;
                }

                // Exibir informações
                if (doctorNameEl && doctorInfo) {
                    doctorNameEl.textContent = doctorInfo.name || 'N/A';
                }
                
                let html = '<div class="table-responsive"><table class="table table-bordered table-hover">';
                html += '<thead class="table-light"><tr><th>Dia da Semana</th><th>Horários</th></tr></thead>';
                html += '<tbody>';

                businessHoursArray.forEach((day) => {
                    html += '<tr>';
                    html += `<td><strong>${day.weekday_name || 'N/A'}</strong></td>`;
                    html += '<td>';
                    
                    if (day.hours && Array.isArray(day.hours) && day.hours.length > 0) {
                        day.hours.forEach((hour, index) => {
                            if (index > 0) html += '<br>';
                            html += `<span class="badge bg-primary me-1">${hour.start_time || 'N/A'}</span> até <span class="badge bg-primary">${hour.end_time || 'N/A'}</span>`;
                            
                            if (hour.break_start_time && hour.break_end_time) {
                                html += ` <small class="text-muted">(Intervalo: ${hour.break_start_time} - ${hour.break_end_time})</small>`;
                            }
                        });
                    } else {
                        html += '<span class="text-muted">Não trabalha neste dia</span>';
                    }
                    
                    html += '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                
                if (loadingEl) {
                    loadingEl.classList.add('d-none');
                    loadingEl.style.display = 'none';
                }
                
                if (listEl) {
                    listEl.innerHTML = html;
                } else {
                    return;
                }
                
                if (contentEl) {
                    contentEl.classList.remove('d-none');
                    contentEl.style.display = 'block';
                } else {
                    return;
                }
            })
            .catch(error => {
                if (loadingEl) {
                    loadingEl.classList.add('d-none');
                    loadingEl.style.display = 'none';
                }
                if (errorEl) {
                    errorEl.classList.remove('d-none');
                    errorEl.style.display = 'block';
                    document.getElementById('business-hours-error-message').textContent = 'Erro ao carregar informações: ' + error.message;
                }
            });
    }

    // Carregar horários quando data mudar
    dateInput.addEventListener('change', function() {
        const doctorId = doctorSelect.value;
        if (doctorId) {
            loadAvailableSlots(doctorId);
        }
    });

    // Carregar horários quando tipo de consulta mudar
    appointmentTypeSelect.addEventListener('change', function() {
        const doctorId = doctorSelect.value;
        if (doctorId && dateInput.value) {
            loadAvailableSlots(doctorId);
        }
    });

    // Atualizar campos hidden quando horário for selecionado
    timeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            startsAtInput.value = selectedOption.dataset.start;
            endsAtInput.value = selectedOption.dataset.end;
        } else {
            startsAtInput.value = '';
            endsAtInput.value = '';
        }
    });

    // Carregar informações no carregamento inicial se já houver médico selecionado
    if (currentDoctorId && doctorSelect.value) {
        loadCalendar(currentDoctorId);
        loadAppointmentTypes(currentDoctorId);
        loadSpecialties(currentDoctorId);
        
        // Carregar horários disponíveis com os valores iniciais
        if (initialDate) {
            loadAvailableSlots(currentDoctorId, true);
        }
    }

    function resetDependentFields() {
        calendarIdInput.value = '';
        appointmentTypeSelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
        appointmentTypeSelect.disabled = true;
        specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
        specialtySelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Primeiro selecione médico e data</option>';
        timeSelect.disabled = true;
    }

    function loadCalendar(doctorId) {
        fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/calendars`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    // Cada médico tem apenas um calendário
                    calendarIdInput.value = data[0].id;
                } else {
                    calendarIdInput.value = '';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar calendário:', error);
                calendarIdInput.value = '';
            });
    }

    function loadAppointmentTypes(doctorId) {
        appointmentTypeSelect.disabled = true;
        appointmentTypeSelect.innerHTML = '<option value="">Carregando tipos...</option>';

        fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/appointment-types`)
            .then(response => response.json())
            .then(data => {
                appointmentTypeSelect.innerHTML = '<option value="">Selecione um tipo</option>';
                
                let currentTypeFound = false;
                data.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = `${type.name} (${type.duration_min} min)`;
                    option.dataset.duration = type.duration_min;
                    
                    // Preservar seleção atual se o tipo pertencer a este médico
                    if (currentAppointmentTypeId && type.id === currentAppointmentTypeId) {
                        option.selected = true;
                        currentTypeFound = true;
                    }
                    
                    appointmentTypeSelect.appendChild(option);
                });
                
                appointmentTypeSelect.disabled = false;
                
                // Se o tipo atual não foi encontrado, limpar seleção
                if (currentAppointmentTypeId && !currentTypeFound) {
                    appointmentTypeSelect.value = '';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar tipos de consulta:', error);
                appointmentTypeSelect.innerHTML = '<option value="">Erro ao carregar tipos</option>';
            });
    }

    function loadSpecialties(doctorId) {
        specialtySelect.disabled = true;
        specialtySelect.innerHTML = '<option value="">Carregando especialidades...</option>';

        fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/specialties`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                specialtySelect.innerHTML = '<option value="">Selecione uma especialidade</option>';
                
                let currentSpecialtyFound = false;
                if (data && data.length > 0) {
                    data.forEach(specialty => {
                        const option = document.createElement('option');
                        option.value = specialty.id;
                        option.textContent = specialty.name;
                        
                        // Preservar seleção atual se a especialidade pertencer a este médico
                        if (currentSpecialtyId && specialty.id === currentSpecialtyId) {
                            option.selected = true;
                            currentSpecialtyFound = true;
                        }
                        
                        specialtySelect.appendChild(option);
                    });
                } else {
                    specialtySelect.innerHTML = '<option value="">Nenhuma especialidade cadastrada para este médico</option>';
                }
                
                specialtySelect.disabled = false;
                
                // Se a especialidade atual não foi encontrada, limpar seleção
                if (currentSpecialtyId && !currentSpecialtyFound) {
                    specialtySelect.value = '';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar especialidades:', error);
                specialtySelect.innerHTML = '<option value="">Erro ao carregar especialidades</option>';
                specialtySelect.disabled = false;
            });
    }

    function loadAvailableSlots(doctorId, isInitialLoad = false) {
        const date = dateInput.value;
        const appointmentTypeId = appointmentTypeSelect.value;

        if (!doctorId || !date) {
            timeSelect.innerHTML = '<option value="">Primeiro selecione médico e data</option>';
            timeSelect.disabled = true;
            return;
        }

        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Carregando horários...</option>';

        const url = `/workspace/${tenantSlug}/api/doctors/${doctorId}/available-slots?date=${date}`;
        const finalUrl = appointmentTypeId ? `${url}&appointment_type_id=${appointmentTypeId}` : url;

        fetch(finalUrl)
            .then(response => response.json())
            .then(data => {
                timeSelect.innerHTML = '<option value="">Selecione um horário</option>';
                
                if (data.length === 0) {
                    timeSelect.innerHTML = '<option value="">Nenhum horário disponível para esta data</option>';
                } else {
                    let currentTimeFound = false;
                    data.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = `${slot.start}-${slot.end}`;
                        option.textContent = `${slot.start} - ${slot.end}`;
                        option.dataset.start = slot.datetime_start;
                        option.dataset.end = slot.datetime_end;
                        
                        // Se for carregamento inicial, selecionar o horário correspondente
                        if (isInitialLoad && initialTimeStart && initialTimeEnd) {
                            if (slot.start === initialTimeStart && slot.end === initialTimeEnd) {
                                option.selected = true;
                                currentTimeFound = true;
                                startsAtInput.value = slot.datetime_start;
                                endsAtInput.value = slot.datetime_end;
                            }
                        }
                        
                        timeSelect.appendChild(option);
                    });
                    
                    // Se não encontrou o horário exato, tentar encontrar o mais próximo
                    if (isInitialLoad && !currentTimeFound && initialTimeStart) {
                        const options = timeSelect.options;
                        for (let i = 0; i < options.length; i++) {
                            const option = options[i];
                            if (option.dataset.start) {
                                const optionStart = new Date(option.dataset.start);
                                const initialStart = new Date(initialStartsAt);
                                
                                const diffMinutes = Math.abs((optionStart - initialStart) / 1000 / 60);
                                if (diffMinutes <= 5) {
                                    option.selected = true;
                                    startsAtInput.value = option.dataset.start;
                                    endsAtInput.value = option.dataset.end;
                                    currentTimeFound = true;
                                    break;
                                }
                            }
                        }
                    }
                    
                    // Se ainda não encontrou, adicionar o horário atual como opção
                    if (isInitialLoad && !currentTimeFound && initialTimeStart && initialTimeEnd) {
                        const option = document.createElement('option');
                        option.value = `${initialTimeStart}-${initialTimeEnd}`;
                        option.textContent = `${initialTimeStart} - ${initialTimeEnd} (horário atual)`;
                        option.dataset.start = initialStartsAt;
                        option.dataset.end = initialEndsAt;
                        option.selected = true;
                        option.style.color = '#dc3545';
                        timeSelect.insertBefore(option, timeSelect.firstChild.nextSibling);
                        startsAtInput.value = initialStartsAt;
                        endsAtInput.value = initialEndsAt;
                    }
                }
                
                timeSelect.disabled = false;
            })
            .catch(error => {
                console.error('Erro ao carregar horários disponíveis:', error);
                timeSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
                
                if (isInitialLoad && initialTimeStart && initialTimeEnd) {
                    const option = document.createElement('option');
                    option.value = `${initialTimeStart}-${initialTimeEnd}`;
                    option.textContent = `${initialTimeStart} - ${initialTimeEnd} (horário atual)`;
                    option.dataset.start = initialStartsAt;
                    option.dataset.end = initialEndsAt;
                    option.selected = true;
                    timeSelect.innerHTML = '';
                    timeSelect.appendChild(option);
                    startsAtInput.value = initialStartsAt;
                    endsAtInput.value = initialEndsAt;
                }
            });
    }

    // Validação antes de enviar o formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!startsAtInput.value || !endsAtInput.value) {
            e.preventDefault();
            alert('Por favor, selecione um horário disponível.');
            return false;
        }
    });
});
</script>
@endpush

@endsection

