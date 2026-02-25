import { applyGridPageSizeSelector } from '../grid/pageSizeSelector';

export function init() {
    initCampaignsGrid();
    initCampaignRunsGrid();
    initCampaignRecipientsGrid();
    initCampaignForm();
}

function initCampaignsGrid() {
    const wrapper = document.getElementById('campaigns-grid-wrapper');
    const target = document.getElementById('campaigns-grid');

    if (!wrapper || !target || !window.gridjs) {
        return;
    }

    if (target.dataset.gridInitialized === '1') {
        return;
    }

    const gridUrl = resolveGridUrl(wrapper);
    if (!gridUrl) {
        // eslint-disable-next-line no-console
        console.error('[campaigns] grid URL ausente');
        return;
    }

    const defaultLimit = resolveLimit();

    const columns = [
        { id: 'name', name: 'Nome' },
        { id: 'type', name: 'Tipo' },
        {
            id: 'status_badge',
            name: 'Status',
            formatter: (cell) => gridjs.html(cell ?? ''),
        },
        { id: 'channels', name: 'Canais' },
        { id: 'scheduled_at', name: 'Agendado para' },
        { id: 'created_at', name: 'Criado em' },
        {
            id: 'actions',
            name: 'Ações',
            formatter: (cell) => gridjs.html(cell ?? ''),
        },
    ];

    const baseUrl = appendQuery(gridUrl, {
        page: 1,
        limit: defaultLimit,
    });

    new gridjs.Grid({
        columns,
        server: {
            url: baseUrl,
            method: 'GET',
            then: (data) => data.data || [],
            total: (data) => Number(data?.meta?.total || 0),
        },
        search: {
            enabled: true,
            server: {
                url: (prev, keyword) => appendQuery(prev, {
                    search: keyword || '',
                    page: 1,
                }),
            },
        },
        sort: {
            multiColumn: false,
            server: {
                url: (prev, columnsState) => {
                    if (!Array.isArray(columnsState) || columnsState.length === 0) {
                        return prev;
                    }

                    const col = columnsState[0];
                    const direction = col.direction === 1 ? 'asc' : 'desc';

                    return appendQuery(prev, {
                        'sort[column]': col.id,
                        'sort[direction]': direction,
                        page: 1,
                    });
                },
            },
        },
        pagination: {
            enabled: true,
            limit: defaultLimit,
            server: {
                url: (prev, page, limit) => appendQuery(prev, {
                    page: Number(page) + 1,
                    limit: Number(limit),
                }),
            },
        },
        language: {
            search: { placeholder: 'Pesquisar...' },
            pagination: { previous: 'Anterior', next: 'Próxima' },
            loading: 'Carregando...',
            noRecordsFound: 'Nenhum registro encontrado',
            error: 'Erro ao carregar dados',
        },
        className: {
            table: 'w-full text-left',
        },
    }).render(target);

    target.dataset.gridInitialized = '1';

    if (
        !applyGridPageSizeSelector({
            wrapperSelector: '#campaigns-grid-wrapper',
            storageKey: 'tenant_campaigns_page_size',
            defaultLimit,
        })
    ) {
        return;
    }

    bindCampaignsIndexRowClick();
}

function initCampaignRunsGrid() {
    const wrapper = document.getElementById('campaign-runs-grid-wrapper');
    const target = document.getElementById('campaign-runs-grid');

    if (!wrapper || !target || !window.gridjs) {
        return;
    }

    if (target.dataset.gridInitialized === '1') {
        return;
    }

    const gridUrl = wrapper.dataset.gridUrl;
    if (!gridUrl) {
        return;
    }

    const defaultLimit = resolveLimit('tenant_campaign_runs_page_size');
    const columns = [
        { id: 'id', name: '#' },
        {
            id: 'status_badge',
            name: 'Status',
            formatter: (cell) => gridjs.html(cell ?? ''),
        },
        { id: 'started_at', name: 'Iniciada em' },
        { id: 'finished_at', name: 'Finalizada em' },
        { id: 'totals', name: 'Totais' },
        {
            id: 'actions',
            name: 'Ações',
            formatter: (cell) => gridjs.html(cell ?? ''),
        },
    ];

    const baseUrl = appendQuery(gridUrl, {
        page: 1,
        limit: defaultLimit,
    });

    new gridjs.Grid({
        columns,
        server: {
            url: baseUrl,
            method: 'GET',
            then: (data) => data.data || [],
            total: (data) => Number(data?.meta?.total || 0),
        },
        search: {
            enabled: true,
            server: {
                url: (prev, keyword) => appendQuery(prev, {
                    search: keyword || '',
                    page: 1,
                }),
            },
        },
        sort: {
            multiColumn: false,
            server: {
                url: (prev, columnsState) => {
                    if (!Array.isArray(columnsState) || columnsState.length === 0) {
                        return prev;
                    }

                    const col = columnsState[0];
                    const direction = col.direction === 1 ? 'asc' : 'desc';

                    return appendQuery(prev, {
                        'sort[column]': col.id,
                        'sort[direction]': direction,
                        page: 1,
                    });
                },
            },
        },
        pagination: {
            enabled: true,
            limit: defaultLimit,
            server: {
                url: (prev, page, limit) => appendQuery(prev, {
                    page: Number(page) + 1,
                    limit: Number(limit),
                }),
            },
        },
        language: {
            search: { placeholder: 'Pesquisar...' },
            pagination: { previous: 'Anterior', next: 'Próxima' },
            loading: 'Carregando...',
            noRecordsFound: 'Nenhum registro encontrado',
            error: 'Erro ao carregar dados',
        },
        className: {
            table: 'w-full text-left',
        },
    }).render(target);

    target.dataset.gridInitialized = '1';

    applyGridPageSizeSelector({
        wrapperSelector: '#campaign-runs-grid-wrapper',
        storageKey: 'tenant_campaign_runs_page_size',
        defaultLimit,
    });
}

function initCampaignRecipientsGrid() {
    const wrapper = document.getElementById('campaign-recipients-grid-wrapper');
    const target = document.getElementById('campaign-recipients-grid');

    if (!wrapper || !target || !window.gridjs) {
        return;
    }

    if (target.dataset.gridInitialized === '1') {
        return;
    }

    const gridUrl = wrapper.dataset.gridUrl;
    if (!gridUrl) {
        return;
    }

    const defaultLimit = resolveLimit('tenant_campaign_recipients_page_size');
    const columns = [
        { id: 'channel', name: 'Canal' },
        { id: 'destination', name: 'Destino' },
        {
            id: 'status_badge',
            name: 'Status',
            formatter: (cell) => gridjs.html(cell ?? ''),
        },
        { id: 'sent_at', name: 'Enviado em' },
        { id: 'error_message', name: 'Erro' },
        {
            id: 'actions',
            name: 'Ações',
            formatter: (cell) => gridjs.html(cell ?? ''),
        },
    ];

    const baseUrl = appendQuery(gridUrl, {
        page: 1,
        limit: defaultLimit,
    });

    new gridjs.Grid({
        columns,
        server: {
            url: baseUrl,
            method: 'GET',
            then: (data) => data.data || [],
            total: (data) => Number(data?.meta?.total || 0),
        },
        search: {
            enabled: true,
            server: {
                url: (prev, keyword) => appendQuery(prev, {
                    search: keyword || '',
                    page: 1,
                }),
            },
        },
        sort: {
            multiColumn: false,
            server: {
                url: (prev, columnsState) => {
                    if (!Array.isArray(columnsState) || columnsState.length === 0) {
                        return prev;
                    }

                    const col = columnsState[0];
                    const direction = col.direction === 1 ? 'asc' : 'desc';

                    return appendQuery(prev, {
                        'sort[column]': col.id,
                        'sort[direction]': direction,
                        page: 1,
                    });
                },
            },
        },
        pagination: {
            enabled: true,
            limit: defaultLimit,
            server: {
                url: (prev, page, limit) => appendQuery(prev, {
                    page: Number(page) + 1,
                    limit: Number(limit),
                }),
            },
        },
        language: {
            search: { placeholder: 'Pesquisar...' },
            pagination: { previous: 'Anterior', next: 'Próxima' },
            loading: 'Carregando...',
            noRecordsFound: 'Nenhum registro encontrado',
            error: 'Erro ao carregar dados',
        },
        className: {
            table: 'w-full text-left',
        },
    }).render(target);

    target.dataset.gridInitialized = '1';

    applyGridPageSizeSelector({
        wrapperSelector: '#campaign-recipients-grid-wrapper',
        storageKey: 'tenant_campaign_recipients_page_size',
        defaultLimit,
    });
}

function resolveGridUrl(wrapper) {
    const urlFromDataset = wrapper.dataset.gridUrl;
    if (urlFromDataset) {
        return urlFromDataset;
    }

    const slug = window.tenantSlug || (window.tenant && window.tenant.slug) || '';
    if (!slug) {
        return '';
    }

    return `/workspace/${slug}/campaigns/grid-data`;
}

function resolveLimit(storageKey = 'tenant_campaigns_page_size') {
    const params = new URLSearchParams(window.location.search);
    const fromQuery = Number.parseInt(params.get('limit') || '', 10);
    if ([10, 25, 50, 100].includes(fromQuery)) {
        return fromQuery;
    }

    try {
        const fromStorage = Number.parseInt(localStorage.getItem(storageKey) || '', 10);
        if ([10, 25, 50, 100].includes(fromStorage)) {
            return fromStorage;
        }
    } catch (error) {
        // noop
    }

    return 10;
}

function appendQuery(url, params) {
    const targetUrl = new URL(url, window.location.origin);

    Object.entries(params || {}).forEach(([key, value]) => {
        if (value === undefined || value === null) {
            return;
        }

        targetUrl.searchParams.set(key, String(value));
    });

    if (targetUrl.origin === window.location.origin) {
        return `${targetUrl.pathname}${targetUrl.search}`;
    }

    return targetUrl.toString();
}

function bindCampaignsIndexRowClick() {
    const grid = document.getElementById('campaigns-grid');
    if (!grid) {
        return;
    }

    const wrapper = document.getElementById('campaigns-grid-wrapper');
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

function initCampaignForm() {
    const form = document.getElementById('campaign-form');
    if (!form) {
        return;
    }

    const fixedChannelInput = form.querySelector('input[data-fixed-channel="true"]');
    const channelCheckboxes = Array.from(form.querySelectorAll('.js-channel-checkbox'));
    const typeSelect = form.querySelector('#campaign-type');

    const emailSection = form.querySelector('#campaign-email-section');
    const whatsappSection = form.querySelector('#campaign-whatsapp-section');
    const whatsappMessageTypeSelect = form.querySelector('#whatsapp-message-type');
    const whatsappMediaSourceSelect = form.querySelector('#whatsapp-media-source');
    const whatsappTextWrapper = form.querySelector('#whatsapp-text-wrapper');
    const whatsappMediaWrapper = form.querySelector('#whatsapp-media-wrapper');
    const whatsappMediaUrlWrapper = form.querySelector('#whatsapp-media-url-wrapper');
    const whatsappMediaAssetWrapper = form.querySelector('#whatsapp-media-asset-wrapper');
    const whatsappMediaKindSelect = form.querySelector('select[name="content_json[whatsapp][media][kind]"]');
    const whatsappMediaAssetIdInput = form.querySelector('#whatsapp-media-asset-id');
    const whatsappMediaUploadInput = form.querySelector('#whatsapp-media-upload-file');
    const whatsappMediaUploadButton = form.querySelector('#whatsapp-media-upload-btn');
    const whatsappMediaUploadFeedback = form.querySelector('#whatsapp-media-upload-feedback');

    const emailAttachmentsUploadInput = form.querySelector('#email-attachments-upload-input');
    const emailAttachmentsUploadButton = form.querySelector('#email-attachments-upload-btn');
    const emailAttachmentsList = form.querySelector('#email-attachments-list');
    const emailAttachmentsUploadFeedback = form.querySelector('#email-attachments-upload-feedback');

    const automationSection = form.querySelector('#campaign-automation-section');
    const automationInputs = Array.from(form.querySelectorAll('.js-automation-input'));

    const requireEmailField = form.querySelector('#audience-require-email');
    const requireWhatsappField = form.querySelector('#audience-require-whatsapp');
    const requireEmailCheck = form.querySelector('#audience-require-email-check');
    const requireWhatsappCheck = form.querySelector('#audience-require-whatsapp-check');
    const assetUploadUrl = form.dataset.assetUploadUrl || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const whatsappKindToUploadKind = {
        image: 'whatsapp_image',
        video: 'whatsapp_video',
        document: 'whatsapp_document',
        audio: 'whatsapp_audio',
    };

    const whatsappKindToAccept = {
        image: 'image/*',
        video: 'video/*',
        document: 'application/*,text/*,.pdf',
        audio: 'audio/*',
    };

    const toggleContainerState = (container, enabled) => {
        if (!container) {
            return;
        }

        container.classList.toggle('hidden', !enabled);
        container.querySelectorAll('input, select, textarea, button').forEach((element) => {
            element.disabled = !enabled;
        });
    };

    const setFeedback = (element, message, type = 'muted') => {
        if (!element) {
            return;
        }

        element.textContent = message || '';
        element.classList.remove('text-gray-500', 'dark:text-gray-400', 'text-red-600', 'dark:text-red-400', 'text-green-600', 'dark:text-green-400');

        if (type === 'error') {
            element.classList.add('text-red-600', 'dark:text-red-400');
            return;
        }

        if (type === 'success') {
            element.classList.add('text-green-600', 'dark:text-green-400');
            return;
        }

        element.classList.add('text-gray-500', 'dark:text-gray-400');
    };

    const parseJson = async (response) => {
        try {
            return await response.json();
        } catch (error) {
            return null;
        }
    };

    const uploadAsset = async (file, kind) => {
        if (!assetUploadUrl) {
            throw new Error('Endpoint de upload não configurado.');
        }

        if (!csrfToken) {
            throw new Error('Token CSRF não encontrado.');
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('kind', kind);

        const response = await fetch(assetUploadUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
            credentials: 'same-origin',
        });

        const payload = await parseJson(response);
        if (!response.ok) {
            const firstError = payload?.errors
                ? Object.values(payload.errors).flat()[0]
                : null;
            throw new Error(firstError || payload?.message || 'Falha no upload do arquivo.');
        }

        return payload;
    };

    const formatBytes = (bytes) => {
        const size = Number(bytes || 0);
        if (!Number.isFinite(size) || size <= 0) {
            return null;
        }

        if (size < 1024) {
            return `${size} B`;
        }

        return `${(size / 1024).toFixed(1)} KB`;
    };

    const createHiddenInput = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value ?? '';
        return input;
    };

    const selectedChannels = () => {
        if (fixedChannelInput && fixedChannelInput.value) {
            return [fixedChannelInput.value.toLowerCase()];
        }

        return channelCheckboxes
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.value.toLowerCase());
    };

    const updateAudienceRequirements = (channels) => {
        const emailSelected = channels.includes('email');
        const whatsappSelected = channels.includes('whatsapp');

        if (requireEmailField) {
            requireEmailField.value = emailSelected ? '1' : '0';
        }
        if (requireWhatsappField) {
            requireWhatsappField.value = whatsappSelected ? '1' : '0';
        }
        if (requireEmailCheck) {
            requireEmailCheck.checked = emailSelected;
        }
        if (requireWhatsappCheck) {
            requireWhatsappCheck.checked = whatsappSelected;
        }
    };

    const updateWhatsappMode = (isWhatsappEnabled) => {
        const messageType = (whatsappMessageTypeSelect?.value || '').toLowerCase();
        const mediaSource = (whatsappMediaSourceSelect?.value || '').toLowerCase();

        toggleContainerState(whatsappTextWrapper, isWhatsappEnabled && messageType === 'text');
        toggleContainerState(whatsappMediaWrapper, isWhatsappEnabled && messageType === 'media');
        toggleContainerState(
            whatsappMediaUrlWrapper,
            isWhatsappEnabled && messageType === 'media' && mediaSource === 'url',
        );
        toggleContainerState(
            whatsappMediaAssetWrapper,
            isWhatsappEnabled && messageType === 'media' && mediaSource === 'upload',
        );
    };

    const updateChannelSections = () => {
        const channels = selectedChannels();
        const emailEnabled = channels.includes('email');
        const whatsappEnabled = channels.includes('whatsapp');

        toggleContainerState(emailSection, emailEnabled);
        toggleContainerState(whatsappSection, whatsappEnabled);
        updateWhatsappMode(whatsappEnabled);
        updateAudienceRequirements(channels);
    };

    const updateAutomationSection = () => {
        const enabled = typeSelect?.value === 'automated';

        if (automationSection) {
            automationSection.classList.toggle('hidden', !enabled);
        }

        automationInputs.forEach((input) => {
            input.disabled = !enabled;
        });
    };

    const resolveWhatsappUploadKind = () => {
        const kind = (whatsappMediaKindSelect?.value || '').toLowerCase();
        return whatsappKindToUploadKind[kind] || 'whatsapp_document';
    };

    const updateWhatsappUploadAccept = () => {
        if (!whatsappMediaUploadInput) {
            return;
        }

        const kind = (whatsappMediaKindSelect?.value || '').toLowerCase();
        whatsappMediaUploadInput.setAttribute('accept', whatsappKindToAccept[kind] || '*/*');
    };

    const appendEmailAttachmentItem = (payload) => {
        if (!emailAttachmentsList) {
            return;
        }

        const nextIndexRaw = Number.parseInt(emailAttachmentsList.dataset.nextIndex || '0', 10);
        const nextIndex = Number.isNaN(nextIndexRaw)
            ? emailAttachmentsList.querySelectorAll('.js-email-attachment-item').length
            : nextIndexRaw;

        emailAttachmentsList.dataset.nextIndex = String(nextIndex + 1);

        const item = document.createElement('li');
        item.className = 'js-email-attachment-item rounded-md border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/30';

        const top = document.createElement('div');
        top.className = 'flex items-start justify-between gap-3';

        const info = document.createElement('div');
        info.className = 'min-w-0';

        const filename = String(payload.filename || `asset_${payload.asset_id}`);
        const mime = String(payload.mime || 'application/octet-stream');
        const sizeLabel = formatBytes(payload.size);

        const title = document.createElement('p');
        title.className = 'truncate text-sm font-medium text-gray-800 dark:text-gray-100';
        title.textContent = filename;

        const details = document.createElement('p');
        details.className = 'text-xs text-gray-500 dark:text-gray-400';
        details.textContent = `ID ${payload.asset_id} · ${mime}${sizeLabel ? ` · ${sizeLabel}` : ''}`;

        info.appendChild(title);
        info.appendChild(details);

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'js-remove-email-attachment text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300';
        removeButton.textContent = 'Remover';

        top.appendChild(info);
        top.appendChild(removeButton);
        item.appendChild(top);

        item.appendChild(createHiddenInput(`content_json[email][attachments][${nextIndex}][source]`, 'upload'));
        item.appendChild(createHiddenInput(`content_json[email][attachments][${nextIndex}][asset_id]`, payload.asset_id));
        item.appendChild(createHiddenInput(`content_json[email][attachments][${nextIndex}][filename]`, filename));
        item.appendChild(createHiddenInput(`content_json[email][attachments][${nextIndex}][mime]`, mime));
        item.appendChild(createHiddenInput(`content_json[email][attachments][${nextIndex}][size]`, payload.size || 0));

        emailAttachmentsList.appendChild(item);
    };

    channelCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', updateChannelSections);
    });

    if (typeSelect) {
        typeSelect.addEventListener('change', updateAutomationSection);
    }

    if (whatsappMessageTypeSelect) {
        whatsappMessageTypeSelect.addEventListener('change', () => {
            updateWhatsappMode(selectedChannels().includes('whatsapp'));
        });
    }

    if (whatsappMediaSourceSelect) {
        whatsappMediaSourceSelect.addEventListener('change', () => {
            updateWhatsappMode(selectedChannels().includes('whatsapp'));
        });
    }

    if (whatsappMediaKindSelect) {
        whatsappMediaKindSelect.addEventListener('change', () => {
            updateWhatsappUploadAccept();

            if (whatsappMediaAssetIdInput) {
                whatsappMediaAssetIdInput.value = '';
            }

            setFeedback(whatsappMediaUploadFeedback, 'Tipo de mídia alterado. Faça novo upload para atualizar o asset.', 'muted');
        });
    }

    if (whatsappMediaUploadButton) {
        whatsappMediaUploadButton.addEventListener('click', async () => {
            if (!whatsappMediaUploadInput) {
                return;
            }

            const file = whatsappMediaUploadInput.files?.[0];
            if (!file) {
                setFeedback(whatsappMediaUploadFeedback, 'Selecione um arquivo para upload.', 'error');
                return;
            }

            whatsappMediaUploadButton.disabled = true;
            setFeedback(whatsappMediaUploadFeedback, 'Enviando arquivo...', 'muted');

            try {
                const payload = await uploadAsset(file, resolveWhatsappUploadKind());
                if (whatsappMediaAssetIdInput) {
                    whatsappMediaAssetIdInput.value = String(payload.asset_id || '');
                }

                const sizeLabel = formatBytes(payload.size);
                setFeedback(
                    whatsappMediaUploadFeedback,
                    `Arquivo anexado: ${payload.filename || 'arquivo'}${sizeLabel ? ` (${sizeLabel})` : ''}.`,
                    'success',
                );
                whatsappMediaUploadInput.value = '';
            } catch (error) {
                setFeedback(whatsappMediaUploadFeedback, error?.message || 'Falha ao enviar arquivo.', 'error');
            } finally {
                whatsappMediaUploadButton.disabled = false;
            }
        });
    }

    if (emailAttachmentsList) {
        emailAttachmentsList.addEventListener('click', (event) => {
            const button = event.target.closest('.js-remove-email-attachment');
            if (!button) {
                return;
            }

            event.preventDefault();
            const item = button.closest('.js-email-attachment-item');
            if (item) {
                item.remove();
            }
        });
    }

    if (emailAttachmentsUploadButton) {
        emailAttachmentsUploadButton.addEventListener('click', async () => {
            if (!emailAttachmentsUploadInput || !emailAttachmentsList) {
                return;
            }

            const files = Array.from(emailAttachmentsUploadInput.files || []);
            if (files.length === 0) {
                setFeedback(emailAttachmentsUploadFeedback, 'Selecione ao menos um arquivo para anexar.', 'error');
                return;
            }

            const maxItems = Number.parseInt(emailAttachmentsList.dataset.maxItems || '3', 10) || 3;
            const currentItems = emailAttachmentsList.querySelectorAll('.js-email-attachment-item').length;
            const availableSlots = Math.max(0, maxItems - currentItems);

            if (availableSlots <= 0) {
                setFeedback(emailAttachmentsUploadFeedback, `Limite máximo de ${maxItems} anexos atingido.`, 'error');
                return;
            }

            const filesToUpload = files.slice(0, availableSlots);
            const failedUploads = [];

            emailAttachmentsUploadButton.disabled = true;
            emailAttachmentsUploadInput.disabled = true;
            setFeedback(emailAttachmentsUploadFeedback, 'Enviando anexos...', 'muted');

            // eslint-disable-next-line no-restricted-syntax
            for (const file of filesToUpload) {
                try {
                    const payload = await uploadAsset(file, 'email_attachment');
                    appendEmailAttachmentItem(payload);
                } catch (error) {
                    failedUploads.push(error?.message || `Falha ao enviar ${file.name}.`);
                }
            }

            emailAttachmentsUploadButton.disabled = false;
            emailAttachmentsUploadInput.disabled = false;
            emailAttachmentsUploadInput.value = '';

            if (failedUploads.length > 0) {
                setFeedback(emailAttachmentsUploadFeedback, failedUploads[0], 'error');
                return;
            }

            if (files.length > filesToUpload.length) {
                setFeedback(
                    emailAttachmentsUploadFeedback,
                    `Apenas ${filesToUpload.length} arquivo(s) enviado(s) devido ao limite de ${maxItems} anexos.`,
                    'muted',
                );
                return;
            }

            setFeedback(emailAttachmentsUploadFeedback, 'Anexo(s) enviado(s) com sucesso.', 'success');
        });
    }

    form.addEventListener('submit', (event) => {
        const channels = selectedChannels();
        if (!channels.includes('whatsapp')) {
            return;
        }

        const messageType = (whatsappMessageTypeSelect?.value || '').toLowerCase();
        const mediaSource = (whatsappMediaSourceSelect?.value || '').toLowerCase();
        const assetId = String(whatsappMediaAssetIdInput?.value || '').trim();

        if (messageType === 'media' && mediaSource === 'upload' && assetId === '') {
            event.preventDefault();
            setFeedback(whatsappMediaUploadFeedback, 'Faça upload do arquivo para gerar o asset_id antes de salvar.', 'error');
        }
    });

    updateChannelSections();
    updateAutomationSection();
    updateWhatsappUploadAccept();
}
