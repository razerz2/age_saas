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

    const appointmentDetails = document.getElementById('appointment-details');

    const loadAppointmentDetails = (appointmentId) => {
        if (!appointmentId || !appointmentDetails || !config.detailsUrlTemplate) {
            return;
        }

        document.querySelectorAll('.appointment-item').forEach((item) => {
            item.classList.remove('active-item');
        });

        const clickedItem = document.querySelector(`[data-appointment-id="${appointmentId}"]`);
        if (clickedItem) {
            clickedItem.classList.add('active-item');
        }

        fetch(config.detailsUrlTemplate.replace('__ID__', appointmentId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html, application/json',
            },
            credentials: 'same-origin',
        })
            .then(async (response) => {
                const contentType = response.headers.get('content-type');

                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (!data.success) {
                        throw new Error(data.message || 'Erro ao carregar detalhes');
                    }
                    return data.html || data;
                }

                if (!response.ok) {
                    const text = await response.text();
                    if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                        const match = text.match(/403|Forbidden|nÃ£o tem permissÃ£o/i);
                        if (match) {
                            throw new Error('VocÃª nÃ£o tem permissÃ£o para visualizar este agendamento.');
                        }
                        throw new Error(
                            'Erro ao carregar detalhes. Verifique se vocÃª tem permissÃ£o para visualizar este agendamento.',
                        );
                    }
                    throw new Error(text || 'Erro ao carregar detalhes');
                }

                return response.text();
            })
            .then((html) => {
                appointmentDetails.innerHTML = html;
            })
            .catch((error) => {
                // eslint-disable-next-line no-console
                console.error('Erro ao carregar detalhes:', error);
                let errorMessage = 'Erro ao carregar detalhes do agendamento.';

                if (error.message) {
                    if (
                        error.message.includes('permissÃ£o') ||
                        error.message.includes('403') ||
                        error.message.includes('Forbidden')
                    ) {
                        errorMessage = 'VocÃª nÃ£o tem permissÃ£o para visualizar este agendamento.';
                    } else if (error.message.includes('404') || error.message.includes('nÃ£o encontrado')) {
                        errorMessage = 'Agendamento nÃ£o encontrado.';
                    } else if (!error.message.includes('<!DOCTYPE') && !error.message.includes('<html')) {
                        errorMessage = error.message;
                    }
                }

                appointmentDetails.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert-circle me-1"></i>
                        ${errorMessage}
                    </div>
                `;
            });
    };

    const updateStatus = (appointmentId, status) => {
        if (!appointmentId || !status || !config.updateStatusUrlTemplate) {
            return;
        }

        confirmAction({
            title: 'Alterar status',
            message: 'Tem certeza que deseja alterar o status?',
            confirmText: 'Alterar',
            cancelText: 'Cancelar',
            type: 'warning',
            onConfirm: () => {
                const formData = new FormData();
                formData.append('status', status);
                formData.append('_token', config.csrf);

                fetch(config.updateStatusUrlTemplate.replace('__ID__', appointmentId), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            showAlert({
                                type: 'error',
                                title: 'Erro',
                                message: `Erro ao atualizar status: ${data.message || 'Erro desconhecido'}`,
                            });
                        }
                    })
                    .catch((error) => {
                        // eslint-disable-next-line no-console
                        console.error('Erro:', error);
                        showAlert({ type: 'error', title: 'Erro', message: 'Erro ao atualizar status' });
                    });
            },
        });
    };

    const completeAppointment = (appointmentId) => {
        if (!appointmentId || !config.completeUrlTemplate) {
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

                fetch(config.completeUrlTemplate.replace('__ID__', appointmentId), {
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
                        console.error('Erro:', error);
                        showAlert({ type: 'error', title: 'Erro', message: 'Erro ao finalizar atendimento' });
                    });
            },
        });
    };

    const viewFormResponse = (appointmentId) => {
        if (!appointmentId || !config.formResponseUrlTemplate) {
            return;
        }

        const modalBody = document.getElementById('form-response-modal-body');
        if (!modalBody) {
            return;
        }

        modalBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2 text-muted">Carregando formulÃ¡rio...</p>
            </div>
        `;

        const modalElement = document.getElementById('form-response-modal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }

        fetch(config.formResponseUrlTemplate.replace('__ID__', appointmentId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    modalBody.innerHTML = data.html;
                } else {
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="mdi mdi-alert-circle me-1"></i>
                            ${data.message || 'Erro ao carregar formulÃ¡rio.'}
                        </div>
                    `;
                }
            })
            .catch((error) => {
                // eslint-disable-next-line no-console
                console.error('Erro:', error);
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert-circle me-1"></i>
                        Erro ao carregar formulÃ¡rio. Tente novamente.
                    </div>
                `;
            });
    };

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
