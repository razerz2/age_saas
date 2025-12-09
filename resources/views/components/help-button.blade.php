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
       class="btn btn-outline-info btn-sm d-inline-flex align-items-center ms-3"
       title="Abrir manual do sistema nesta seção">
        <i class="mdi mdi-help-circle-outline me-1"></i>
        Ajuda
    </a>
@endif

