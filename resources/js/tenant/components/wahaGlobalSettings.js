export function initTenantWahaGlobalSettings() {
    const panel = document.getElementById('tenant-waha-global-panel');
    if (!panel) {
        return;
    }
    if (panel.dataset.wahaInitialized === '1') {
        return;
    }
    panel.dataset.wahaInitialized = '1';

    const panelTabContainer = panel.closest('[x-show]');
    const AUTO_REFRESH_STATUS_INTERVAL_MS = 5000;

    let autoRefreshTimer = null;
    let autoRefreshInFlight = false;
    let panelVisibilityObserver = null;

    const endpoints = {
        status: panel.dataset.statusUrl || '',
        qr: panel.dataset.qrUrl || '',
        bindWebhook: panel.dataset.actionBindWebhookUrl || '',
        start: panel.dataset.actionStartUrl || '',
        restart: panel.dataset.actionRestartUrl || '',
        stop: panel.dataset.actionStopUrl || '',
        logout: panel.dataset.actionLogoutUrl || '',
    };

    const dom = {
        instanceName: document.getElementById('waha-instance-name'),
        sessionStatus: document.getElementById('waha-session-status'),
        lastError: document.getElementById('waha-last-error'),
        webhookExpectedUrl: document.getElementById('waha-webhook-expected-url'),
        webhookCurrentUrl: document.getElementById('waha-webhook-current-url'),
        webhookStatus: document.getElementById('waha-webhook-status'),
        bindWebhookButton: panel.querySelector('[data-waha-bind-webhook]'),
        feedback: document.getElementById('waha-action-feedback'),
        qrEmpty: document.getElementById('waha-qr-empty'),
        qrWrapper: document.getElementById('waha-qr-wrapper'),
        qrImage: document.getElementById('waha-qr-image'),
    };

    const setFeedback = (message, tone = 'neutral') => {
        if (!dom.feedback) return;

        dom.feedback.textContent = message || '';
        dom.feedback.classList.remove('text-gray-600', 'dark:text-gray-300', 'text-red-600', 'dark:text-red-400', 'text-green-600', 'dark:text-green-400');

        if (tone === 'error') {
            dom.feedback.classList.add('text-red-600', 'dark:text-red-400');
            return;
        }

        if (tone === 'success') {
            dom.feedback.classList.add('text-green-600', 'dark:text-green-400');
            return;
        }

        dom.feedback.classList.add('text-gray-600', 'dark:text-gray-300');
    };

    const renderWebhook = (webhookPayload) => {
        const expectedUrl = String(webhookPayload?.expected_url || '').trim();
        const currentUrl = String(webhookPayload?.current_url || '').trim();
        const configured = Boolean(webhookPayload?.configured);
        const statusText = configured ? 'Configurado' : (currentUrl ? 'Divergente' : 'Não configurado');

        if (dom.webhookExpectedUrl) {
            dom.webhookExpectedUrl.textContent = expectedUrl || '-';
        }

        if (dom.webhookCurrentUrl) {
            dom.webhookCurrentUrl.textContent = currentUrl || 'Não configurado';
        }

        if (dom.webhookStatus) {
            dom.webhookStatus.textContent = statusText;
            dom.webhookStatus.classList.remove(
                'border-emerald-200',
                'bg-emerald-50',
                'text-emerald-700',
                'dark:border-emerald-700/40',
                'dark:bg-emerald-900/20',
                'dark:text-emerald-300',
                'border-amber-200',
                'bg-amber-50',
                'text-amber-700',
                'dark:border-amber-700/40',
                'dark:bg-amber-900/20',
                'dark:text-amber-300'
            );

            if (configured) {
                dom.webhookStatus.classList.add(
                    'border-emerald-200',
                    'bg-emerald-50',
                    'text-emerald-700',
                    'dark:border-emerald-700/40',
                    'dark:bg-emerald-900/20',
                    'dark:text-emerald-300'
                );
            } else {
                dom.webhookStatus.classList.add(
                    'border-amber-200',
                    'bg-amber-50',
                    'text-amber-700',
                    'dark:border-amber-700/40',
                    'dark:bg-amber-900/20',
                    'dark:text-amber-300'
                );
            }
        }

        if (dom.bindWebhookButton) {
            dom.bindWebhookButton.classList.toggle('hidden', configured);
        }
    };

    const renderQr = (qrPayload) => {
        const hasData = Boolean(qrPayload && qrPayload.ok && qrPayload.data);

        if (!dom.qrWrapper || !dom.qrImage || !dom.qrEmpty) {
            return;
        }

        if (!hasData) {
            dom.qrImage.removeAttribute('src');
            dom.qrWrapper.classList.add('hidden');
            dom.qrEmpty.classList.remove('hidden');
            return;
        }

        const mimeType = qrPayload.mimetype || 'image/png';
        const rawData = String(qrPayload.data || '');
        dom.qrImage.src = rawData.startsWith('data:')
            ? rawData
            : `data:${mimeType};base64,${rawData}`;
        dom.qrWrapper.classList.remove('hidden');
        dom.qrEmpty.classList.add('hidden');
    };

    const updateFromStatusPayload = (payload) => {
        const instance = payload?.instance || {};
        const session = payload?.session || {};

        if (dom.instanceName) {
            dom.instanceName.textContent = instance.instance_name || '-';
        }

        if (dom.sessionStatus) {
            dom.sessionStatus.textContent = session.status || 'UNKNOWN';
        }

        if (dom.lastError) {
            const errorText = instance.last_error || 'Sem erros recentes.';
            dom.lastError.textContent = errorText;
            dom.lastError.classList.remove('text-gray-500', 'dark:text-gray-400', 'text-red-600', 'dark:text-red-400');
            if (instance.last_error) {
                dom.lastError.classList.add('text-red-600', 'dark:text-red-400');
            } else {
                dom.lastError.classList.add('text-gray-500', 'dark:text-gray-400');
            }
        }

        if (payload?.qr) {
            renderQr(payload.qr);
        } else {
            renderQr(null);
        }

        renderWebhook(payload?.webhook || null);
    };

    const parseJsonResponse = async (response) => {
        try {
            return await response.json();
        } catch (error) {
            return {
                ok: false,
                message: 'Resposta invalida do servidor.',
            };
        }
    };

    const csrfToken = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }

        const input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    };

    const request = async (url, method = 'GET') => {
        if (!url) {
            return {
                ok: false,
                message: 'Endpoint WAHA não configurado na tela.',
            };
        }

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
            });

            const payload = await parseJsonResponse(response);

            if (!response.ok && payload?.ok !== true) {
                return {
                    ok: false,
                    message: payload?.message || 'Falha ao comunicar com o servidor WAHA.',
                    payload,
                };
            }

            return {
                ok: payload?.ok === true,
                message: payload?.message || '',
                payload,
            };
        } catch (error) {
            return {
                ok: false,
                message: 'Erro de conexão com o servidor. Tente novamente.',
            };
        }
    };

    const refreshStatus = async ({ silent = false, source = 'manual' } = {}) => {
        if (!silent) {
            setFeedback('Atualizando status da sessão WAHA...');
        }

        let result;
        try {
            result = await request(endpoints.status, 'GET');
        } catch (error) {
            if (!silent || source === 'auto') {
                setFeedback('Falha ao atualizar status WAHA.', 'error');
            }
            return;
        }

        if (!result.ok) {
            if (!silent || source === 'auto') {
                setFeedback(result.message || 'Falha ao atualizar status WAHA.', 'error');
            }
            return;
        }

        updateFromStatusPayload(result.payload);
        if (!silent) {
            setFeedback('Status WAHA atualizado.', 'success');
        }
    };

    const refreshQr = async () => {
        setFeedback('Atualizando QR Code WAHA...');
        let result;
        try {
            result = await request(endpoints.qr, 'GET');
        } catch (error) {
            renderQr(null);
            setFeedback('Falha ao atualizar QR Code WAHA.', 'error');
            return;
        }

        if (!result.ok) {
            renderQr(null);
            setFeedback(result.message || 'Falha ao atualizar QR Code WAHA.', 'error');
            return;
        }

        const qrPayload = result.payload?.qr || null;
        renderQr(qrPayload);

        if (qrPayload?.ok) {
            setFeedback('QR Code atualizado.', 'success');
            return;
        }

        setFeedback(
            qrPayload?.message || 'QR Code ainda não disponível para esta sessão.',
            'neutral'
        );
    };

    const actionButtons = panel.querySelectorAll('[data-waha-action]');
    actionButtons.forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();
            const action = button.getAttribute('data-waha-action') || '';
            const endpoint = endpoints[action];

            if (!endpoint) {
                setFeedback('Ação WAHA indisponível na tela.', 'error');
                return;
            }

            button.disabled = true;
            setFeedback(`Executando ação ${action.toUpperCase()}...`);

            try {
                const result = await request(endpoint, 'POST');
                if (!result.ok) {
                    setFeedback(result.message || `Falha ao executar ação ${action.toUpperCase()}.`, 'error');
                    return;
                }

                const statusPayload = result.payload?.status;
                if (statusPayload && statusPayload.ok) {
                    updateFromStatusPayload(statusPayload);
                } else {
                    await refreshStatus();
                }

                const successMessage = result.message || `Ação ${action.toUpperCase()} executada com sucesso.`;
                setFeedback(successMessage, 'success');
            } catch (error) {
                setFeedback(`Falha ao executar ação ${action.toUpperCase()}.`, 'error');
            } finally {
                button.disabled = false;
            }
        });
    });

    const refreshStatusButton = panel.querySelector('[data-waha-refresh-status]');
    if (refreshStatusButton) {
        refreshStatusButton.addEventListener('click', async (event) => {
            event.preventDefault();
            await refreshStatus();
        });
    }

    const refreshQrButton = panel.querySelector('[data-waha-refresh-qr]');
    if (refreshQrButton) {
        refreshQrButton.addEventListener('click', async (event) => {
            event.preventDefault();
            await refreshQr();
        });
    }

    if (dom.bindWebhookButton) {
        dom.bindWebhookButton.addEventListener('click', async (event) => {
            event.preventDefault();

            if (!endpoints.bindWebhook) {
                setFeedback('Ação de webhook WAHA indisponível na tela.', 'error');
                return;
            }

            dom.bindWebhookButton.disabled = true;
            setFeedback('Vinculando webhook WAHA...');

            try {
                const result = await request(endpoints.bindWebhook, 'POST');
                if (!result.ok) {
                    setFeedback(result.message || 'Falha ao vincular webhook WAHA.', 'error');
                    return;
                }

                const statusPayload = result.payload?.status;
                if (statusPayload && statusPayload.ok) {
                    updateFromStatusPayload(statusPayload);
                } else {
                    await refreshStatus();
                }

                setFeedback(result.message || 'Webhook WAHA vinculado com sucesso.', 'success');
            } catch (error) {
                setFeedback('Falha ao vincular webhook WAHA.', 'error');
            } finally {
                dom.bindWebhookButton.disabled = false;
            }
        });
    }

    const isWahaTabVisible = () => {
        const target = panelTabContainer || panel;
        if (!target || document.hidden) {
            return false;
        }

        return target.offsetParent !== null;
    };

    const runAutoRefreshTick = async () => {
        if (!isWahaTabVisible() || autoRefreshInFlight) {
            return;
        }

        autoRefreshInFlight = true;
        try {
            await refreshStatus({ silent: true, source: 'auto' });
        } finally {
            autoRefreshInFlight = false;
        }
    };

    const startAutoRefresh = ({ immediate = false } = {}) => {
        if (autoRefreshTimer !== null) {
            return;
        }

        autoRefreshTimer = window.setInterval(runAutoRefreshTick, AUTO_REFRESH_STATUS_INTERVAL_MS);
        if (immediate) {
            runAutoRefreshTick();
        }
    };

    const stopAutoRefresh = () => {
        if (autoRefreshTimer !== null) {
            window.clearInterval(autoRefreshTimer);
            autoRefreshTimer = null;
        }
        autoRefreshInFlight = false;
    };

    const reconcileAutoRefresh = ({ immediate = false } = {}) => {
        if (isWahaTabVisible()) {
            startAutoRefresh({ immediate });
            return;
        }

        stopAutoRefresh();
    };

    const handleDocumentVisibilityChange = () => {
        reconcileAutoRefresh({ immediate: true });
    };

    document.addEventListener('visibilitychange', handleDocumentVisibilityChange);

    if (panelTabContainer && typeof MutationObserver !== 'undefined') {
        panelVisibilityObserver = new MutationObserver(() => {
            reconcileAutoRefresh({ immediate: true });
        });
        panelVisibilityObserver.observe(panelTabContainer, {
            attributes: true,
            attributeFilter: ['style', 'class'],
        });
    }

    window.addEventListener('beforeunload', () => {
        stopAutoRefresh();
        if (panelVisibilityObserver) {
            panelVisibilityObserver.disconnect();
            panelVisibilityObserver = null;
        }
        document.removeEventListener('visibilitychange', handleDocumentVisibilityChange);
    });

    if (isWahaTabVisible()) {
        refreshStatus();
    }
    reconcileAutoRefresh();
}
