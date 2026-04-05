export function initTenantWhatsAppSettings() {
    const driverSelect = document.getElementById('whatsapp_driver');
    const tenancyConfig = document.getElementById('whatsapp_tenancy_config');
    const globalConfig = document.getElementById('whatsapp_global_config');

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
        showElement(globalConfig, driverSelect.value === 'global');
        updateBotWebhookPreview();
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

    const getCsrfToken = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }
        const input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    };

    const normalizeProviderValue = (value) => {
        const normalized = String(value ?? '')
            .trim()
            .toLowerCase();

        if (!normalized) {
            return '';
        }

        if (
            normalized === 'whatsapp_business' ||
            normalized === 'whatsapp-business' ||
            normalized === 'meta' ||
            normalized === 'official' ||
            normalized === 'oficial'
        ) {
            return 'whatsapp_business';
        }

        if (normalized.includes('zapi') || normalized.includes('z-api')) {
            return 'zapi';
        }

        if (normalized.includes('waha')) {
            return 'waha';
        }

        if (normalized.includes('evolution') || normalized.includes('evo')) {
            return 'evolution';
        }

        if (
            normalized.includes('meta') ||
            normalized.includes('official') ||
            normalized.includes('oficial') ||
            normalized.includes('business')
        ) {
            return 'whatsapp_business';
        }

        return '';
    };

    const providerLabel = (provider) => {
        if (provider === 'zapi') return 'Z-API';
        if (provider === 'waha') return 'WAHA';
        if (provider === 'evolution') return 'Evolution API';
        return 'WhatsApp Business (Meta)';
    };

    const resolveSharedProvider = () => {
        const webhookPreview = document.getElementById('bot_webhook_preview');
        const fallbackProvider =
            normalizeProviderValue(webhookPreview?.dataset.defaultProvider || '') || 'whatsapp_business';

        const driver = String(driverSelect?.value || 'global')
            .trim()
            .toLowerCase();

        if (driver === 'tenancy') {
            const tenancyProviderSelect = document.getElementById('whatsapp-provider-select');
            return normalizeProviderValue(tenancyProviderSelect?.value || '') || fallbackProvider;
        }

        const globalProviderSelect = document.getElementById('whatsapp_global_provider');
        return normalizeProviderValue(globalProviderSelect?.value || '') || fallbackProvider;
    };

    const updateBotWebhookPreview = () => {
        const webhookPreview = document.getElementById('bot_webhook_preview');
        const webhookInput = document.getElementById('bot_webhook_url');
        const providerLabelNode = document.getElementById('bot_webhook_provider_label');

        if (!webhookPreview || !webhookInput || !providerLabelNode) {
            return;
        }

        const template = String(webhookPreview.dataset.webhookTemplate || '').trim();
        if (!template) {
            return;
        }

        const fallbackProvider =
            normalizeProviderValue(webhookPreview.dataset.defaultProvider || '') || 'whatsapp_business';
        const selectedMode = document.querySelector('input[name="whatsapp_bot_provider_mode"]:checked');
        const mode = selectedMode ? selectedMode.value : 'shared_with_notifications';

        let provider = '';
        if (mode === 'dedicated') {
            const botProviderSelect = document.getElementById('whatsapp_bot_provider');
            provider = normalizeProviderValue(botProviderSelect?.value || '');
        } else {
            provider = resolveSharedProvider();
        }

        const effectiveProvider = provider || fallbackProvider;
        const webhookUrl = template.replace('__provider__', effectiveProvider);

        webhookInput.value = webhookUrl;
        providerLabelNode.textContent = providerLabel(effectiveProvider);
    };

    const setupConnectionTest = (buttonId, badgeId, messageId) => {
        const button = document.getElementById(buttonId);
        if (!button) return;

        button.addEventListener('click', async (event) => {
            event.preventDefault();

            let url = button.getAttribute('data-test-url');
            if (!url) return;

            if (buttonId.includes('waha')) {
                const suffix = 'provider=waha';
                url += url.includes('?') ? `&${suffix}` : `?${suffix}`;
            }

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
                        'X-CSRF-TOKEN': getCsrfToken(),
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
                            ? 'Conexão realizada com sucesso.'
                            : 'Falha ao testar conexão. Verifique as configurações.');
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
                    messageLabel.textContent = 'Preencha o número de destino e a mensagem para enviar o teste.';
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
                        'X-CSRF-TOKEN': getCsrfToken(),
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
                            : 'Falha ao enviar mensagem de teste. Verifique as configurações.');
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
        providerSelect.addEventListener('change', () => {
            updateProviderVisibility();
            updateBotWebhookPreview();
        });
    }

    const globalProviderSelect = document.getElementById('whatsapp_global_provider');
    if (globalProviderSelect) {
        globalProviderSelect.addEventListener('change', updateBotWebhookPreview);
    }

    const botProviderSelect = document.getElementById('whatsapp_bot_provider');
    if (botProviderSelect) {
        botProviderSelect.addEventListener('change', updateBotWebhookPreview);
    }

    document.querySelectorAll('input[name="whatsapp_bot_provider_mode"]').forEach((radio) => {
        radio.addEventListener('change', updateBotWebhookPreview);
    });

    setupConnectionTest('btn-test-meta', 'meta-test-badge', 'meta-test-message');
    setupConnectionTest('btn-test-zapi', 'zapi-test-badge', 'zapi-test-message');
    setupConnectionTest('btn-test-waha', 'waha-test-badge', 'waha-test-message');
    setupConnectionTest('btn-test-evolution', 'evolution-test-badge', 'evolution-test-message');
    setupConnectionTest('btn-test-bot-meta', 'bot-meta-test-badge', 'bot-meta-test-message');
    setupConnectionTest('btn-test-bot-zapi', 'bot-zapi-test-badge', 'bot-zapi-test-message');
    setupConnectionTest('btn-test-bot-waha', 'bot-waha-test-badge', 'bot-waha-test-message');
    setupConnectionTest('btn-test-bot-evolution', 'bot-evolution-test-badge', 'bot-evolution-test-message');

    setupSendToggle('btn-toggle-meta-send', 'meta-send-form');
    setupSendToggle('btn-toggle-zapi-send', 'zapi-send-form');
    setupSendToggle('btn-toggle-waha-send', 'waha-send-form');
    setupSendToggle('btn-toggle-evolution-send', 'evolution-send-form');
    setupSendToggle('btn-toggle-bot-meta-send', 'bot-meta-send-form');
    setupSendToggle('btn-toggle-bot-zapi-send', 'bot-zapi-send-form');
    setupSendToggle('btn-toggle-bot-waha-send', 'bot-waha-send-form');
    setupSendToggle('btn-toggle-bot-evolution-send', 'bot-evolution-send-form');

    setupSendTest('btn-send-meta-test', 'meta-test-number', 'meta-test-message-input', 'meta-send-badge', 'meta-send-message');
    setupSendTest('btn-send-zapi-test', 'zapi-test-number', 'zapi-test-message-input', 'zapi-send-badge', 'zapi-send-message');
    setupSendTest('btn-send-waha-test', 'waha-test-number', 'waha-test-message-input', 'waha-send-badge', 'waha-send-message');
    setupSendTest('btn-send-evolution-test', 'evolution-test-number', 'evolution-test-message-input', 'evolution-send-badge', 'evolution-send-message');
    setupSendTest('btn-send-bot-meta-test', 'bot-meta-test-number', 'bot-meta-test-message-input', 'bot-meta-send-badge', 'bot-meta-send-message');
    setupSendTest('btn-send-bot-zapi-test', 'bot-zapi-test-number', 'bot-zapi-test-message-input', 'bot-zapi-send-badge', 'bot-zapi-send-message');
    setupSendTest('btn-send-bot-waha-test', 'bot-waha-test-number', 'bot-waha-test-message-input', 'bot-waha-send-badge', 'bot-waha-send-message');
    setupSendTest('btn-send-bot-evolution-test', 'bot-evolution-test-number', 'bot-evolution-test-message-input', 'bot-evolution-send-badge', 'bot-evolution-send-message');

    tenancyConfig.querySelectorAll('.d-none').forEach((element) => {
        element.style.display = 'none';
    });

    updateDriverVisibility();
    updateProviderVisibility();
    updateBotWebhookPreview();
}
