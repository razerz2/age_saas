export function init() {
	initCalendarEventsPage();
	initCalendarSyncIndex();
}

function initCalendarEventsPage() {
	const calendarEl = document.getElementById('calendar');
	if (!calendarEl) return;

	const tenantSlug = window.tenantSlug || (window.tenant && window.tenant.slug);
	if (!tenantSlug) return;

	const eventsUrl = calendarEl.dataset.eventsUrl;
	if (!eventsUrl || !window.FullCalendar) return;

	const calendar = new window.FullCalendar.Calendar(calendarEl, {
		initialView: 'dayGridMonth',
		locale: 'pt-br',
		height: 'auto',
		firstDay: 0,
		headerToolbar: {
			left: 'prev,next today',
			center: 'title',
			right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
		},
		buttonText: {
			today: 'Hoje',
			month: 'MÃªs',
			week: 'Semana',
			day: 'Dia',
			list: 'Lista',
		},
		events: eventsUrl,
		eventDidMount: function (info) {
			if (info.event.extendedProps && info.event.extendedProps.status) {
				info.el.setAttribute('data-status', info.event.extendedProps.status);
			}

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

			info.el.addEventListener('mouseenter', function () {
				this.style.transform = 'translateY(-2px) scale(1.02)';
			});

			info.el.addEventListener('mouseleave', function () {
				this.style.transform = 'translateY(0) scale(1)';
			});
		},
		eventClick: function (info) {
			info.jsEvent.preventDefault();

			const appointmentId = info.event.id;
			const extendedProps = info.event.extendedProps || {};

			if (extendedProps.is_recurring || (appointmentId && appointmentId.startsWith('recurring_'))) {
				let recurringId = null;

				if (extendedProps.recurring_appointment_id) {
					recurringId = extendedProps.recurring_appointment_id;
				} else if (appointmentId && appointmentId.startsWith('recurring_')) {
					const recurringIdMatch = appointmentId.match(
						/^recurring_([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})_/
					);
					if (recurringIdMatch && recurringIdMatch[1]) {
						recurringId = recurringIdMatch[1];
					}
				}

				if (recurringId) {
					window.location.href = `/workspace/${tenantSlug}/agendamentos/recorrentes/${recurringId}`;
					return;
				}
			}

			if (appointmentId) {
				window.location.href = `/workspace/${tenantSlug}/appointments/${appointmentId}`;
			}
		},
		dateClick: function (info) {
			console.log('Data clicada:', info.dateStr);
		},
		dayMaxEvents: 3,
		moreLinkClick: 'day',
		titleFormat: {
			year: 'numeric',
			month: 'long',
		},
		eventColor: '#0062ff',
		eventBorderColor: '#0048cc',
		slotMinTime: '06:00:00',
		slotMaxTime: '22:00:00',
		slotDuration: '00:15:00',
		allDaySlot: false,
		navLinks: true,
		editable: false,
		selectable: false,
		selectMirror: true,
		dayMaxEvents: true,
		weekends: true,
	});

	calendar.render();

	setTimeout(function () {
		const fcToolbar = document.querySelector('.fc-toolbar');
		if (fcToolbar) {
			fcToolbar.classList.add('shadow-sm');
		}
	}, 100);
}

function initCalendarSyncIndex() {
	const table = document.getElementById('datatable-list');
	if (!table || !window.jQuery) return;

	const $ = window.jQuery;
	if (typeof $.fn.DataTable === 'function') {
		$('#datatable-list').DataTable({
			pageLength: 25,
			responsive: true,
			autoWidth: false,
			scrollX: false,
			scrollCollapse: false,
			pagingType: 'simple_numbers',
			language: {
				url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json',
			},
		});
	}
}
