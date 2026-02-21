function parseInitialWeekdays(container) {
    if (!container) return [];
    const raw = container.dataset.initialSelected;
    if (raw) {
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed.map(String);
            }
        } catch (error) {
            // ignore parse errors
        }
    }

    return Array.from(container.querySelectorAll('.weekday-badge'))
        .map((badge) => badge.dataset.id)
        .filter(Boolean)
        .map(String);
}

function initWeekdaysPicker() {
    const weekdaySelect = document.getElementById('weekday-select');
    const addWeekdayBtn = document.getElementById('add-weekday-btn');
    const clearWeekdaysBtn = document.getElementById('clear-weekdays-btn');
    const selectedContainer = document.getElementById('selected-weekdays');
    const inputsContainer = document.getElementById('weekdays-inputs');

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

    let selectedWeekdays = parseInitialWeekdays(selectedContainer);

    function updateInputs() {
        inputsContainer.innerHTML = '';
        selectedWeekdays.forEach((weekday) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'weekdays[]';
            input.value = weekday;
            inputsContainer.appendChild(input);
        });
    }

    function updateDisplay() {
        selectedContainer.innerHTML = '';
        if (selectedWeekdays.length === 0) {
            selectedContainer.innerHTML =
                '<p class="text-sm text-gray-500 dark:text-gray-400">Nenhum dia selecionado</p>';
            updateInputs();
            return;
        }

        selectedWeekdays.forEach((weekday) => {
            const name = weekdayNames[weekday] || `Dia ${weekday}`;
            const badge = document.createElement('span');
            badge.className =
                'weekday-badge inline-flex items-center gap-2 rounded-full bg-primary px-3 py-1 text-sm font-medium text-white mr-2 mb-2';
            badge.dataset.id = weekday;
            badge.innerHTML = `<span>${name}</span>`;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className =
                'weekday-remove inline-flex h-4 w-4 items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30';
            removeBtn.setAttribute('aria-label', 'Remover');
            removeBtn.textContent = '×';
            removeBtn.addEventListener('click', (event) => {
                event.preventDefault();
                selectedWeekdays = selectedWeekdays.filter((id) => id !== weekday);
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

        if (selectedWeekdays.includes(weekday)) {
            if (typeof showAlert === 'function') {
                showAlert({
                    type: 'warning',
                    title: 'Atenção',
                    message: 'Este dia já foi adicionado'
                });
            }
            return;
        }

        selectedWeekdays.push(weekday);
        weekdaySelect.value = '';
        updateDisplay();
    }

    if (addWeekdayBtn) {
        addWeekdayBtn.addEventListener('click', (event) => {
            event.preventDefault();
            addWeekday();
        });
    }

    weekdaySelect.addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            addWeekday();
        }
    });

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

    updateDisplay();
}

export function init() {
    bindBusinessHoursIndexRowClick();
    initWeekdaysPicker();
}

function bindBusinessHoursIndexRowClick() {
    const grid = document.getElementById('business-hours-grid');
    if (!grid) {
        return;
    }

    const wrapper = document.getElementById('business-hours-grid-wrapper');
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
