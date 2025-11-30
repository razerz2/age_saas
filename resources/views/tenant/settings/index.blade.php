@extends('layouts.connect_plus.app')

@section('title', 'Configurações')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-settings"></i>
        </span>
        Configurações
    </h3>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                {{-- Navegação de Abas --}}
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">
                            <i class="mdi mdi-cog-outline me-2"></i>Geral
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="appointments-tab" data-bs-toggle="tab" href="#appointments" role="tab" aria-controls="appointments" aria-selected="false">
                            <i class="mdi mdi-calendar-clock me-2"></i>Agendamentos
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="calendar-tab" data-bs-toggle="tab" href="#calendar" role="tab" aria-controls="calendar" aria-selected="false">
                            <i class="mdi mdi-calendar-range me-2"></i>Calendário
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="notifications-tab" data-bs-toggle="tab" href="#notifications" role="tab" aria-controls="notifications" aria-selected="false">
                            <i class="mdi mdi-bell-outline me-2"></i>Notificações
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="integrations-tab" data-bs-toggle="tab" href="#integrations" role="tab" aria-controls="integrations" aria-selected="false">
                            <i class="mdi mdi-link-variant me-2"></i>Integrações
                        </a>
                    </li>
                </ul>

                {{-- Conteúdo das Abas --}}
                <div class="tab-content p-4" id="settingsTabsContent">
                    {{-- Aba Geral --}}
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <form method="POST" action="{{ route('tenant.settings.update.general') }}">
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
                        <form method="POST" action="{{ route('tenant.settings.update.appointments') }}">
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
                        <form method="POST" action="{{ route('tenant.settings.update.calendar') }}">
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

                    {{-- Aba Notificações --}}
                    <div class="tab-pane fade" id="notifications" role="tabpanel">
                        <form method="POST" action="{{ route('tenant.settings.update.notifications') }}">
                            @csrf
                            
                            <h4 class="mb-4">Configurações de Notificações</h4>
                            <p class="text-muted mb-4">
                                Configure quais tipos de notificações você deseja receber no sistema.
                            </p>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card border shadow-sm">
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-3 p-3 rounded" style="border: 1px solid #e9ecef;">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="notifications_appointments_enabled"
                                                       name="notifications_appointments_enabled"
                                                       value="1"
                                                       {{ $settings['notifications.appointments.enabled'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notifications_appointments_enabled">
                                                    <strong class="d-block mb-1">Notificações de Agendamentos</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Receba notificações quando agendamentos forem criados, atualizados, 
                                                        cancelados, reagendados ou quando o status mudar.
                                                    </p>
                                                </label>
                                            </div>

                                            <div class="form-check form-switch mb-3 p-3 rounded" style="border: 1px solid #e9ecef;">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="notifications_form_responses_enabled"
                                                       name="notifications_form_responses_enabled"
                                                       value="1"
                                                       {{ $settings['notifications.form_responses.enabled'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notifications_form_responses_enabled">
                                                    <strong class="d-block mb-1">Notificações de Respostas de Formulários</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Receba notificações quando pacientes responderem aos formulários.
                                                    </p>
                                                </label>
                                            </div>

                                            <div class="form-check form-switch mb-3 p-3 rounded" style="border: 1px solid #e9ecef;">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="notifications_email_enabled"
                                                       name="notifications_email_enabled"
                                                       value="1"
                                                       {{ $settings['notifications.email.enabled'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notifications_email_enabled">
                                                    <strong class="d-block mb-1">Notificações por E-mail</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Receba notificações por e-mail quando eventos importantes ocorrerem.
                                                    </p>
                                                </label>
                                            </div>

                                            <div class="form-check form-switch p-3 rounded" style="border: 1px solid #e9ecef;">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="notifications_whatsapp_enabled"
                                                       name="notifications_whatsapp_enabled"
                                                       value="1"
                                                       {{ $settings['notifications.whatsapp.enabled'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notifications_whatsapp_enabled">
                                                    <strong class="d-block mb-1">Notificações por WhatsApp</strong>
                                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                                        Receba notificações por WhatsApp quando eventos importantes ocorrerem.
                                                    </p>
                                                </label>
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

                    {{-- Aba Integrações --}}
                    <div class="tab-pane fade" id="integrations" role="tabpanel">
                        <form method="POST" action="{{ route('tenant.settings.update.integrations') }}">
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
                                                            <a href="{{ route('tenant.integrations.create') }}" class="btn btn-primary btn-sm me-2">
                                                                <i class="mdi mdi-plus-circle me-2"></i>Cadastrar Integração
                                                            </a>
                                                            <a href="{{ route('tenant.integrations.index') }}" class="btn btn-outline-secondary btn-sm">
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
                                                        <a href="{{ route('tenant.integrations.edit', $googleCalendarIntegration->id) }}" class="btn btn-outline-primary btn-sm">
                                                            <i class="mdi mdi-pencil me-2"></i>Editar Integração
                                                        </a>
                                                        <a href="{{ route('tenant.oauth-accounts.index') }}" class="btn btn-outline-secondary btn-sm">
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
        // Inicializar tabs - usar jQuery para garantir compatibilidade
        $('#settingsTabs a[data-bs-toggle="tab"]').on('click', function (e) {
            e.preventDefault();
            var target = $(this).attr('href');
            
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
    });
</script>
@endpush
@endsection
