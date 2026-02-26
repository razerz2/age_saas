import { copyToClipboard } from '../utils/copyToClipboard';

function showCopySuccess(button) {
    if (typeof window.showAlert === 'function') {
        window.showAlert({
            type: 'success',
            title: 'Copiado!',
            message: 'Variável copiada para a área de transferência.',
        });
        return;
    }

    indicateCopied(button);
}

function showCopyError() {
    if (typeof window.showAlert === 'function') {
        window.showAlert({
            type: 'error',
            title: 'Erro',
            message: 'Não foi possível copiar a variável.',
        });
    }
}

function indicateCopied(button) {
    if (!button || button.dataset.copyBusy === '1') {
        return;
    }

    const originalHtml = button.innerHTML;
    button.dataset.copyBusy = '1';
    button.title = 'Copiado!';
    button.setAttribute('aria-label', 'Copiado!');
    button.innerHTML =
        '<svg class="h-3.5 w-3.5 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>';

    window.setTimeout(() => {
        button.innerHTML = originalHtml;
        button.title = 'Copiar variável';
        button.setAttribute('aria-label', 'Copiar variável');
        delete button.dataset.copyBusy;
    }, 800);
}

function setModalTriggerState(modalId, isExpanded) {
    if (!modalId) {
        return;
    }

    const triggers = document.querySelectorAll(`[data-variables-modal-open="${modalId}"]`);
    triggers.forEach((trigger) => {
        trigger.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        trigger.classList.toggle('bg-blue-50', isExpanded);
        trigger.classList.toggle('dark:bg-blue-900/20', isExpanded);
    });
}

function openVariablesModal(modal) {
    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    setModalTriggerState(modal.id, true);
}

function closeVariablesModal(modal) {
    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
    setModalTriggerState(modal.id, false);
}

function bindVariableCopy() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('.js-copy-template-variable');
        if (!button) {
            return;
        }

        const variable = button.dataset.copyVariable || '';
        if (!variable) {
            return;
        }

        copyToClipboard(variable)
            .then(() => showCopySuccess(button))
            .catch(() => showCopyError());
    });
}

function bindVariablesModal() {
    document.addEventListener('click', (event) => {
        const openTrigger = event.target.closest('[data-variables-modal-open]');
        if (openTrigger) {
            const modalId = openTrigger.dataset.variablesModalOpen;
            const modal = modalId ? document.getElementById(modalId) : null;
            openVariablesModal(modal);
            return;
        }

        const closeTrigger = event.target.closest('[data-variables-modal-close]');
        if (closeTrigger) {
            const modal = closeTrigger.closest('.js-variables-modal');
            closeVariablesModal(modal);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        const openedModal = document.querySelector('.js-variables-modal:not(.hidden)');
        if (openedModal) {
            closeVariablesModal(openedModal);
        }
    });
}

export function initTemplateVariablesUi() {
    if (window.__templateVariablesUiBound) {
        return;
    }

    window.__templateVariablesUiBound = true;
    bindVariableCopy();
    bindVariablesModal();
}
