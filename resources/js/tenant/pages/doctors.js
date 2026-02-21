function parseInitialSelected(container) {
    if (!container) return [];
    const raw = container.dataset.initialSelected;
    if (raw) {
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed.map(String);
            }
        } catch (error) {
            // ignore parse failures
        }
    }

    return Array.from(container.querySelectorAll('.specialty-badge'))
        .map((badge) => badge.dataset.id)
        .filter(Boolean)
        .map(String);
}

function renderPlaceholder(container, style) {
    if (style === 'bootstrap') {
        container.innerHTML =
            '<p class="text-muted mb-0"><i class="mdi mdi-information-outline me-1"></i>Nenhuma especialidade selecionada</p>';
        return;
    }

    container.innerHTML =
        '<p class="text-gray-500 dark:text-gray-400 mb-0"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Nenhuma especialidade selecionada</p>';
}

function buildBadge(style, specialtyId, name) {
    if (style === 'bootstrap') {
        const badge = document.createElement('span');
        badge.className = 'badge bg-primary me-2 mb-2 specialty-badge';
        badge.dataset.id = specialtyId;
        badge.style.fontSize = '13px';
        badge.style.padding = '8px 14px';
        badge.style.display = 'inline-flex';
        badge.style.alignItems = 'center';
        badge.style.gap = '6px';
        badge.innerHTML = `<i class="mdi mdi-stethoscope"></i>${name}`;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-close btn-close-white ms-1';
        removeBtn.style.fontSize = '10px';
        removeBtn.style.opacity = '0.8';
        removeBtn.setAttribute('aria-label', 'Remover');

        badge.appendChild(removeBtn);
        return { badge, removeBtn };
    }

    const badge = document.createElement('span');
    badge.className =
        'inline-flex items-center gap-2 px-3 py-2 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-sm font-medium mr-2 mb-2 specialty-badge';
    badge.dataset.id = specialtyId;
    badge.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>${name}`;

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'text-blue-600 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-100 ml-1';
    removeBtn.setAttribute('aria-label', 'Remover');
    removeBtn.innerHTML =
        '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';

    badge.appendChild(removeBtn);
    return { badge, removeBtn };
}

function showAlertSafe(payload) {
    if (typeof showAlert === 'function') {
        showAlert(payload);
    }
}

function bindDoctorsIndexRowClick() {
    const grid = document.getElementById('doctors-grid');
    if (!grid) {
        return;
    }

    const wrapper = document.getElementById('doctors-grid-wrapper');
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

function bindDoctorDeleteConfirm() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-confirm-doctor-delete]');
        if (!form) {
            return;
        }

        event.preventDefault();

        const doctorName = form.dataset.doctorName || 'este mÈdico';

        if (typeof confirmAction === 'function') {
            confirmAction({
                title: 'Excluir mÈdico',
                message: `Tem certeza que deseja excluir ${doctorName}? Esta aÁ„o n„o pode ser desfeita.`,
                confirmText: 'Excluir',
                cancelText: 'Cancelar',
                type: 'error',
                onConfirm: () => form.submit(),
            });
            return;
        }

        if (window.confirm(`Tem certeza que deseja excluir ${doctorName}? Esta aÁ„o n„o pode ser desfeita.`)) {
            form.submit();
        }
    });
}

function initSpecialtiesSelector() {
    const specialtySelect = document.getElementById('specialty-select');
    const addSpecialtyBtn = document.getElementById('add-specialty-btn');
    const clearSpecialtiesBtn = document.getElementById('clear-specialties-btn');
    const selectedContainer = document.getElementById('selected-specialties');
    const inputsContainer = document.getElementById('specialties-inputs');
    const form = document.querySelector('form');

    if (!specialtySelect || !selectedContainer || !inputsContainer) return;

    const badgeStyle = selectedContainer.dataset.badgeStyle || 'tailwind';
    let selectedSpecialties = parseInitialSelected(selectedContainer);

    function updateInputs() {
        inputsContainer.innerHTML = '';
        selectedSpecialties.forEach((specialtyId) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'specialties[]';
            input.value = specialtyId;
            inputsContainer.appendChild(input);
        });
    }

    function updateDisplay() {
        selectedContainer.innerHTML = '';

        if (selectedSpecialties.length === 0) {
            renderPlaceholder(selectedContainer, badgeStyle);
            updateInputs();
            return;
        }

        selectedSpecialties.forEach((specialtyId) => {
            const option = specialtySelect.querySelector(`option[value="${specialtyId}"]`);
            if (!option) return;

            const { badge, removeBtn } = buildBadge(badgeStyle, specialtyId, option.dataset.name);
            removeBtn.addEventListener('click', (event) => {
                event.preventDefault();
                selectedSpecialties = selectedSpecialties.filter((id) => id !== specialtyId);
                updateDisplay();
            });
            selectedContainer.appendChild(badge);
        });

        updateInputs();
    }

    function addSpecialty() {
        const specialtyId = specialtySelect.value;
        if (!specialtyId) {
            showAlertSafe({
                type: 'warning',
                title: 'Aten√ß√£o',
                message: 'Por favor, selecione uma especialidade'
            });
            specialtySelect.focus();
            return;
        }

        const normalizedId = String(specialtyId);
        if (selectedSpecialties.includes(normalizedId)) {
            showAlertSafe({
                type: 'warning',
                title: 'Aten√ß√£o',
                message: 'Esta especialidade j√° foi adicionada'
            });
            specialtySelect.focus();
            return;
        }

        selectedSpecialties.push(normalizedId);
        specialtySelect.value = '';
        updateDisplay();
    }

    if (addSpecialtyBtn) {
        addSpecialtyBtn.addEventListener('click', (event) => {
            event.preventDefault();
            addSpecialty();
        });
    }

    specialtySelect.addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            addSpecialty();
        }
    });

    if (clearSpecialtiesBtn) {
        clearSpecialtiesBtn.addEventListener('click', () => {
            if (selectedSpecialties.length === 0) return;

            if (typeof confirmAction === 'function') {
                confirmAction({
                    title: 'Remover especialidades',
                    message: 'Deseja remover todas as especialidades selecionadas?',
                    confirmText: 'Remover',
                    cancelText: 'Cancelar',
                    type: 'warning',
                    onConfirm: () => {
                        selectedSpecialties = [];
                        updateDisplay();
                    }
                });
                return;
            }

            selectedSpecialties = [];
            updateDisplay();
        });
    }

    if (form) {
        form.addEventListener('submit', (event) => {
            if (selectedSpecialties.length === 0) {
                event.preventDefault();
                showAlertSafe({
                    type: 'warning',
                    title: 'Aten√ß√£o',
                    message: 'Por favor, selecione pelo menos uma especialidade m√©dica.'
                });
                specialtySelect.focus();
            }
        });
    }

    updateDisplay();
}

export function init() {
    bindDoctorsIndexRowClick();
    bindDoctorDeleteConfirm();
    initSpecialtiesSelector();
}

