function initFormsSpecialtySelect() {
    const doctorSelect = document.getElementById('doctor_id');
    const specialtySelect = document.getElementById('specialty_id');

    if (!doctorSelect || !specialtySelect) return;

    const urlTemplate = doctorSelect.dataset.specialtiesUrlTemplate;
    const initialSpecialtyId = specialtySelect.dataset.initialSpecialtyId || '';

    if (!urlTemplate) return;

    const loadSpecialties = (doctorId, selectedSpecialtyId) => {
        specialtySelect.innerHTML = '<option value="">Carregando especialidades...</option>';
        specialtySelect.disabled = true;

        if (!doctorId) {
            specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
            return;
        }

        fetch(urlTemplate.replace('__DOCTOR_ID__', doctorId))
            .then((response) => response.json())
            .then((data) => {
                specialtySelect.innerHTML = '<option value="">Selecione uma especialidade</option>';

                if (data.length === 0) {
                    specialtySelect.innerHTML =
                        '<option value="">Este médico não possui especialidades cadastradas</option>';
                    return;
                }

                data.forEach((specialty) => {
                    const option = document.createElement('option');
                    option.value = specialty.id;
                    option.textContent = specialty.name;
                    if (
                        selectedSpecialtyId &&
                        String(selectedSpecialtyId) === String(specialty.id)
                    ) {
                        option.selected = true;
                    }
                    specialtySelect.appendChild(option);
                });
                specialtySelect.disabled = false;
            })
            .catch((error) => {
                // eslint-disable-next-line no-console
                console.error('Erro ao carregar especialidades:', error);
                specialtySelect.innerHTML = '<option value="">Erro ao carregar especialidades</option>';
            });
    };

    doctorSelect.addEventListener('change', () => {
        loadSpecialties(doctorSelect.value, null);
    });

    if (doctorSelect.value) {
        loadSpecialties(doctorSelect.value, initialSpecialtyId || null);
    } else {
        specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
        specialtySelect.disabled = true;
    }
}

function initFormsPreviewActions() {
    const actionButtons = document.querySelectorAll('[data-form-preview-action]');
    if (!actionButtons.length) return;

    actionButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.dataset.formPreviewAction;
            if (action === 'print') {
                window.print();
            } else if (action === 'close') {
                window.close();
            }
        });
    });
}

function initFormsBuilder() {
    const formBuilder = document.getElementById('form-builder');
    if (!formBuilder) return;

    const tenantSlug = formBuilder.dataset.tenantSlug;
    const formId = formBuilder.dataset.formId;
    const csrfToken = formBuilder.dataset.csrfToken;

    if (!tenantSlug || !formId || !csrfToken) return;

    const alertContainer = document.getElementById('alert-container');

    const showAlert = (message, type = 'success') => {
        if (!alertContainer) return;
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    };

    const toggleHidden = (element, shouldShow) => {
        if (!element) return;
        element.classList.toggle('hidden', !shouldShow);
    };

    const panels = {
        addSection: document.getElementById('addSectionPanel'),
        editSection: document.getElementById('editSectionPanel'),
        addQuestion: document.getElementById('addQuestionPanel'),
        editQuestion: document.getElementById('editQuestionPanel'),
        addOption: document.getElementById('addOptionPanel')
    };

    const hideAllPanels = () => {
        Object.values(panels).forEach((panel) => {
            if (panel) {
                panel.classList.add('hidden');
            }
        });
    };

    const openPanel = (panelKey) => {
        hideAllPanels();
        const panel = panels[panelKey];
        if (panel) {
            panel.classList.remove('hidden');
        }
    };

    hideAllPanels();

    const addSectionForm = document.getElementById('addSectionForm');
    if (addSectionForm) {
        addSectionForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(addSectionForm);
            formData.append('position', document.querySelectorAll('.section-container').length);

            try {
                const response = await fetch(`/workspace/${tenantSlug}/forms/${formId}/sections`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                const data = await response.json();
                if (response.ok) {
                    location.reload();
                } else {
                    showAlert(
                        `Erro ao adicionar seção: ${data.message || 'Erro desconhecido'}`,
                        'danger'
                    );
                }
            } catch (error) {
                showAlert(`Erro ao adicionar seção: ${error.message}`, 'danger');
            }
        });
    }

    document.querySelectorAll('.edit-section-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const sectionId = btn.dataset.sectionId;
            const sectionTitle = btn.dataset.sectionTitle || '';
            const editSectionId = document.getElementById('edit_section_id');
            const editSectionTitle = document.getElementById('edit_section_title');
            if (!editSectionId || !editSectionTitle) return;
            editSectionId.value = sectionId;
            editSectionTitle.value = sectionTitle;
            openPanel('editSection');
        });
    });

    const editSectionForm = document.getElementById('editSectionForm');
    if (editSectionForm) {
        editSectionForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const sectionId = document.getElementById('edit_section_id')?.value;
            const formData = new FormData(editSectionForm);

            try {
                const response = await fetch(`/workspace/${tenantSlug}/sections/${sectionId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        Accept: 'application/json'
                    },
                    body: JSON.stringify({
                        title: formData.get('title')
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    location.reload();
                } else {
                    showAlert(
                        `Erro ao editar seção: ${data.message || 'Erro desconhecido'}`,
                        'danger'
                    );
                }
            } catch (error) {
                showAlert(`Erro ao editar seção: ${error.message}`, 'danger');
            }
        });
    }

    document.querySelectorAll('.delete-section-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const sectionId = btn.dataset.sectionId;
            confirmAction({
                title: 'Deletar seção',
                message:
                    'Tem certeza que deseja deletar esta seção? Todas as perguntas serão movidas para "Perguntas Gerais".',
                confirmText: 'Deletar',
                cancelText: 'Cancelar',
                type: 'error',
                onConfirm: async () => {
                    try {
                        const response = await fetch(
                            `/workspace/${tenantSlug}/sections/${sectionId}`,
                            {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            }
                        );

                        const data = await response.json();
                        if (response.ok) {
                            location.reload();
                        } else {
                            showAlert(
                                `Erro ao deletar seção: ${data.message || 'Erro desconhecido'}`,
                                'danger'
                            );
                        }
                    } catch (error) {
                        showAlert(`Erro ao deletar seção: ${error.message}`, 'danger');
                    }
                }
            });
        });
    });

    document.getElementById('addSectionBtn')?.addEventListener('click', () => {
        openPanel('addSection');
    });

    const addQuestionBtn = document.getElementById('addQuestionBtn');
    if (addQuestionBtn) {
        if (
            document.querySelectorAll('.section-container').length > 0 ||
            document.querySelectorAll('.questions-list').length > 0
        ) {
            addQuestionBtn.disabled = false;
        }

        addQuestionBtn.addEventListener('click', () => {
            const sections = document.querySelectorAll('.section-container');
            const select = document.getElementById('question_section_select');
            const questionForm = document.getElementById('addQuestionForm');
            const optionsContainer = document.getElementById('options-container');
            const optionsList = document.getElementById('options-list');
            const questionType = document.getElementById('question_type');

            if (questionForm) {
                questionForm.reset();
            }

            if (questionType) {
                questionType.value = 'text';
            }

            if (optionsList) {
                optionsList.innerHTML = '';
            }

            if (optionsContainer) {
                toggleHidden(optionsContainer, false);
            }

            if (!select) return;
            if (sections.length === 0) {
                select.value = '';
                select.disabled = true;
            } else {
                select.disabled = false;
            }
            openPanel('addQuestion');
        });
    }

    document.querySelectorAll('[data-builder-cancel]').forEach((btn) => {
        btn.addEventListener('click', () => {
            hideAllPanels();
        });
    });

    const questionType = document.getElementById('question_type');
    if (questionType) {
        questionType.addEventListener('change', () => {
            const optionsContainer = document.getElementById('options-container');
            const optionsList = document.getElementById('options-list');
            if (!optionsContainer || !optionsList) return;
            if (questionType.value === 'single_choice' || questionType.value === 'multi_choice') {
                toggleHidden(optionsContainer, true);
            } else {
                toggleHidden(optionsContainer, false);
                optionsList.innerHTML = '';
            }
        });
    }

    let optionIndex = 0;
    const addOptionBtn = document.getElementById('add-option-btn');
    if (addOptionBtn) {
        addOptionBtn.addEventListener('click', () => {
            const optionsList = document.getElementById('options-list');
            if (!optionsList) return;
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-input-group grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-2';
            optionDiv.innerHTML = `
                <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="options[${optionIndex}][label]" placeholder="Rótulo" required>
                <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="options[${optionIndex}][value]" placeholder="Valor" required>
                <button type="button" class="remove-option-btn inline-flex items-center justify-center gap-1 rounded-md bg-error text-white text-xs font-semibold transition hover:bg-error/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-error/50 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                    <i class="mdi mdi-delete"></i>
                </button>
            `;
            optionsList.appendChild(optionDiv);
            optionIndex += 1;

            optionDiv.querySelector('.remove-option-btn')?.addEventListener('click', () => {
                optionDiv.remove();
            });
        });
    }

    const addQuestionForm = document.getElementById('addQuestionForm');
    if (addQuestionForm) {
        addQuestionForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(addQuestionForm);

            const sectionSelect = document.getElementById('question_section_select');
            const sectionId = sectionSelect ? sectionSelect.value || null : null;

            const options = [];
            document.querySelectorAll('#options-list .option-input-group').forEach((group) => {
                const labelInput = group.querySelector('input[name*="[label]"]');
                const valueInput = group.querySelector('input[name*="[value]"]');
                const label = labelInput?.value;
                const value = valueInput?.value;
                if (label && value) {
                    options.push({ label, value });
                }
            });

            const questionData = {
                section_id: sectionId,
                label: formData.get('label'),
                help_text: formData.get('help_text') || null,
                type: formData.get('type'),
                required: formData.get('required') === '1',
                position: 0
            };

            try {
                const response = await fetch(`/workspace/${tenantSlug}/forms/${formId}/questions`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        Accept: 'application/json'
                    },
                    body: JSON.stringify(questionData)
                });

                const data = await response.json();
                if (response.ok) {
                    const questionId = data.question.id;

                    if (options.length > 0) {
                        for (const option of options) {
                            await fetch(`/workspace/${tenantSlug}/questions/${questionId}/options`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Content-Type': 'application/json',
                                    Accept: 'application/json'
                                },
                                body: JSON.stringify({
                                    label: option.label,
                                    value: option.value,
                                    position: 0
                                })
                            });
                        }
                    }

                    location.reload();
                } else {
                    showAlert(
                        `Erro ao adicionar pergunta: ${data.message || 'Erro desconhecido'}`,
                        'danger'
                    );
                }
            } catch (error) {
                showAlert(`Erro ao adicionar pergunta: ${error.message}`, 'danger');
            }
        });
    }

    document.querySelectorAll('.edit-question-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const questionId = btn.dataset.questionId;

            try {
                const questionItem = btn.closest('.question-item');
                if (!questionItem) return;
                const label = questionItem.querySelector('.question-label')?.textContent || '';
                const type = questionItem.dataset.questionType;
                const required = questionItem.dataset.questionRequired === 'true';
                const sectionId = questionItem.dataset.questionSectionId || '';

                const editQuestionId = document.getElementById('edit_question_id');
                const editQuestionLabel = document.getElementById('edit_question_label');
                const editQuestionSectionSelect = document.getElementById(
                    'edit_question_section_select'
                );
                const editQuestionType = document.getElementById('edit_question_type');
                const editQuestionRequired = document.getElementById('edit_question_required');

                if (
                    !editQuestionId ||
                    !editQuestionLabel ||
                    !editQuestionSectionSelect ||
                    !editQuestionType ||
                    !editQuestionRequired
                ) {
                    return;
                }

                editQuestionId.value = questionId;
                editQuestionLabel.value = label;
                editQuestionSectionSelect.value = sectionId;
                editQuestionType.value = type;
                editQuestionRequired.checked = required;

                const optionsList = document.getElementById('edit-options-list');
                if (optionsList) {
                    optionsList.innerHTML = '';
                    const existingOptions = questionItem.querySelectorAll('.option-item');
                    let editOptionIndex = 0;

                    existingOptions.forEach((opt) => {
                        const optionDiv = document.createElement('div');
                        optionDiv.className = 'option-input-group grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-2';
                        const text = opt.textContent.trim();
                        optionDiv.innerHTML = `
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="edit_options[${editOptionIndex}][label]" value="${text}" placeholder="Rótulo" required>
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="edit_options[${editOptionIndex}][value]" value="${text
                                .toLowerCase()
                                .replace(/\\s+/g, '_')}" placeholder="Valor" required>
                            <button type="button" class="remove-option-btn inline-flex items-center justify-center gap-1 rounded-md bg-error text-white text-xs font-semibold transition hover:bg-error/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-error/50 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        `;
                        optionsList.appendChild(optionDiv);
                        editOptionIndex += 1;

                        optionDiv.querySelector('.remove-option-btn')?.addEventListener('click', () => {
                            optionDiv.remove();
                        });
                    });
                }

                const editOptionsContainer = document.getElementById('edit-options-container');
                if (editOptionsContainer) {
                    if (type === 'single_choice' || type === 'multi_choice') {
                        toggleHidden(editOptionsContainer, true);
                    } else {
                        toggleHidden(editOptionsContainer, false);
                    }
                }

                openPanel('editQuestion');
            } catch (error) {
                showAlert(`Erro ao carregar pergunta: ${error.message}`, 'danger');
            }
        });
    });

    document.querySelectorAll('.delete-question-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const questionId = btn.dataset.questionId;
            confirmAction({
                title: 'Deletar pergunta',
                message: 'Tem certeza que deseja deletar esta pergunta?',
                confirmText: 'Deletar',
                cancelText: 'Cancelar',
                type: 'error',
                onConfirm: async () => {
                    try {
                        const response = await fetch(
                            `/workspace/${tenantSlug}/questions/${questionId}`,
                            {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            }
                        );

                        const data = await response.json();
                        if (response.ok) {
                            location.reload();
                        } else {
                            showAlert(
                                `Erro ao deletar pergunta: ${data.message || 'Erro desconhecido'}`,
                                'danger'
                            );
                        }
                    } catch (error) {
                        showAlert(`Erro ao deletar pergunta: ${error.message}`, 'danger');
                    }
                }
            });
        });
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.add-option-to-question-btn');
        if (!btn) return;
        const questionId = btn.dataset.questionId;
        const optionQuestionId = document.getElementById('option_question_id');
        if (!optionQuestionId) return;
        optionQuestionId.value = questionId;
        openPanel('addOption');
    });

    const addOptionForm = document.getElementById('addOptionForm');
    if (addOptionForm) {
        addOptionForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const questionId = document.getElementById('option_question_id')?.value;
            const formData = new FormData(addOptionForm);

            try {
                const response = await fetch(`/workspace/${tenantSlug}/questions/${questionId}/options`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        Accept: 'application/json'
                    },
                    body: JSON.stringify({
                        label: formData.get('label'),
                        value: formData.get('value'),
                        position: 0
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    hideAllPanels();
                    location.reload();
                } else {
                    showAlert(
                        `Erro ao adicionar opção: ${data.message || 'Erro desconhecido'}`,
                        'danger'
                    );
                }
            } catch (error) {
                showAlert(`Erro ao adicionar opção: ${error.message}`, 'danger');
            }
        });
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-option-btn');
        if (!btn) return;
        const optionId = btn.dataset.optionId;
        confirmAction({
            title: 'Deletar opção',
            message: 'Tem certeza que deseja deletar esta opção?',
            confirmText: 'Deletar',
            cancelText: 'Cancelar',
            type: 'error',
            onConfirm: () => {
                fetch(`/workspace/${tenantSlug}/options/${optionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.message) {
                            location.reload();
                        } else {
                            showAlert(
                                `Erro ao deletar opção: ${data.message || 'Erro desconhecido'}`,
                                'danger'
                            );
                        }
                    })
                    .catch((error) => {
                        showAlert(`Erro ao deletar opção: ${error.message}`, 'danger');
                    });
            }
        });
    });

    const editQuestionForm = document.getElementById('editQuestionForm');
    if (editQuestionForm) {
        editQuestionForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const questionId = document.getElementById('edit_question_id')?.value;
            const formData = new FormData(editQuestionForm);

            const questionData = {
                section_id: formData.get('section_id') || null,
                label: formData.get('label'),
                help_text: formData.get('help_text') || null,
                type: formData.get('type'),
                required: formData.get('required') === '1',
                position: 0
            };

            try {
                const response = await fetch(`/workspace/${tenantSlug}/questions/${questionId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        Accept: 'application/json'
                    },
                    body: JSON.stringify(questionData)
                });

                const data = await response.json();
                if (response.ok) {
                    location.reload();
                } else {
                    showAlert(
                        `Erro ao editar pergunta: ${data.message || 'Erro desconhecido'}`,
                        'danger'
                    );
                }
            } catch (error) {
                showAlert(`Erro ao editar pergunta: ${error.message}`, 'danger');
            }
        });
    }

    const editQuestionType = document.getElementById('edit_question_type');
    if (editQuestionType) {
        editQuestionType.addEventListener('change', () => {
            const editOptionsContainer = document.getElementById('edit-options-container');
            if (!editOptionsContainer) return;
            if (
                editQuestionType.value === 'single_choice' ||
                editQuestionType.value === 'multi_choice'
            ) {
                toggleHidden(editOptionsContainer, true);
            } else {
                toggleHidden(editOptionsContainer, false);
            }
        });
    }
}

export function init() {
    bindFormsIndexRowClick();
    initFormsSpecialtySelect();
    initFormsPreviewActions();
    initFormsBuilder();
}

function bindFormsIndexRowClick() {
    const grid = document.getElementById('forms-grid');
    if (!grid) {
        return;
    }

    const wrapper = document.getElementById('forms-grid-wrapper');
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
