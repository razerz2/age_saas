const UUID_PATTERN = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

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

function extractApiErrorMessage(payload, fallback = 'Erro ao atualizar status.') {
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
        csrf: configEl.dataset.csrf || '',
        initialId: configEl.dataset.initialId || '',
    };

    const getCsrfToken = () => {
        const tokenFromMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        return tokenFromMeta || config.csrf || '';
    };

    const appointmentDetails = document.getElementById('appointment-details');
    const formResponseModal = document.getElementById('form-response-modal');
    const formResponseModalDialog = document.getElementById('form-response-modal-dialog');
    const formResponseModalBody = document.getElementById('form-response-modal-body');

    const normalizeFormResponseModalDialog = () => {
        if (!formResponseModalDialog) {
            return;
        }

        [
            'w-screen',
            'h-screen',
            'min-h-screen',
            'h-full',
            'max-w-full',
            'max-h-full',
        ].forEach((className) => formResponseModalDialog.classList.remove(className));

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
        document.body.classList.remove('overflow-hidden');
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

        document.querySelectorAll('.appointment-item').forEach((item) => {
            item.classList.remove('active-item');
        });

        const clickedItem = document.querySelector(`[data-appointment-id="${appointmentId}"]`);
        if (clickedItem) {
            clickedItem.classList.add('active-item');
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
                const message = error?.message?.trim() || 'Erro ao carregar detalhes do agendamento.';
                renderError(appointmentDetails, message);
            });
    };

    const updateStatus = (appointmentId, status) => {
        if (!appointmentId || !status || !config.updateStatusUrlTemplate) {
            return;
        }

        if (!UUID_PATTERN.test(appointmentId)) {
            showAlert({ type: 'error', title: 'Erro', message: 'ID de agendamento invalido.' });
            return;
        }

        confirmAction({
            title: 'Alterar status',
            message: 'Tem certeza que deseja alterar o status?',
            confirmText: 'Alterar',
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

                fetch(buildUrl(config.updateStatusUrlTemplate, appointmentId), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status }),
                })
                    .then(async (response) => {
                        const statusCode = response.status;
                        const rawText = await response.text();
                        let data = null;

                        try {
                            data = rawText ? JSON.parse(rawText) : null;
                        } catch (error) {
                            data = null;
                        }

                        // Debug temporario para diagnosticar contrato frontend/backend
                        // eslint-disable-next-line no-console
                        console.info('updateStatus response', { statusCode, rawText, data });

                        if (!response.ok) {
                            if (response.status === 419) {
                                throw new Error('Sessao expirada (CSRF). Recarregue a pagina e tente novamente.');
                            }

                            throw new Error(
                                extractApiErrorMessage(
                                    data,
                                    rawText?.trim() || `Erro HTTP ${statusCode} ao atualizar status.`,
                                ),
                            );
                        }

                        return data || {};
                    })
                    .then((data) => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            const detailedMessage = extractApiErrorMessage(data, 'Erro ao atualizar status.');
                            showAlert({
                                type: 'error',
                                title: 'Erro',
                                message: detailedMessage,
                            });
                        }
                    })
                    .catch((error) => {
                        // eslint-disable-next-line no-console
                        console.error('Erro ao atualizar status:', error);
                        showAlert({
                            type: 'error',
                            title: 'Erro',
                            message: error?.message?.trim() || 'Erro ao atualizar status.',
                        });
                    });
            },
        });
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
                const formData = new FormData();
                formData.append('_token', config.csrf);

                fetch(buildUrl(config.completeUrlTemplate, appointmentId), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
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
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    formResponseModalBody.innerHTML = data.html;
                } else {
                    formResponseModalBody.innerHTML = `
                        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                            <div class="text-sm text-red-700 dark:text-red-300">
                                ${data.message || 'Erro ao carregar formulario.'}
                            </div>
                        </div>
                    `;
                }
            })
            .catch((error) => {
                // eslint-disable-next-line no-console
                console.error('Erro ao carregar formulario:', error);
                formResponseModalBody.innerHTML = `
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <div class="text-sm text-red-700 dark:text-red-300">
                            Erro ao carregar formulario. Tente novamente.
                        </div>
                    </div>
                `;
            });
    };

    if (formResponseModal) {
        formResponseModal.querySelectorAll('[data-form-response-modal-close]').forEach((trigger) => {
            trigger.addEventListener('click', closeFormResponseModal);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !formResponseModal.classList.contains('hidden')) {
                closeFormResponseModal();
            }
        });
    }

    const appointmentList = document.getElementById('appointments-list');
    if (appointmentList) {
        appointmentList.addEventListener('click', (event) => {
            const item = event.target.closest('[data-appointment-id]');
            if (!item) {
                return;
            }

            event.preventDefault();
            loadAppointmentDetails(item.dataset.appointmentId);
        });
    }

    document.addEventListener('click', (event) => {
        const actionEl = event.target.closest('[data-medical-action]');
        if (!actionEl) {
            return;
        }

        const action = actionEl.dataset.medicalAction;
        const appointmentId = actionEl.dataset.appointmentId || '';

        if (action === 'update-status') {
            updateStatus(appointmentId, actionEl.dataset.status || '');
        } else if (action === 'complete-appointment') {
            completeAppointment(appointmentId);
        } else if (action === 'view-form-response') {
            viewFormResponse(appointmentId);
        }
    });

    if (config.initialId) {
        loadAppointmentDetails(config.initialId);
    }
}
