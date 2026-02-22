import { applyGridPageSizeSelector } from '../grid/pageSizeSelector';
export function init() {
    if (
        !applyGridPageSizeSelector({
            wrapperSelector: '#online-appointments-grid-wrapper',
            storageKey: 'tenant_online_appointments_page_size',
            defaultLimit: 10,
        })
    ) {
        return;
    }
    bindOnlineAppointmentsIndexRowClick();
}

function bindOnlineAppointmentsIndexRowClick() {
    const grid = document.getElementById('online-appointments-grid');
    if (!grid) {
        return;
    }

    const wrapper = document.getElementById('online-appointments-grid-wrapper');
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
