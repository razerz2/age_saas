
document.addEventListener('DOMContentLoaded', () => {
    const page = document.body?.dataset?.page;
    if (!page) return;

    import(`./pages/${page}.js`)
        .then((module) => {
            if (typeof module.init === 'function') {
                module.init();
            }
        })
        .catch(() => {
            // Falha silenciosa se a página não tiver módulo dedicado.
        });
});

