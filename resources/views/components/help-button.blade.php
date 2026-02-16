@props(['module' => null])

@php
    // Mapeamento de módulos para seções do manual
    $manualSections = [
        'users' => 'estrutura-local',
        'doctors' => 'medicos',
        'specialties' => 'medicos',
        'patients' => 'pacientes',
        'calendars' => 'calendarios',
        'business-hours' => 'calendarios',
        'appointment-types' => 'calendarios',
        'appointments' => 'agendamentos',
        'forms' => 'formularios',
        'integrations' => 'integracao',
        'settings' => 'configuracao-inicial',
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

