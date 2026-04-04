function nextIndex(container, rowSelector, inputPrefix) {
    const rows = Array.from(container.querySelectorAll(rowSelector));
    if (rows.length === 0) {
        return 0;
    }

    let maxIndex = -1;
    rows.forEach((row) => {
        const firstField = row.querySelector(`input[name^="${inputPrefix}["], select[name^="${inputPrefix}["]`);
        if (!firstField || !firstField.name) return;

        const match = firstField.name.match(/\[(\d+)\]/);
        if (!match) return;

        const idx = parseInt(match[1], 10);
        if (!Number.isNaN(idx) && idx > maxIndex) {
            maxIndex = idx;
        }
    });

    return maxIndex + 1;
}

function appendTemplateRow(templateId, container, index) {
    const template = document.getElementById(templateId);
    if (!template) return;

    const html = template.innerHTML.replaceAll('__INDEX__', String(index));
    container.insertAdjacentHTML('beforeend', html);
}

function bindRemoveRow(container, rowSelector, buttonSelector) {
    container.addEventListener('click', (event) => {
        const button = event.target.closest(buttonSelector);
        if (!button) return;

        const rows = container.querySelectorAll(rowSelector);
        if (rows.length <= 1) {
            return;
        }

        const row = button.closest(rowSelector);
        if (row) {
            row.remove();
        }
    });
}

function normalizeTime(value) {
    if (typeof value !== 'string') return '';
    const raw = value.trim();
    if (raw === '') return '';

    const match = raw.match(/^(\d{2}:\d{2})/);
    return match ? match[1] : '';
}

function parseInitialHours(section) {
    const raw = section.dataset.initialHours;
    if (!raw) return [];

    let parsed;
    try {
        parsed = JSON.parse(raw);
    } catch (_error) {
        return [];
    }

    if (!Array.isArray(parsed)) {
        return [];
    }

    return parsed
        .map((hour) => ({
            weekday: Number.parseInt(hour?.weekday, 10),
            start_time: normalizeTime(hour?.start_time ?? ''),
            end_time: normalizeTime(hour?.end_time ?? ''),
            break_start_time: normalizeTime(hour?.break_start_time ?? ''),
            break_end_time: normalizeTime(hour?.break_end_time ?? ''),
        }))
        .filter((hour) => Number.isInteger(hour.weekday) && hour.weekday >= 0 && hour.weekday <= 6);
}

function sortBusinessHours(hours, weekdayOrder) {
    const indexByDay = new Map(weekdayOrder.map((day, idx) => [day, idx]));

    return [...hours].sort((a, b) => {
        const orderA = indexByDay.has(a.weekday) ? indexByDay.get(a.weekday) : Number.MAX_SAFE_INTEGER;
        const orderB = indexByDay.has(b.weekday) ? indexByDay.get(b.weekday) : Number.MAX_SAFE_INTEGER;
        return orderA - orderB;
    });
}

function createHiddenInput(name, value) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value ?? '';
    return input;
}

function setupBusinessHoursTable() {
    const section = document.getElementById('agenda-business-hours-section');
    if (!section) return;

    const weekdays = JSON.parse(section.dataset.weekdays || '{}');
    const weekdayOrder = JSON.parse(section.dataset.weekdayOrder || '[1,2,3,4,5,6,0]');

    const tableBody = document.getElementById('business-hours-table-body');
    const emptyState = document.getElementById('business-hours-empty-state');
    const hiddenInputs = document.getElementById('business-hours-hidden-inputs');

    const openModalButton = document.getElementById('open-business-hour-modal');
    const modal = document.getElementById('business-hour-modal');
    const modalTitle = document.getElementById('business-hour-modal-title');
    const modalError = document.getElementById('business-hour-modal-error');
    const saveModalButton = document.getElementById('save-business-hour-modal');

    const weekdaySelect = document.getElementById('business-hour-modal-weekday');
    const startInput = document.getElementById('business-hour-modal-start-time');
    const endInput = document.getElementById('business-hour-modal-end-time');
    const hasBreakInput = document.getElementById('business-hour-modal-has-break');
    const breakStartInput = document.getElementById('business-hour-modal-break-start');
    const breakEndInput = document.getElementById('business-hour-modal-break-end');

    if (
        !tableBody || !emptyState || !hiddenInputs || !openModalButton || !modal || !modalTitle || !modalError ||
        !saveModalButton || !weekdaySelect || !startInput || !endInput || !hasBreakInput || !breakStartInput || !breakEndInput
    ) {
        return;
    }

    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    let businessHours = sortBusinessHours(parseInitialHours(section), weekdayOrder);
    let editingWeekday = null;

    const setBreakFieldsEnabled = (enabled) => {
        breakStartInput.disabled = !enabled;
        breakEndInput.disabled = !enabled;

        if (!enabled) {
            breakStartInput.value = '';
            breakEndInput.value = '';
        }
    };

    const showModalError = (message) => {
        modalError.textContent = message;
        modalError.classList.remove('hidden');
    };

    const clearModalError = () => {
        modalError.textContent = '';
        modalError.classList.add('hidden');
    };

    const openModal = (mode, weekday) => {
        clearModalError();

        const usedWeekdays = new Set(businessHours.map((hour) => hour.weekday));
        const currentWeekday = Number.isInteger(weekday) ? weekday : null;

        weekdaySelect.innerHTML = '';
        weekdayOrder.forEach((day) => {
            if (usedWeekdays.has(day) && day !== currentWeekday) {
                return;
            }

            const option = document.createElement('option');
            option.value = String(day);
            option.textContent = weekdays[String(day)] || String(day);
            weekdaySelect.appendChild(option);
        });

        if (weekdaySelect.options.length === 0) {
            window.alert('Todos os dias da semana já possuem horário cadastrado.');
            return;
        }

        if (mode === 'edit' && currentWeekday !== null) {
            modalTitle.textContent = 'Editar horário';
            saveModalButton.textContent = 'Salvar alterações';

            const current = businessHours.find((hour) => hour.weekday === currentWeekday);
            if (!current) {
                return;
            }

            weekdaySelect.value = String(current.weekday);
            startInput.value = current.start_time;
            endInput.value = current.end_time;

            const hasBreak = current.break_start_time !== '' || current.break_end_time !== '';
            hasBreakInput.checked = hasBreak;
            setBreakFieldsEnabled(hasBreak);
            breakStartInput.value = current.break_start_time;
            breakEndInput.value = current.break_end_time;

            editingWeekday = currentWeekday;
        } else {
            modalTitle.textContent = 'Adicionar horário';
            saveModalButton.textContent = 'Salvar horário';
            weekdaySelect.selectedIndex = 0;
            startInput.value = '';
            endInput.value = '';
            hasBreakInput.checked = false;
            setBreakFieldsEnabled(false);
            editingWeekday = null;
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        clearModalError();
    };

    const validateModal = () => {
        const weekday = Number.parseInt(weekdaySelect.value, 10);
        const start = normalizeTime(startInput.value);
        const end = normalizeTime(endInput.value);
        const useBreak = hasBreakInput.checked;
        const breakStart = useBreak ? normalizeTime(breakStartInput.value) : '';
        const breakEnd = useBreak ? normalizeTime(breakEndInput.value) : '';

        if (!Number.isInteger(weekday) || weekday < 0 || weekday > 6) {
            return { error: 'Selecione um dia da semana válido.' };
        }

        if (start === '' || end === '') {
            return { error: 'Informe a hora inicial e a hora final.' };
        }

        if (end <= start) {
            return { error: 'O horário de término deve ser maior que o horário de início.' };
        }

        if ((breakStart !== '' && breakEnd === '') || (breakStart === '' && breakEnd !== '')) {
            return { error: 'Informe os dois campos do intervalo ou deixe ambos em branco.' };
        }

        if (breakStart !== '' && breakEnd !== '') {
            if (breakEnd <= breakStart) {
                return { error: 'O intervalo deve terminar depois de começar.' };
            }

            if (breakStart <= start || breakEnd >= end) {
                return { error: 'O intervalo deve estar dentro do horário de atendimento.' };
            }
        }

        const hasCollision = businessHours.some((hour) => {
            if (editingWeekday !== null && hour.weekday === editingWeekday) {
                return false;
            }

            return hour.weekday === weekday;
        });

        if (hasCollision) {
            return { error: 'Já existe horário cadastrado para este dia da semana.' };
        }

        return {
            value: {
                weekday,
                start_time: start,
                end_time: end,
                break_start_time: breakStart,
                break_end_time: breakEnd,
            },
        };
    };

    const renderHiddenInputs = () => {
        hiddenInputs.innerHTML = '';

        const sorted = sortBusinessHours(businessHours, weekdayOrder);
        sorted.forEach((hour, index) => {
            hiddenInputs.appendChild(createHiddenInput(`business_hours[${index}][weekday]`, String(hour.weekday)));
            hiddenInputs.appendChild(createHiddenInput(`business_hours[${index}][start_time]`, hour.start_time));
            hiddenInputs.appendChild(createHiddenInput(`business_hours[${index}][end_time]`, hour.end_time));
            hiddenInputs.appendChild(createHiddenInput(`business_hours[${index}][break_start_time]`, hour.break_start_time));
            hiddenInputs.appendChild(createHiddenInput(`business_hours[${index}][break_end_time]`, hour.break_end_time));
        });
    };

    const renderTable = () => {
        businessHours = sortBusinessHours(businessHours, weekdayOrder);
        tableBody.innerHTML = '';

        if (businessHours.length === 0) {
            emptyState.classList.remove('hidden');
            renderHiddenInputs();
            return;
        }

        emptyState.classList.add('hidden');

        businessHours.forEach((hour) => {
            const tr = document.createElement('tr');

            const dayCell = document.createElement('td');
            dayCell.className = 'px-3 py-2 text-sm text-gray-800 dark:text-gray-200';
            dayCell.textContent = weekdays[String(hour.weekday)] || String(hour.weekday);

            const attendanceCell = document.createElement('td');
            attendanceCell.className = 'px-3 py-2 text-sm text-gray-700 dark:text-gray-300';
            attendanceCell.textContent = `${hour.start_time} às ${hour.end_time}`;

            const breakCell = document.createElement('td');
            breakCell.className = 'px-3 py-2 text-sm text-gray-700 dark:text-gray-300';
            breakCell.textContent =
                hour.break_start_time !== '' && hour.break_end_time !== ''
                    ? `${hour.break_start_time} às ${hour.break_end_time}`
                    : 'Sem intervalo';

            const actionsCell = document.createElement('td');
            actionsCell.className = 'px-3 py-2 text-right';

            const actionsWrap = document.createElement('div');
            actionsWrap.className = 'inline-flex items-center gap-2';

            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'btn btn-outline';
            editButton.dataset.action = 'edit-hour';
            editButton.dataset.weekday = String(hour.weekday);
            editButton.textContent = 'Editar';

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-outline text-red-600 hover:text-red-700';
            removeButton.dataset.action = 'remove-hour';
            removeButton.dataset.weekday = String(hour.weekday);
            removeButton.textContent = 'Remover';

            actionsWrap.appendChild(editButton);
            actionsWrap.appendChild(removeButton);
            actionsCell.appendChild(actionsWrap);

            tr.appendChild(dayCell);
            tr.appendChild(attendanceCell);
            tr.appendChild(breakCell);
            tr.appendChild(actionsCell);

            tableBody.appendChild(tr);
        });

        renderHiddenInputs();
    };

    openModalButton.addEventListener('click', () => openModal('create'));

    tableBody.addEventListener('click', (event) => {
        const target = event.target.closest('button[data-action]');
        if (!target) return;

        const weekday = Number.parseInt(target.dataset.weekday ?? '', 10);
        if (!Number.isInteger(weekday)) return;

        if (target.dataset.action === 'edit-hour') {
            openModal('edit', weekday);
            return;
        }

        if (target.dataset.action === 'remove-hour') {
            const weekdayLabel = weekdays[String(weekday)] || 'este dia';
            const confirmed = window.confirm(`Deseja remover o horário de atendimento de ${weekdayLabel}?`);
            if (!confirmed) return;

            businessHours = businessHours.filter((hour) => hour.weekday !== weekday);
            renderTable();
        }
    });

    modal.querySelectorAll('[data-business-hour-modal-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    hasBreakInput.addEventListener('change', () => {
        setBreakFieldsEnabled(hasBreakInput.checked);
    });

    saveModalButton.addEventListener('click', () => {
        clearModalError();

        const result = validateModal();
        if (result.error) {
            showModalError(result.error);
            return;
        }

        const { value } = result;
        if (editingWeekday === null) {
            businessHours.push(value);
        } else {
            businessHours = businessHours.map((hour) => {
                if (hour.weekday !== editingWeekday) return hour;
                return value;
            });
        }

        renderTable();
        closeModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    renderTable();
}

function setupAppointmentTypes() {
    const typesContainer = document.getElementById('appointment-types-rows');
    if (!typesContainer) {
        return;
    }

    const addTypeButton = document.getElementById('add-appointment-type-row');
    if (addTypeButton) {
        addTypeButton.addEventListener('click', () => {
            const index = nextIndex(typesContainer, '.appointment-type-row', 'appointment_types');
            appendTemplateRow('appointment-type-row-template', typesContainer, index);
        });
    }

    bindRemoveRow(typesContainer, '.appointment-type-row', '.remove-appointment-type-row');
}

export function init() {
    setupBusinessHoursTable();
    setupAppointmentTypes();
}