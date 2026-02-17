function parseSelected(container) {
    if (!container) return [];
    return Array.from(container.querySelectorAll('.weekday-badge'))
        .map((badge) => badge.dataset.id)
        .filter(Boolean)
        .map(String);
}

function initWeekdaysModal() {
    const weekdaySelect = document.getElementById('weekday-select');
    const addWeekdayBtn = document.getElementById('add-weekday-btn');
    const clearWeekdaysBtn = document.getElementById('clear-weekdays-btn');
    const selectedContainer = document.getElementById('selected-weekdays');
    const inputsContainer = document.getElementById('weekdays-inputs');
    const addHourForm = document.querySelector('#addHourModal form');
    const addHourModal = document.getElementById('addHourModal');

    if (!weekdaySelect || !selectedContainer || !inputsContainer) return;

    const weekdayNames = {
        0: 'Domingo',
        1: 'Segunda-feira',
        2: 'Terça-feira',
        3: 'Quarta-feira',
        4: 'Quinta-feira',
        5: 'Sexta-feira',
        6: 'Sábado'
    };

    let selectedWeekdays = [];

    function renderEmpty() {
        selectedContainer.innerHTML =
            '<p class="text-muted mb-0"><i class="mdi mdi-information-outline me-1"></i>Nenhum dia selecionado</p>';
    }

    function updateInputs() {
        inputsContainer.innerHTML = '';
        selectedWeekdays.forEach((weekday) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'weekdays[]';
            input.value = parseInt(weekday, 10);
            inputsContainer.appendChild(input);
        });
    }

    function updateDisplay() {
        selectedContainer.innerHTML = '';
        if (selectedWeekdays.length === 0) {
            renderEmpty();
            updateInputs();
            return;
        }

        selectedWeekdays.forEach((weekday) => {
            const name = weekdayNames[weekday] || `Dia ${weekday}`;
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary me-2 mb-2 weekday-badge';
            badge.dataset.id = String(weekday);
            badge.style.fontSize = '13px';
            badge.style.padding = '8px 14px';
            badge.style.display = 'inline-flex';
            badge.style.alignItems = 'center';
            badge.style.gap = '6px';
            badge.innerHTML = `<i class="mdi mdi-calendar-week"></i>${name}`;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-close btn-close-white ms-1';
            removeBtn.style.fontSize = '10px';
            removeBtn.style.opacity = '0.8';
            removeBtn.setAttribute('aria-label', 'Remover');
            removeBtn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const targetId = String(weekday);
                selectedWeekdays = selectedWeekdays.filter((id) => String(id) !== targetId);
                updateDisplay();
            });

            badge.appendChild(removeBtn);
            selectedContainer.appendChild(badge);
        });

        updateInputs();
    }

    function addWeekday() {
        const weekday = weekdaySelect.value;
        if (weekday === '') {
            if (typeof showAlert === 'function') {
                showAlert({
                    type: 'warning',
                    title: 'Atenção',
                    message: 'Por favor, selecione um dia da semana'
                });
            }
            return;
        }

        const weekdayStr = String(weekday);
        if (selectedWeekdays.some((id) => String(id) === weekdayStr)) {
            if (typeof showAlert === 'function') {
                showAlert({
                    type: 'warning',
                    title: 'Atenção',
                    message: 'Este dia já foi adicionado'
                });
            }
            return;
        }

        selectedWeekdays.push(parseInt(weekday, 10));
        weekdaySelect.value = '';
        updateDisplay();
    }

    if (addWeekdayBtn) {
        addWeekdayBtn.addEventListener('click', () => {
            addWeekday();
        });
    }

    if (clearWeekdaysBtn) {
        clearWeekdaysBtn.addEventListener('click', () => {
            if (selectedWeekdays.length === 0) return;

            if (typeof confirmAction === 'function') {
                confirmAction({
                    title: 'Remover dias selecionados',
                    message: 'Deseja remover todos os dias selecionados?',
                    confirmText: 'Remover',
                    cancelText: 'Cancelar',
                    type: 'warning',
                    onConfirm: () => {
                        selectedWeekdays = [];
                        updateDisplay();
                    }
                });
            } else {
                selectedWeekdays = [];
                updateDisplay();
            }
        });
    }

    if (addHourForm) {
        addHourForm.addEventListener('submit', (event) => {
            if (selectedWeekdays.length === 0) {
                event.preventDefault();
                if (typeof showAlert === 'function') {
                    showAlert({
                        type: 'warning',
                        title: 'Atenção',
                        message: 'Por favor, selecione pelo menos um dia da semana.'
                    });
                }
                return;
            }

            const startTime = addHourForm.querySelector('input[name="start_time"]')?.value;
            const endTime = addHourForm.querySelector('input[name="end_time"]')?.value;

            if (!startTime || !endTime) {
                event.preventDefault();
                if (typeof showAlert === 'function') {
                    showAlert({
                        type: 'warning',
                        title: 'Atenção',
                        message: 'Por favor, preencha os horários de início e fim.'
                    });
                }
            }
        });
    }

    if (addHourModal) {
        addHourModal.addEventListener('hidden.bs.modal', () => {
            selectedWeekdays = [];
            updateDisplay();
            if (addHourForm) {
                addHourForm.reset();
            }
        });
    }

    selectedWeekdays = parseSelected(selectedContainer);
    updateDisplay();
}

function initEditButtons() {
    document.querySelectorAll('[data-action=\"edit-hour\"]').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.hourId;
            const form = document.getElementById('editHourForm');
            if (!form) return;
            form.action = form.dataset.actionTemplate.replace(':id', id);
            form.querySelector('select[name=\"weekday\"]').value = button.dataset.weekday;
            form.querySelector('input[name=\"start_time\"]').value = button.dataset.startTime;
            form.querySelector('input[name=\"end_time\"]').value = button.dataset.endTime;
            form.querySelector('input[name=\"break_start_time\"]').value =
                button.dataset.breakStart || '';
            form.querySelector('input[name=\"break_end_time\"]').value =
                button.dataset.breakEnd || '';

            if (window.bootstrap?.Modal) {
                new window.bootstrap.Modal(document.getElementById('editHourModal')).show();
            }
        });
    });

    document.querySelectorAll('[data-action=\"edit-type\"]').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.typeId;
            const form = document.getElementById('editTypeForm');
            if (!form) return;
            form.action = form.dataset.actionTemplate.replace(':id', id);
            form.querySelector('input[name=\"name\"]').value = button.dataset.typeName;
            form.querySelector('input[name=\"duration_min\"]').value = button.dataset.typeDuration;
            form.querySelector('select[name=\"is_active\"]').value =
                button.dataset.typeActive === '1' || button.dataset.typeActive === 'true'
                    ? '1'
                    : '0';

            if (window.bootstrap?.Modal) {
                new window.bootstrap.Modal(document.getElementById('editTypeModal')).show();
            }
        });
    });
}

export function init() {
    initWeekdaysModal();
    initEditButtons();
}
