
document.addEventListener('DOMContentLoaded', () => {
    const page = document.body?.dataset?.page;
    if (!page) return;

    // eslint-disable-next-line no-console
    console.log('[tenant/app] data-page:', page);

    // Use a glob so Vite includes all page entrypoints in the build output.
    const pages = import.meta.glob('./pages/*.js');
    const key = `./pages/${page}.js`;
    const loader = pages[key];

    if (!loader) {
        // eslint-disable-next-line no-console
        console.error('[tenant/app] unknown page module', key);
        return;
    }

    loader()
        .then((module) => {
            if (typeof module.init === 'function') {
                module.init();
            }
        })
        .catch((err) => {
            // eslint-disable-next-line no-console
            console.error('[tenant/app] failed to load page module', page, err);
        });
});
