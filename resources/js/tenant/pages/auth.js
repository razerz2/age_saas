function initLoginCsrfRefresh() {
    const form = document.getElementById('login-form');
    if (!form) return;
    const refreshUrl = form.dataset.refreshUrl;
    if (!refreshUrl) return;

    setInterval(() => {
        fetch(refreshUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html'
            },
            credentials: 'same-origin'
        })
            .then((response) => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error('Resposta não OK');
            })
            .then((html) => {
                try {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newToken = doc.querySelector('input[name="_token"]')?.value;
                    if (!newToken) return;

                    const formToken = document.querySelector('#login-form input[name="_token"]');
                    if (formToken && formToken.value !== newToken) {
                        formToken.value = newToken;
                    }
                } catch (error) {
                    // ignore parse errors
                }
            })
            .catch(() => {
                // ignore errors silently
            });
    }, 4 * 60 * 1000);
}

function initTwoFactorInput() {
    const codeInput = document.getElementById('code');
    if (!codeInput) return;

    codeInput.addEventListener('input', () => {
        codeInput.value = codeInput.value.replace(/[^0-9]/g, '');
        if (codeInput.value.length === 6) {
            // Optional auto-submit: codeInput.form?.submit();
        }
    });
}

export function init() {
    initLoginCsrfRefresh();
    initTwoFactorInput();
}
