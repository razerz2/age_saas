@props(['module' => null])

@php
    // Mapeamento de módulos para seções do manual
    $manualSections = [
        'users' => 'usuarios-e-permissoes',
        'doctors' => 'usuarios-e-permissoes',
        'specialties' => 'especialidades',
        'patients' => 'pacientes',
        'calendars' => 'agenda-profissional',
        'business-hours' => 'agenda-profissional',
        'appointment-types' => 'agenda-profissional',
        'agenda-settings' => 'agenda-profissional',
        'agenda_settings' => 'agenda-profissional',
        'doctor-settings' => 'agenda-profissional',
        'doctor_settings' => 'agenda-profissional',
        'appointments' => 'agendamentos',
        'recurring-appointments' => 'agendamentos-recorrentes',
        'recurring_appointments' => 'agendamentos-recorrentes',
        'medical-appointments' => 'atendimento',
        'medical_appointments' => 'atendimento',
        'online-appointments' => 'consultas-online',
        'online_appointments' => 'consultas-online',
        'campaigns' => 'campanhas',
        'campaign-templates' => 'templates-campanhas',
        'campaign_templates' => 'templates-campanhas',
        'forms' => 'formularios',
        'responses' => 'respostas',
        'integrations' => 'integracoes-e-sincronizacao',
        'calendar-sync' => 'integracoes-e-sincronizacao',
        'calendar_sync' => 'integracoes-e-sincronizacao',
        'reports' => 'relatorios',
        'settings' => 'configuracoes',
    ];

    $section = $manualSections[$module] ?? null;
    $manualUrl = route('landing.manual') . ($section ? '#' . $section : '');
@endphp

@if($module)
    <a href="{{ $manualUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       class="inline-flex items-center gap-2 rounded-md border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:hover:border-blue-500 dark:hover:bg-gray-800"
       title="Abrir manual do sistema nesta seção">
        <i class="mdi mdi-help-circle-outline text-base"></i>
        Ajuda
    </a>
@endif
