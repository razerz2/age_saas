@extends('layouts.connect_plus.app')

@section('title', 'Agenda')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-calendar-check text-primary me-2"></i>
            Agenda
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.calendars.index') }}">Calendários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    Agenda
                </li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-2">
                                <i class="mdi mdi-calendar-text text-primary me-2"></i>
                                Agenda - {{ $calendar->name ?? 'Calendário Principal' }}
                            </h4>
                            <p class="card-description mb-0 text-muted">
                                <i class="mdi mdi-information-outline me-1"></i>
                                Visualização completa de compromissos neste calendário.
                            </p>
                        </div>
                    </div>

                    <div id="calendar" class="calendar-container"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <link href="{{ asset('css/tenant-calendar.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <script>
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
                events: "{{ route('tenant.calendars.events', $calendar->id) }}",

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
                    const recurringAppointmentsBaseUrl = '{{ route("tenant.recurring-appointments.index") }}'.replace('/agendamentos/recorrentes', '/agendamentos/recorrentes');
                    
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
                            window.location.href = `/tenant/agendamentos/recorrentes/${recurringId}`;
                            return;
                        }
                    }
                    
                    // Redireciona para a página de detalhes do agendamento normal
                    if (appointmentId) {
                        window.location.href = `/tenant/appointments/${appointmentId}`;
                    }
                },

                // Callback ao clicar em uma data
                dateClick: function(info) {
                    // Aqui podemos futuramente abrir modal para criar nova consulta
                    console.log('Data clicada:', info.dateStr);
                    // Pode adicionar um modal ou redirecionamento para criar agendamento
                    // window.location.href = `/tenant/appointments/create?date=${info.dateStr}`;
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
