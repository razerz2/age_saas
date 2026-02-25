function isDeleteForm(form) {
    if (!form || form.tagName !== 'FORM') {
        return false;
    }

    const methodInput = form.querySelector('input[name="_method"]');
    if (!methodInput) {
        return false;
    }

    return String(methodInput.value || '').toUpperCase() === 'DELETE';
}

function inferEntityFromAction(form, fallback = 'registro') {
    if (!form?.action) {
        return fallback;
    }

    try {
        const url = new URL(form.action, window.location.origin);
        const segments = url.pathname.split('/').filter(Boolean);
        if (segments.length < 2) {
            return fallback;
        }

        const maybeId = segments[segments.length - 1];
        if (/^\d+$/.test(maybeId) && segments.length >= 2) {
            return segments[segments.length - 2].replace(/-/g, ' ');
        }

        return maybeId.replace(/-/g, ' ');
    } catch (error) {
        return fallback;
    }
}

function ensureDeleteFormId(form, index) {
    if (form.id) {
        return form.id;
    }

    let moduleName = 'entity';
    let entityId = String(index + 1);

    try {
        const url = new URL(form.action, window.location.origin);
        const segments = url.pathname.split('/').filter(Boolean);
        const maybeId = segments[segments.length - 1];

        if (/^\d+$/.test(maybeId) && segments.length >= 2) {
            entityId = maybeId;
            moduleName = segments[segments.length - 2];
        } else if (segments.length >= 1) {
            moduleName = segments[segments.length - 1];
        }
    } catch (error) {
        // noop
    }

    form.id = `${moduleName}-delete-form-${entityId}`.replace(/[^a-zA-Z0-9-_]/g, '-');
    return form.id;
}

function ensureDeleteTrigger(form, index) {
    const trigger = form.querySelector('button, [role="button"], a');
    if (!trigger) {
        return null;
    }

    const formId = ensureDeleteFormId(form, index);
    const userLabel = form.dataset.userName || `o ${inferEntityFromAction(form)}`;

    if (trigger.tagName === 'BUTTON') {
        trigger.type = 'button';
    }

    trigger.dataset.deleteTrigger = '1';
    trigger.dataset.deleteForm = `#${formId}`;
    trigger.dataset.deleteTitle = trigger.dataset.deleteTitle || 'Confirmar exclusão';
    trigger.dataset.deleteMessage =
        trigger.dataset.deleteMessage ||
        `Tem certeza que deseja excluir ${userLabel}? Esta ação não pode ser desfeita.`;

    if (trigger.classList.contains('table-action-btn')) {
        trigger.classList.add('tenant-action-delete');
    }

    return trigger;
}

function annotateActionButtons() {
    const actionButtons = document.querySelectorAll('.table-action-btn');

    actionButtons.forEach((button) => {
        const title = String(button.getAttribute('title') || '').toLowerCase();

        if (title.startsWith('ver') || title.startsWith('visualizar')) {
            button.classList.add('tenant-action-view');
        } else if (title.startsWith('editar')) {
            button.classList.add('tenant-action-edit');
        } else if (
            title.startsWith('excluir') ||
            title.startsWith('remover') ||
            title.startsWith('desconectar')
        ) {
            button.classList.add('tenant-action-delete');
        }
    });
}

function openDeleteConfirm(form, trigger) {
    if (typeof window.confirmAction !== 'function') {
        return;
    }

    const title = trigger?.dataset?.deleteTitle || 'Confirmar exclusão';
    const message =
        trigger?.dataset?.deleteMessage ||
        `Tem certeza que deseja excluir ${form?.dataset?.userName || 'este registro'}? Esta ação não pode ser desfeita.`;

    window.confirmAction({
        title,
        message,
        type: 'danger',
        confirmText: 'Excluir',
        cancelText: 'Cancelar',
        onConfirm: () => {
            form.dataset.deleteConfirmed = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
            delete form.dataset.deleteConfirmed;
        },
    });
}

function bindDeleteConfirm() {
    document.addEventListener(
        'click',
        (event) => {
            const trigger = event.target.closest('[data-delete-trigger]');
            if (!trigger) {
                return;
            }

            const selector = trigger.dataset.deleteForm;
            const form = selector ? document.querySelector(selector) : trigger.closest('form');
            if (!isDeleteForm(form)) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            openDeleteConfirm(form, trigger);
        },
        true,
    );

    document.addEventListener(
        'submit',
        (event) => {
            const form = event.target.closest('form');
            if (!isDeleteForm(form)) {
                return;
            }

            if (form.dataset.deleteConfirmed === '1') {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            const trigger = ensureDeleteTrigger(form, 0);
            openDeleteConfirm(form, trigger);
        },
        true,
    );
}

export function initTenantDeleteConfirm() {
    if (window.__tenantDeleteConfirmBound === true) {
        return;
    }

    window.__tenantDeleteConfirmBound = true;

    const deleteForms = Array.from(document.querySelectorAll('form')).filter((form) => isDeleteForm(form));
    deleteForms.forEach((form, index) => {
        if (form.hasAttribute('onsubmit') && String(form.getAttribute('onsubmit')).includes('confirm(')) {
            form.removeAttribute('onsubmit');
        }

        ensureDeleteTrigger(form, index);
    });

    annotateActionButtons();
    bindDeleteConfirm();
}

