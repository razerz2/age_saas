@extends('layouts.connect_plus.app')

@section('title', 'Agenda')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Agenda </h3>

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
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        Agenda - {{ $calendar->name ?? 'Calendário' }}
                    </h4>
                    <p class="card-description">
                        Visualização completa de compromissos neste calendário.
                    </p>

                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <style>
        /* Ajuste básico para o calendário ocupar bem a área */
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }
        
        /* Estilo para eventos clicáveis */
        .fc-event {
            cursor: pointer;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        
        .fc-event:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
    </style>
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

                // Callback ao clicar em um evento - redireciona para detalhes
                eventClick: function(info) {
                    // Previne o comportamento padrão (abrir popup)
                    info.jsEvent.preventDefault();
                    
                    // Obtém o ID do agendamento do evento
                    const appointmentId = info.event.id;
                    
                    // Redireciona para a página de detalhes do agendamento
                    if (appointmentId) {
                        window.location.href = `/tenant/appointments/${appointmentId}`;
                    }
                },

                dateClick: function(info) {
                    // Aqui podemos futuramente abrir modal para criar nova consulta
                    console.log('Data clicada:', info.dateStr);
                }
            });

            calendar.render();
        });
    </script>
@endpush
