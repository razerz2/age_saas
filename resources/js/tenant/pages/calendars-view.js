import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';

export function init() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        return;
    }

    const eventsUrl = calendarEl.dataset.eventsUrl;
    if (!eventsUrl) {
        return;
    }

    const modalEl = document.getElementById('eventModal');
    const modalTitleEl = document.getElementById('eventModalTitle');
    const modalSubtitleEl = document.getElementById('eventModalSubtitle');
    const modalStartEl = document.getElementById('eventModalStart');
    const modalEndEl = document.getElementById('eventModalEnd');
    const modalMetaEl = document.getElementById('eventModalMeta');

    const formatDateTime = (value) => {
        if (!value) {
            return '-';
        }

        const date = value instanceof Date ? value : new Date(value);
        if (Number.isNaN(date.getTime())) {
            return '-';
        }

        return new Intl.DateTimeFormat('pt-BR', {
            dateStyle: 'full',
            timeStyle: 'short',
        }).format(date);
    };

    const openModal = ({ title, subtitle, start, end, meta }) => {
        if (!modalEl) {
            return;
        }

        if (modalTitleEl) modalTitleEl.textContent = title || 'Evento';
        if (modalSubtitleEl) modalSubtitleEl.textContent = subtitle || 'Detalhes do evento';
        if (modalStartEl) modalStartEl.textContent = formatDateTime(start);
        if (modalEndEl) modalEndEl.textContent = formatDateTime(end);
        if (modalMetaEl) modalMetaEl.textContent = meta || '-';

        modalEl.classList.remove('hidden');
        modalEl.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        if (!modalEl) {
            return;
        }

        modalEl.classList.add('hidden');
        modalEl.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    if (modalEl) {
        modalEl.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modalEl.classList.contains('hidden')) {
                closeModal();
            }
        });
    }

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        locale: ptBrLocale,
        initialView: 'dayGridMonth',
        height: 'auto',
        dayMaxEvents: true,
        navLinks: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
        },
        events: (fetchInfo, successCallback, failureCallback) => {
            const url = new URL(eventsUrl, window.location.origin);
            url.searchParams.set('start', fetchInfo.startStr);
            url.searchParams.set('end', fetchInfo.endStr);

            fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                },
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then((events) => {
                    successCallback(Array.isArray(events) ? events : []);
                })
                .catch((error) => {
                    failureCallback(error);
                    if (typeof window.showAlert === 'function') {
                        window.showAlert({
                            type: 'error',
                            title: 'Erro',
                            message: 'Não foi possível carregar os eventos do calendário.',
                        });
                    }
                });
        },
        eventClick: (info) => {
            info.jsEvent.preventDefault();

            const props = info.event.extendedProps || {};
            const metaParts = [];
            if (props.patient) metaParts.push(`Paciente: ${props.patient}`);
            if (props.type) metaParts.push(`Tipo: ${props.type}`);
            if (props.specialty) metaParts.push(`Especialidade: ${props.specialty}`);
            if (props.status) metaParts.push(`Status: ${props.status}`);

            openModal({
                title: info.event.title,
                subtitle: props.notes || 'Detalhes do evento',
                start: info.event.start,
                end: info.event.end,
                meta: metaParts.join(' | ') || '-',
            });
        },
    });

    calendar.render();
}
