export function initTenantWhatsAppSettings() {
    const driverSelect = document.getElementById('whatsapp_driver');
    const tenancyConfig = document.getElementById('whatsapp_tenancy_config');

    if (!driverSelect || !tenancyConfig) {
        return;
    }

    const showElement = (element, show) => {
        if (!element) return;
        element.style.display = show ? '' : 'none';
        element.classList.toggle('d-none', !show);
    };

    const updateDriverVisibility = () => {
        showElement(tenancyConfig, driverSelect.value === 'tenancy');
    };

    const updateProviderVisibility = () => {
        const providerSelect = document.getElementById('whatsapp-provider-select');
        if (!providerSelect) return;

        const selectedProvider = providerSelect.value;
        const sections = document.querySelectorAll('.whatsapp-provider-section');
        sections.forEach((section) => {
            const sectionProvider = section.getAttribute('data-provider');
            showElement(section, sectionProvider === selectedProvider);
        });
    };

    const updateBadge = (badge, state, successText, errorText) => {
        if (!badge) return;

        badge.classList.remove('d-none', 'bg-secondary', 'bg-success', 'bg-danger');

        if (state === 'loading') {
            badge.classList.add('bg-secondary');
            badge.textContent = 'Testando...';
            return;
        }

        if (state === 'sending') {
            badge.classList.add('bg-secondary');
            badge.textContent = 'Enviando...';
            return;
        }

        if (state === 'ok') {
            badge.classList.add('bg-success');
            badge.textContent = successText;
            return;
        }

        badge.classList.add('bg-danger');
        badge.textContent = errorText;
    };

    const parseResponse = async (response) => {
        try {
            return await response.json();
        } catch (error) {
            return { status: 'ERROR', message: 'Resposta invalida do servidor.' };
        }
    };

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    const setupConnectionTest = (buttonId, badgeId, messageId) => {
        const button = document.getElementById(buttonId);
        if (!button) return;

        button.addEventListener('click', async (event) => {
            event.preventDefault();

            const url = button.getAttribute('data-test-url');
            if (!url) return;

            const badge = document.getElementById(badgeId);
            const message = document.getElementById(messageId);

            updateBadge(badge, 'loading');
            if (message) message.textContent = '';

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });

                const data = await parseResponse(response);
                const status = String(data?.status ?? 'ERROR').toUpperCase();
                const ok = status === 'OK' || status === 'SUCCESS' || status === 'WORKING';

                updateBadge(badge, ok ? 'ok' : 'error', 'Conectado', 'Erro');
                if (message) {
                    message.textContent =
                        data?.message ||
                        (ok
                            ? 'Conexao realizada com sucesso.'
                            : 'Falha ao testar conexao. Verifique as configuracoes.');
                }
            } catch (error) {
                updateBadge(badge, 'error', 'Conectado', 'Erro');
                if (message) {
                    message.textContent = 'Erro ao comunicar com o servidor. Tente novamente.';
                }
            }
        });
    };

    const setupSendToggle = (buttonId, formId) => {
        const button = document.getElementById(buttonId);
        const form = document.getElementById(formId);

        if (!button || !form) return;

        button.addEventListener('click', (event) => {
            event.preventDefault();
            const shouldShow = form.classList.contains('d-none');
            showElement(form, shouldShow);
        });
    };

    const setupSendTest = (buttonId, numberId, messageInputId, badgeId, messageId) => {
        const button = document.getElementById(buttonId);
        if (!button) return;

        button.addEventListener('click', async (event) => {
            event.preventDefault();

            const url = button.getAttribute('data-send-url');
            if (!url) return;

            const numberInput = document.getElementById(numberId);
            const messageInput = document.getElementById(messageInputId);
            const badge = document.getElementById(badgeId);
            const messageLabel = document.getElementById(messageId);

            const number = numberInput ? numberInput.value.trim() : '';
            const text = messageInput ? messageInput.value.trim() : '';

            if (!number || !text) {
                if (messageLabel) {
                    messageLabel.textContent = 'Preencha o numero de destino e a mensagem para enviar o teste.';
                }
                return;
            }

            updateBadge(badge, 'sending');
            if (messageLabel) messageLabel.textContent = '';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        number,
                        message: text,
                    }),
                });

                const data = await parseResponse(response);
                const status = String(data?.status ?? 'ERROR').toUpperCase();
                const ok = status === 'OK' || status === 'SUCCESS' || status === 'WORKING';

                updateBadge(badge, ok ? 'ok' : 'error', 'Enviado', 'Erro');
                if (messageLabel) {
                    messageLabel.textContent =
                        data?.message ||
                        (ok
                            ? 'Mensagem enviada com sucesso.'
                            : 'Falha ao enviar mensagem de teste. Verifique as configuracoes.');
                }
            } catch (error) {
                updateBadge(badge, 'error', 'Enviado', 'Erro');
                if (messageLabel) {
                    messageLabel.textContent = 'Erro ao comunicar com o servidor. Tente novamente.';
                }
            }
        });
    };

    driverSelect.addEventListener('change', updateDriverVisibility);

    const providerSelect = document.getElementById('whatsapp-provider-select');
    if (providerSelect) {
        providerSelect.addEventListener('change', updateProviderVisibility);
    }

    setupConnectionTest('btn-test-meta', 'meta-test-badge', 'meta-test-message');
    setupConnectionTest('btn-test-zapi', 'zapi-test-badge', 'zapi-test-message');
    setupConnectionTest('btn-test-waha', 'waha-test-badge', 'waha-test-message');

    setupSendToggle('btn-toggle-meta-send', 'meta-send-form');
    setupSendToggle('btn-toggle-zapi-send', 'zapi-send-form');
    setupSendToggle('btn-toggle-waha-send', 'waha-send-form');

    setupSendTest('btn-send-meta-test', 'meta-test-number', 'meta-test-message-input', 'meta-send-badge', 'meta-send-message');
    setupSendTest('btn-send-zapi-test', 'zapi-test-number', 'zapi-test-message-input', 'zapi-send-badge', 'zapi-send-message');
    setupSendTest('btn-send-waha-test', 'waha-test-number', 'waha-test-message-input', 'waha-send-badge', 'waha-send-message');

    tenancyConfig.querySelectorAll('.d-none').forEach((element) => {
        element.style.display = 'none';
    });

    updateDriverVisibility();
    updateProviderVisibility();
}
