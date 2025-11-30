@extends('layouts.connect_plus.app')

@section('title', 'Criar Agendamento')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Agendamento </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.appointments.index') }}">Agendamentos</a>
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
                                <i class="mdi mdi-calendar-plus text-primary me-2"></i>
                                Novo Agendamento
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para criar um novo agendamento</p>
                        </div>
                    </div>

                    {{-- Exibição de erros de validação --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <strong><i class="mdi mdi-alert-circle me-1"></i> Erro de Validação!</strong>
                            <ul class="mt-2 mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('tenant.appointments.store') }}" method="POST">
                        @csrf

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
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
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
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-account me-1"></i>
                                            Paciente <span class="text-danger">*</span>
                                        </label>
                                        <select name="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                            <option value="">Selecione um paciente</option>
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>{{ $patient->full_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('patient_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @php
                                    $settings = \App\Models\Tenant\TenantSetting::getAll();
                                    $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
                                @endphp
                                @if($defaultMode === 'user_choice')
                                    @include('tenant.appointments.partials.appointment_mode_select', ['appointment' => null])
                                @endif
                            </div>
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
                                        <input type="date" id="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror @error('starts_at') is-invalid @enderror" 
                                               name="appointment_date" value="{{ old('appointment_date') }}" required>
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
                                        <select name="appointment_time" id="appointment_time" class="form-control @error('appointment_time') is-invalid @enderror" required disabled>
                                            <option value="">Primeiro selecione a data</option>
                                        </select>
                                        @error('appointment_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Horários disponíveis baseados nas configurações do médico</small>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="starts_at" id="starts_at">
                            <input type="hidden" name="ends_at" id="ends_at">
                        </div>

                        {{-- Seção: Observações --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-note-text me-2"></i>
                                Observações
                            </h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-note-text me-1"></i>
                                            Observações
                                        </label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                  name="notes" rows="4" 
                                                  placeholder="Digite observações sobre o agendamento (opcional)">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.appointments.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Agendamento
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tenant-appointments.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const doctorSelect = document.getElementById('doctor_id');
    const appointmentTypeSelect = document.getElementById('appointment_type');
    const specialtySelect = document.getElementById('specialty_id');
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const startsAtInput = document.getElementById('starts_at');
    const endsAtInput = document.getElementById('ends_at');

    // Carregar tipos e especialidades quando médico for selecionado
    doctorSelect.addEventListener('change', function() {
        const doctorId = this.value;
        
        if (!doctorId) {
            // Resetar todos os campos dependentes
            resetDependentFields();
            return;
        }

        // Carregar tipos de consulta
        loadAppointmentTypes(doctorId);
        
        // Carregar especialidades
        loadSpecialties(doctorId);
    });

    // Carregar horários disponíveis quando data ou tipo de consulta mudar
    dateInput.addEventListener('change', function() {
        loadAvailableSlots();
    });

    appointmentTypeSelect.addEventListener('change', function() {
        loadAvailableSlots();
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

    // Validação antes de enviar o formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!startsAtInput.value || !endsAtInput.value) {
            e.preventDefault();
            alert('Por favor, selecione um horário disponível.');
            return false;
        }
    });

    function resetDependentFields() {
        appointmentTypeSelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
        appointmentTypeSelect.disabled = true;
        
        specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
        specialtySelect.disabled = true;
        
        timeSelect.innerHTML = '<option value="">Primeiro selecione a data</option>';
        timeSelect.disabled = true;
    }

    function loadAppointmentTypes(doctorId) {
        fetch(`/tenant/api/doctors/${doctorId}/appointment-types`)
            .then(response => response.json())
            .then(data => {
                appointmentTypeSelect.innerHTML = '<option value="">Selecione um tipo</option>';
                data.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = `${type.name} (${type.duration_min} min)`;
                    option.dataset.duration = type.duration_min;
                    appointmentTypeSelect.appendChild(option);
                });
                appointmentTypeSelect.disabled = false;
            })
            .catch(error => {
                console.error('Erro ao carregar tipos de consulta:', error);
                appointmentTypeSelect.innerHTML = '<option value="">Erro ao carregar tipos</option>';
            });
    }

    function loadSpecialties(doctorId) {
        fetch(`/tenant/api/doctors/${doctorId}/specialties`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                specialtySelect.innerHTML = '<option value="">Selecione uma especialidade</option>';
                
                if (data && data.length > 0) {
                    data.forEach(specialty => {
                        const option = document.createElement('option');
                        option.value = specialty.id;
                        option.textContent = specialty.name;
                        specialtySelect.appendChild(option);
                    });
                } else {
                    specialtySelect.innerHTML = '<option value="">Nenhuma especialidade cadastrada para este médico</option>';
                }
                
                specialtySelect.disabled = false;
            })
            .catch(error => {
                console.error('Erro ao carregar especialidades:', error);
                specialtySelect.innerHTML = '<option value="">Erro ao carregar especialidades</option>';
                specialtySelect.disabled = false;
            });
    }

    function loadAvailableSlots() {
        const doctorId = doctorSelect.value;
        const date = dateInput.value;
        const appointmentTypeId = appointmentTypeSelect.value;

        if (!doctorId || !date) {
            timeSelect.innerHTML = '<option value="">Primeiro selecione médico e data</option>';
            timeSelect.disabled = true;
            return;
        }

        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Carregando horários...</option>';

        const url = `/tenant/api/doctors/${doctorId}/available-slots?date=${date}`;
        const finalUrl = appointmentTypeId ? `${url}&appointment_type_id=${appointmentTypeId}` : url;

        fetch(finalUrl)
            .then(response => response.json())
            .then(data => {
                timeSelect.innerHTML = '<option value="">Selecione um horário</option>';
                
                if (data.length === 0) {
                    timeSelect.innerHTML = '<option value="">Nenhum horário disponível para esta data</option>';
                } else {
                    data.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = `${slot.start}-${slot.end}`;
                        option.textContent = `${slot.start} - ${slot.end}`;
                        option.dataset.start = slot.datetime_start;
                        option.dataset.end = slot.datetime_end;
                        timeSelect.appendChild(option);
                    });
                }
                
                timeSelect.disabled = false;
            })
            .catch(error => {
                console.error('Erro ao carregar horários disponíveis:', error);
                timeSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
            });
    }

    // Se houver valores antigos (old), carregar os campos
    @if(old('doctor_id'))
        doctorSelect.dispatchEvent(new Event('change'));
    @endif
});
</script>
@endpush

@endsection

