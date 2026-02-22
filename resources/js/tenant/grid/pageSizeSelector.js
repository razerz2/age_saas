export function applyGridPageSizeSelector({
    wrapperSelector,
    storageKey,
    allowed = [10, 25, 50, 100],
    defaultLimit = 10,
    queryParam = 'limit',
}) {
    if (!wrapperSelector || !storageKey) {
        return true;
    }

    const wrapper = document.querySelector(wrapperSelector);
    if (!wrapper) {
        return true;
    }

    const parseAllowedLimit = (value, fallback = null) => {
        const parsed = Number.parseInt(String(value), 10);
        return allowed.includes(parsed) ? parsed : fallback;
    };

    const getStoredLimit = () => {
        try {
            return parseAllowedLimit(localStorage.getItem(storageKey));
        } catch (error) {
            return null;
        }
    };

    const setStoredLimit = (limit) => {
        try {
            localStorage.setItem(storageKey, String(limit));
        } catch (error) {
            // noop
        }
    };

    const currentUrl = new URL(window.location.href);
    const urlLimit = parseAllowedLimit(currentUrl.searchParams.get(queryParam));
    const storedLimit = getStoredLimit();

    if (!urlLimit && storedLimit) {
        currentUrl.searchParams.set(queryParam, String(storedLimit));
        window.location.replace(currentUrl.toString());
        return false;
    }

    let currentLimit = urlLimit || storedLimit || defaultLimit;
    currentLimit = parseAllowedLimit(currentLimit, defaultLimit);

    const injectSelector = () => {
        const footer = wrapper.querySelector('.gridjs-footer');
        const pagination = footer?.querySelector('.gridjs-pagination');
        const summary = pagination?.querySelector('.gridjs-summary');
        const pages = pagination?.querySelector('.gridjs-pages');
        if (!footer || !pagination || !summary || !pages) {
            return false;
        }

        const existingBlock = pagination.querySelector('[data-page-size="1"]');
        if (existingBlock) {
            const existingSelect = existingBlock.querySelector('select');
            if (existingSelect && existingSelect.value !== String(currentLimit)) {
                existingSelect.value = String(currentLimit);
            }
            return true;
        }

        const sizeWrapper = document.createElement('div');
        sizeWrapper.setAttribute('data-page-size', '1');
        sizeWrapper.className = 'inline-flex items-center gap-2';

        const prefix = document.createElement('span');
        prefix.className = 'text-sm text-gray-600 dark:text-gray-300';
        prefix.textContent = 'Exibir';

        const select = document.createElement('select');
        select.className =
            'appearance-none pr-9 pl-3 h-9 leading-9 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200';

        allowed.forEach((size) => {
            const option = document.createElement('option');
            option.value = String(size);
            option.textContent = String(size);
            if (size === currentLimit) {
                option.selected = true;
            }
            select.appendChild(option);
        });

        const suffix = document.createElement('span');
        suffix.className = 'text-sm text-gray-600 dark:text-gray-300';
        suffix.textContent = 'por página';

        select.addEventListener('change', () => {
            const nextLimit = parseAllowedLimit(select.value, currentLimit);
            if (!nextLimit) {
                return;
            }

            currentLimit = nextLimit;
            setStoredLimit(nextLimit);

            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set(queryParam, String(nextLimit));
            window.location.assign(nextUrl.toString());
        });

        sizeWrapper.appendChild(prefix);
        sizeWrapper.appendChild(select);
        sizeWrapper.appendChild(suffix);

        let summaryGroup = pagination.querySelector('[data-footer-summary-group="1"]');
        if (!summaryGroup) {
            summaryGroup = document.createElement('div');
            summaryGroup.setAttribute('data-footer-summary-group', '1');
            summaryGroup.className = 'flex flex-wrap items-center gap-3';
            pagination.insertBefore(summaryGroup, summary);
            summaryGroup.appendChild(summary);
        }

        summaryGroup.insertBefore(sizeWrapper, summary);
        return true;
    };

    let attempts = 0;
    const maxAttempts = 20;
    const interval = window.setInterval(() => {
        attempts += 1;
        const mounted = injectSelector();
        if (mounted || attempts >= maxAttempts) {
            window.clearInterval(interval);
        }
    }, 150);

    const scheduleInjection = () => window.setTimeout(injectSelector, 0);
    wrapper.addEventListener('click', scheduleInjection);
    wrapper.addEventListener('input', scheduleInjection);

    return true;
}
