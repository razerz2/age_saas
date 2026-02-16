@extends('layouts.tailadmin.app')

@section('title', 'Agenda')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Agenda
                </h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.calendars.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Calendários</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Agenda</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Agenda - {{ $calendar->name ?? 'Calendário Principal' }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Visualização completa de compromissos neste calendário.
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div id="calendar" class="calendar-container"></div>
        </div>
    </div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <link href="{{ asset('css/tenant-calendar.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <script>
        const tenantSlug = '{{ tenant()->subdomain }}';
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                height: 'auto',
                firstDay: 0, // Domingo = 0

                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },

                // Personalização dos textos dos botões em português
                buttonText: {
                    today: 'Hoje',
                    month: 'Mês',
                    week: 'Semana',
                    day: 'Dia',
                    list: 'Lista'
                },

                // Rota que deve fornecer os eventos em JSON
                events: "{{ workspace_route('tenant.calendars.events', ['id' => $calendar->id]) }}",

                // Personalização de eventos
                eventDidMount: function(info) {
                    // Adiciona atributo de status para aplicar cores CSS
                    if (info.event.extendedProps && info.event.extendedProps.status) {
                        info.el.setAttribute('data-status', info.event.extendedProps.status);
                    }

                    // Tooltip com informações do evento
                    if (info.event.extendedProps) {
                        const props = info.event.extendedProps;
                        let tooltipContent = info.event.title;
                        
                        if (props.type) {
                            tooltipContent += `\nTipo: ${props.type}`;
                        }
                        if (props.specialty) {
                            tooltipContent += `\nEspecialidade: ${props.specialty}`;
                        }
                        
                        info.el.setAttribute('title', tooltipContent);
                    }

                    // Efeito hover melhorado
                    info.el.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-2px) scale(1.02)';
                    });
                    
                    info.el.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0) scale(1)';
                    });
                },

                // Callback ao clicar em um evento - redireciona para detalhes
                eventClick: function(info) {
                    // Previne o comportamento padrão (abrir popup)
                    info.jsEvent.preventDefault();
                    
                    // Obtém o ID do agendamento do evento
                    const appointmentId = info.event.id;
                    const extendedProps = info.event.extendedProps || {};
                    
                    // URL base para agendamentos recorrentes
                    const recurringAppointmentsBaseUrl = '{{ workspace_route("tenant.recurring-appointments.index") }}'.replace('/agendamentos/recorrentes', '/agendamentos/recorrentes');
                    
                    // Verifica se é um agendamento recorrente
                    if (extendedProps.is_recurring || (appointmentId && appointmentId.startsWith('recurring_'))) {
                        // Extrai o ID do agendamento recorrente do formato: recurring_{recurring_id}_{date}_{rule_id}
                        // Formato: recurring_7cf8484c-3bd3-41f1-a5e0-8a100cb89e07_2025-12-02_...
                        let recurringId = null;
                        
                        if (extendedProps.recurring_appointment_id) {
                            // Usa o ID diretamente se disponível
                            recurringId = extendedProps.recurring_appointment_id;
                        } else if (appointmentId && appointmentId.startsWith('recurring_')) {
                            // Extrai o UUID do formato recurring_{uuid}_{date}_{rule_id}
                            const recurringIdMatch = appointmentId.match(/^recurring_([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})_/);
                            if (recurringIdMatch && recurringIdMatch[1]) {
                                recurringId = recurringIdMatch[1];
                            }
                        }
                        
                        if (recurringId) {
                            window.location.href = `/workspace/${tenantSlug}/agendamentos/recorrentes/${recurringId}`;
                            return;
                        }
                    }
                    
                    // Redireciona para a página de detalhes do agendamento normal
                    if (appointmentId) {
                        window.location.href = `/workspace/${tenantSlug}/appointments/${appointmentId}`;
                    }
                },

                // Callback ao clicar em uma data
                dateClick: function(info) {
                    // Aqui podemos futuramente abrir modal para criar nova consulta
                    console.log('Data clicada:', info.dateStr);
                    // Pode adicionar um modal ou redirecionamento para criar agendamento
                    // window.location.href = `/workspace/${tenantSlug}/appointments/create?date=${info.dateStr}`;
                },

                // Melhorias visuais
                dayMaxEvents: 3, // Limite de eventos visíveis antes de mostrar "more"
                moreLinkClick: 'day', // Ao clicar em "more", muda para visualização do dia
                
                // Estilo do título do calendário
                titleFormat: {
                    year: 'numeric',
                    month: 'long'
                },

                // Cores padrão para eventos sem status específico
                eventColor: '#0062ff',
                eventBorderColor: '#0048cc',
                
                // Configurações de visualização
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                slotDuration: '00:15:00',
                allDaySlot: false,

                // Melhorias de usabilidade
                navLinks: true, // Permite clicar no título para mudar de visualização
                editable: false,
                selectable: false,
                selectMirror: true,
                dayMaxEvents: true,
                weekends: true
            });

            calendar.render();

            // Adiciona classes CSS ao calendário após renderização
            setTimeout(function() {
                const fcToolbar = document.querySelector('.fc-toolbar');
                if (fcToolbar) {
                    fcToolbar.classList.add('shadow-sm');
                }
            }, 100);
        });
    </script>
@endpush

@endsection
