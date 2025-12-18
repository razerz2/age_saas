@extends('layouts.connect_plus.app')

@section('title', 'Configurações')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h3 class="page-title mb-0">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-settings"></i>
            </span>
            Configurações
        </h3>
        <x-help-button module="settings" />
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                {{-- Navegação de Abas --}}
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    {{-- 0. Informações da Clínica --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="registration-tab" data-bs-toggle="tab" href="#registration" role="tab" aria-controls="registration" aria-selected="true">
                            <i class="mdi mdi-hospital-building me-2"></i>Clínica
                        </a>
                    </li>
                    {{-- 1. Configurações Básicas --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="general-tab" data-bs-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="false">
                            <i class="mdi mdi-cog-outline me-2"></i>Geral
                        </a>
                    </li>
                    {{-- 2. Agendamentos --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="appointments-tab" data-bs-toggle="tab" href="#appointments" role="tab" aria-controls="appointments" aria-selected="false">
                            <i class="mdi mdi-calendar-clock me-2"></i>Agendamentos
                        </a>
                    </li>
                    {{-- 3. Calendário --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="calendar-tab" data-bs-toggle="tab" href="#calendar" role="tab" aria-controls="calendar" aria-selected="false">
                            <i class="mdi mdi-calendar-range me-2"></i>Calendário
                        </a>
                    </li>
                    {{-- 4. Profissionais --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="professionals-tab" data-bs-toggle="tab" href="#professionals" role="tab" aria-controls="professionals" aria-selected="false">
                            <i class="mdi mdi-stethoscope me-2"></i>Profissionais
                        </a>
                    </li>
                    {{-- 5. Usuários & Permissões --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="users-tab" data-bs-toggle="tab" href="#users" role="tab" aria-controls="users" aria-selected="false">
                            <i class="mdi mdi-account-group me-2"></i>Usuários & Permissões
                        </a>
                    </li>
                    {{-- 6. Notificações --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="notifications-tab" data-bs-toggle="tab" href="#notifications" role="tab" aria-controls="notifications" aria-selected="false">
                            <i class="mdi mdi-bell-outline me-2"></i>Notificações
                        </a>
                    </li>
                    {{-- 7. Financeiro --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="finance-tab" data-bs-toggle="tab" href="#finance" role="tab" aria-controls="finance" aria-selected="false">
                            <i class="mdi mdi-currency-usd me-2"></i>Financeiro
                        </a>
                    </li>
                    {{-- 8. Integrações --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="integrations-tab" data-bs-toggle="tab" href="#integrations" role="tab" aria-controls="integrations" aria-selected="false">
                            <i class="mdi mdi-link-variant me-2"></i>Integrações
                        </a>
                    </li>
                    {{-- 9. Link de Agendamento --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="public-booking-tab" data-bs-toggle="tab" href="#public-booking" role="tab" aria-controls="public-booking" aria-selected="false">
                            <i class="mdi mdi-link-variant me-2"></i>Link de Agendamento
                        </a>
                    </li>
                    {{-- 10. Aparência --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="appearance-tab" data-bs-toggle="tab" href="#appearance" role="tab" aria-controls="appearance" aria-selected="false">
                            <i class="mdi mdi-palette me-2"></i>Aparência
                        </a>
                    </li>
                </ul>

                {{-- Conteúdo das Abas --}}
                <div class="tab-content p-4" id="settingsTabsContent">
                    {{-- Aba Informações da Clínica --}}
                    <div class="tab-pane fade show active" id="registration" role="tabpanel">
                        <form method="POST" action="{{ workspace_route('tenant.settings.update.registration') }}">
                            @csrf
                            
                            <h4 class="mb-4">Informações da Clínica</h4>
                            <p class="text-muted mb-4">
                                Visualize e edite as informações básicas de cadastro da sua clínica.
                            </p>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Razão Social</label>
                                    <input type="text" class="form-control" name="legal_name" value="{{ old('legal_name', $currentTenant->legal_name) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome Fantasia</label>
                                    <input type="text" class="form-control" name="trade_name" value="{{ old('trade_name', $currentTenant->trade_name) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">E-mail de Contato</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email', $currentTenant->email) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telefone</label>
                                    <input type="text" class="form-control" name="phone" value="{{ old('phone', $currentTenant->phone) }}">
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-4">Endereço</h5>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Logradouro</label>
                                    <input type="text" class="form-control" id="endereco" name="endereco" value="{{ old('endereco', $localizacao->endereco ?? '') }}" required placeholder="Rua, Av...">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Número</label>
                                    <input type="text" class="form-control" name="n_endereco" value="{{ old('n_endereco', $localizacao->n_endereco ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Complemento</label>
                                    <input type="text" class="form-control" name="complemento" value="{{ old('complemento', $localizacao->complemento ?? '') }}">
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label">Bairro</label>
                                    <input type="text" class="form-control" id="bairro" name="bairro" value="{{ old('bairro', $localizacao->bairro ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">CEP</label>
                                    <input type="text" class="form-control" id="cep" name="cep" value="{{ old('cep', $localizacao->cep ?? '') }}" required placeholder="00000-000">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select" id="estado_id" name="estado_id" required>
                                        <option value="">Selecione o estado</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cidade</label>
                                    <select class="form-select" id="cidade_id" name="cidade_id" required>
                                        <option value="">Selecione o estado primeiro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Aba Geral --}}
                    <div class="tab-pane fade" id="general" role="tabpanel">
                        <form method="POST" action="{{ workspace_route('tenant.settings.update.general') }}">
                            @csrf
                            
                            <h4 class="mb-4">Configurações Gerais</h4>
                            <p class="text-muted mb-4">
                                Configure as preferências gerais do sistema.
                            </p>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fuso Horário</label>
                                    <select class="form-select" name="timezone" required>
                                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::AMERICA) as $tz)
                                            <option value="{{ $tz }}" {{ $settings['timezone'] == $tz ? 'selected' : '' }}>
                                                {{ $tz }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Fuso horário usado para exibir datas e horários</small>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Formato de Data</label>
                                    <select class="form-select" name="date_format" required>
                                        <option value="d/m/Y" {{ $settings['date_format'] == 'd/m/Y' ? 'selected' : '' }}>dd/mm/aaaa</option>
                                        <option value="Y-m-d" {{ $settings['date_format'] == 'Y-m-d' ? 'selected' : '' }}>aaaa-mm-dd</option>
                                        <option value="m/d/Y" {{ $settings['date_format'] == 'm/d/Y' ? 'selected' : '' }}>mm/dd/aaaa</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Formato de Hora</label>
                                    <select class="form-select" name="time_format" required>
                                        <option value="H:i" {{ $settings['time_format'] == 'H:i' ? 'selected' : '' }}>24 horas (14:30)</option>
                                        <option value="h:i A" {{ $settings['time_format'] == 'h:i A' ? 'selected' : '' }}>12 horas (02:30 PM)</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Idioma</label>
                                    <select class="form-select" name="language" required>
                                        <option value="pt_BR" {{ $settings['language'] == 'pt_BR' ? 'selected' : '' }}>Português (Brasil)</option>
                                        <option value="en_US" {{ $settings['language'] == 'en_US' ? 'selected' : '' }}>English (US)</option>
                                        <option value="es_ES" {{ $settings['language'] == 'es_ES' ? 'selected' : '' }}>Español</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Aba Agendamentos --}}
                    <div class="tab-pane fade" id="appointments" role="tabpanel">
                        <form method="POST" action="{{ workspace_route('tenant.settings.update.appointments') }}">
                            @csrf
                            
                            <h4 class="mb-4">Configurações de Agendamentos</h4>
                            <p class="text-muted mb-4">
                                Configure o comportamento padrão dos agendamentos.
                            </p>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Duração Padrão (minutos)</label>
                                    <input type="number" class="form-control" name="appointments_default_duration" 
                                           value="{{ $settings['appointments.default_duration'] }}" 
                                           min="15" max="480" step="15" required>
                                    <small class="text-muted">Duração padrão de uma consulta (15, 30, 45, 60, etc.)</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Intervalo Entre Consultas (minutos)</label>
                                    <input type="number" class="form-control" name="appointments_interval_between" 
                                           value="{{ $settings['appointments.interval_between'] }}" 
                                           min="0" max="60" step="5">
                                    <small class="text-muted">Tempo de intervalo entre uma consulta e outra</small>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch p-3 rounded bg-light" style="border: 1px solid #e9ecef;">
                                        <input class="form-check-input" type="checkbox" 
                                               id="appointments_auto_confirm"
                                               name="appointments_auto_confirm"
                                               value="1"
                                               {{ $settings['appointments.auto_confirm'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="appointments_auto_confirm">
                                            <strong class="d-block mb-1">Confirmar Agendamentos Automaticamente</strong>
                                            <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                Quando habilitado, novos agendamentos são automaticamente confirmados.
                                            </p>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch p-3 rounded bg-light" style="border: 1px solid #e9ecef;">
                                        <input class="form-check-input" type="checkbox" 
                                               id="appointments_allow_cancellation"
                                               name="appointments_allow_cancellation"
                                               value="1"
                                               {{ $settings['appointments.allow_cancellation'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="appointments_allow_cancellation">
                                            <strong class="d-block mb-1">Permitir Cancelamento de Agendamentos</strong>
                                            <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                Permite que pacientes e médicos cancelem agendamentos.
                                            </p>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3" id="cancellation_hours_group" 
                                     style="{{ $settings['appointments.allow_cancellation'] ? '' : 'display:none;' }}">
                                    <label class="form-label">Horas Mínimas para Cancelamento</label>
                                    <input type="number" class="form-control" name="appointments_cancellation_hours" 
                                           value="{{ $settings['appointments.cancellation_hours'] }}" 
                                           min="1" step="1">
                                    <small class="text-muted">Mínimo de horas antes do agendamento para permitir cancelamento</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Horas para Lembrete (antes do agendamento)</label>
                                    <input type="number" class="form-control" name="appointments_reminder_hours" 
                                           value="{{ $settings['appointments.reminder_hours'] }}" 
                                           min="1" max="168" step="1">
                                    <small class="text-muted">Quantas horas antes do agendamento enviar lembrete (máx. 168 = 7 dias)</small>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Modo de Atendimento Padrão <span class="text-danger">*</span></label>
                                    <select class="form-select" name="appointments_default_appointment_mode" required>
                                        <option value="presencial" {{ ($settings['appointments.default_appointment_mode'] ?? 'user_choice') == 'presencial' ? 'selected' : '' }}>
                                            Sempre presencial
                                        </option>
                                        <option value="online" {{ ($settings['appointments.default_appointment_mode'] ?? 'user_choice') == 'online' ? 'selected' : '' }}>
                                            Sempre online
                                        </option>
                                        <option value="user_choice" {{ ($settings['appointments.default_appointment_mode'] ?? 'user_choice') == 'user_choice' ? 'selected' : '' }}>
                                            Paciente escolhe
                                        </option>
                                    </select>
                                    <small class="text-muted">
                                        Define como o modo de atendimento será escolhido ao criar agendamentos. 
                                        Se "Paciente escolhe", o campo será exibido para seleção.
                                    </small>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Aba Calendário --}}
                    <div class="tab-pane fade" id="calendar" role="tabpanel">
                        <form method="POST" action="{{ workspace_route('tenant.settings.update.calendar') }}">
                            @csrf
                            
                            <h4 class="mb-4">Configurações de Calendário</h4>
                            <p class="text-muted mb-4">
                                Configure os horários padrão e dias da semana de funcionamento.
                            </p>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Horário de Início Padrão</label>
                                    <input type="time" class="form-control" name="calendar_default_start_time" 
                                           value="{{ $settings['calendar.default_start_time'] }}" required>
                                    <small class="text-muted">Horário padrão de início do expediente</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Horário de Término Padrão</label>
                                    <input type="time" class="form-control" name="calendar_default_end_time" 
                                           value="{{ $settings['calendar.default_end_time'] }}" required>
                                    <small class="text-muted">Horário padrão de término do expediente</small>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Dias da Semana Padrão</label>
                                    <div class="row">
                                        @php
                                            $weekdays = [
                                                0 => 'Domingo',
                                                1 => 'Segunda-feira',
                                                2 => 'Terça-feira',
                                                3 => 'Quarta-feira',
                                                4 => 'Quinta-feira',
                                                5 => 'Sexta-feira',
                                                6 => 'Sábado'
                                            ];
                                            $selectedWeekdays = explode(',', $settings['calendar.default_weekdays']);
                                        @endphp
                                        @foreach ($weekdays as $day => $name)
                                            <div class="col-md-3 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="calendar_default_weekdays[]" 
                                                           value="{{ $day }}"
                                                           id="weekday_{{ $day }}"
                                                           {{ in_array($day, $selectedWeekdays) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="weekday_{{ $day }}">
                                                        {{ $name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">Selecione os dias da semana que serão usados como padrão para novos horários comerciais</small>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch p-3 rounded bg-light" style="border: 1px solid #e9ecef;">
                                        <input class="form-check-input" type="checkbox" 
                                               id="calendar_show_weekends"
                                               name="calendar_show_weekends"
                                               value="1"
                                               {{ $settings['calendar.show_weekends'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="calendar_show_weekends">
                                            <strong class="d-block mb-1">Mostrar Finais de Semana no Calendário</strong>
                                            <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                Exibe sábado e domingo na visualização do calendário, mesmo que não sejam dias de funcionamento.
                                            </p>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Aba Profissionais --}}
                    <div class="tab-pane fade" id="professionals" role="tabpanel">
                        <form method="POST" action="{{ workspace_route('tenant.settings.update.professionals') }}">
                            @csrf
                            
                            <h4 class="mb-4">Configurações de Profissionais</h4>
                            <p class="text-muted mb-4">
                                Configure os rótulos personalizados para profissionais (Médico, Profissional, Psicólogo, etc.).
                            </p>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-cog-outline me-2"></i>Personalização de Rótulos
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-3 p-3 rounded" style="border: 1px solid #e9ecef;">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="professional_customization_enabled"
                                                       name="professional_customization_enabled"
                                                       value="1"
                                                       {{ ($settings['professional.customization_enabled'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="professional_customization_enabled">
                                                    <strong class="d-block mb-1">Habilitar personalização por profissão?</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Quando desabilitado, o sistema sempre usa "Médico", "Médicos" e "CRM". 
                                                        Quando habilitado, você pode personalizar os rótulos globalmente, por especialidade ou por profissional individual.
                                                    </p>
                                                </label>
                                            </div>

                                            <div id="professional_customization_fields" style="display: {{ ($settings['professional.customization_enabled'] ?? false) ? 'block' : 'none' }};">
                                                <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
                                                    <i class="mdi mdi-information-outline me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                                                    <div class="flex-grow-1">
                                                        <strong class="d-block mb-2">Como funciona:</strong>
                                                        <ul class="mb-0" style="font-size: 0.9rem;">
                                                            <li><strong>Rótulos Globais:</strong> Aplicados quando não há personalização por especialidade ou profissional.</li>
                                                            <li><strong>Rótulos por Especialidade:</strong> Configure em cada especialidade para sobrescrever os globais.</li>
                                                            <li><strong>Rótulos Individuais:</strong> Configure em cada profissional para sobrescrever especialidade e globais.</li>
                                                            <li><strong>Hierarquia:</strong> Profissional individual → Especialidade → Global → Padrão</li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Rótulo Singular (Global)</label>
                                                        <input type="text" class="form-control" 
                                                               name="professional_label_singular" 
                                                               value="{{ $settings['professional.label_singular'] ?? '' }}" 
                                                               placeholder="Ex: Profissional, Psicólogo, Dentista"
                                                               maxlength="50">
                                                        <small class="text-muted">Exemplo: "Profissional" ou "Psicólogo"</small>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Rótulo Plural (Global)</label>
                                                        <input type="text" class="form-control" 
                                                               name="professional_label_plural" 
                                                               value="{{ $settings['professional.label_plural'] ?? '' }}" 
                                                               placeholder="Ex: Profissionais, Psicólogos, Dentistas"
                                                               maxlength="50">
                                                        <small class="text-muted">Exemplo: "Profissionais" ou "Psicólogos"</small>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Rótulo de Registro (Global)</label>
                                                        <input type="text" class="form-control" 
                                                               name="professional_registration_label" 
                                                               value="{{ $settings['professional.registration_label'] ?? '' }}" 
                                                               placeholder="Ex: CRM, CRP, CRO"
                                                               maxlength="50">
                                                        <small class="text-muted">Exemplo: "CRM", "CRP" ou "CRO"</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Aba Usuários & Permissões --}}
                    <div class="tab-pane fade" id="users" role="tabpanel">
                        <form method="POST" action="{{ workspace_route('tenant.settings.update.user-defaults') }}">
                            @csrf
                            
                            <h4 class="mb-4">Configurações de Usuários & Permissões</h4>
                            <p class="text-muted mb-4">
                                Defina quais módulos serão atribuídos automaticamente ao criar novos usuários por perfil.
                            </p>

                            <div class="row">
                                {{-- Módulos padrão para Usuário Comum --}}
                                <div class="col-md-6 mb-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-account me-2"></i>Módulos Padrão – Usuário Comum
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted small mb-3">
                                                Selecione os módulos que serão atribuídos automaticamente ao criar um novo usuário comum.
                                            </p>
                                            @php
                                                $allModules = App\Models\Tenant\Module::all();
                                                // Remover módulo "usuários" e "configurações" da lista de opções
                                                $availableModules = collect($allModules)->reject(function($module) {
                                                    return in_array($module['key'], ['users', 'settings']);
                                                })->values()->all();
                                                $commonUserModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_common_user', '[]'), true) ?? [];
                                            @endphp
                                            <div class="border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                                                @foreach($availableModules as $module)
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox"
                                                               name="user_defaults[modules_common_user][]"
                                                               value="{{ $module['key'] }}"
                                                               id="module_common_{{ $module['key'] }}"
                                                               {{ in_array($module['key'], $commonUserModules) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="module_common_{{ $module['key'] }}">
                                                            {{ $module['name'] }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Módulos padrão para Médico --}}
                                <div class="col-md-6 mb-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-doctor me-2"></i>Módulos Padrão – Médico
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted small mb-3">
                                                Selecione os módulos que serão atribuídos automaticamente ao criar um novo usuário médico.
                                            </p>
                                            @php
                                                $doctorModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_doctor', '[]'), true) ?? [];
                                            @endphp
                                            <div class="border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                                                @foreach($availableModules as $module)
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox"
                                                               name="user_defaults[modules_doctor][]"
                                                               value="{{ $module['key'] }}"
                                                               id="module_doctor_{{ $module['key'] }}"
                                                               {{ in_array($module['key'], $doctorModules) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="module_doctor_{{ $module['key'] }}">
                                                            {{ $module['name'] }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info d-flex align-items-start" role="alert">
                                <i class="mdi mdi-information-outline me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                                <div class="flex-grow-1">
                                    <strong class="d-block mb-2">Como funciona:</strong>
                                    <ul class="mb-0" style="font-size: 0.9rem;">
                                        <li>Os módulos selecionados serão aplicados automaticamente ao criar novos usuários com o perfil correspondente.</li>
                                        <li>Usuários <strong>Administradores</strong> não são afetados por essas configurações, pois possuem acesso total ao sistema.</li>
                                        <li>As configurações não afetam usuários já existentes, apenas novos usuários criados após a configuração.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Aba Notificações --}}
                    <div class="tab-pane fade" id="notifications" role="tabpanel">
                        <form method="POST" action="{{ workspace_route('tenant.settings.update.notifications') }}">
                            @csrf
                            
                            <h4 class="mb-4">Configurações de Notificações</h4>
                            <p class="text-muted mb-4">
                                Configure quais tipos de notificações você deseja receber no sistema e como enviar notificações aos pacientes.
                            </p>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-bell-outline me-2"></i>Notificações Internas
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-3 p-3 rounded" style="border: 1px solid #e9ecef;">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="notifications_appointments_enabled"
                                                       name="notifications_appointments_enabled"
                                                       value="1"
                                                       {{ ($settings['notifications.appointments.enabled'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notifications_appointments_enabled">
                                                    <strong class="d-block mb-1">Notificações de Agendamentos</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Receba notificações quando agendamentos forem criados, atualizados, 
                                                        cancelados, reagendados ou quando o status mudar.
                                                    </p>
                                                </label>
                                            </div>

                                            <div class="form-check form-switch p-3 rounded" style="border: 1px solid #e9ecef;">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="notifications_form_responses_enabled"
                                                       name="notifications_form_responses_enabled"
                                                       value="1"
                                                       {{ ($settings['notifications.form_responses.enabled'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notifications_form_responses_enabled">
                                                    <strong class="d-block mb-1">Notificações de Respostas de Formulários</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Receba notificações quando pacientes responderem aos formulários.
                                                    </p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Configurações de Email --}}
                                <div class="col-md-12 mb-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-email-outline me-2"></i>Configurações de Email
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-3 p-3 rounded" style="border: 1px solid #e9ecef;">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="notifications_send_email_to_patients"
                                                       name="notifications_send_email_to_patients"
                                                       value="1"
                                                       {{ ($settings['notifications.send_email_to_patients'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notifications_send_email_to_patients">
                                                    <strong class="d-block mb-1">Enviar e-mails aos pacientes</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Quando habilitado, os pacientes receberão notificações por email sobre agendamentos, formulários, etc.
                                                    </p>
                                                </label>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Driver de Email</label>
                                                <select class="form-select" name="email_driver" id="email_driver" required>
                                                    <option value="global" {{ ($settings['email.driver'] ?? 'global') == 'global' ? 'selected' : '' }}>Usar serviço global do sistema</option>
                                                    <option value="tenancy" {{ ($settings['email.driver'] ?? 'global') == 'tenancy' ? 'selected' : '' }}>Usar SMTP próprio</option>
                                                </select>
                                                <small class="text-muted">Escolha entre usar o serviço global ou configurar seu próprio SMTP</small>
                                            </div>

                                            <div id="email_tenancy_config" style="display: {{ ($settings['email.driver'] ?? 'global') == 'tenancy' ? 'block' : 'none' }};">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Host SMTP</label>
                                                        <input type="text" class="form-control" name="email_host" 
                                                               value="{{ $settings['email.host'] ?? '' }}" 
                                                               placeholder="smtp.exemplo.com">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Porta</label>
                                                        <input type="number" class="form-control" name="email_port" 
                                                               value="{{ $settings['email.port'] ?? '' }}" 
                                                               placeholder="587">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Usuário</label>
                                                        <input type="text" class="form-control" name="email_username" 
                                                               value="{{ $settings['email.username'] ?? '' }}" 
                                                               placeholder="usuario@exemplo.com">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Senha</label>
                                                        <input type="password" class="form-control" name="email_password" 
                                                               value="{{ $settings['email.password'] ?? '' }}" 
                                                               placeholder="••••••••">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Nome do Remetente</label>
                                                        <input type="text" class="form-control" name="email_from_name" 
                                                               value="{{ $settings['email.from_name'] ?? '' }}" 
                                                               placeholder="Nome da Clínica">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Email do Remetente</label>
                                                        <input type="email" class="form-control" name="email_from_address" 
                                                               value="{{ $settings['email.from_address'] ?? '' }}" 
                                                               placeholder="noreply@exemplo.com">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Configurações de WhatsApp --}}
                                <div class="col-md-12 mb-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-whatsapp me-2 text-success"></i>Configurações de WhatsApp
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-3 p-3 rounded" style="border: 1px solid #e9ecef;">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="notifications_send_whatsapp_to_patients"
                                                       name="notifications_send_whatsapp_to_patients"
                                                       value="1"
                                                       {{ ($settings['notifications.send_whatsapp_to_patients'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notifications_send_whatsapp_to_patients">
                                                    <strong class="d-block mb-1">Enviar WhatsApp aos pacientes</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Quando habilitado, os pacientes receberão notificações por WhatsApp sobre agendamentos, formulários, etc.
                                                    </p>
                                                </label>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Driver de WhatsApp</label>
                                                <select class="form-select" name="whatsapp_driver" id="whatsapp_driver" required>
                                                    <option value="global" {{ ($settings['whatsapp.driver'] ?? 'global') == 'global' ? 'selected' : '' }}>Usar serviço global do sistema</option>
                                                    <option value="tenancy" {{ ($settings['whatsapp.driver'] ?? 'global') == 'tenancy' ? 'selected' : '' }}>Usar API própria</option>
                                                </select>
                                                <small class="text-muted">Escolha entre usar o serviço global ou configurar sua própria API de WhatsApp</small>
                                            </div>

                                            <div id="whatsapp_tenancy_config" style="display: {{ ($settings['whatsapp.driver'] ?? 'global') == 'tenancy' ? 'block' : 'none' }};">
                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label class="form-label">API URL</label>
                                                        <input type="url" class="form-control" name="whatsapp_api_url" 
                                                               value="{{ $settings['whatsapp.api_url'] ?? '' }}" 
                                                               placeholder="https://api.exemplo.com/send">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">API Token</label>
                                                        <input type="text" class="form-control" name="whatsapp_api_token" 
                                                               value="{{ $settings['whatsapp.api_token'] ?? '' }}" 
                                                               placeholder="seu-token-aqui">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Sender (Remetente)</label>
                                                        <input type="text" class="form-control" name="whatsapp_sender" 
                                                               value="{{ $settings['whatsapp.sender'] ?? '' }}" 
                                                               placeholder="5511999999999">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Aba Financeiro --}}
                    <div class="tab-pane fade" id="finance" role="tabpanel">
                        @php
                            $financeEnabled = tenant_setting('finance.enabled') === 'true';
                            $user = auth()->user();
                            // Garantir que modules seja sempre um array
                            $userModules = [];
                            if ($user && $user->modules) {
                                if (is_array($user->modules)) {
                                    $userModules = $user->modules;
                                } elseif (is_string($user->modules)) {
                                    $decoded = json_decode($user->modules, true);
                                    $userModules = is_array($decoded) ? $decoded : [];
                                }
                            }
                            $hasFinanceModule = ($user && $user->role === 'admin') || in_array('finance', $userModules);
                        @endphp

                        @if(!$financeEnabled)
                            <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
                                <i class="mdi mdi-alert-circle-outline me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                                <div class="flex-grow-1">
                                    <strong class="d-block mb-2">Módulo Financeiro Desabilitado</strong>
                                    <p class="mb-3" style="font-size: 0.9rem;">
                                        O módulo financeiro não está habilitado para este tenant. Para habilitar e configurar, 
                                        acesse a página de configurações completas.
                                    </p>
                                    <a href="{{ workspace_route('tenant.settings.finance.index') }}" class="btn btn-primary btn-sm">
                                        <i class="mdi mdi-cog me-2"></i>Habilitar e Configurar
                                    </a>
                                </div>
                            </div>
                        @elseif(!$hasFinanceModule)
                            <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
                                <i class="mdi mdi-alert-circle-outline me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                                <div class="flex-grow-1">
                                    <strong class="d-block mb-2">Sem Acesso ao Módulo Financeiro</strong>
                                    <p class="mb-0" style="font-size: 0.9rem;">
                                        Você não possui permissão para acessar o módulo financeiro. Entre em contato com o administrador 
                                        para solicitar acesso.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="mb-2">Configurações Financeiras</h4>
                                    <p class="text-muted mb-0">
                                        Configure o módulo financeiro, integração com Asaas e regras de cobrança.
                                    </p>
                                </div>
                                <a href="{{ workspace_route('tenant.settings.finance.index') }}" class="btn btn-primary">
                                    <i class="mdi mdi-cog me-2"></i>Configurações Completas
                                </a>
                            </div>

                            <div class="alert alert-info d-flex align-items-start" role="alert">
                                <i class="mdi mdi-information-outline me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                                <div class="flex-grow-1">
                                    <strong class="d-block mb-2">Módulo Financeiro Ativo</strong>
                                    <p class="mb-0" style="font-size: 0.9rem;">
                                        Clique em "Configurações Completas" para acessar todas as opções de configuração do módulo financeiro, 
                                        incluindo integração com Asaas, regras de cobrança, comissões e muito mais.
                                    </p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-credit-card me-2"></i>Status do Módulo
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>Módulo Financeiro</span>
                                                <span class="badge bg-success">
                                                    <i class="mdi mdi-check-circle me-1"></i>Habilitado
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-link-variant me-2"></i>Integração Asaas
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $asaasEnv = tenant_setting('finance.asaas.environment', 'sandbox');
                                                $asaasKey = tenant_setting('finance.asaas.api_key', '');
                                            @endphp
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span>Ambiente</span>
                                                <span class="badge {{ $asaasEnv === 'production' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ $asaasEnv === 'production' ? 'Produção' : 'Sandbox' }}
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>API Key</span>
                                                <span class="badge {{ !empty($asaasKey) ? 'bg-success' : 'bg-danger' }}">
                                                    {{ !empty($asaasKey) ? 'Configurada' : 'Não Configurada' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Aba Integrações --}}
                    <div class="tab-pane fade" id="integrations" role="tabpanel">
                        <form method="POST" action="{{ workspace_route('tenant.settings.update.integrations') }}">
                            @csrf
                            
                            <h4 class="mb-4">Configurações de Integrações</h4>
                            <p class="text-muted mb-4">
                                Configure as integrações com serviços externos.
                            </p>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light d-flex align-items-center justify-content-between">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-google me-2 text-primary"></i>Google Calendar
                                            </h5>
                                            @if($hasGoogleCalendarIntegration && $googleCalendarIntegration)
                                                <span class="badge bg-success">
                                                    <i class="mdi mdi-check-circle me-1"></i>Configurado
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="mdi mdi-alert-circle me-1"></i>Não Configurado
                                                </span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            @if(!$hasGoogleCalendarIntegration)
                                                <div class="alert alert-warning border-warning d-flex align-items-start mb-4" role="alert">
                                                    <i class="mdi mdi-alert-circle-outline me-3" style="font-size: 1.75rem; flex-shrink: 0;"></i>
                                                    <div class="flex-grow-1">
                                                        <strong class="d-block mb-2">Atenção! Integração não configurada</strong>
                                                        <p class="mb-2" style="font-size: 0.9rem;">
                                                            Para habilitar a sincronização com Google Calendar, é necessário cadastrar primeiro a integração 
                                                            com a chave <code class="bg-light px-2 py-1 rounded">google_calendar</code> e configurar a API no campo de configuração (JSON).
                                                        </p>
                                                        <div class="mt-3">
                                                            <a href="{{ workspace_route('tenant.integrations.create') }}" class="btn btn-primary btn-sm me-2">
                                                                <i class="mdi mdi-plus-circle me-2"></i>Cadastrar Integração
                                                            </a>
                                                            <a href="{{ workspace_route('tenant.integrations.index') }}" class="btn btn-outline-secondary btn-sm">
                                                                <i class="mdi mdi-view-list me-2"></i>Ver Todas as Integrações
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="form-check form-switch mb-3 p-3 rounded {{ !$hasGoogleCalendarIntegration ? 'bg-light' : '' }}" 
                                                 style="border: 1px solid {{ !$hasGoogleCalendarIntegration ? '#dee2e6' : '#e9ecef' }};">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="integrations_google_calendar_enabled"
                                                       name="integrations_google_calendar_enabled"
                                                       value="1"
                                                       {{ $settings['integrations.google_calendar.enabled'] ? 'checked' : '' }}
                                                       {{ !$hasGoogleCalendarIntegration ? 'disabled' : '' }}
                                                       style="cursor: {{ !$hasGoogleCalendarIntegration ? 'not-allowed' : 'pointer' }};">
                                                <label class="form-check-label {{ !$hasGoogleCalendarIntegration ? 'text-muted' : '' }}" 
                                                       for="integrations_google_calendar_enabled"
                                                       style="cursor: {{ !$hasGoogleCalendarIntegration ? 'not-allowed' : 'pointer' }};">
                                                    <strong class="d-block mb-1">Habilitar Sincronização com Google Calendar</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Sincronize seus agendamentos com o Google Calendar.
                                                        @if(!$hasGoogleCalendarIntegration)
                                                            <br><span class="badge bg-danger mt-1">
                                                                <i class="mdi mdi-alert me-1"></i>Cadastre a integração primeiro
                                                            </span>
                                                        @endif
                                                    </p>
                                                </label>
                                            </div>

                                            <div class="form-check form-switch p-3 rounded {{ !$hasGoogleCalendarIntegration ? 'bg-light' : '' }}" 
                                                 id="google_calendar_auto_sync_group"
                                                 style="display: {{ $settings['integrations.google_calendar.enabled'] && $hasGoogleCalendarIntegration ? 'block' : 'none' }}; border: 1px solid {{ !$hasGoogleCalendarIntegration ? '#dee2e6' : '#e9ecef' }};">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="integrations_google_calendar_auto_sync"
                                                       name="integrations_google_calendar_auto_sync"
                                                       value="1"
                                                       {{ $settings['integrations.google_calendar.auto_sync'] ? 'checked' : '' }}
                                                       {{ !$hasGoogleCalendarIntegration ? 'disabled' : '' }}
                                                       style="cursor: {{ !$hasGoogleCalendarIntegration ? 'not-allowed' : 'pointer' }};">
                                                <label class="form-check-label" 
                                                       for="integrations_google_calendar_auto_sync"
                                                       style="cursor: {{ !$hasGoogleCalendarIntegration ? 'not-allowed' : 'pointer' }};">
                                                    <strong class="d-block mb-1">Sincronização Automática</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Sincronize automaticamente os agendamentos com o Google Calendar em tempo real.
                                                    </p>
                                                </label>
                                            </div>

                                            @if($hasGoogleCalendarIntegration && $googleCalendarIntegration)
                                                <div class="mt-4 pt-3 border-top">
                                                    <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                                                        <i class="mdi mdi-check-circle-outline me-2"></i>
                                                        <div>
                                                            <strong class="d-block">Integração configurada com sucesso!</strong>
                                                            <small>A integração Google Calendar está cadastrada e pronta para uso.</small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ workspace_route('tenant.integrations.edit', ['id' => $googleCalendarIntegration->id]) }}" class="btn btn-outline-primary btn-sm">
                                                            <i class="mdi mdi-pencil me-2"></i>Editar Integração
                                                        </a>
                                                        <a href="{{ workspace_route('tenant.oauth-accounts.index') }}" class="btn btn-outline-secondary btn-sm">
                                                            <i class="mdi mdi-link-variant me-2"></i>Gerenciar Contas OAuth
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Aba Link de Agendamento Público --}}
                    <div class="tab-pane fade" id="public-booking" role="tabpanel">
                        <h4 class="mb-4">Link de Agendamento Público</h4>
                        <p class="text-muted mb-4">
                            Compartilhe este link com seus pacientes para que eles possam agendar consultas diretamente pela internet.
                        </p>

                        @if($publicBookingUrl)
                            <div class="card border shadow-sm mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="mdi mdi-link-variant me-2"></i>Seu Link de Agendamento
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Link para compartilhar:</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="publicBookingLink" 
                                                   value="{{ $publicBookingUrl }}" readonly>
                                            <button class="btn btn-primary" type="button" onclick="copyPublicBookingLink()">
                                                <i class="mdi mdi-content-copy me-2"></i>Copiar Link
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="mdi mdi-information-outline me-1"></i>
                                            Clique em "Copiar Link" para copiar o endereço completo
                                        </small>
                                    </div>

                                    <div class="alert alert-success d-flex align-items-start" id="copySuccessAlert" role="alert" style="display: none;">
                                        <i class="mdi mdi-check-circle-outline me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <strong class="d-block mb-2">Link copiado com sucesso!</strong>
                                            <p class="mb-0" style="font-size: 0.9rem;">
                                                O link foi copiado para a área de transferência. Agora você pode colar em qualquer lugar.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning" role="alert">
                                <i class="mdi mdi-alert-circle-outline me-2"></i>
                                <strong>Atenção:</strong> Não foi possível gerar o link de agendamento público. Verifique se o tenant está configurado corretamente.
                            </div>
                        @endif

                        <div class="card border shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="mdi mdi-information-outline me-2"></i>Sobre o Link de Agendamento Público
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="mdi mdi-calendar-check me-2 text-primary"></i>Como funciona?
                                    </h6>
                                    <p class="text-muted mb-3">
                                        O link de agendamento público permite que seus pacientes agendem consultas diretamente pela internet, 
                                        sem precisar entrar em contato por telefone ou WhatsApp. É uma forma prática e moderna de receber agendamentos.
                                    </p>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            <strong>Fácil de usar:</strong> Os pacientes acessam o link e seguem um processo simples e intuitivo
                                        </li>
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            <strong>Disponível 24/7:</strong> Seus pacientes podem agendar a qualquer hora do dia ou da noite
                                        </li>
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            <strong>Reduz filas:</strong> Diminui o volume de ligações e mensagens para agendamento
                                        </li>
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            <strong>Organização automática:</strong> Os agendamentos são registrados diretamente no sistema
                                        </li>
                                    </ul>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="mdi mdi-share-variant me-2 text-primary"></i>Onde compartilhar?
                                    </h6>
                                    <p class="text-muted mb-3">
                                        Você pode adicionar este link em vários lugares para facilitar o acesso dos seus pacientes:
                                    </p>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="mdi mdi-facebook me-3 text-primary" style="font-size: 1.5rem;"></i>
                                                <div>
                                                    <strong class="d-block">Redes Sociais</strong>
                                                    <small class="text-muted">Adicione o link na bio do Instagram, Facebook, LinkedIn ou outras redes sociais da sua clínica ou consultório</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="mdi mdi-whatsapp me-3 text-success" style="font-size: 1.5rem;"></i>
                                                <div>
                                                    <strong class="d-block">WhatsApp</strong>
                                                    <small class="text-muted">Envie o link diretamente para pacientes ou adicione em mensagens automáticas</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="mdi mdi-email me-3 text-danger" style="font-size: 1.5rem;"></i>
                                                <div>
                                                    <strong class="d-block">E-mail</strong>
                                                    <small class="text-muted">Inclua o link em assinaturas de e-mail ou em campanhas de marketing</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="mdi mdi-web me-3 text-info" style="font-size: 1.5rem;"></i>
                                                <div>
                                                    <strong class="d-block">Site ou Blog</strong>
                                                    <small class="text-muted">Adicione um botão ou link no seu site para facilitar o agendamento</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info d-flex align-items-start" role="alert">
                                    <i class="mdi mdi-lightbulb-outline me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                                    <div class="flex-grow-1">
                                        <strong class="d-block mb-2">Dica:</strong>
                                        <p class="mb-0" style="font-size: 0.9rem;">
                                            Para médicos autônomos, clínicas e empresas, este link é uma excelente forma de profissionalizar 
                                            o atendimento e facilitar o processo de agendamento. Quanto mais fácil for para o paciente agendar, 
                                            mais consultas você receberá!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Aba Aparência --}}
                    <div class="tab-pane fade" id="appearance" role="tabpanel">
                        <form method="POST" action="{{ workspace_route('tenant.settings.update.appearance') }}" enctype="multipart/form-data" id="appearance-form">
                            @csrf
                            
                            <h4 class="mb-4">Configurações de Aparência</h4>
                            <p class="text-muted mb-4">
                                Personalize a aparência do sistema com seu logo e favicon. Se não informar nenhuma imagem, será usada a padrão do sistema.
                            </p>

                            <div class="row">
                                {{-- Logo do Menu (Normal) --}}
                                <div class="col-md-4 mb-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-image me-2"></i>Logo Normal
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $currentLogo = \App\Models\Tenant\TenantSetting::get('appearance.logo');
                                                $logoUrl = $currentLogo ? asset('storage/' . $currentLogo) : asset('connect_plus/assets/images/logos/AllSync-Logo-A.png');
                                            @endphp
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Logo Atual</label>
                                                <div class="border rounded p-3 bg-light text-center">
                                                    <img src="{{ $logoUrl }}" alt="Logo" id="logo-preview" 
                                                         style="max-width: 100%; max-height: 150px; object-fit: contain;">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Selecionar Nova Imagem</label>
                                                <input type="file" class="form-control" name="logo" id="logo-input" 
                                                       accept="image/png,image/jpeg,image/jpg,image/gif,image/svg+xml"
                                                       onchange="previewImage(this, 'logo-preview')">
                                                <small class="text-muted">
                                                    Formatos aceitos: PNG, JPG, GIF, SVG. Tamanho recomendado: 200x60px
                                                </small>
                                            </div>

                                            @if($currentLogo)
                                                <div class="mb-3">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('logo')">
                                                        <i class="mdi mdi-delete me-2"></i>Remover Logo
                                                    </button>
                                                    <input type="hidden" name="remove_logo" id="remove-logo" value="0">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Logo Retrátil (Mini) --}}
                                <div class="col-md-4 mb-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-image-outline me-2"></i>Logo Retrátil
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $currentLogoMini = \App\Models\Tenant\TenantSetting::get('appearance.logo_mini');
                                                $logoMiniUrl = $currentLogoMini ? asset('storage/' . $currentLogoMini) : ($currentLogo ? asset('storage/' . $currentLogo) : asset('connect_plus/assets/images/logos/AllSync-Logo-A.png'));
                                            @endphp
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Logo Atual</label>
                                                <div class="border rounded p-3 bg-light text-center">
                                                    <img src="{{ $logoMiniUrl }}" alt="Logo Retrátil" id="logo-mini-preview" 
                                                         style="max-width: 100%; max-height: 150px; object-fit: contain;">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Selecionar Nova Imagem</label>
                                                <input type="file" class="form-control" name="logo_mini" id="logo-mini-input" 
                                                       accept="image/png,image/jpeg,image/jpg,image/gif,image/svg+xml"
                                                       onchange="previewImage(this, 'logo-mini-preview')">
                                                <small class="text-muted">
                                                    Formatos aceitos: PNG, JPG, GIF, SVG. Tamanho recomendado: 60x60px (quadrado)
                                                </small>
                                            </div>

                                            @if($currentLogoMini)
                                                <div class="mb-3">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('logo_mini')">
                                                        <i class="mdi mdi-delete me-2"></i>Remover Logo Retrátil
                                                    </button>
                                                    <input type="hidden" name="remove_logo_mini" id="remove-logo-mini" value="0">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Favicon --}}
                                <div class="col-md-4 mb-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-file-image me-2"></i>Favicon
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $currentFavicon = \App\Models\Tenant\TenantSetting::get('appearance.favicon');
                                                $faviconUrl = $currentFavicon ? asset('storage/' . $currentFavicon) : asset('connect_plus/assets/images/favicon.png');
                                            @endphp
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Favicon Atual</label>
                                                <div class="border rounded p-3 bg-light text-center">
                                                    <img src="{{ $faviconUrl }}" alt="Favicon" id="favicon-preview" 
                                                         style="max-width: 64px; max-height: 64px; object-fit: contain;">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Selecionar Nova Imagem</label>
                                                <input type="file" class="form-control" name="favicon" id="favicon-input" 
                                                       accept="image/png,image/x-icon,image/svg+xml"
                                                       onchange="previewImage(this, 'favicon-preview')">
                                                <small class="text-muted">
                                                    Formatos aceitos: PNG, ICO, SVG. Tamanho recomendado: 32x32px ou 64x64px
                                                </small>
                                            </div>

                                            @if($currentFavicon)
                                                <div class="mb-3">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('favicon')">
                                                        <i class="mdi mdi-delete me-2"></i>Remover Favicon Personalizado
                                                    </button>
                                                    <input type="hidden" name="remove_favicon" id="remove-favicon" value="0">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info d-flex align-items-start" role="alert">
                                <i class="mdi mdi-information-outline me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                                <div class="flex-grow-1">
                                    <strong class="d-block mb-2">Importante:</strong>
                                    <ul class="mb-0" style="font-size: 0.9rem;">
                                        <li>As imagens serão redimensionadas automaticamente se necessário</li>
                                        <li>Para melhor resultado, use imagens de alta qualidade</li>
                                        <li>Se não informar nenhuma imagem, será usada a padrão do sistema</li>
                                        <li>Após salvar, pode ser necessário limpar o cache do navegador para ver as mudanças</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('styles')
    <link href="{{ asset('css/tenant-settings.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Verificar se há mensagem de sucesso com redirecionamento para aba específica
        @if(session('redirect_to_tab'))
            const targetTab = $('#settingsTabs a[href="#{{ session('redirect_to_tab') }}"]');
            if (targetTab.length) {
                // Remover active de todas as tabs e panes
                $('#settingsTabs a[data-bs-toggle="tab"]').removeClass('active').attr('aria-selected', 'false');
                $('.tab-pane').removeClass('show active');
                
                // Ativar a tab e pane correspondente
                targetTab.addClass('active').attr('aria-selected', 'true');
                $('#{{ session('redirect_to_tab') }}').addClass('show active');
                
                // Adicionar hash na URL
                if (history.pushState) {
                    history.pushState(null, null, '#{{ session('redirect_to_tab') }}');
                } else {
                    window.location.hash = '#{{ session('redirect_to_tab') }}';
                }
                
                // Scroll suave até a aba
                $('html, body').animate({
                    scrollTop: $('#settingsTabs').offset().top - 20
                }, 500);
            }
        @endif
        
        // Verificar se há hash na URL para abrir aba específica
        if (window.location.hash) {
            const hash = window.location.hash.substring(1); // Remove o #
            const targetTab = $('#settingsTabs a[href="#' + hash + '"]');
            
            if (targetTab.length) {
                // Remover active de todas as tabs e panes
                $('#settingsTabs a[data-bs-toggle="tab"]').removeClass('active').attr('aria-selected', 'false');
                $('.tab-pane').removeClass('show active');
                
                // Ativar a tab e pane correspondente ao hash
                targetTab.addClass('active').attr('aria-selected', 'true');
                $('#' + hash).addClass('show active');
                
                // Scroll suave até a aba
                $('html, body').animate({
                    scrollTop: $('#settingsTabs').offset().top - 20
                }, 500);
            }
        }
        
        // Inicializar tabs - usar jQuery para garantir compatibilidade
        $('#settingsTabs a[data-bs-toggle="tab"]').on('click', function (e) {
            e.preventDefault();
            var target = $(this).attr('href');
            
            // Atualizar hash na URL sem recarregar a página
            if (history.pushState) {
                history.pushState(null, null, target);
            } else {
                window.location.hash = target;
            }
            
            // Remover active de todas as tabs e panes
            $('#settingsTabs a[data-bs-toggle="tab"]').removeClass('active').attr('aria-selected', 'false');
            $('.tab-pane').removeClass('show active');
            
            // Adicionar active na tab clicada e seu pane
            $(this).addClass('active').attr('aria-selected', 'true');
            $(target).addClass('show active');
        });
        
        // Mostrar/ocultar campo de horas de cancelamento
        $('#appointments_allow_cancellation').on('change', function() {
            if ($(this).is(':checked')) {
                $('#cancellation_hours_group').show();
            } else {
                $('#cancellation_hours_group').hide();
            }
        });

        // Mostrar/ocultar opção de sincronização automática do Google Calendar
        $('#integrations_google_calendar_enabled').on('change', function() {
            if ($(this).is(':checked') && !$(this).prop('disabled')) {
                $('#google_calendar_auto_sync_group').show();
            } else {
                $('#google_calendar_auto_sync_group').hide();
            }
        });

        // Converter array de checkboxes em string separada por vírgula para dias da semana
        $('form[action*="calendar"]').on('submit', function(e) {
            const checkboxes = $('input[name="calendar_default_weekdays[]"]:checked');
            const values = checkboxes.map(function() {
                return $(this).val();
            }).get();
            
            // Criar campo hidden com os valores
            if ($(this).find('input[name="calendar_default_weekdays"]').length === 0) {
                $(this).append('<input type="hidden" name="calendar_default_weekdays" value="' + values.join(',') + '">');
            } else {
                $(this).find('input[name="calendar_default_weekdays"]').val(values.join(','));
            }
        });

        // Mostrar/ocultar configurações de email baseado no driver
        $('#email_driver').on('change', function() {
            if ($(this).val() === 'tenancy') {
                $('#email_tenancy_config').show();
            } else {
                $('#email_tenancy_config').hide();
            }
        });

        // Mostrar/ocultar configurações de WhatsApp baseado no driver
        $('#whatsapp_driver').on('change', function() {
            if ($(this).val() === 'tenancy') {
                $('#whatsapp_tenancy_config').show();
            } else {
                $('#whatsapp_tenancy_config').hide();
            }
        });

        // Mostrar/ocultar campos de personalização de profissionais
        $('#professional_customization_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('#professional_customization_fields').show();
            } else {
                $('#professional_customization_fields').hide();
            }
        });
    });

    // Função para copiar o link de agendamento público
    function copyPublicBookingLink() {
        const linkInput = document.getElementById('publicBookingLink');
        if (!linkInput) {
            alert('Link não encontrado.');
            return;
        }

        const link = linkInput.value;

        // Tentar usar a API Clipboard moderna
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link).then(function() {
                showCopySuccess();
            }).catch(function(err) {
                console.error('Erro ao copiar:', err);
                fallbackCopy(link);
            });
        } else {
            // Fallback para navegadores mais antigos
            fallbackCopy(link);
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess();
        } catch (err) {
            console.error('Erro ao copiar:', err);
            alert('Erro ao copiar. Por favor, copie manualmente.');
        }
        
        document.body.removeChild(textarea);
    }

    function showCopySuccess() {
        const alert = document.getElementById('copySuccessAlert');
        if (alert) {
            alert.style.display = 'flex';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 3000);
        }
    }

    // Função para preview de imagem
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Função para remover imagem
    function removeImage(type) {
        if (confirm('Tem certeza que deseja remover a imagem personalizada? Será usada a imagem padrão do sistema.')) {
            const inputId = 'remove-' + type;
            const input = document.getElementById(inputId);
            if (input) {
                input.value = '1';
            }
            
            // Resetar preview para imagem padrão
            const previewId = type + '-preview';
            const preview = document.getElementById(previewId);
            if (preview) {
                if (type === 'logo') {
                    preview.src = '{{ asset("connect_plus/assets/images/logos/AllSync-Logo-A.png") }}';
                } else if (type === 'logo_mini') {
                    // Se houver logo normal, usar ele, senão usar padrão
                    const logoPreview = document.getElementById('logo-preview');
                    if (logoPreview && logoPreview.src) {
                        preview.src = logoPreview.src;
                    } else {
                        preview.src = '{{ asset("connect_plus/assets/images/logos/AllSync-Logo-A.png") }}';
                    }
                } else if (type === 'favicon') {
                    preview.src = '{{ asset("connect_plus/assets/images/favicon.png") }}';
                }
            }
            
            // Limpar input de arquivo
            const fileInputId = type + '-input';
            const fileInput = document.getElementById(fileInputId);
            if (fileInput) {
                fileInput.value = '';
            }
            
            // Ocultar botão de remover
            const removeBtn = event.target.closest('.mb-3');
            if (removeBtn) {
                removeBtn.style.display = 'none';
            }
        }
    }

    // Configuração de Localização (Estados/Cidades)
    document.addEventListener('DOMContentLoaded', function() {
        const stateSelect = document.getElementById('estado_id');
        const citySelect = document.getElementById('cidade_id');
        const zipcodeField = document.getElementById('cep');
        const addressField = document.getElementById('endereco');
        const neighborhoodField = document.getElementById('bairro');

        const currentEstadoId = '{{ $localizacao->estado_id ?? "" }}';
        const currentCidadeId = '{{ $localizacao->cidade_id ?? "" }}';
        const brazilId = '{{ $brazilId }}';

        async function loadStates() {
            if (!stateSelect) return;
            stateSelect.innerHTML = '<option value="">Carregando estados...</option>';
            try {
                const response = await fetch('{{ route('api.public.estados', ['pais' => ':paisId']) }}'.replace(':paisId', brazilId));
                const data = await response.json();
                stateSelect.innerHTML = '<option value="">Selecione o estado</option>';
                data.forEach(state => {
                    const option = document.createElement('option');
                    option.value = state.id_estado;
                    option.dataset.abbr = state.uf;
                    option.textContent = state.nome_estado;
                    if (currentEstadoId == state.id_estado) {
                        option.selected = true;
                    }
                    stateSelect.appendChild(option);
                });

                if (stateSelect.value) {
                    loadCities(stateSelect.value);
                }
            } catch (error) {
                console.error('Erro ao carregar estados:', error);
                stateSelect.innerHTML = '<option value="">Erro ao carregar</option>';
            }
        }

        async function loadCities(stateId) {
            if (!citySelect) return;
            if (!stateId) {
                citySelect.innerHTML = '<option value="">Selecione o estado primeiro</option>';
                return;
            }
            citySelect.innerHTML = '<option value="">Carregando cidades...</option>';
            try {
                const response = await fetch('{{ route('api.public.cidades', ['estado' => ':id']) }}'.replace(':id', stateId));
                const data = await response.json();
                citySelect.innerHTML = '<option value="">Selecione a cidade</option>';
                data.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id_cidade;
                    option.dataset.name = city.nome_cidade;
                    option.textContent = city.nome_cidade;
                    if (currentCidadeId == city.id_cidade) {
                        option.selected = true;
                    }
                    citySelect.appendChild(option);
                });
            } catch (error) {
                console.error('Erro ao carregar cidades:', error);
                citySelect.innerHTML = '<option value="">Erro ao carregar</option>';
            }
        }

        if (stateSelect) {
            stateSelect.addEventListener('change', function() {
                loadCities(this.value);
            });
        }

        if (zipcodeField) {
            zipcodeField.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 8) value = value.substring(0, 8);
                if (value.length > 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5);
                }
                e.target.value = value;

                if (value.replace(/\D/g, '').length === 8) {
                    fetch(`https://viacep.com.br/ws/${value.replace(/\D/g, '')}/json/`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.erro) {
                                if (addressField) addressField.value = data.logradouro;
                                if (neighborhoodField) neighborhoodField.value = data.bairro;
                                
                                if (data.uf) {
                                    for (let i = 0; i < stateSelect.options.length; i++) {
                                        if (stateSelect.options[i].dataset.abbr === data.uf) {
                                            stateSelect.selectedIndex = i;
                                            loadCities(stateSelect.value).then(() => {
                                                if (data.localidade) {
                                                    for (let j = 0; j < citySelect.options.length; j++) {
                                                        if (citySelect.options[j].dataset.name.toLowerCase() === data.localidade.toLowerCase()) {
                                                            citySelect.selectedIndex = j;
                                                            break;
                                                        }
                                                    }
                                                }
                                            });
                                            break;
                                        }
                                    }
                                }
                            }
                        });
                }
            });
        }

        loadStates();
    });
</script>
@endpush
@endsection
