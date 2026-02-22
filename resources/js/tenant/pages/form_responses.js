import { applyGridPageSizeSelector } from '../grid/pageSizeSelector';
export function init() {
    if (
        !applyGridPageSizeSelector({
            wrapperSelector: '#responses-grid',
            storageKey: 'tenant_form_responses_page_size',
            defaultLimit: 10,
        })
    ) {
        return;
    }
    bindResponsesIndexRowClick();
}

function bindResponsesIndexRowClick() {
    const container = document.getElementById('responses-grid');
    if (!container) {
        return;
    }

    const linkSelector = container?.dataset?.rowClickLinkSelector || 'a[title="Ver"]';
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
        container.querySelectorAll('tbody tr, .gridjs-tr').forEach((row) => {
            markRowClickable(row);
        });
    };

    updateRows();

    const observer = new MutationObserver(() => {
        updateRows();
    });
    observer.observe(container, { childList: true, subtree: true });

    container.addEventListener('click', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        if (event.target.closest(excludedSelector)) {
            return;
        }

        const row = event.target.closest('tr') || event.target.closest('.gridjs-tr');
        if (!row || !container.contains(row)) {
            return;
        }

        const href = resolveRowHref(row);
        if (!href) {
            return;
        }

        window.location.href = href;
    });
}
