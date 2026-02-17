export function init() {
    initDataTable();
    bindDeleteConfirm();
}

function initDataTable() {
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
        const table = document.getElementById('datatable-list');
        if (!table) {
            return;
        }
        window.jQuery(table).DataTable({
            pageLength: 25,
            responsive: true,
            autoWidth: false,
            scrollX: false,
            scrollCollapse: false,
            pagingType: 'simple_numbers',
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json',
            },
        });
    }
}

function bindDeleteConfirm() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm-delete=\"true\"]');
        if (!form) {
            return;
        }
        event.preventDefault();
        const formName = form.dataset.formName || 'N/A';
        const patientName = form.dataset.patientName || 'N/A';

        confirmAction({
            title: 'Excluir resposta',
            message: `Tem certeza que deseja excluir a resposta do formulário \"${formName}\" do paciente \"${patientName}\"?\n\nEsta ação não pode ser desfeita e irá remover:\n- A resposta do formulário\n- Todas as respostas das perguntas relacionadas`,
            confirmText: 'Excluir',
            cancelText: 'Cancelar',
            type: 'error',
            onConfirm: () => form.submit(),
        });
    });
}
