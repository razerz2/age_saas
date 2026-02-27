import Sortable from 'sortablejs';

const UUID_PATTERN = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

const STATUS_CLASS_MAP = {
    scheduled: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    rescheduled: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
    confirmed: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
    arrived: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    in_service: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    attended: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
    completed: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
    canceled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    no_show: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
};

const STATUS_LABEL_MAP = {
    arrived: 'Chegou',
    in_service: 'Em Atendimento',
    completed: 'Concluido',
    no_show: 'Nao Compareceu',
    canceled: 'Cancelado',
    rescheduled: 'Remarcado',
};

const BADGE_BASE_CLASS = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium';

function buildUrl(template, appointmentId) {
    return template.replace('__ID__', encodeURIComponent(appointmentId));
}

function renderDetailsLoading(container) {
    container.innerHTML = `
        <div class="h-full min-h-[220px] flex items-center justify-center text-center text-gray-500 dark:text-gray-400">
            <div>
                <div class="inline-flex items-center justify-center w-10 h-10 rounded-full border-2 border-gray-200 border-t-blue-500 animate-spin"></div>
                <p class="mt-3 text-sm">Carregando detalhes do agendamento...</p>
            </div>
        </div>
    `;
}

function renderError(container, message) {
    container.innerHTML = `
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <div class="flex items-start gap-2 text-red-700 dark:text-red-300 text-sm">
                <span class="mdi mdi-alert-circle-outline text-base mt-0.5"></span>
                <span>${message}</span>
            </div>
        </div>
    `;
}

function renderNoSelection(container) {
    container.innerHTML = `
        <div class="h-full min-h-[220px] flex items-center justify-center text-center text-gray-500 dark:text-gray-400">
            <div>
                <span class="mdi mdi-information-outline text-4xl text-gray-400 dark:text-gray-500"></span>
                <p class="mt-2 text-sm">Selecione um agendamento para visualizar os detalhes.</p>
            </div>
        </div>
    `;
}

function extractApiErrorMessage(payload, fallback = 'Erro ao processar solicitacao.') {
    if (!payload || typeof payload !== 'object') {
        return fallback;
    }

    if (typeof payload.message === 'string' && payload.message.trim()) {
        return payload.message.trim();
    }

    if (payload.errors && typeof payload.errors === 'object') {
        for (const value of Object.values(payload.errors)) {
            if (Array.isArray(value) && value.length > 0) {
                return String(value[0]);
            }
            if (typeof value === 'string' && value.trim()) {
                return value.trim();
            }
        }
    }

    return fallback;
}

function parseJsonSafely(rawText) {
    if (!rawText) {
        return null;
    }
    try {
        return JSON.parse(rawText);
    } catch (error) {
        return null;
    }
}

function normalizeForSort(value) {
    return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function getStatusLabel(status, fallback = '') {
    const normalized = String(status || '').toLowerCase();
    return fallback || STATUS_LABEL_MAP[normalized] || normalized;
}

function statusNeedsNote(status) {
    return ['no_show', 'canceled', 'rescheduled'].includes(String(status || '').toLowerCase());
}

function statusNeedsReschedule(status) {
    return String(status || '').toLowerCase() === 'rescheduled';
}

function parseTimestamp(value) {
    const timestamp = Date.parse(String(value || ''));
    return Number.isNaN(timestamp) ? Number.POSITIVE_INFINITY : timestamp;
}

function parseQueuePosition(value) {
    const parsed = Number.parseInt(String(value || ''), 10);
    return Number.isNaN(parsed) ? Number.POSITIVE_INFINITY : parsed;
}

function updateBadgeElement(badgeEl, status, label) {
    if (!badgeEl) {
        return;
    }

    const normalizedStatus = String(status || '').toLowerCase();
    badgeEl.className = `${BADGE_BASE_CLASS} ${STATUS_CLASS_MAP[normalizedStatus] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'}`;
    badgeEl.textContent = getStatusLabel(normalizedStatus, label);
}

function updateLateClass(item) {
    if (!item) {
        return;
    }

    const status = String(item.dataset.status || '').toLowerCase();
    const startAt = parseTimestamp(item.dataset.startAt);
    const isTerminalStatus = ['completed', 'canceled', 'cancelled', 'no_show'].includes(status);
    const isLate = !isTerminalStatus && Number.isFinite(startAt) && startAt < Date.now();
    item.classList.toggle('is-late', isLate);
}

export function init() {
    const configEl = document.getElementById('medical-appointments-config');
    if (!configEl) {
        return;
    }

    const config = {
        detailsUrlTemplate: configEl.dataset.detailsUrlTemplate || '',
        updateStatusUrlTemplate: configEl.dataset.updateStatusUrlTemplate || '',
        completeUrlTemplate: configEl.dataset.completeUrlTemplate || '',
        formResponseUrlTemplate: configEl.dataset.formResponseUrlTemplate || '',
        reorderUrl: configEl.dataset.reorderUrl || '',
        currentDate: configEl.dataset.currentDate || '',
        csrf: configEl.dataset.csrf || '',
        initialId: configEl.dataset.initialId || '',
    };

    const state = {
        selectedAppointmentId: config.initialId || '',
        sortBy: 'manual',
        sortDirection: 'asc',
        sortable: null,
        isDragging: false,
        isSavingOrder: false,
        previousOrder: [],
    };

    const getCsrfToken = () => {
        const tokenFromMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        return tokenFromMeta || config.csrf || '';
    };

    const appointmentList = document.querySelector('[data-queue-list="1"]') || document.getElementById('appointments-list');
    const appointmentDetails = document.getElementById('appointment-details');
    const sortBySelect = document.getElementById('appointments-sort-by');
    const sortDirButton = document.getElementById('appointments-sort-dir');
    const manualHelp = document.getElementById('manual-order-help');

    const statusModal = document.getElementById('medical-status-modal');
    const statusForm = document.getElementById('medical-status-form');
    const statusAppointmentIdInput = document.getElementById('medical-status-appointment-id');
    const statusSelect = document.getElementById('medical-status-select');
    const statusNoteWrapper = document.getElementById('medical-status-note-wrapper');
    const statusNote = document.getElementById('medical-status-note');
    const statusRescheduleWrapper = document.getElementById('medical-status-reschedule-wrapper');
    const statusRescheduleAt = document.getElementById('medical-status-reschedule-at');
    const statusError = document.getElementById('medical-status-error');
    const statusSubmitButton = document.getElementById('medical-status-submit');

    const formResponseModal = document.getElementById('form-response-modal');
    const formResponseModalDialog = document.getElementById('form-response-modal-dialog');
    const formResponseModalBody = document.getElementById('form-response-modal-body');

    const getAppointmentItems = () => {
        if (!appointmentList) {
            return [];
        }
        return Array.from(appointmentList.querySelectorAll('[data-queue-item="1"][data-appointment-id]'));
    };

    const debugQueueBindings = (stage = 'init') => {
        // eslint-disable-next-line no-console
        console.debug('[medical-appointments] queue bindings', {
            stage,
            listExists: Boolean(appointmentList),
            itemCount: document.querySelectorAll('[data-queue-item="1"]').length,
            handleCount: document.querySelectorAll('[data-queue-handle="1"]').length,
        });
    };

    const getAppointmentItemById = (appointmentId) => {
        if (!appointmentId || !appointmentList) {
            return null;
        }
        return appointmentList.querySelector(`[data-queue-item="1"][data-appointment-id="${appointmentId}"]`);
    };

    const getOrderedIds = () => getAppointmentItems().map((item) => item.dataset.appointmentId).filter(Boolean);

    const ensureListEmptyState = () => {
        if (!appointmentList) {
            return;
        }
        if (getAppointmentItems().length > 0 || appointmentList.querySelector('[data-role="list-empty-state"]')) {
            return;
        }

        const emptyState = document.createElement('div');
        emptyState.setAttribute('data-role', 'list-empty-state');
        emptyState.className = 'px-4 py-10 text-center text-gray-500 dark:text-gray-400';
        emptyState.innerHTML = `
            <span class="mdi mdi-calendar-remove-outline text-4xl text-gray-400 dark:text-gray-500"></span>
            <p class="mt-2 text-sm">Nenhum agendamento para este dia.</p>
        `;
        appointmentList.appendChild(emptyState);
    };

    const removeListEmptyState = () => {
        const emptyState = appointmentList?.querySelector('[data-role="list-empty-state"]');
        if (emptyState) {
            emptyState.remove();
        }
    };

    const syncQueuePositionsFromDom = () => {
        getAppointmentItems().forEach((item, index) => {
            item.dataset.queuePosition = String(index + 1);
        });
    };

    const restoreOrder = (orderedIds) => {
        if (!appointmentList || !Array.isArray(orderedIds) || orderedIds.length === 0) {
            return;
        }

        const itemById = new Map(getAppointmentItems().map((item) => [item.dataset.appointmentId, item]));
        orderedIds.forEach((appointmentId) => {
            const item = itemById.get(appointmentId);
            if (item) {
                appointmentList.appendChild(item);
            }
        });
    };

    const compareItems = (left, right, sortBy, direction) => {
        const factor = direction === 'desc' ? -1 : 1;

        if (sortBy === 'manual') {
            const leftQueue = parseQueuePosition(left.dataset.queuePosition);
            const rightQueue = parseQueuePosition(right.dataset.queuePosition);
            if (leftQueue !== rightQueue) {
                return leftQueue < rightQueue ? -1 : 1;
            }

            const leftTime = parseTimestamp(left.dataset.startAt);
            const rightTime = parseTimestamp(right.dataset.startAt);
            if (leftTime !== rightTime) {
                return leftTime < rightTime ? -1 : 1;
            }

            return normalizeForSort(left.dataset.paciente).localeCompare(normalizeForSort(right.dataset.paciente), 'pt-BR');
        }

        if (sortBy === 'horario') {
            const leftTime = parseTimestamp(left.dataset.startAt);
            const rightTime = parseTimestamp(right.dataset.startAt);
            if (leftTime === rightTime) {
                return factor * normalizeForSort(left.dataset.paciente).localeCompare(normalizeForSort(right.dataset.paciente), 'pt-BR');
            }
            return factor * (leftTime < rightTime ? -1 : 1);
        }

        const leftValue = normalizeForSort(left.dataset[sortBy]);
        const rightValue = normalizeForSort(right.dataset[sortBy]);
        const byText = leftValue.localeCompare(rightValue, 'pt-BR');
        if (byText !== 0) {
            return factor * byText;
        }

        const leftTime = parseTimestamp(left.dataset.startAt);
        const rightTime = parseTimestamp(right.dataset.startAt);
        if (leftTime === rightTime) {
            return 0;
        }
        return factor * (leftTime < rightTime ? -1 : 1);
    };

    const sortListInDom = () => {
        if (!appointmentList) {
            return;
        }

        const sortedItems = [...getAppointmentItems()].sort((left, right) => {
            return compareItems(left, right, state.sortBy, state.sortDirection);
        });
        sortedItems.forEach((item) => appointmentList.appendChild(item));
    };

    const updateSortDirectionButton = () => {
        if (!sortDirButton) {
            return;
        }

        const isManual = state.sortBy === 'manual';
        const isAsc = state.sortDirection === 'asc';
        sortDirButton.disabled = isManual;
        sortDirButton.classList.toggle('opacity-60', isManual);
        sortDirButton.classList.toggle('cursor-not-allowed', isManual);

        if (isManual) {
            sortDirButton.innerHTML = '<i class="mdi mdi-drag mr-1 text-sm"></i>Manual';
            sortDirButton.dataset.direction = 'asc';
            return;
        }

        sortDirButton.innerHTML = isAsc
            ? '<i class="mdi mdi-sort-ascending mr-1 text-sm"></i>Crescente'
            : '<i class="mdi mdi-sort-descending mr-1 text-sm"></i>Decrescente';
        sortDirButton.dataset.direction = state.sortDirection;
    };

    const setManualMode = (enabled) => {
        if (!appointmentList) {
            return;
        }

        appointmentList.classList.toggle('manual-mode', enabled);
        if (manualHelp) {
            manualHelp.textContent = enabled
                ? 'Modo manual ativo: arraste pelos icones para reordenar a fila.'
                : 'Ordenacao automatica ativa. Selecione "Ordem manual" para arrastar a fila.';
        }

        if (!state.sortable) {
            debugQueueBindings('sortable-init');
            state.sortable = Sortable.create(appointmentList, {
                animation: 150,
                handle: '[data-queue-handle="1"]',
                draggable: '[data-queue-item="1"]',
                swapThreshold: 0.65,
                ghostClass: 'queue-ghost',
                chosenClass: 'queue-chosen',
                dragClass: 'queue-drag',
                forceFallback: true,
                fallbackOnBody: true,
                fallbackTolerance: 3,
                filter: 'a, button:not([data-queue-handle="1"]), input, textarea, select, label',
                preventOnFilter: false,
                disabled: !enabled,
                onChoose: (event) => {
                    // eslint-disable-next-line no-console
                    console.debug('[medical-appointments] sortable onChoose', {
                        oldIndex: event.oldIndex,
                        itemId: event.item?.dataset?.appointmentId || null,
                    });
                },
                onStart: () => {
                    state.isDragging = true;
                    state.previousOrder = getOrderedIds();
                    // eslint-disable-next-line no-console
                    console.debug('[medical-appointments] sortable onStart');
                },
                onEnd: async (event) => {
                    state.isDragging = false;
                    // eslint-disable-next-line no-console
                    console.debug('[medical-appointments] sortable onEnd', {
                        oldIndex: event.oldIndex,
                        newIndex: event.newIndex,
                    });

                    if (event.oldIndex === event.newIndex) {
                        return;
                    }

                    const csrfToken = getCsrfToken();
                    if (!csrfToken || !config.reorderUrl) {
                        restoreOrder(state.previousOrder);
                        showAlert({ type: 'error', title: 'Erro', message: 'Nao foi possivel salvar a ordem manual.' });
                        return;
                    }

                    if (state.isSavingOrder) {
                        restoreOrder(state.previousOrder);
                        return;
                    }

                    state.isSavingOrder = true;
                    const orderedIds = getOrderedIds();

                    try {
                        const response = await fetch(config.reorderUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                ordered_ids: orderedIds,
                                current_date: config.currentDate || null,
                            }),
                        });

                        const rawText = await response.text();
                        const data = parseJsonSafely(rawText);
                        if (!response.ok || !data?.success) {
                            throw new Error(extractApiErrorMessage(data, rawText?.trim() || `Erro HTTP ${response.status} ao salvar ordem.`));
                        }

                        syncQueuePositionsFromDom();
                    } catch (error) {
                        restoreOrder(state.previousOrder);
                        syncQueuePositionsFromDom();
                        showAlert({ type: 'error', title: 'Erro', message: error?.message?.trim() || 'Erro ao salvar ordem manual.' });
                    } finally {
                        state.isSavingOrder = false;
                    }
                },
            });
            // eslint-disable-next-line no-console
            console.debug('[medical-appointments] sortable disabled state', {
                mode: enabled ? 'manual-on' : 'manual-off',
                disabled: state.sortable.option('disabled'),
            });
        } else {
            state.sortable.option('disabled', !enabled);
            // eslint-disable-next-line no-console
            console.debug('[medical-appointments] sortable disabled state', {
                mode: enabled ? 'manual-on' : 'manual-off',
                disabled: state.sortable.option('disabled'),
            });
        }

        if (enabled) {
            syncQueuePositionsFromDom();
        } else {
            state.isDragging = false;
        }
    };

    const applyCurrentSort = () => {
        sortListInDom();
        updateSortDirectionButton();
        setManualMode(state.sortBy === 'manual');
    };

    const normalizeFormResponseModalDialog = () => {
        if (!formResponseModalDialog) {
            return;
        }

        ['w-screen', 'h-screen', 'min-h-screen', 'h-full', 'max-w-full', 'max-h-full'].forEach((className) => {
            formResponseModalDialog.classList.remove(className);
        });
        formResponseModalDialog.classList.add('w-full', 'max-w-4xl', 'max-h-[80vh]', 'flex', 'flex-col');
        formResponseModalDialog.style.maxWidth = '56rem';
        formResponseModalDialog.style.maxHeight = '80vh';
    };

    const openFormResponseModal = () => {
        if (!formResponseModal) {
            return;
        }
        normalizeFormResponseModalDialog();
        formResponseModal.classList.remove('hidden');
        formResponseModal.classList.add('flex');
        formResponseModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    };

    const closeFormResponseModal = () => {
        if (!formResponseModal) {
            return;
        }
        formResponseModal.classList.add('hidden');
        formResponseModal.classList.remove('flex');
        formResponseModal.setAttribute('aria-hidden', 'true');
        if (!statusModal || statusModal.classList.contains('hidden')) {
            document.body.classList.remove('overflow-hidden');
        }
    };

    const renderFormResponseLoading = () => {
        if (!formResponseModalBody) {
            return;
        }
        formResponseModalBody.innerHTML = `
            <div class="flex items-center justify-center py-12 text-center text-gray-500 dark:text-gray-400">
                <div>
                    <div class="mx-auto inline-flex h-10 w-10 animate-spin rounded-full border-2 border-gray-200 border-t-blue-500"></div>
                    <p class="mt-3 text-sm">Carregando formulario...</p>
                </div>
            </div>
        `;
    };

    const loadAppointmentDetails = (appointmentId) => {
        if (!appointmentId || !appointmentDetails || !config.detailsUrlTemplate) {
            return;
        }
        if (!UUID_PATTERN.test(appointmentId)) {
            renderError(appointmentDetails, 'ID de agendamento invalido. Atualize a pagina e tente novamente.');
            return;
        }

        state.selectedAppointmentId = appointmentId;
        getAppointmentItems().forEach((item) => item.classList.remove('active-item'));
        const selectedItem = getAppointmentItemById(appointmentId);
        if (selectedItem) {
            selectedItem.classList.add('active-item');
        }

        renderDetailsLoading(appointmentDetails);
        fetch(buildUrl(config.detailsUrlTemplate, appointmentId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html, application/json',
            },
            credentials: 'same-origin',
        })
            .then(async (response) => {
                const contentType = response.headers.get('content-type') || '';

                if (contentType.includes('application/json')) {
                    const data = await response.json();
                    if (!data.success) {
                        throw new Error(data.message || 'Erro ao carregar detalhes.');
                    }
                    return data.html || '';
                }

                if (!response.ok) {
                    const fallback = await response.text();
                    throw new Error(fallback || 'Erro ao carregar detalhes.');
                }
                return response.text();
            })
            .then((html) => {
                appointmentDetails.innerHTML = html;
            })
            .catch((error) => {
                // eslint-disable-next-line no-console
                console.error('Erro ao carregar detalhes:', error);
                renderError(appointmentDetails, error?.message?.trim() || 'Erro ao carregar detalhes do agendamento.');
            });
    };

    const refreshSelectedAppointmentDetails = () => {
        if (state.selectedAppointmentId) {
            loadAppointmentDetails(state.selectedAppointmentId);
        }
    };

    const removeAppointmentFromList = (appointmentId) => {
        const item = getAppointmentItemById(appointmentId);
        if (!item) {
            return;
        }
        item.remove();
        ensureListEmptyState();
    };

    const updateAppointmentListItem = (appointment) => {
        if (!appointment || typeof appointment !== 'object') {
            return;
        }

        const item = getAppointmentItemById(appointment.id);
        if (!item) {
            return;
        }

        const startsAtTime = appointment.starts_at_time || '--:--';
        const patientName = appointment.patient_name || 'N/A';
        const typeName = appointment.type_name || 'Tipo nao informado';
        const status = String(appointment.status || '').toLowerCase();

        item.dataset.startAt = appointment.starts_at_iso || '';
        item.dataset.status = status;
        item.dataset.paciente = normalizeForSort(patientName);
        item.dataset.medico = normalizeForSort(appointment.doctor_name || '');
        item.dataset.tipo = normalizeForSort(typeName);
        if (appointment.queue_position !== null && appointment.queue_position !== undefined) {
            item.dataset.queuePosition = String(appointment.queue_position);
        }

        const titleEl = item.querySelector('[data-role="item-title"]');
        if (titleEl) {
            titleEl.textContent = `${startsAtTime} - ${patientName}`;
        }
        const typeEl = item.querySelector('[data-role="item-type"]');
        if (typeEl) {
            typeEl.textContent = typeName;
        }

        updateBadgeElement(item.querySelector('[data-role="item-status-badge"]'), status, appointment.status_label || '');
        updateLateClass(item);
    };

    const setStatusModalError = (message) => {
        if (!statusError) {
            return;
        }
        if (!message) {
            statusError.classList.add('hidden');
            statusError.textContent = '';
            return;
        }
        statusError.classList.remove('hidden');
        statusError.textContent = message;
    };

    const toggleStatusConditionalFields = () => {
        const selectedStatus = String(statusSelect?.value || '').toLowerCase();
        const noteRequired = statusNeedsNote(selectedStatus);
        const rescheduleRequired = statusNeedsReschedule(selectedStatus);

        if (statusNoteWrapper) {
            statusNoteWrapper.classList.toggle('hidden', !noteRequired);
        }
        if (statusNote) {
            statusNote.required = noteRequired;
            if (!noteRequired) {
                statusNote.value = '';
            }
        }

        if (statusRescheduleWrapper) {
            statusRescheduleWrapper.classList.toggle('hidden', !rescheduleRequired);
        }
        if (statusRescheduleAt) {
            statusRescheduleAt.required = rescheduleRequired;
            if (!rescheduleRequired) {
                statusRescheduleAt.value = '';
            }
        }
    };

    const openStatusModal = ({ appointmentId, currentStatus, currentStartsAt }) => {
        if (!statusModal || !statusForm || !statusAppointmentIdInput || !statusSelect) {
            return;
        }
        if (!appointmentId || !UUID_PATTERN.test(appointmentId)) {
            showAlert({ type: 'error', title: 'Erro', message: 'ID de agendamento invalido.' });
            return;
        }

        statusAppointmentIdInput.value = appointmentId;
        statusSelect.value = String(currentStatus || 'arrived').toLowerCase();
        if (statusNote) {
            statusNote.value = '';
        }
        if (statusRescheduleAt) {
            statusRescheduleAt.value = currentStartsAt || '';
        }

        setStatusModalError('');
        toggleStatusConditionalFields();
        statusModal.classList.remove('hidden');
        statusModal.classList.add('flex');
        statusModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    };

    const closeStatusModal = () => {
        if (!statusModal) {
            return;
        }
        statusModal.classList.add('hidden');
        statusModal.classList.remove('flex');
        statusModal.setAttribute('aria-hidden', 'true');
        if (!formResponseModal || formResponseModal.classList.contains('hidden')) {
            document.body.classList.remove('overflow-hidden');
        }

        if (statusForm) {
            statusForm.reset();
        }
        setStatusModalError('');
        toggleStatusConditionalFields();
    };

    const setStatusSubmitLoading = (loading) => {
        if (!statusSubmitButton) {
            return;
        }
        statusSubmitButton.disabled = loading;
        statusSubmitButton.classList.toggle('opacity-60', loading);
        statusSubmitButton.classList.toggle('cursor-not-allowed', loading);
        if (loading) {
            statusSubmitButton.dataset.originalText = statusSubmitButton.textContent || 'Salvar Status';
            statusSubmitButton.textContent = 'Salvando...';
            return;
        }
        statusSubmitButton.textContent = statusSubmitButton.dataset.originalText || 'Salvar Status';
    };

    const submitStatusChange = async (event) => {
        event.preventDefault();
        if (!statusAppointmentIdInput || !statusSelect || !config.updateStatusUrlTemplate) {
            return;
        }

        const appointmentId = String(statusAppointmentIdInput.value || '').trim();
        const status = String(statusSelect.value || '').toLowerCase();
        const note = String(statusNote?.value || '').trim();
        const rescheduleAt = String(statusRescheduleAt?.value || '').trim();

        if (!UUID_PATTERN.test(appointmentId)) {
            setStatusModalError('ID de agendamento invalido.');
            return;
        }
        if (!status) {
            setStatusModalError('Selecione um status.');
            return;
        }
        if (statusNeedsNote(status) && !note) {
            setStatusModalError('Informe o motivo para o status selecionado.');
            return;
        }
        if (statusNeedsReschedule(status) && !rescheduleAt) {
            setStatusModalError('Informe a nova data e hora para remarcacao.');
            return;
        }

        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            setStatusModalError('Token CSRF nao encontrado. Recarregue a pagina e tente novamente.');
            return;
        }

        setStatusModalError('');
        setStatusSubmitLoading(true);

        try {
            const response = await fetch(buildUrl(config.updateStatusUrlTemplate, appointmentId), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    status,
                    note: note || null,
                    reschedule_at: rescheduleAt || null,
                    current_date: config.currentDate || null,
                }),
            });

            const rawText = await response.text();
            const data = parseJsonSafely(rawText);
            if (!response.ok || !data?.success) {
                if (response.status >= 500 && data?.trace_id) {
                    // eslint-disable-next-line no-console
                    console.error('[medical-appointments] update status failed', {
                        trace_id: data.trace_id,
                        appointment_id: appointmentId,
                        status,
                        response: data,
                    });
                }

                if (response.status === 419) {
                    throw new Error('Sessao expirada (CSRF). Recarregue a pagina e tente novamente.');
                }
                throw new Error(extractApiErrorMessage(data, rawText?.trim() || `Erro HTTP ${response.status} ao atualizar status.`));
            }

            if (data.remove_from_day) {
                removeAppointmentFromList(appointmentId);
                if (state.selectedAppointmentId === appointmentId) {
                    const nextItem = getAppointmentItems()[0];
                    state.selectedAppointmentId = nextItem?.dataset.appointmentId || '';
                    if (state.selectedAppointmentId) {
                        loadAppointmentDetails(state.selectedAppointmentId);
                    } else if (appointmentDetails) {
                        renderNoSelection(appointmentDetails);
                    }
                }
            } else {
                updateAppointmentListItem(data.appointment || {});
                if (state.sortBy !== 'manual') {
                    sortListInDom();
                }
                if (state.selectedAppointmentId === appointmentId) {
                    refreshSelectedAppointmentDetails();
                }
            }

            showAlert({
                type: 'success',
                title: 'Status atualizado',
                message: data.message || 'Status alterado com sucesso.',
            });
            closeStatusModal();
        } catch (error) {
            setStatusModalError(error?.message?.trim() || 'Erro ao atualizar status.');
        } finally {
            setStatusSubmitLoading(false);
        }
    };

    const completeAppointment = (appointmentId) => {
        if (!appointmentId || !config.completeUrlTemplate) {
            return;
        }
        if (!UUID_PATTERN.test(appointmentId)) {
            showAlert({ type: 'error', title: 'Erro', message: 'ID de agendamento invalido.' });
            return;
        }

        confirmAction({
            title: 'Finalizar atendimento',
            message: 'Tem certeza que deseja finalizar este atendimento?',
            confirmText: 'Finalizar',
            cancelText: 'Cancelar',
            type: 'warning',
            onConfirm: () => {
                const csrfToken = getCsrfToken();
                if (!csrfToken) {
                    showAlert({
                        type: 'error',
                        title: 'Sessao expirada',
                        message: 'Token CSRF nao encontrado. Recarregue a pagina e tente novamente.',
                    });
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);

                fetch(buildUrl(config.completeUrlTemplate, appointmentId), {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                })
                    .then((response) => {
                        if (response.redirected) {
                            window.location.href = response.url;
                            return null;
                        }
                        return response.json();
                    })
                    .then((data) => {
                        if (data && !data.success) {
                            showAlert({
                                type: 'error',
                                title: 'Erro',
                                message: `Erro ao finalizar atendimento: ${data.message || 'Erro desconhecido'}`,
                            });
                        }
                    })
                    .catch((error) => {
                        // eslint-disable-next-line no-console
                        console.error('Erro ao finalizar atendimento:', error);
                        showAlert({ type: 'error', title: 'Erro', message: 'Erro ao finalizar atendimento.' });
                    });
            },
        });
    };

    const viewFormResponse = (appointmentId) => {
        if (!appointmentId || !config.formResponseUrlTemplate) {
            return;
        }
        if (!UUID_PATTERN.test(appointmentId)) {
            showAlert({ type: 'error', title: 'Erro', message: 'ID de agendamento invalido.' });
            return;
        }
        if (!formResponseModalBody) {
            return;
        }

        renderFormResponseLoading();
        openFormResponseModal();

        fetch(buildUrl(config.formResponseUrlTemplate, appointmentId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        })
            .then(async (response) => {
                const rawText = await response.text();
                const data = parseJsonSafely(rawText);
                if (!response.ok) {
                    throw new Error(extractApiErrorMessage(data, rawText?.trim() || `Erro HTTP ${response.status} ao carregar formulario.`));
                }
                return data;
            })
            .then((data) => {
                if (data.success) {
                    formResponseModalBody.innerHTML = data.html;
                    return;
                }

                formResponseModalBody.innerHTML = `
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <div class="text-sm text-red-700 dark:text-red-300">
                            ${data.message || 'Erro ao carregar formulario.'}
                        </div>
                    </div>
                `;
            })
            .catch((error) => {
                // eslint-disable-next-line no-console
                console.error('Erro ao carregar formulario:', error);
                formResponseModalBody.innerHTML = `
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <div class="text-sm text-red-700 dark:text-red-300">
                            ${error?.message?.trim() || 'Erro ao carregar formulario. Tente novamente.'}
                        </div>
                    </div>
                `;
            });
    };

    if (sortBySelect) {
        sortBySelect.addEventListener('change', () => {
            const nextSortBy = String(sortBySelect.value || 'manual');
            const wasManual = state.sortBy === 'manual';
            state.sortBy = nextSortBy;

            if (nextSortBy === 'manual') {
                state.sortDirection = 'asc';
                applyCurrentSort();
                return;
            }

            if (wasManual) {
                showAlert({
                    type: 'info',
                    title: 'Ordenacao automatica',
                    message: 'Modo manual desativado. Selecione "Ordem manual" para voltar a arrastar.',
                });
            }

            applyCurrentSort();
        });
    }

    if (sortDirButton) {
        sortDirButton.addEventListener('click', () => {
            if (state.sortBy === 'manual') {
                return;
            }
            state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc';
            applyCurrentSort();
        });
    }

    if (appointmentList) {
        appointmentList.addEventListener('click', (event) => {
            if (state.isDragging) {
                return;
            }
            const detailsTrigger = event.target.closest('[data-open-details="1"][data-appointment-id]');
            if (!detailsTrigger) {
                return;
            }
            event.preventDefault();
            loadAppointmentDetails(detailsTrigger.dataset.appointmentId);
        });
    }

    document.addEventListener('click', (event) => {
        const actionEl = event.target.closest('[data-medical-action]');
        if (actionEl) {
            event.preventDefault();
            event.stopPropagation();

            const action = actionEl.dataset.medicalAction;
            const appointmentId = actionEl.dataset.appointmentId || '';
            if (action === 'open-status-modal') {
                openStatusModal({
                    appointmentId,
                    currentStatus: actionEl.dataset.currentStatus || 'arrived',
                    currentStartsAt: actionEl.dataset.currentStartsAt || '',
                });
            } else if (action === 'complete-appointment') {
                completeAppointment(appointmentId);
            } else if (action === 'view-form-response') {
                viewFormResponse(appointmentId);
            }
        }

        if (event.target.closest('[data-medical-status-close]')) {
            closeStatusModal();
        }
        if (event.target.closest('[data-form-response-modal-close]')) {
            closeFormResponseModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }
        if (statusModal && !statusModal.classList.contains('hidden')) {
            closeStatusModal();
            return;
        }
        if (formResponseModal && !formResponseModal.classList.contains('hidden')) {
            closeFormResponseModal();
        }
    });

    if (statusSelect) {
        statusSelect.addEventListener('change', () => {
            setStatusModalError('');
            toggleStatusConditionalFields();
        });
    }

    if (statusForm) {
        statusForm.addEventListener('submit', submitStatusChange);
    }

    applyCurrentSort();
    getAppointmentItems().forEach((item) => updateLateClass(item));
    removeListEmptyState();
    ensureListEmptyState();

    if (config.initialId) {
        loadAppointmentDetails(config.initialId);
    } else {
        const firstItem = getAppointmentItems()[0];
        if (firstItem) {
            loadAppointmentDetails(firstItem.dataset.appointmentId || '');
        }
    }
}
