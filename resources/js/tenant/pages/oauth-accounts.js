export function init() {
    const table = document.getElementById('datatable-list');
    if (!table) {
        return;
    }

    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
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
