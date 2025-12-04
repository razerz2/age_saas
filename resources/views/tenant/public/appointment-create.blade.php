<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>Novo Agendamento — {{ $tenant->trade_name ?? $tenant->legal_name ?? 'Sistema' }}</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/css/style.css') }}">

    <link rel="shortcut icon" href="{{ asset('connect_plus/assets/images/favicon.png') }}">

    <style>
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .card-title {
            font-weight: 600;
        }
        h5.text-primary {
            font-weight: 600;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        .btn-lg {
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        #appointment_time:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
        }
        .public-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card public-card">
                        <div class="card-body p-5">
                            
                            {{-- Cabeçalho --}}
                            <div class="text-center mb-4">
                                <h3 class="card-title mb-2">
                                    <i class="mdi mdi-calendar-plus text-primary me-2"></i>
                                    Novo Agendamento
                                </h3>
                                <p class="text-muted mb-0">Preencha os dados abaixo para realizar seu agendamento</p>
                            </div>

                            {{-- Nome do Paciente --}}
                            @if(isset($patientName) && $patientName)
                                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                                    <i class="mdi mdi-account-circle me-2" style="font-size: 24px;"></i>
                                    <div>
                                        <strong>Paciente:</strong> {{ $patientName }}
                                    </div>
                                </div>
                            @endif

                            {{-- Mensagens --}}
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="mdi mdi-alert-circle me-2"></i>
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="mdi mdi-alert-circle me-2"></i>
                                    <strong>Erro!</strong> Por favor, verifique os campos abaixo.
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form class="forms-sample" action="{{ route('public.appointment.store', ['tenant' => $tenant->subdomain]) }}" method="POST">
                                @csrf

                                {{-- Seção: Informações Básicas --}}
                                <div class="mb-4">
                                    <h5 class="mb-3 text-primary">
                                        <i class="mdi mdi-information-outline me-2"></i>
                                        Informações Básicas
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-12">
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
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
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
                                        @php
                                            $settings = \App\Models\Tenant\TenantSetting::getAll();
                                            $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
                                        @endphp
                                        @if($defaultMode === 'user_choice')
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-video-account me-1"></i>
                                                        Modo de Consulta <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="appointment_mode" class="form-control @error('appointment_mode') is-invalid @enderror" required>
                                                        <option value="presencial" {{ old('appointment_mode', 'presencial') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                                                        <option value="online" {{ old('appointment_mode') == 'online' ? 'selected' : '' }}>Online</option>
                                                    </select>
                                                    @error('appointment_mode')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
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
                                                <div class="d-flex gap-2">
                                                    <input type="date" id="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                                           name="appointment_date" value="{{ old('appointment_date') }}" 
                                                           min="{{ date('Y-m-d') }}" required>
                                                    <button type="button" class="btn btn-info btn-sm" id="btn-show-business-hours" 
                                                            data-bs-toggle="modal" data-bs-target="#businessHoursModal" 
                                                            title="Ver dias trabalhados do médico" disabled>
                                                        <i class="mdi mdi-calendar-clock"></i>
                                                    </button>
                                                </div>
                                                @error('appointment_date')
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
                                    <input type="hidden" name="calendar_id" id="calendar_id">
                                </div>

                                {{-- Seção: Observações --}}
                                <div class="mb-4">
                                    <h5 class="mb-3 text-primary">
                                        <i class="mdi mdi-information me-2"></i>
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
                                    <a href="{{ route('public.patient.identify', ['tenant' => $tenant->subdomain]) }}" class="btn btn-light">
                                        <i class="mdi mdi-arrow-left me-1"></i>
                                        Voltar
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="mdi mdi-content-save me-1"></i>
                                        Confirmar Agendamento
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
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

    {{-- JS --}}
    <script src="{{ asset('connect_plus/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/misc.js') }}"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tenant = '{{ $tenant->subdomain }}';
        const doctorSelect = document.getElementById('doctor_id');
        const calendarIdInput = document.getElementById('calendar_id'); // Campo hidden
        const appointmentTypeSelect = document.getElementById('appointment_type');
        const specialtySelect = document.getElementById('specialty_id');
        const dateInput = document.getElementById('appointment_date');
        const timeSelect = document.getElementById('appointment_time');
        const startsAtInput = document.getElementById('starts_at');
        const endsAtInput = document.getElementById('ends_at');

        // Setar data mínima como hoje
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);

        // Carregar calendário (automático), tipos e especialidades quando médico for selecionado
        doctorSelect.addEventListener('change', function() {
            const doctorId = this.value;
            
            if (!doctorId) {
                resetDependentFields();
                document.getElementById('btn-show-business-hours').disabled = true;
                return;
            }

            // Habilitar botão de ver dias trabalhados
            document.getElementById('btn-show-business-hours').disabled = false;

            // Seleciona automaticamente o primeiro calendário do médico
            loadCalendarAuto(doctorId);
            loadAppointmentTypes(doctorId);
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
            if (!calendarIdInput.value) {
                e.preventDefault();
                alert('Erro: Calendário não foi selecionado. Por favor, selecione um médico novamente.');
                return false;
            }
            
            if (!startsAtInput.value || !endsAtInput.value) {
                e.preventDefault();
                alert('Por favor, selecione um horário disponível.');
                return false;
            }
        });

        function resetDependentFields() {
            calendarIdInput.value = '';
            
            appointmentTypeSelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
            appointmentTypeSelect.disabled = true;
            
            specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
            specialtySelect.disabled = true;
            
            timeSelect.innerHTML = '<option value="">Primeiro selecione a data</option>';
            timeSelect.disabled = true;
        }

        function loadCalendarAuto(doctorId) {
            // Carrega o primeiro calendário do médico automaticamente
            fetch(`/t/${tenant}/agendamento/api/doctors/${doctorId}/calendars`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        // Seleciona automaticamente o primeiro calendário
                        calendarIdInput.value = data[0].id;
                        console.log('Calendário selecionado automaticamente:', data[0].name);
                    } else {
                        console.error('Nenhum calendário encontrado para este médico');
                        calendarIdInput.value = '';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar calendário:', error);
                    calendarIdInput.value = '';
                });
        }

        function loadAppointmentTypes(doctorId) {
            fetch(`/t/${tenant}/agendamento/api/doctors/${doctorId}/appointment-types`)
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
            fetch(`/t/${tenant}/agendamento/api/doctors/${doctorId}/specialties`)
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

            const url = `/t/${tenant}/agendamento/api/doctors/${doctorId}/available-slots?date=${date}`;
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

        // Carregar dias trabalhados quando o modal for aberto
        const businessHoursModal = document.getElementById('businessHoursModal');
        businessHoursModal.addEventListener('show.bs.modal', function() {
            const doctorId = doctorSelect.value;
            if (doctorId) {
                loadBusinessHours(doctorId);
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

            fetch(`/t/${tenant}/agendamento/api/doctors/${doctorId}/business-hours`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (loadingEl) {
                        loadingEl.classList.add('d-none');
                        loadingEl.style.display = 'none';
                    }

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
    });
    </script>

</body>

</html>

