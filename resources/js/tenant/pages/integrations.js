function initDataTable() {
    if (!window.jQuery) return;
    const table = window.jQuery('#datatable-list');
    if (!table.length || !table.DataTable) return;

    table.DataTable({
        pageLength: 25,
        responsive: true,
        autoWidth: false,
        scrollX: false,
        scrollCollapse: false,
        pagingType: 'simple_numbers',
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json'
        }
    });
}

function initTooltips() {
    if (!window.bootstrap?.Tooltip) return;
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.forEach((trigger) => {
        new window.bootstrap.Tooltip(trigger);
    });
}

function initAlertDismiss() {
    document.querySelectorAll('[data-dismiss="alert"]').forEach((button) => {
        button.addEventListener('click', () => {
            const wrapper = button.closest('div');
            if (wrapper) {
                wrapper.remove();
            }
        });
    });
}

function initConfirmForms() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            if (typeof confirmAction !== 'function') {
                form.submit();
                return;
            }

            confirmAction({
                title: form.dataset.confirmTitle || 'Confirmar ação',
                message: form.dataset.confirmMessage || 'Tem certeza que deseja continuar?',
                confirmText: form.dataset.confirmConfirmText || 'Confirmar',
                cancelText: form.dataset.confirmCancelText || 'Cancelar',
                type: form.dataset.confirmType || 'warning',
                onConfirm: () => form.submit()
            });
        });
    });
}

export function init() {
    initDataTable();
    initTooltips();
    initAlertDismiss();
    initConfirmForms();
}
