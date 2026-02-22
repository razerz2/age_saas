import { applyGridPageSizeSelector } from '../grid/pageSizeSelector';
import { initEntitySearchModal } from '../components/entitySearchModal';

export function init() {
    if (
        !applyGridPageSizeSelector({
            wrapperSelector: '#appointments-grid-wrapper',
            storageKey: 'tenant_appointments_page_size',
            defaultLimit: 10,
        })
    ) {
        return;
    }
	let tenantSlug = window.tenantSlug || (window.tenant && window.tenant.slug) || null;
	if (!tenantSlug) {
		// Hardening: make the failure explicit and try a safe fallback.
		// Expected path: /workspace/{slug}/...
		const parts = String(window.location?.pathname || '').split('/').filter(Boolean);
		if (parts[0] === 'workspace' && parts[1]) {
			tenantSlug = parts[1];
			window.tenantSlug = tenantSlug;
			window.tenant = window.tenant || {};
			window.tenant.slug = tenantSlug;
		} else {
			// eslint-disable-next-line no-console
			console.error('[appointments] tenantSlug ausente — JS não inicializado');
			return;
		}
	}

	// eslint-disable-next-line no-console
	console.log('[appointments] init ok', { tenantSlug });

	bindAppointmentsIndexRowClick();

	document.querySelectorAll('.js-stop-propagation').forEach((el) => {
		el.addEventListener('click', (event) => {
			event.stopPropagation();
		});
	});

	document.querySelectorAll('.js-dismiss').forEach((btn) => {
		btn.addEventListener('click', () => {
			const wrapper = btn.closest('[data-dismissible]');
			if (wrapper) {
				wrapper.style.display = 'none';
			}
		});
	});

	bindCopyBookingLinks();
	initEntitySearchModal();

	const doctorSelect = document.getElementById('doctor_id');
	const doctorNameInput = document.getElementById('doctor_name');
	const appointmentTypeSelect = document.getElementById('appointment_type');
	const specialtySelect = document.getElementById('specialty_id');
	const dateInput = document.getElementById('appointment_date');
	const timeSelect = document.getElementById('appointment_time');
	const startsAtInput = document.getElementById('starts_at');
	const endsAtInput = document.getElementById('ends_at');
	const calendarIdInput = document.getElementById('calendar_id');
	const businessHoursModal = document.getElementById('businessHoursModal');
	const btnShowBusinessHours = document.getElementById('btn-show-business-hours');
	const appointmentTypeWrapper = appointmentTypeSelect?.closest('[data-appointment-type-wrapper]') || null;
	const openDatePickerButtons = document.querySelectorAll('[data-action="open-date-picker"]');

	const tryOpenDatePicker = () => {
		if (!dateInput) return;
		try {
			if (typeof dateInput.showPicker === 'function') {
				dateInput.showPicker();
				return;
			}
		} catch {
			// Fallback para navegadores/dispositivos sem suporte ao showPicker.
		}
		dateInput.focus();
	};

	openDatePickerButtons.forEach((button) => {
		button.addEventListener('click', () => {
			tryOpenDatePicker();
		});
	});

	if (dateInput) {
		dateInput.addEventListener('click', () => {
			tryOpenDatePicker();
		});
	}

	// Tipo de Consulta nunca deve aparecer na UI (padrão do público).
	appointmentTypeWrapper?.classList.add('hidden');

	if (!doctorSelect || !dateInput || !timeSelect || !startsAtInput || !endsAtInput) {
		return;
	}

	const form = document.querySelector('form');
	const isEditMode = form && form.dataset && form.dataset.appointmentEdit === 'true';
	const currentAppointmentTypeId = form ? form.dataset.currentAppointmentTypeId : null;
	const currentSpecialtyId = form ? form.dataset.currentSpecialtyId : null;
	const initialDate = form ? form.dataset.initialDate : null;
	const initialTimeStart = form ? form.dataset.initialTimeStart : null;
	const initialTimeEnd = form ? form.dataset.initialTimeEnd : null;
	const initialStartsAt = form ? form.dataset.initialStartsAt : null;
	const initialEndsAt = form ? form.dataset.initialEndsAt : null;

	function setTimeState(placeholder, disabled = true, clearHidden = true) {
		timeSelect.innerHTML = `<option value="">${placeholder}</option>`;
		timeSelect.disabled = disabled;

		if (clearHidden) {
			startsAtInput.value = '';
			endsAtInput.value = '';
		}
	}

	function setInitialState() {
		const hasDoctor = Boolean(doctorSelect.value);
		const hasDate = Boolean(dateInput.value);

		if (!isEditMode) {
			dateInput.disabled = true;
			setTimeState('Primeiro selecione médico', true);
			return;
		}

		dateInput.disabled = !hasDoctor;

		if (!hasDoctor) {
			setTimeState('Primeiro selecione médico', true);
			return;
		}

		if (!hasDate) {
			setTimeState('Selecione uma data', true);
			return;
		}

		setTimeState('Carregando horários...', true, false);
	}

	function refreshSlots({ initial = false, initialTimeStart = null, initialTimeEnd = null, initialStartsAt = null, initialEndsAt = null } = {}) {
		const doctorId = doctorSelect.value;
		const date = dateInput.value;
		const typeId = appointmentTypeSelect ? appointmentTypeSelect.value : null;

		if (!doctorId) {
			dateInput.disabled = true;
			setTimeState('Primeiro selecione médico', true);
			return;
		}

		dateInput.disabled = false;

		if (!date) {
			setTimeState('Selecione uma data', true);
			return;
		}

		loadAvailableSlots(
			doctorId,
			typeId,
			initial,
			initialTimeStart,
			initialTimeEnd,
			initialStartsAt,
			initialEndsAt
		);
	}

	function getDoctorName() {
		if (doctorNameInput) {
			return doctorNameInput.value || doctorSelect.dataset.selectedName || 'N/A';
		}

		if (doctorSelect.tagName === 'SELECT') {
			const selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
			return selectedOption ? selectedOption.textContent.trim() : 'N/A';
		}

		return doctorSelect.dataset.selectedName || 'N/A';
	}

	// Helpers compartilhados
	function resetDependentFields() {
		if (appointmentTypeSelect) {
			appointmentTypeSelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
			appointmentTypeSelect.disabled = true;
		}

		if (specialtySelect) {
			specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
			specialtySelect.disabled = true;
		}

		dateInput.value = '';
		dateInput.disabled = true;
		setTimeState('Primeiro selecione médico', true);
	}

	function loadAppointmentTypes(doctorId, currentTypeId = null) {
		if (!appointmentTypeSelect) return;

		appointmentTypeSelect.disabled = true;
		appointmentTypeSelect.innerHTML = '<option value="">Carregando tipos...</option>';

		fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/appointment-types`)
			.then((response) => response.json())
			.then((data) => {
				const types = Array.isArray(data) ? data : [];

				if (types.length === 0) {
					appointmentTypeSelect.innerHTML = '<option value="">Nenhum tipo de consulta disponível</option>';
					appointmentTypeSelect.disabled = true;
					return;
				}

				appointmentTypeSelect.innerHTML = '<option value="">Selecione um tipo</option>';

				let currentTypeFound = false;
				types.forEach((type) => {
					const option = document.createElement('option');
					option.value = type.id;
					option.textContent = `${type.name} (${type.duration_min} min)`;
					option.dataset.duration = type.duration_min;
					if (currentTypeId && String(currentTypeId) === String(type.id)) {
						option.selected = true;
						currentTypeFound = true;
					}
					appointmentTypeSelect.appendChild(option);
				});

				// Preferir o valor atual (edição). Caso não exista (create), auto-selecionar o primeiro.
				if (!currentTypeFound) {
					appointmentTypeSelect.value = String(types[0].id);
				}

				appointmentTypeSelect.disabled = false;

				if (dateInput?.value) {
					refreshSlots();
				}
			})
			.catch(() => {
				appointmentTypeSelect.innerHTML = '<option value="">Erro ao carregar tipos</option>';
			});
	}

	function loadSpecialties(doctorId, currentSpecialtyId = null) {
		if (!specialtySelect) return;

		fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/specialties`)
			.then((response) => {
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				return response.json();
			})
			.then((data) => {
				specialtySelect.innerHTML = '<option value="">Selecione uma especialidade</option>';

				if (data && data.length > 0) {
					data.forEach((specialty) => {
						const option = document.createElement('option');
						option.value = specialty.id;
						option.textContent = specialty.name;
						if (currentSpecialtyId && String(currentSpecialtyId) === String(specialty.id)) {
							option.selected = true;
						}
						specialtySelect.appendChild(option);
					});
				} else {
					specialtySelect.innerHTML = '<option value="">Nenhuma especialidade cadastrada para este médico</option>';
				}

				specialtySelect.disabled = false;
			})
			.catch(() => {
				specialtySelect.innerHTML = '<option value="">Erro ao carregar especialidades</option>';
				specialtySelect.disabled = false;
			});
	}

	function loadAvailableSlots(
		doctorId,
		appointmentTypeId,
		initial = false,
		initialTimeStart = null,
		initialTimeEnd = null,
		initialStartsAt = null,
		initialEndsAt = null
	) {
		const date = dateInput.value;
		if (!doctorId || !date) {
			setTimeState(doctorId ? 'Selecione uma data' : 'Primeiro selecione médico', true);
			return;
		}

		timeSelect.disabled = true;
		timeSelect.innerHTML = '<option value="">Carregando horários...</option>';

		const baseUrl = `/workspace/${tenantSlug}/api/doctors/${doctorId}/available-slots?date=${date}`;
		const finalUrl = appointmentTypeId ? `${baseUrl}&appointment_type_id=${appointmentTypeId}` : baseUrl;

		fetch(finalUrl)
			.then((response) => {
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				return response.json();
			})
			.then((data) => {
				const slots = Array.isArray(data) ? data : Array.isArray(data?.data) ? data.data : [];
				timeSelect.innerHTML = '<option value="">Selecione um horário</option>';

				if (slots.length === 0) {
					timeSelect.innerHTML = '<option value="">Nenhum horário disponível para esta data</option>';
					timeSelect.disabled = true;
					return;
				}

				let selectedFound = false;
				slots.forEach((slot) => {
					const slotStart = slot.datetime_start || slot.start || slot.start_time;
					const slotEnd = slot.datetime_end || slot.end || slot.end_time;
					const slotLabel =
						slot.label ||
						(slot.start_time && slot.end_time
							? `${slot.start_time} - ${slot.end_time}`
							: slot.start && slot.end
								? `${slot.start} - ${slot.end}`
								: slot.start);

					const option = document.createElement('option');
					option.value = slot.label || slot.start || slotStart || '';
					option.textContent = slotLabel || '';
					option.dataset.start = slotStart || '';
					option.dataset.end = slotEnd || '';

					if (
						initial &&
						initialTimeStart &&
						initialTimeEnd &&
						((slot.start_time && slot.end_time && slot.start_time === initialTimeStart && slot.end_time === initialTimeEnd) ||
							(slot.start && slot.end && slot.start.includes(initialTimeStart)))
					) {
						option.selected = true;
						startsAtInput.value = slotStart || '';
						endsAtInput.value = slotEnd || '';
						selectedFound = true;
					}

					timeSelect.appendChild(option);
				});

				timeSelect.disabled = false;

				if (initial && !selectedFound && initialTimeStart && initialTimeEnd && initialStartsAt && initialEndsAt) {
					const option = document.createElement('option');
					option.value = `${initialTimeStart}-${initialTimeEnd}`;
					option.textContent = `${initialTimeStart} - ${initialTimeEnd} (horÃ¡rio atual)`;
					option.dataset.start = initialStartsAt;
					option.dataset.end = initialEndsAt;
					option.selected = true;
					option.style.color = '#dc3545';
					timeSelect.insertBefore(option, timeSelect.firstChild.nextSibling);
					startsAtInput.value = initialStartsAt;
					endsAtInput.value = initialEndsAt;
				}

				if (!selectedFound && !initial) {
					startsAtInput.value = '';
					endsAtInput.value = '';
				}
			})
			.catch(() => {
				timeSelect.innerHTML = '<option value="">Nenhum horário disponível</option>';
				timeSelect.disabled = true;
			});
	}

	// Eventos comuns (create/edit)
	doctorSelect.addEventListener('change', () => {
		const doctorId = doctorSelect.value;
		const shouldPreserveDate = doctorSelect.dataset.preserveDateOnNextChange === '1';
		delete doctorSelect.dataset.preserveDateOnNextChange;

		if (!doctorId) {
			resetDependentFields();
			if (btnShowBusinessHours) btnShowBusinessHours.disabled = true;
			if (calendarIdInput) calendarIdInput.value = '';
			return;
		}

		dateInput.disabled = true;
		if (!shouldPreserveDate) {
			dateInput.value = '';
			setTimeState('Selecione uma data', true);
		}
		dateInput.disabled = false;

		if (btnShowBusinessHours) btnShowBusinessHours.disabled = false;

		if (calendarIdInput) {
			fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/calendars`)
				.then((response) => response.json())
				.then((data) => {
					calendarIdInput.value = data && data.length > 0 ? data[0].id : '';
				})
				.catch(() => {
					calendarIdInput.value = '';
				});
		}

		loadAppointmentTypes(doctorId, shouldPreserveDate ? currentAppointmentTypeId : null);
		loadSpecialties(doctorId, shouldPreserveDate ? currentSpecialtyId : null);
		if (shouldPreserveDate) {
			refreshSlots();
		}
	});

	if (doctorSelect.tagName !== 'SELECT') {
		doctorSelect.addEventListener('doctor:selected', () => {
			doctorSelect.dispatchEvent(new Event('change'));
		});
	}

	dateInput.addEventListener('change', () => {
		refreshSlots();
	});

	dateInput.addEventListener('input', () => {
		refreshSlots();
	});

	if (appointmentTypeSelect) {
		appointmentTypeSelect.addEventListener('change', () => {
			refreshSlots();
		});
	}

	timeSelect.addEventListener('change', () => {
		const selectedOption = timeSelect.options[timeSelect.selectedIndex];
		if (selectedOption && selectedOption.value) {
			startsAtInput.value = selectedOption.dataset.start || '';
			endsAtInput.value = selectedOption.dataset.end || '';
		} else {
			startsAtInput.value = '';
			endsAtInput.value = '';
		}
	});

	if (form) {
		form.addEventListener('submit', (e) => {
			if (!startsAtInput.value || !endsAtInput.value) {
				e.preventDefault();
				if (typeof window.showAlert === 'function') {
					window.showAlert({
						type: 'warning',
						title: 'Atenção',
						message: 'Por favor, selecione um horário disponível.',
					});
				}
			}
		});
	}

	setInitialState();

	// Fechar modal de dias trabalhados (create: Tailwind overlay, edit: Bootstrap modal)
	document.querySelectorAll('.js-close-business-hours-modal').forEach((btn) => {
		btn.addEventListener('click', () => {
			const modal = document.getElementById('businessHoursModal');
			if (!modal) return;

			// Tenta via Bootstrap (edit) se disponível
			if (window.bootstrap && typeof window.bootstrap.Modal !== 'undefined') {
				const bsModal = window.bootstrap.Modal.getInstance(modal) || new window.bootstrap.Modal(modal);
				bsModal.hide();
			} else {
				// Fallback Tailwind (create)
				modal.classList.add('hidden');
			}
		});
	});

	// Se houver valor inicial de médico na create, dispara change para carregar dependentes
	if (doctorSelect && doctorSelect.dataset.initialValue) {
		if (!doctorSelect.value) {
			doctorSelect.value = doctorSelect.dataset.initialValue;
		}
		if (dateInput.value) {
			doctorSelect.dataset.preserveDateOnNextChange = '1';
		}
		doctorSelect.dispatchEvent(new Event('change'));
	}

	// Inicializa dados de edição
	if (isEditMode && doctorSelect && doctorSelect.value) {
		doctorSelect.dataset.preserveDateOnNextChange = '1';
		doctorSelect.dispatchEvent(new Event('change'));
		if (initialDate) {
			dateInput.value = initialDate;
			refreshSlots({
				initial: true,
				initialTimeStart,
				initialTimeEnd,
				initialStartsAt,
				initialEndsAt,
			});
		}
	}

	// Modal de dias trabalhados (Tailwind ou Bootstrap, dependendo da view)
	if (businessHoursModal) {
		window.loadBusinessHours = function loadBusinessHours(doctorId) {
			const loadingEl = document.getElementById('business-hours-loading');
			const contentEl = document.getElementById('business-hours-content');
			const errorEl = document.getElementById('business-hours-error');
			const emptyEl = document.getElementById('business-hours-empty');
			const listEl = document.getElementById('business-hours-list');
			const doctorNameEl = document.getElementById('business-hours-doctor-name');

			if (loadingEl) {
				loadingEl.classList.remove('d-none');
				loadingEl.style.display = 'block';
			}
			if (contentEl) {
				contentEl.classList.add('d-none');
				contentEl.style.display = 'none';
			}
			if (errorEl) {
				errorEl.classList.add('d-none');
				errorEl.style.display = 'none';
			}
			if (emptyEl) {
				emptyEl.classList.add('d-none');
				emptyEl.style.display = 'none';
			}

			if (!doctorId) {
				if (loadingEl) loadingEl.style.display = 'none';
				if (errorEl) {
					errorEl.classList.remove('d-none');
					errorEl.style.display = 'block';
					const msgEl = document.getElementById('business-hours-error-message');
					if (msgEl) msgEl.textContent = 'Por favor, selecione um médico primeiro.';
				}
				return;
			}

			fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/business-hours`)
				.then((response) => {
					if (!response.ok) {
						throw new Error(`HTTP error! status: ${response.status}`);
					}
					return response.json();
				})
				.then((data) => {
					if (loadingEl) loadingEl.style.display = 'none';

					let businessHoursArray = null;
					let doctorInfo = null;

					if (Array.isArray(data)) {
						businessHoursArray = data;
						doctorInfo = { name: getDoctorName() };
					} else if (data && typeof data === 'object') {
						if (data.error) {
							if (errorEl) {
								errorEl.classList.remove('d-none');
								errorEl.style.display = 'block';
								const msgEl = document.getElementById('business-hours-error-message');
								if (msgEl) msgEl.textContent = data.error;
							}
							return;
						}
						businessHoursArray = data.business_hours;
						doctorInfo = data.doctor;
					} else {
						if (errorEl) {
							errorEl.classList.remove('d-none');
							errorEl.style.display = 'block';
							const msgEl = document.getElementById('business-hours-error-message');
							if (msgEl) msgEl.textContent = 'Formato de dados inválido recebido da API.';
						}
						return;
					}

					if (!businessHoursArray || businessHoursArray.length === 0) {
						if (emptyEl) {
							emptyEl.classList.remove('d-none');
							emptyEl.style.display = 'block';
						}
						return;
					}

					if (doctorNameEl && doctorInfo) {
						doctorNameEl.textContent = doctorInfo.name || 'N/A';
					}

					let html = '<div class="table-responsive"><table class="table table-bordered table-hover">';
					html += '<thead class="table-light"><tr><th>Dia da Semana</th><th>Horários</th></tr></thead>';
					html += '<tbody>';

					businessHoursArray.forEach((day) => {
						html += '<tr>';
						html += `<td><strong>${day.weekday_name || 'N/A'}</strong></td>`;
						html += '<td>';

						if (day.hours && Array.isArray(day.hours) && day.hours.length > 0) {
							day.hours.forEach((hour, index) => {
								if (index > 0) html += '<br>';
								html += `<span class="badge bg-primary me-1">${hour.start_time || 'N/A'}</span> até <span class="badge bg-primary">${hour.end_time || 'N/A'}</span>`;
								if (hour.break_start_time && hour.break_end_time) {
									html += ` <small class="text-muted">(Intervalo: ${hour.break_start_time} - ${hour.break_end_time})</small>`;
								}
							});
						} else {
							html += '<span class="text-muted">Não trabalha neste dia</span>';
						}

						html += '</td>';
						html += '</tr>';
					});

					html += '</tbody></table></div>';

					if (listEl) {
						listEl.innerHTML = html;
					}

					if (contentEl) {
						contentEl.classList.remove('d-none');
						contentEl.style.display = 'block';
					}
				})
				.catch((error) => {
					if (errorEl) {
						errorEl.classList.remove('d-none');
						errorEl.style.display = 'block';
						const msgEl = document.getElementById('business-hours-error-message');
						if (msgEl) msgEl.textContent = `Erro ao carregar informações: ${error.message}`;
					}
				});
		};
	}

	if (businessHoursModal && window.bootstrap && typeof window.bootstrap.Modal !== 'undefined') {
		businessHoursModal.addEventListener('show.bs.modal', () => {
			if (doctorSelect && typeof window.loadBusinessHours === 'function') {
				window.loadBusinessHours(doctorSelect.value);
			}
		});
	}

	if (btnShowBusinessHours && businessHoursModal) {
		btnShowBusinessHours.addEventListener('click', () => {
			if (doctorSelect && !doctorSelect.value) {
				if (typeof window.showAlert === 'function') {
					window.showAlert({
						type: 'warning',
						title: 'Atenção',
						message: 'Selecione um médico primeiro para ver os dias trabalhados.',
					});
				}
				return;
			}

			businessHoursModal.classList.remove('hidden');
			if (typeof window.loadBusinessHours === 'function') {
				window.loadBusinessHours(doctorSelect.value);
			}
		});
	}

	const $ = window.jQuery || window.$;
	if ($) {
		initRecurringCreate($, tenantSlug);
		initRecurringEdit($, tenantSlug);
	}
}

function bindAppointmentsIndexRowClick() {
	const grid = document.getElementById('appointments-grid');
	if (!grid) {
		return;
	}

	const wrapper = document.getElementById('appointments-grid-wrapper');
	const linkSelector = wrapper?.dataset?.rowClickLinkSelector || 'a[title="Ver"]';
	const excludedSelector = 'a, button, input, select, textarea, label, [data-no-row-click], [role="button"]';

	const resolveRowHref = (row) => {
		if (!row) {
			return null;
		}

		if (row.dataset.href) {
			return row.dataset.href;
		}

		const showLink = row.querySelector(linkSelector);
		if (showLink?.href) {
			row.dataset.href = showLink.href;
			return showLink.href;
		}

		return null;
	};

	const markRowClickable = (row) => {
		if (!row) {
			return;
		}

		if (resolveRowHref(row)) {
			row.classList.add('cursor-pointer', 'row-clickable');
		}
	};

	const updateRows = () => {
		grid.querySelectorAll('tbody tr, .gridjs-tr').forEach((row) => {
			markRowClickable(row);
		});
	};

	updateRows();

	const observer = new MutationObserver(() => {
		updateRows();
	});
	observer.observe(grid, { childList: true, subtree: true });

	grid.addEventListener('click', (event) => {
		if (event.defaultPrevented) {
			return;
		}

		if (event.target.closest(excludedSelector)) {
			return;
		}

		const row = event.target.closest('tr') || event.target.closest('.gridjs-tr');
		if (!row || !grid.contains(row)) {
			return;
		}

		const href = resolveRowHref(row);
		if (!href) {
			return;
		}

		window.location.href = href;
	});
}

function bindCopyBookingLinks() {
	const feedbackTimers = new WeakMap();
	document.querySelectorAll('[data-copy-booking-link]').forEach((button) => {
		button.addEventListener('click', () => {
			const link = button.dataset.bookingLink;
			if (!link) return;

			const onSuccess = () => {
				const feedback =
					button.closest('.flex-1')?.querySelector('[data-copy-feedback]') ||
					button.closest('.rounded-lg')?.querySelector('[data-copy-feedback]') ||
					document.querySelector('[data-copy-feedback]');
				if (!feedback) return;

				feedback.classList.remove('hidden');
				const activeTimer = feedbackTimers.get(feedback);
				if (activeTimer) {
					clearTimeout(activeTimer);
				}
				const timerId = setTimeout(() => feedback.classList.add('hidden'), 3000);
				feedbackTimers.set(feedback, timerId);
			};

			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(link).then(onSuccess).catch(() => fallbackCopy(link, onSuccess));
			} else {
				fallbackCopy(link, onSuccess);
			}
		});
	});
}

function fallbackCopy(text, onSuccess) {
	const textarea = document.createElement('textarea');
	textarea.value = text;
	textarea.style.position = 'fixed';
	textarea.style.opacity = '0';
	document.body.appendChild(textarea);
	textarea.select();

	try {
		document.execCommand('copy');
		if (typeof onSuccess === 'function') {
			onSuccess();
		}
	} catch {
		if (typeof window.showAlert === 'function') {
			window.showAlert({
				type: 'error',
				title: 'Erro',
				message: 'NÃ£o foi possÃ­vel copiar o link.',
			});
		}
	}

	document.body.removeChild(textarea);
}

function initRecurringCreate($, tenantSlug) {
	const form = document.getElementById('recurring-appointment-form');
	if (!form) return;

	let ruleIndex = 1;
	let businessHours = [];
	let doctorId = null;
	let appointmentTypeId = null;
	let startDate = null;

	function initState() {
		doctorId = $('#doctor_id').val();
		appointmentTypeId = $('#appointment_type_id').val();
		startDate = $('#start_date').val();

		resetRules();
		updateFirstRuleRequired();

		if (doctorId) {
			loadBusinessHours();
			loadSpecialties();

			const specialtyId = $('#specialty_id').val();
			if (specialtyId) {
				$('#specialty_id').trigger('change');
			}

			if (appointmentTypeId) {
				updateAllRules();
			}
		}
	}

	$('#end_type')
		.off('change.recurringCreate')
		.on('change.recurringCreate', function () {
			const endType = $(this).val();
			const showEndDate = endType === 'date';
			$('#end_date_field').toggle(showEndDate);
		})
		.trigger('change');

	$('#doctor_id')
		.off('change.recurringCreate')
		.on('change.recurringCreate', function () {
			doctorId = $(this).val();

			if (!doctorId) {
				businessHours = [];
				$('#specialty_id')
					.html('<option value="">Primeiro selecione um médico</option>')
					.prop('disabled', true);
				$('#appointment_type_id')
					.html('<option value="">Primeiro selecione uma especialidade</option>')
					.prop('disabled', true);
				resetRules();
				return;
			}

			loadBusinessHours();
			loadSpecialties();
		});

	$('#specialty_id')
		.off('change.recurringCreate')
		.on('change.recurringCreate', function () {
			const specialtyId = $(this).val();

			if (!doctorId || !specialtyId) {
				$('#appointment_type_id')
					.html('<option value="">Primeiro selecione uma especialidade</option>')
					.prop('disabled', true);
				resetRules();
				return;
			}

			const $appointmentTypeSelect = $('#appointment_type_id');
			$appointmentTypeSelect.html('<option value="">Carregando...</option>').prop('disabled', true);

			$.ajax({
				url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/appointment-types`,
				method: 'GET',
				success: function (data) {
					$appointmentTypeSelect.empty();

					if (data.length === 0) {
						$appointmentTypeSelect.append('<option value="">Nenhum tipo de consulta disponÃ­vel</option>');
						$appointmentTypeSelect.prop('disabled', true);
						return;
					}

					$appointmentTypeSelect.append('<option value="">Selecione um tipo</option>');
					data.forEach(function (type) {
						$appointmentTypeSelect.append(
							`<option value="${type.id}">${type.name} (${type.duration_min} min)</option>`
						);
					});

					$appointmentTypeSelect.prop('disabled', false);
				},
				error: function (xhr) {
					console.error('Erro ao buscar tipos de consulta:', xhr);
					$appointmentTypeSelect.html('<option value="">Erro ao carregar tipos de consulta</option>');
				},
			});
		});

	$('#appointment_type_id')
		.off('change.recurringCreate')
		.on('change.recurringCreate', function () {
			appointmentTypeId = $(this).val();
			updateAllRules();
		});

	$('#start_date')
		.off('change.recurringCreate')
		.on('change.recurringCreate', function () {
			startDate = $(this).val();
			updateAllRules();
		});

	function loadBusinessHours() {
		if (!doctorId) return;

		$.ajax({
			url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/business-hours`,
			method: 'GET',
			success: function (data) {
				businessHours = data;
				updateAllRules();
			},
			error: function (xhr) {
				console.error('Erro ao buscar horários do médico:', xhr);
				showAlert({
					type: 'error',
					title: 'Erro',
					message: 'Erro ao carregar horários do médico. Por favor, tente novamente.',
				});
			},
		});
	}

	function loadSpecialties() {
		if (!doctorId) return;

		const $specialtySelect = $('#specialty_id');
		$specialtySelect.html('<option value="">Carregando...</option>').prop('disabled', true);

		$.ajax({
			url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/specialties`,
			method: 'GET',
			success: function (data) {
				$specialtySelect.empty();

				if (data.length === 0) {
					$specialtySelect.append('<option value="">Nenhuma especialidade cadastrada</option>');
				} else {
					$specialtySelect.append('<option value="">Selecione uma especialidade</option>');
					data.forEach(function (specialty) {
						$specialtySelect.append(`<option value="${specialty.id}">${specialty.name}</option>`);
					});
				}

				$specialtySelect.prop('disabled', false);
				$('#appointment_type_id')
					.html('<option value="">Primeiro selecione uma especialidade</option>')
					.prop('disabled', true);
			},
			error: function (xhr) {
				console.error('Erro ao buscar especialidades:', xhr);
				$specialtySelect.html('<option value="">Erro ao carregar especialidades</option>').prop('disabled', false);
			},
		});
	}

	function resetRules() {
		$('.rule-item').each(function () {
			const $ruleItem = $(this);
			const $weekdaySelect = $ruleItem.find('.rule-weekday');
			const $timeSlotSelect = $ruleItem.find('.rule-time-slot');

			$weekdaySelect
				.empty()
				.append('<option value="">Selecione o tipo de consulta primeiro</option>')
				.prop('disabled', true);
			$timeSlotSelect
				.empty()
				.append('<option value="">Selecione o dia da semana primeiro</option>')
				.prop('disabled', true);
			$ruleItem.find('.rule-start-time').val('');
			$ruleItem.find('.rule-end-time').val('');
		});
	}

	function updateAllRules() {
		$('.rule-item').each(function () {
			const $item = $(this);
			if (!$item.hasClass('rule-confirmed')) {
				updateRule($item);
			}
		});
	}

	function updateOtherRules(excludeRuleItem) {
		$('.rule-item').each(function () {
			const $item = $(this);
			if (!$item.hasClass('rule-confirmed') && (!excludeRuleItem || !$item.is(excludeRuleItem))) {
				updateRule($item);
			}
		});
	}

	function getSelectedWeekdays(excludeRuleItem) {
		const selectedWeekdays = [];
		$('.rule-item').each(function () {
			const $item = $(this);
			if (excludeRuleItem && $item.is(excludeRuleItem)) {
				return;
			}
			const isConfirmed = $item.hasClass('rule-confirmed');
			const isFirstRule = $item.is($('.rule-item').first());

			if (isConfirmed || isFirstRule) {
				const weekday = $item.find('.rule-weekday').val();
				if (weekday && weekday !== '') {
					selectedWeekdays.push(weekday);
				}
			}
		});
		return selectedWeekdays;
	}

	function updateRule($ruleItem) {
		if ($ruleItem.hasClass('rule-confirmed')) {
			return;
		}

		doctorId = $('#doctor_id').val();
		appointmentTypeId = $('#appointment_type_id').val();
		startDate = $('#start_date').val();

		const $weekdaySelect = $ruleItem.find('.rule-weekday');
		const $timeSlotSelect = $ruleItem.find('.rule-time-slot');

		const currentSelectedWeekday = $weekdaySelect.val();
		$weekdaySelect.empty();
		$timeSlotSelect.empty();
		$timeSlotSelect.prop('disabled', true);

		if (!appointmentTypeId) {
			$weekdaySelect.append('<option value="">Selecione o tipo de consulta primeiro</option>');
			$weekdaySelect.prop('disabled', true);
			return;
		}

		if (!doctorId || businessHours.length === 0) {
			$weekdaySelect.append('<option value="">Primeiro selecione um médico</option>');
			$weekdaySelect.prop('disabled', true);
			return;
		}

		const selectedWeekdays = getSelectedWeekdays($ruleItem);

		let availableWeekdays = [];
		businessHours.forEach(function (bh) {
			if (!selectedWeekdays.includes(bh.weekday_string) || bh.weekday_string === currentSelectedWeekday) {
				availableWeekdays.push(bh);
				$weekdaySelect.append(`<option value="${bh.weekday_string}">${bh.weekday_name}</option>`);
			}
		});

		$weekdaySelect.prop('disabled', false);

		if (currentSelectedWeekday && availableWeekdays.find((bh) => bh.weekday_string === currentSelectedWeekday)) {
			$weekdaySelect.val(currentSelectedWeekday);
		}

		setupRuleHandlers($ruleItem);

		if ($ruleItem.is($('.rule-item').first())) {
			updateFirstRuleRequired();
		}

		if (!currentSelectedWeekday && availableWeekdays.length > 0) {
			const firstWeekday = availableWeekdays[0].weekday_string;
			$weekdaySelect.val(firstWeekday);
			loadTimeSlotsForDay($ruleItem, firstWeekday);
		} else if (currentSelectedWeekday) {
			loadTimeSlotsForDay($ruleItem, currentSelectedWeekday);
		}
	}

	function loadTimeSlotsForDay($ruleItem, weekdayString) {
		const $timeSlotSelect = $ruleItem.find('.rule-time-slot');
		const $startTimeInput = $ruleItem.find('.rule-start-time');
		const $endTimeInput = $ruleItem.find('.rule-end-time');

		$timeSlotSelect.empty();
		$timeSlotSelect.append('<option value="">Carregando...</option>');
		$timeSlotSelect.prop('disabled', true);
		$startTimeInput.val('');
		$endTimeInput.val('');

		doctorId = $('#doctor_id').val();
		appointmentTypeId = $('#appointment_type_id').val();
		startDate = $('#start_date').val();

		if (!doctorId || !appointmentTypeId || !startDate) {
			$timeSlotSelect.empty();
			const missingFields = [];
			if (!doctorId) missingFields.push('médico');
			if (!appointmentTypeId) missingFields.push('tipo de consulta');
			if (!startDate) missingFields.push('data inicial');
			$timeSlotSelect.append(`<option value="">Selecione: ${missingFields.join(', ')}</option>`);
			return;
		}

		$.ajax({
			url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/available-slots-recurring`,
			method: 'GET',
			data: {
				weekday: weekdayString,
				appointment_type_id: appointmentTypeId,
				start_date: startDate,
			},
			success: function (slots) {
				$timeSlotSelect.empty();

				if (slots.length === 0) {
					$timeSlotSelect.append('<option value="">Nenhum horÃ¡rio disponÃ­vel</option>');
					$timeSlotSelect.prop('disabled', true);
					return;
				}

				$timeSlotSelect.append('<option value="">Selecione um horÃ¡rio</option>');
				slots.forEach(function (slot) {
					$timeSlotSelect.append(
						`<option value="${slot.start}|${slot.end}" data-start="${slot.start}" data-end="${slot.end}">${slot.display}</option>`
					);
				});

				$timeSlotSelect.prop('disabled', false);
			},
			error: function (xhr) {
				console.error('Erro ao buscar horários disponíveis:', xhr);
				$timeSlotSelect.empty();
				$timeSlotSelect.append('<option value="">Erro ao carregar horários</option>');
				$timeSlotSelect.prop('disabled', true);
			},
		});
	}

	function setupRuleHandlers($ruleItem) {
		const $weekdaySelect = $ruleItem.find('.rule-weekday');
		const $timeSlotSelect = $ruleItem.find('.rule-time-slot');
		const $startTimeInput = $ruleItem.find('.rule-start-time');
		const $endTimeInput = $ruleItem.find('.rule-end-time');

		$weekdaySelect.off('change').on('change', function () {
			doctorId = $('#doctor_id').val();
			appointmentTypeId = $('#appointment_type_id').val();
			startDate = $('#start_date').val();

			const weekdayString = $(this).val();
			if ($ruleItem.hasClass('rule-confirmed')) {
				return;
			}

			if (weekdayString) {
				loadTimeSlotsForDay($ruleItem, weekdayString);
				setTimeout(function () {
					updateOtherRules($ruleItem);
				}, 100);
			} else {
				$timeSlotSelect.empty();
				$timeSlotSelect.append('<option value="">Selecione o dia</option>');
				$timeSlotSelect.prop('disabled', true);
				setTimeout(function () {
					updateOtherRules($ruleItem);
				}, 100);
			}
		});

		$timeSlotSelect.off('change').on('change', function () {
			const selectedOption = $(this).find('option:selected');
			const startTime = selectedOption.data('start');
			const endTime = selectedOption.data('end');

			if (startTime && endTime) {
				$startTimeInput.val(startTime);
				$endTimeInput.val(endTime);
			} else {
				$startTimeInput.val('');
				$endTimeInput.val('');
			}
		});
	}

	$('#add-rule')
		.off('click.recurringCreate')
		.on('click.recurringCreate', function () {
			const $firstRule = $('.rule-item').first();

			const currentWeekday = $firstRule.find('.rule-weekday').val();
			const currentTimeSlot = $firstRule.find('.rule-time-slot').val();
			const currentStartTime = $firstRule.find('.rule-start-time').val();
			const currentEndTime = $firstRule.find('.rule-end-time').val();
			const currentTimeSlotText = $firstRule.find('.rule-time-slot option:selected').text();

			if (!currentWeekday || !currentTimeSlot || !currentStartTime || !currentEndTime) {
				showAlert({
					type: 'warning',
					title: 'Atenção',
					message:
						'Por favor, selecione um dia da semana e um horÃ¡rio na primeira regra antes de adicionar outra.',
				});
				return;
			}

			const selectedWeekdays = [];
			$('.rule-item.rule-confirmed').each(function () {
				const weekday = $(this).find('.rule-weekday').val();
				if (weekday && weekday !== '') {
					selectedWeekdays.push(weekday);
				}
			});

			if (selectedWeekdays.includes(currentWeekday)) {
				showAlert({
					type: 'warning',
					title: 'Atenção',
					message: 'Este dia da semana jÃ¡ foi adicionado em outra regra. NÃ£o Ã© possÃ­vel duplicar dias.',
				});
				return;
			}

			const currentWeekdayName = $firstRule.find('.rule-weekday option:selected').text();

			const ruleHtml = `
            <div class="rule-item mb-3 p-3 border rounded rule-confirmed">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="fw-semibold mb-2">Dia da Semana <span class="text-danger">*</span></label>
                            <select class="form-control rule-weekday" disabled>
                                <option value="${currentWeekday}" selected>${currentWeekdayName}</option>
                            </select>
                            <input type="hidden" name="rules[${ruleIndex}][weekday]" value="${currentWeekday}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="fw-semibold mb-2">HorÃ¡rio DisponÃ­vel <span class="text-danger">*</span></label>
                            <select class="form-control rule-time-slot" disabled>
                                <option value="${currentTimeSlot}" selected>${currentTimeSlotText}</option>
                            </select>
                            <input type="hidden" name="rules[${ruleIndex}][start_time]" value="${currentStartTime}">
                            <input type="hidden" name="rules[${ruleIndex}][end_time]" value="${currentEndTime}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end rule-button-col">
                        <label class="fw-semibold mb-2 rule-label-spacer" style="visibility: hidden;">&nbsp;</label>
                        <button type="button" class="rule-action-btn remove-rule inline-flex items-center justify-center gap-1 rounded-md bg-error text-white text-xs font-semibold transition hover:bg-error/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-error/50 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                            <i class="mdi mdi-delete"></i> Remover
                        </button>
                    </div>
                    <input type="hidden" name="rules[${ruleIndex}][frequency]" value="weekly">
                    <input type="hidden" name="rules[${ruleIndex}][interval]" value="1">
                </div>
            </div>
        `;

			const $newRule = $(ruleHtml);
			$('#rules-container').append($newRule);

			$firstRule.find('.rule-weekday').val('');
			$firstRule.find('.rule-time-slot').val('').prop('disabled', true);
			$firstRule.find('.rule-start-time').val('');
			$firstRule.find('.rule-end-time').val('');

			updateRule($firstRule);

			ruleIndex++;
			updateRemoveButtons();
			updateFirstRuleRequired();
		});

	$('#recurring-appointment-form')
		.off('submit.recurringCreate')
		.on('submit.recurringCreate', function (e) {
			let validConfirmedRules = 0;
			$('.rule-item.rule-confirmed').each(function () {
				const weekday = $(this).find('input[name*="[weekday]"]').val();
				const startTime = $(this).find('input[name*="[start_time]"]').val();
				const endTime = $(this).find('input[name*="[end_time]"]').val();

				if (weekday && startTime && endTime) {
					validConfirmedRules++;
				}
			});

			const $firstRule = $('.rule-item').first();
			if (!$firstRule.hasClass('rule-confirmed')) {
				const firstWeekday = $firstRule.find('.rule-weekday').val();
				const firstStartTime = $firstRule.find('.rule-start-time').val();
				const firstEndTime = $firstRule.find('.rule-end-time').val();

				if (firstWeekday && firstStartTime && firstEndTime) {
					validConfirmedRules++;
				}
			}

			if (validConfirmedRules === 0) {
				e.preventDefault();
				showAlert({
					type: 'warning',
					title: 'Atenção',
					message:
						'Por favor, adicione pelo menos uma regra de recorrÃªncia completa (dia da semana e horÃ¡rio).',
				});
				return false;
			}

			const $firstRuleInputs = $firstRule.find('input[name*="rules[0]"], select[name*="rules[0]"]');
			if (validConfirmedRules > 0 && $firstRuleInputs.length > 0) {
				const firstWeekday = $firstRule.find('.rule-weekday').val();
				const firstStartTime = $firstRule.find('.rule-start-time').val();
				const firstEndTime = $firstRule.find('.rule-end-time').val();

				if (!firstWeekday || !firstStartTime || !firstEndTime) {
					$firstRuleInputs.each(function () {
						if ($(this).attr('name')) {
							$(this).removeAttr('name');
						}
					});
				}
			}

			return true;
		});

	function updateFirstRuleRequired() {
		const $firstRule = $('.rule-item').first();
		const $firstWeekday = $firstRule.find('.rule-weekday');
		const $firstTimeSlot = $firstRule.find('.rule-time-slot');
		const $requiredIndicators = $firstRule.find('.rule-required-indicator');

		const confirmedRulesCount = $('.rule-item.rule-confirmed').length;

		if (confirmedRulesCount > 0) {
			$firstWeekday.removeAttr('required');
			$firstTimeSlot.removeAttr('required');
			$requiredIndicators.hide();
		} else {
			$firstWeekday.attr('required', 'required');
			$firstTimeSlot.attr('required', 'required');
			$requiredIndicators.show();
		}
	}

	$(document)
		.off('click.recurringCreateRemove', '.remove-rule')
		.on('click.recurringCreateRemove', '.remove-rule', function () {
			$(this).closest('.rule-item').remove();
			updateRemoveButtons();
			$('.rule-item').each(function () {
				const $item = $(this);
				if (!$item.hasClass('rule-confirmed')) {
					updateRule($item);
				}
			});
			updateFirstRuleRequired();
		});

	function updateRemoveButtons() {
		const ruleCount = $('.rule-item').length;
		$('.rule-item').each(function (index) {
			const $addBtn = $(this).find('#add-rule');
			const $removeBtn = $(this).find('.remove-rule');

			if (index === 0) {
				$addBtn.show();
				$removeBtn.hide();
			} else {
				$addBtn.hide();
				$removeBtn.show();
			}
		});
	}

	initState();
	updateRemoveButtons();
}

function initRecurringEdit($, tenantSlug) {
	const form = document.querySelector('form[data-recurring-edit=\"true\"]');
	if (!form) return;

	let ruleIndex = $('.rule-item').length;
	let businessHours = [];
	let doctorId = null;
	let appointmentTypeId = null;
	let startDate = null;
	const recurringAppointmentId = form.dataset.recurringId;
	const currentAppointmentTypeId = form.dataset.currentAppointmentTypeId;

	$('#end_type')
		.off('change.recurringEdit')
		.on('change.recurringEdit', function () {
			const endType = $(this).val();
			$('#total_sessions_field').toggle(endType === 'total_sessions');
			$('#end_date_field').toggle(endType === 'date');
		});

	function loadRecurringAppointmentTypes(doctorId) {
		const $appointmentTypeSelect = $('#appointment_type_id');

		if (!doctorId) {
			$appointmentTypeSelect.html('<option value="">Primeiro selecione um médico</option>').prop('disabled', true);
			return;
		}

		$appointmentTypeSelect.html('<option value="">Carregando...</option>').prop('disabled', true);

		$.ajax({
			url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/appointment-types`,
			method: 'GET',
			success: function (data) {
				$appointmentTypeSelect.empty();

				if (!data || data.length === 0) {
					$appointmentTypeSelect.append('<option value="">Nenhum tipo de consulta disponÃ­vel</option>');
					$appointmentTypeSelect.prop('disabled', true);
					return;
				}

				$appointmentTypeSelect.append('<option value="">Selecione um tipo</option>');

				let currentTypeFound = false;
				data.forEach(function (type) {
					const selected = currentAppointmentTypeId && type.id === currentAppointmentTypeId ? 'selected' : '';
					if (selected) currentTypeFound = true;
					$appointmentTypeSelect.append(
						`<option value="${type.id}" ${selected}>${type.name} (${type.duration_min} min)</option>`
					);
				});

				$appointmentTypeSelect.prop('disabled', false);

				if (currentAppointmentTypeId && !currentTypeFound) {
					$appointmentTypeSelect.val('');
					appointmentTypeId = null;
				} else if (currentTypeFound) {
					appointmentTypeId = currentAppointmentTypeId;
				}
			},
			error: function (xhr) {
				console.error('Erro ao buscar tipos de consulta:', xhr);
				$appointmentTypeSelect.html('<option value="">Erro ao carregar tipos de consulta</option>');
			},
		});
	}

	function loadBusinessHours() {
		doctorId = $('#doctor_id').val();

		if (!doctorId) {
			businessHours = [];
			updateAllRules();
			return;
		}

		$.ajax({
			url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/business-hours`,
			method: 'GET',
			success: function (data) {
				businessHours = data;
				updateAllRules();
			},
			error: function (xhr) {
				console.error('Erro ao buscar horários do médico:', xhr);
				showAlert({
					type: 'error',
					title: 'Erro',
					message: 'Erro ao carregar horários do médico. Por favor, tente novamente.',
				});
			},
		});
	}

	$('#appointment_type_id')
		.off('change.recurringEdit')
		.on('change.recurringEdit', function () {
			appointmentTypeId = $(this).val();
			updateAllRules();
		});

	$('#start_date')
		.off('change.recurringEdit')
		.on('change.recurringEdit', function () {
			startDate = $(this).val();
			updateAllRules();
		});

	$('#doctor_id')
		.off('change.recurringEdit')
		.on('change.recurringEdit', function () {
			const selectedDoctorId = $(this).val();
			loadRecurringAppointmentTypes(selectedDoctorId);
			loadBusinessHours();
		});

	function initState() {
		doctorId = $('#doctor_id').val();
		appointmentTypeId = $('#appointment_type_id').val();
		startDate = $('#start_date').val();

		if (doctorId) {
			loadRecurringAppointmentTypes(doctorId);
			loadBusinessHours();
		}
	}

	function updateAllRules() {
		$('.rule-item').each(function () {
			updateRule($(this));
		});
	}

	function updateRule($ruleItem) {
		const $weekdaySelect = $ruleItem.find('.rule-weekday');
		const $timeSlotSelect = $ruleItem.find('.rule-time-slot');
		const selectedWeekday = $weekdaySelect.data('selected');
		const selectedTimeSlot = $timeSlotSelect.data('selected');

		$weekdaySelect.empty();
		$timeSlotSelect.empty();
		$timeSlotSelect.prop('disabled', true);

		if (!doctorId || businessHours.length === 0) {
			$weekdaySelect.append('<option value="">Primeiro selecione um médico</option>');
			$weekdaySelect.prop('disabled', true);
			return;
		}

		businessHours.forEach(function (bh) {
			const selected = selectedWeekday === bh.weekday_string ? 'selected' : '';
			$weekdaySelect.append(`<option value="${bh.weekday_string}" ${selected}>${bh.weekday_name}</option>`);
		});

		$weekdaySelect.prop('disabled', false);
		setupRuleHandlers($ruleItem);

		if (selectedWeekday) {
			loadTimeSlotsForDay($ruleItem, selectedWeekday, selectedTimeSlot);
		}
	}

	function loadTimeSlotsForDay($ruleItem, weekdayString, selectedTimeSlot = null) {
		const $timeSlotSelect = $ruleItem.find('.rule-time-slot');
		const $startTimeInput = $ruleItem.find('.rule-start-time');
		const $endTimeInput = $ruleItem.find('.rule-end-time');

		$timeSlotSelect.empty();
		$timeSlotSelect.append('<option value="">Carregando...</option>');
		$timeSlotSelect.prop('disabled', true);

		if (!doctorId || !appointmentTypeId || !startDate) {
			$timeSlotSelect.empty();
			$timeSlotSelect.append('<option value="">Selecione médico, tipo de consulta e data inicial</option>');
			return;
		}

		$.ajax({
			url: `/workspace/${tenantSlug}/api/doctors/${doctorId}/available-slots-recurring`,
			method: 'GET',
			data: {
				weekday: weekdayString,
				appointment_type_id: appointmentTypeId,
				start_date: startDate,
				recurring_appointment_id: recurringAppointmentId,
			},
			success: function (slots) {
				$timeSlotSelect.empty();

				if (slots.length === 0) {
					$timeSlotSelect.append('<option value="">Nenhum horÃ¡rio disponÃ­vel</option>');
					$timeSlotSelect.prop('disabled', true);
					return;
				}

				$timeSlotSelect.append('<option value="">Selecione um horÃ¡rio</option>');
				slots.forEach(function (slot) {
					const timeSlotValue = `${slot.start}|${slot.end}`;
					const selected = selectedTimeSlot === timeSlotValue ? 'selected' : '';
					$timeSlotSelect.append(
						`<option value="${timeSlotValue}" data-start="${slot.start}" data-end="${slot.end}" ${selected}>${slot.display}</option>`
					);
				});

				$timeSlotSelect.prop('disabled', false);

				if (selectedTimeSlot) {
					const selectedOption = $timeSlotSelect.find(`option[value="${selectedTimeSlot}"]`);
					if (selectedOption.length) {
						$startTimeInput.val(selectedOption.data('start'));
						$endTimeInput.val(selectedOption.data('end'));
					}
				}
			},
			error: function (xhr) {
				console.error('Erro ao buscar horários disponíveis:', xhr);
				$timeSlotSelect.empty();
				$timeSlotSelect.append('<option value="">Erro ao carregar horários</option>');
				$timeSlotSelect.prop('disabled', true);
			},
		});
	}

	function setupRuleHandlers($ruleItem) {
		const $weekdaySelect = $ruleItem.find('.rule-weekday');
		const $timeSlotSelect = $ruleItem.find('.rule-time-slot');
		const $startTimeInput = $ruleItem.find('.rule-start-time');
		const $endTimeInput = $ruleItem.find('.rule-end-time');

		$weekdaySelect.off('change').on('change', function () {
			const weekdayString = $(this).val();
			if (weekdayString) {
				loadTimeSlotsForDay($ruleItem, weekdayString);
			} else {
				$timeSlotSelect.empty();
				$timeSlotSelect.append('<option value="">Selecione o dia</option>');
				$timeSlotSelect.prop('disabled', true);
			}
		});

		$timeSlotSelect.off('change').on('change', function () {
			const selectedOption = $(this).find('option:selected');
			const startTime = selectedOption.data('start');
			const endTime = selectedOption.data('end');

			if (startTime && endTime) {
				$startTimeInput.val(startTime);
				$endTimeInput.val(endTime);
			} else {
				$startTimeInput.val('');
				$endTimeInput.val('');
			}
		});
	}

	$('#add-rule')
		.off('click.recurringEdit')
		.on('click.recurringEdit', function () {
			const $lastRule = $('.rule-item').last();

			const currentWeekday = $lastRule.find('.rule-weekday').val();
			const currentTimeSlot = $lastRule.find('.rule-time-slot').val();
			const currentStartTime = $lastRule.find('.rule-start-time').val();
			const currentEndTime = $lastRule.find('.rule-end-time').val();

			const ruleHtml = `
            <div class="rule-item mb-3 p-3 border rounded">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="fw-semibold">Dia da Semana <span class="text-danger">*</span></label>
                            <select name="rules[${ruleIndex}][weekday]" class="form-control rule-weekday" required>
                                ${businessHours.length > 0 ? businessHours.map(bh => `<option value="${bh.weekday_string}" ${bh.weekday_string === currentWeekday ? 'selected' : ''}>${bh.weekday_name}</option>`).join('') : '<option value="">Primeiro selecione um médico</option>'}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="fw-semibold">HorÃ¡rio DisponÃ­vel <span class="text-danger">*</span></label>
                            <select name="rules[${ruleIndex}][time_slot]" class="form-control rule-time-slot" required>
                                <option value="">Selecione o dia e tipo de consulta</option>
                            </select>
                            <input type="hidden" name="rules[${ruleIndex}][start_time]" class="rule-start-time" value="${currentStartTime || ''}">
                            <input type="hidden" name="rules[${ruleIndex}][end_time]" class="rule-end-time" value="${currentEndTime || ''}">
                        </div>
                    </div>
                    <input type="hidden" name="rules[${ruleIndex}][frequency]" value="weekly">
                    <input type="hidden" name="rules[${ruleIndex}][interval]" value="1">
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-group w-100">
                            <label class="fw-semibold">&nbsp;</label>
                            <button type="button" class="remove-rule w-100 inline-flex items-center justify-center gap-1 rounded-md bg-error text-white text-sm font-semibold transition hover:bg-error/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-error/50 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                                <i class="mdi mdi-delete"></i> Remover
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

			const $newRule = $(ruleHtml);
			$('#rules-container').append($newRule);

			setupRuleHandlers($newRule);

			if (currentWeekday) {
				loadTimeSlotsForDay($newRule, currentWeekday, currentTimeSlot);
			} else {
				updateRule($newRule);
			}

			$lastRule.find('.rule-weekday').val('');
			$lastRule.find('.rule-time-slot').val('').prop('disabled', true);
			$lastRule.find('.rule-start-time').val('');
			$lastRule.find('.rule-end-time').val('');

			ruleIndex++;
			updateRemoveButtons();
		});

	$(document)
		.off('click.recurringEditRemove', '.remove-rule')
		.on('click.recurringEditRemove', '.remove-rule', function () {
			$(this).closest('.rule-item').remove();
			updateRemoveButtons();
		});

	function updateRemoveButtons() {
		const ruleCount = $('.rule-item').length;
		$('.rule-item').each(function (index) {
			const $removeBtn = $(this).find('.remove-rule');
			if (index === 0 && ruleCount === 1) {
				$removeBtn.hide();
			} else if (index === 0 && ruleCount > 1) {
				$removeBtn.show();
			} else {
				$removeBtn.show();
			}
		});
	}

	initState();
	updateRemoveButtons();
}
