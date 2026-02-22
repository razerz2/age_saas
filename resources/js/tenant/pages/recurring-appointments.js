import { applyGridPageSizeSelector } from '../grid/pageSizeSelector';
import { initEntitySearchModal } from '../components/entitySearchModal';

function bindRecurringAppointmentsIndexRowClick() {
	const grid = document.getElementById('recurring-appointments-grid');
	if (!grid) {
		return;
	}

	const wrapper = document.getElementById('recurring-appointments-grid-wrapper');
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

export function init() {
    if (
        !applyGridPageSizeSelector({
            wrapperSelector: '#recurring-appointments-grid-wrapper',
            storageKey: 'tenant_recurring_appointments_page_size',
            defaultLimit: 10,
        })
    ) {
        return;
    }
	const tenantSlug = window.tenantSlug || (window.tenant && window.tenant.slug) || null;
	if (!tenantSlug) return;
	bindRecurringAppointmentsIndexRowClick();

	const form = document.getElementById('recurring-appointment-form');
	if (!form) return;

	const doctorSelect = form.querySelector('#doctor_id');
	const specialtySelect = form.querySelector('#specialty_id');
	const appointmentTypeInput = form.querySelector('#appointment_type_id');
	const startDateInput = form.querySelector('#start_date');
	const openDatePickerButton = form.querySelector('[data-action="open-date-picker"]');
	const endTypeSelect = form.querySelector('#end_type');
	const endDateField = form.querySelector('#end_date_field');
	const rulesContainer = form.querySelector('#rules-container');
	const addRuleButton = form.querySelector('#add-rule');

	if (!doctorSelect || !specialtySelect || !appointmentTypeInput || !rulesContainer) return;

	initEntitySearchModal();

	let businessHours = [];
	let ruleIndex = 1;

	function showAlert({ type, title, message }) {
		if (typeof window.showAlert === 'function') {
			window.showAlert({ type, title, message });
			return;
		}
		// eslint-disable-next-line no-alert
		alert(`${title}\n\n${message}`);
	}

	async function fetchJson(url) {
		const response = await fetch(url, {
			headers: {
				Accept: 'application/json',
			},
		});
		if (!response.ok) {
			throw new Error(`HTTP ${response.status}`);
		}
		return response.json();
	}

	function openStartDatePicker() {
		if (!startDateInput || startDateInput.disabled) return;
		try {
			if (typeof startDateInput.showPicker === 'function') {
				startDateInput.showPicker();
				return;
			}
		} catch {
			// fallback
		}
		startDateInput.focus();
	}

	function setStartDateState(hasDoctor, preserveValue = false) {
		if (!startDateInput) return;
		startDateInput.disabled = !hasDoctor;
		if (!hasDoctor || !preserveValue) {
			startDateInput.value = '';
		}
	}

	function getFirstRule() {
		return rulesContainer.querySelector('.rule-item:not(.rule-confirmed)') || rulesContainer.querySelector('.rule-item');
	}

	function getConfirmedRuleItems() {
		return Array.from(rulesContainer.querySelectorAll('.rule-item.rule-confirmed'));
	}

	function getConfirmedWeekdays() {
		return getConfirmedRuleItems()
			.map((item) => item.querySelector('input[name*="[weekday]"]')?.value)
			.filter(Boolean);
	}

	function updateFirstRuleRequired() {
		const firstRule = getFirstRule();
		if (!firstRule) return;

		const weekdaySelect = firstRule.querySelector('.rule-weekday');
		const timeSlotSelect = firstRule.querySelector('.rule-time-slot');
		const indicators = firstRule.querySelectorAll('.rule-required-indicator');

		const hasConfirmed = getConfirmedRuleItems().length > 0;
		if (hasConfirmed) {
			weekdaySelect?.removeAttribute('required');
			timeSlotSelect?.removeAttribute('required');
			indicators.forEach((el) => (el.style.display = 'none'));
		} else {
			weekdaySelect?.setAttribute('required', 'required');
			timeSlotSelect?.setAttribute('required', 'required');
			indicators.forEach((el) => (el.style.display = ''));
		}
	}

	function resetSpecialty() {
		specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
		specialtySelect.disabled = true;
	}

	function resetRules() {
		getConfirmedRuleItems().forEach((item) => item.remove());

		const firstRule = getFirstRule();
		if (!firstRule) return;

		const weekdaySelect = firstRule.querySelector('.rule-weekday');
		const timeSlotSelect = firstRule.querySelector('.rule-time-slot');
		const startTimeInput = firstRule.querySelector('.rule-start-time');
		const endTimeInput = firstRule.querySelector('.rule-end-time');

		if (weekdaySelect) {
			weekdaySelect.innerHTML = '<option value="">Selecione um médico primeiro</option>';
			weekdaySelect.disabled = true;
		}
		if (timeSlotSelect) {
			timeSlotSelect.innerHTML = '<option value="">Selecione um dia da semana primeiro</option>';
			timeSlotSelect.disabled = true;
		}
		if (startTimeInput) startTimeInput.value = '';
		if (endTimeInput) endTimeInput.value = '';

		updateFirstRuleRequired();
	}

	async function loadSpecialties(doctorId) {
		const desiredId = specialtySelect.dataset.initialValue || null;

		specialtySelect.disabled = true;
		specialtySelect.innerHTML = '<option value="">Carregando especialidades...</option>';

		try {
			const data = await fetchJson(`/workspace/${tenantSlug}/api/doctors/${doctorId}/specialties`);
			const list = Array.isArray(data) ? data : [];

			if (list.length === 0) {
				specialtySelect.innerHTML = '<option value="">Nenhuma especialidade cadastrada para este médico</option>';
				specialtySelect.disabled = true;
				return;
			}

			specialtySelect.innerHTML = '<option value="">Selecione uma especialidade</option>';
			list.forEach((specialty) => {
				const option = document.createElement('option');
				option.value = specialty.id;
				option.textContent = specialty.name;
				if (desiredId && String(desiredId) === String(specialty.id)) {
					option.selected = true;
				}
				specialtySelect.appendChild(option);
			});

			specialtySelect.disabled = false;
		} catch {
			specialtySelect.innerHTML = '<option value="">Erro ao carregar especialidades</option>';
			specialtySelect.disabled = true;
		}
	}

	async function loadAppointmentTypeId(doctorId) {
		const currentTypeId = appointmentTypeInput.value || null;
		try {
			const data = await fetchJson(`/workspace/${tenantSlug}/api/doctors/${doctorId}/appointment-types`);
			const list = Array.isArray(data) ? data : [];

			if (list.length === 0) {
				appointmentTypeInput.value = '';
				showAlert({
					type: 'warning',
					title: 'Atenção',
					message: 'Este médico não possui tipos de consulta cadastrados. Não é possível criar recorrência.',
				});
				return false;
			}

			const match = currentTypeId ? list.find((t) => String(t.id) === String(currentTypeId)) : null;
			appointmentTypeInput.value = match ? match.id : list[0].id;
			return true;
		} catch {
			appointmentTypeInput.value = '';
			showAlert({
				type: 'error',
				title: 'Erro',
				message: 'Falha ao carregar tipo de consulta automático do médico.',
			});
			return false;
		}
	}

	async function loadBusinessHours(doctorId) {
		try {
			const data = await fetchJson(`/workspace/${tenantSlug}/api/doctors/${doctorId}/business-hours`);
			businessHours = Array.isArray(data) ? data : [];
		} catch {
			businessHours = [];
		}
	}

	function updateWeekdayOptions(ruleItem) {
		const weekdaySelect = ruleItem.querySelector('.rule-weekday');
		if (!weekdaySelect) return;

		const current = weekdaySelect.value || '';
		const confirmed = new Set(getConfirmedWeekdays());

		weekdaySelect.innerHTML = '';

		if (!doctorSelect.value) {
			weekdaySelect.innerHTML = '<option value="">Selecione um médico primeiro</option>';
			weekdaySelect.disabled = true;
			return;
		}

		if (!appointmentTypeInput.value) {
			weekdaySelect.innerHTML = '<option value="">Aguardando tipo automático do médico</option>';
			weekdaySelect.disabled = true;
			return;
		}

		if (!businessHours || businessHours.length === 0) {
			weekdaySelect.innerHTML = '<option value="">Nenhum dia trabalhado encontrado</option>';
			weekdaySelect.disabled = true;
			return;
		}

		const placeholder = document.createElement('option');
		placeholder.value = '';
		placeholder.textContent = 'Selecione um dia';
		weekdaySelect.appendChild(placeholder);

		let hasAny = false;
		businessHours.forEach((bh) => {
			const value = bh.weekday_string;
			if (!value) return;
			if (confirmed.has(value) && value !== current) return;

			hasAny = true;
			const option = document.createElement('option');
			option.value = value;
			option.textContent = bh.weekday_name || value;
			weekdaySelect.appendChild(option);
		});

		weekdaySelect.disabled = !hasAny;
		if (current && Array.from(weekdaySelect.options).some((o) => o.value === current)) {
			weekdaySelect.value = current;
		}
	}

	async function loadTimeSlotsForDay(ruleItem, weekdayString) {
		const timeSlotSelect = ruleItem.querySelector('.rule-time-slot');
		const startTimeInput = ruleItem.querySelector('.rule-start-time');
		const endTimeInput = ruleItem.querySelector('.rule-end-time');

		if (!timeSlotSelect || !startTimeInput || !endTimeInput) return;

		const initialStart = startTimeInput.value || null;
		const initialEnd = endTimeInput.value || null;

		timeSlotSelect.disabled = true;
		timeSlotSelect.innerHTML = '<option value="">Carregando horários...</option>';

		const doctorId = doctorSelect.value;
		const appointmentTypeId = appointmentTypeInput.value;
		const startDate = startDateInput?.value || null;

		startTimeInput.value = '';
		endTimeInput.value = '';

		const missing = [];
		if (!doctorId) missing.push('médico');
		if (!appointmentTypeId) missing.push('tipo automático');
		if (!startDate) missing.push('data inicial');

		if (missing.length > 0) {
			timeSlotSelect.innerHTML = `<option value="">Selecione: ${missing.join(', ')}</option>`;
			return;
		}

		try {
			const params = new URLSearchParams({
				weekday: weekdayString,
				appointment_type_id: appointmentTypeId,
				start_date: startDate,
			});
			const slots = await fetchJson(`/workspace/${tenantSlug}/api/doctors/${doctorId}/available-slots-recurring?${params}`);
			const list = Array.isArray(slots) ? slots : [];

			timeSlotSelect.innerHTML = '';

			if (list.length === 0) {
				timeSlotSelect.innerHTML = '<option value="">Nenhum horário disponível</option>';
				timeSlotSelect.disabled = true;
				return;
			}

			const placeholder = document.createElement('option');
			placeholder.value = '';
			placeholder.textContent = 'Selecione um horário';
			timeSlotSelect.appendChild(placeholder);

			list.forEach((slot) => {
				const option = document.createElement('option');
				option.value = `${slot.start}|${slot.end}`;
				option.textContent = slot.display || `${slot.start} - ${slot.end}`;
				option.dataset.start = slot.start;
				option.dataset.end = slot.end;
				timeSlotSelect.appendChild(option);
			});

			timeSlotSelect.disabled = false;

			if (initialStart && initialEnd) {
				const opt = Array.from(timeSlotSelect.options).find(
					(o) => o.dataset.start === initialStart && o.dataset.end === initialEnd
				);
				if (opt) {
					opt.selected = true;
					startTimeInput.value = initialStart;
					endTimeInput.value = initialEnd;
				}
			}
		} catch {
			timeSlotSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
			timeSlotSelect.disabled = true;
		}
	}

	function setupFirstRuleHandlers() {
		const firstRule = getFirstRule();
		if (!firstRule) return;
		if (firstRule.dataset.handlersBound === 'true') return;
		firstRule.dataset.handlersBound = 'true';

		const weekdaySelect = firstRule.querySelector('.rule-weekday');
		const timeSlotSelect = firstRule.querySelector('.rule-time-slot');
		const startTimeInput = firstRule.querySelector('.rule-start-time');
		const endTimeInput = firstRule.querySelector('.rule-end-time');

		if (weekdaySelect) {
			weekdaySelect.addEventListener('change', async () => {
				const weekday = weekdaySelect.value || '';
				if (!weekday) {
					if (timeSlotSelect) {
						timeSlotSelect.innerHTML = '<option value="">Selecione um dia da semana primeiro</option>';
						timeSlotSelect.disabled = true;
					}
					if (startTimeInput) startTimeInput.value = '';
					if (endTimeInput) endTimeInput.value = '';
					return;
				}
				await loadTimeSlotsForDay(firstRule, weekday);
			});
		}

		if (timeSlotSelect) {
			timeSlotSelect.addEventListener('change', () => {
				const option = timeSlotSelect.selectedOptions?.[0] || null;
				const start = option?.dataset?.start || '';
				const end = option?.dataset?.end || '';
				if (startTimeInput) startTimeInput.value = start;
				if (endTimeInput) endTimeInput.value = end;
			});
		}
	}

	function buildConfirmedRuleItem({ weekdayValue, weekdayLabel, timeSlotValue, timeSlotLabel, startTime, endTime }) {
		const wrapper = document.createElement('div');
		wrapper.className = 'rule-item mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md rule-confirmed';

		wrapper.innerHTML = `
			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dia da Semana <span class="text-red-500">*</span></label>
					<select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white" disabled>
						<option value="${weekdayValue}" selected>${weekdayLabel}</option>
					</select>
					<input type="hidden" name="rules[${ruleIndex}][weekday]" value="${weekdayValue}">
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Horário Disponível <span class="text-red-500">*</span></label>
					<select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white" disabled>
						<option value="${timeSlotValue}" selected>${timeSlotLabel}</option>
					</select>
					<input type="hidden" name="rules[${ruleIndex}][time_slot]" value="${timeSlotValue}">
					<input type="hidden" name="rules[${ruleIndex}][start_time]" value="${startTime}">
					<input type="hidden" name="rules[${ruleIndex}][end_time]" value="${endTime}">
					<input type="hidden" name="rules[${ruleIndex}][frequency]" value="weekly">
					<input type="hidden" name="rules[${ruleIndex}][interval]" value="1">
				</div>
				<div class="flex items-end">
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 rule-label-spacer">&nbsp;</label>
					<button type="button" class="btn btn-outline remove-rule" aria-label="Remover regra de recorrência">
						Remover
					</button>
				</div>
			</div>
		`;

		ruleIndex += 1;
		return wrapper;
	}

	function handleAddRule() {
		const firstRule = getFirstRule();
		if (!firstRule) return;

		const weekdaySelect = firstRule.querySelector('.rule-weekday');
		const timeSlotSelect = firstRule.querySelector('.rule-time-slot');
		const startTimeInput = firstRule.querySelector('.rule-start-time');
		const endTimeInput = firstRule.querySelector('.rule-end-time');

		const weekdayValue = weekdaySelect?.value || '';
		const timeSlotValue = timeSlotSelect?.value || '';
		const startTime = startTimeInput?.value || '';
		const endTime = endTimeInput?.value || '';
		const weekdayLabel = weekdaySelect?.selectedOptions?.[0]?.textContent || weekdayValue;
		const timeSlotLabel = timeSlotSelect?.selectedOptions?.[0]?.textContent || timeSlotValue;

		if (!weekdayValue || !timeSlotValue || !startTime || !endTime) {
			showAlert({
				type: 'warning',
				title: 'Atenção',
				message: 'Selecione um dia da semana e um horário na primeira regra antes de adicionar outra.',
			});
			return;
		}

		const confirmedWeekdays = getConfirmedWeekdays();
		if (confirmedWeekdays.includes(weekdayValue)) {
			showAlert({
				type: 'warning',
				title: 'Atenção',
				message: 'Este dia da semana já foi adicionado em outra regra. Não é possível duplicar dias.',
			});
			return;
		}

		const confirmed = buildConfirmedRuleItem({
			weekdayValue,
			weekdayLabel,
			timeSlotValue,
			timeSlotLabel,
			startTime,
			endTime,
		});
		rulesContainer.appendChild(confirmed);

		if (weekdaySelect) weekdaySelect.value = '';
		if (timeSlotSelect) {
			timeSlotSelect.innerHTML = '<option value="">Selecione um dia da semana primeiro</option>';
			timeSlotSelect.disabled = true;
		}
		if (startTimeInput) startTimeInput.value = '';
		if (endTimeInput) endTimeInput.value = '';

		updateWeekdayOptions(firstRule);
		updateFirstRuleRequired();
	}

	function initEndTypeToggle() {
		if (!endTypeSelect || !endDateField) return;
		const apply = () => {
			const show = endTypeSelect.value === 'date';
			endDateField.classList.toggle('hidden', !show);
		};
		endTypeSelect.addEventListener('change', apply);
		apply();
	}

	async function handleDoctorChange({ preserveDate = false } = {}) {
		const doctorId = doctorSelect.value || null;

		resetRules();
		resetSpecialty();
		businessHours = [];

		if (!doctorId) {
			appointmentTypeInput.value = '';
			setStartDateState(false);
			return;
		}

		setStartDateState(true, preserveDate);

		await Promise.all([loadSpecialties(doctorId), loadBusinessHours(doctorId), loadAppointmentTypeId(doctorId)]);

		const firstRule = getFirstRule();
		if (firstRule) {
			updateWeekdayOptions(firstRule);
			setupFirstRuleHandlers();
		}
	}

	// Remover regra confirmada (delegado)
	rulesContainer.addEventListener('click', (event) => {
		const button = event.target.closest?.('.remove-rule');
		if (!button) return;
		const rule = button.closest('.rule-item');
		if (!rule) return;
		rule.remove();
		const firstRule = getFirstRule();
		if (firstRule) updateWeekdayOptions(firstRule);
		updateFirstRuleRequired();
	});

	// Submit: exigir ao menos uma regra válida e remover names do primeiro bloco se vazio
	form.addEventListener('submit', (event) => {
		const confirmedItems = getConfirmedRuleItems();
		let validRules = 0;

		confirmedItems.forEach((item) => {
			const weekday = item.querySelector('input[name*="[weekday]"]')?.value || '';
			const start = item.querySelector('input[name*="[start_time]"]')?.value || '';
			const end = item.querySelector('input[name*="[end_time]"]')?.value || '';
			if (weekday && start && end) validRules += 1;
		});

		const firstRule = getFirstRule();
		if (firstRule && !firstRule.classList.contains('rule-confirmed')) {
			const weekday = firstRule.querySelector('.rule-weekday')?.value || '';
			const start = firstRule.querySelector('.rule-start-time')?.value || '';
			const end = firstRule.querySelector('.rule-end-time')?.value || '';
			if (weekday && start && end) validRules += 1;
		}

		if (validRules === 0) {
			event.preventDefault();
			showAlert({
				type: 'warning',
				title: 'Atenção',
				message: 'Adicione pelo menos uma regra completa (dia da semana e horário).',
			});
			return;
		}

		if (confirmedItems.length > 0 && firstRule) {
			const weekday = firstRule.querySelector('.rule-weekday')?.value || '';
			const start = firstRule.querySelector('.rule-start-time')?.value || '';
			const end = firstRule.querySelector('.rule-end-time')?.value || '';
			if (!weekday || !start || !end) {
				firstRule
					.querySelectorAll('input[name*="rules[0]"], select[name*="rules[0]"]')
					.forEach((el) => el.removeAttribute('name'));
			}
		}
	});

	doctorSelect.addEventListener('change', () => handleDoctorChange());
	if (addRuleButton) addRuleButton.addEventListener('click', handleAddRule);

	if (startDateInput) {
		startDateInput.addEventListener('change', () => {
			const firstRule = getFirstRule();
			if (!firstRule) return;
			const weekday = firstRule.querySelector('.rule-weekday')?.value || '';
			if (weekday) loadTimeSlotsForDay(firstRule, weekday);
		});
	}

	if (openDatePickerButton) {
		openDatePickerButton.addEventListener('click', openStartDatePicker);
	}

	if (startDateInput) {
		startDateInput.addEventListener('click', openStartDatePicker);
	}

	initEndTypeToggle();
	setupFirstRuleHandlers();
	if (doctorSelect.dataset.initialValue && !doctorSelect.value) {
		doctorSelect.value = doctorSelect.dataset.initialValue;
	}
	handleDoctorChange({ preserveDate: Boolean(startDateInput?.value) });
}
