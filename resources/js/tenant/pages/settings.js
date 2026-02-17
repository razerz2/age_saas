export function init() {
    ensureAlpine();
    bindSettingsActions();
    bindSettingsTabs();
    bindBookingLinkCopy();
    bindLocationConfig();
    bindFinanceConfig();
}

function ensureAlpine() {
    const root = document.querySelector('[data-alpine=\"true\"]');
    if (!root) {
        return;
    }
    if (window.Alpine) {
        return;
    }
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
    script.defer = true;
    document.head.appendChild(script);
}

function bindSettingsActions() {
    document.addEventListener('click', (event) => {
        const removeBtn = event.target.closest('[data-settings-action=\"remove-logo\"]');
        if (removeBtn) {
            const targetId = removeBtn.dataset.removeTarget;
            const input = document.getElementById(targetId);
            if (input) {
                input.value = '1';
                const form = removeBtn.closest('form');
                form?.submit();
            }
        }

        const regenBtn = event.target.closest('[data-settings-action=\"regenerate-secret\"]');
        if (regenBtn) {
            confirmAction({
                title: 'Gerar novo secret',
                message: 'Tem certeza que deseja gerar um novo secret? Você precisará atualizar a configuração no Asaas.',
                confirmText: 'Gerar',
                cancelText: 'Cancelar',
                type: 'warning',
                onConfirm: () => {
                    const flag = document.getElementById('regenerate_webhook_secret');
                    if (flag) flag.value = '1';
                    const input = document.querySelector('input[name=\"asaas_webhook_secret\"]');
                    if (input) input.value = '';
                },
            });
        }
    });
}

function bindBookingLinkCopy() {
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-copy-link]');
        if (!btn) return;
        const target = btn.dataset.copyLink;
        let input = null;
        if (target === 'publicBookingLink') {
            input = document.getElementById('publicBookingLink');
        } else {
            input = document.querySelector(`[data-copy-source=\"${target}\"]`);
        }
        if (!input) {
            showAlert({ type: 'error', title: 'Erro', message: 'Link não encontrado.' });
            return;
        }
        const link = input.value;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard
                .writeText(link)
                .then(() => showCopySuccess())
                .catch(() => fallbackCopy(link));
        } else {
            fallbackCopy(link);
        }
    });
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        showCopySuccess();
    } catch (err) {
        showAlert({ type: 'error', title: 'Erro', message: 'Erro ao copiar. Por favor, copie manualmente.' });
    }
    document.body.removeChild(textarea);
}

function showCopySuccess() {
    const alert = document.getElementById('copySuccessAlert');
    if (alert) {
        alert.style.display = 'flex';
        setTimeout(() => {
            alert.style.display = 'none';
        }, 3000);
    }
}

function bindSettingsTabs() {
    const config = document.getElementById('settings-config');
    if (!config) return;

    const redirectTab = config.dataset.redirectTab || '';
    if (redirectTab) {
        switchTab(redirectTab);
    }

    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const targetPane = document.getElementById(hash);
        if (targetPane) {
            switchTab(hash);
        }
    }

    const cancellationCheckbox = document.getElementById('appointments_allow_cancellation');
    if (cancellationCheckbox) {
        cancellationCheckbox.addEventListener('change', function onCancelChange() {
            const cancellationGroup = document.getElementById('cancellation_hours_group');
            if (cancellationGroup) {
                cancellationGroup.style.display = this.checked ? 'block' : 'none';
            }
        });
    }

    const googleCalendarCheckbox = document.getElementById('integrations_google_calendar_enabled');
    if (googleCalendarCheckbox) {
        googleCalendarCheckbox.addEventListener('change', function onGoogleChange() {
            const autoSyncGroup = document.getElementById('google_calendar_auto_sync_group');
            if (autoSyncGroup) {
                autoSyncGroup.style.display = this.checked && !this.disabled ? 'block' : 'none';
            }
        });
    }

    const calendarForm = document.querySelector('form[action*=\"calendar\"]');
    if (calendarForm) {
        calendarForm.addEventListener('submit', function onCalendarSubmit() {
            const checkboxes = document.querySelectorAll('input[name=\"calendar_default_weekdays[]\"]:checked');
            const values = Array.from(checkboxes).map((cb) => cb.value);
            let hiddenField = this.querySelector('input[name=\"calendar_default_weekdays\"]');
            if (!hiddenField) {
                hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.name = 'calendar_default_weekdays';
                this.appendChild(hiddenField);
            }
            hiddenField.value = values.join(',');
        });
    }

    const emailDriver = document.getElementById('email_driver');
    if (emailDriver) {
        emailDriver.addEventListener('change', function onEmailChange() {
            const emailConfig = document.getElementById('email_tenancy_config');
            if (emailConfig) {
                emailConfig.style.display = this.value === 'tenancy' ? 'block' : 'none';
            }
        });
    }

    const whatsappDriver = document.getElementById('whatsapp_driver');
    if (whatsappDriver) {
        whatsappDriver.addEventListener('change', function onWhatsappChange() {
            const whatsappConfig = document.getElementById('whatsapp_tenancy_config');
            if (whatsappConfig) {
                whatsappConfig.style.display = this.value === 'tenancy' ? 'block' : 'none';
            }
        });
    }

    const professionalCustomization = document.getElementById('professional_customization_enabled');
    if (professionalCustomization) {
        professionalCustomization.addEventListener('change', function onCustomizationChange() {
            const customizationFields = document.getElementById('professional_customization_fields');
            if (customizationFields) {
                customizationFields.style.display = this.checked ? 'block' : 'none';
            }
        });
    }
}

function switchTab(tabId) {
    document.querySelectorAll('.tab-button').forEach((btn) => {
        btn.classList.remove('active');
        btn.setAttribute('aria-selected', 'false');
    });
    document.querySelectorAll('.tab-content').forEach((pane) => {
        pane.classList.remove('active');
    });
    const activeTab = document.getElementById(`${tabId}-tab`);
    const activePane = document.getElementById(tabId);
    if (activeTab && activePane) {
        activeTab.classList.add('active');
        activeTab.setAttribute('aria-selected', 'true');
        activePane.classList.add('active');
    }
    if (history.pushState) {
        history.pushState(null, null, `#${tabId}`);
    } else {
        window.location.hash = `#${tabId}`;
    }
    const settingsTabs = document.getElementById('settingsTabs');
    if (settingsTabs) {
        window.scrollTo({
            top: settingsTabs.offsetTop - 20,
            behavior: 'smooth',
        });
    }
}

function bindLocationConfig() {
    const config = document.getElementById('settings-config');
    if (!config) return;
    const stateSelect = document.getElementById('estado_id');
    const citySelect = document.getElementById('cidade_id');
    const zipcodeField = document.getElementById('cep');
    const addressField = document.getElementById('endereco');
    const neighborhoodField = document.getElementById('bairro');
    if (!stateSelect || !citySelect) return;

    const currentEstadoId = config.dataset.currentStateId || '';
    const currentCidadeId = config.dataset.currentCityId || '';
    const brazilId = config.dataset.brazilId || '';
    const statesUrlTemplate = config.dataset.statesUrlTemplate || '';
    const citiesUrlTemplate = config.dataset.citiesUrlTemplate || '';

    const loadStates = async () => {
        stateSelect.innerHTML = '<option value=\"\">Carregando estados...</option>';
        try {
            const response = await fetch(statesUrlTemplate.replace(':paisId', brazilId));
            const data = await response.json();
            stateSelect.innerHTML = '<option value=\"\">Selecione o estado</option>';
            data.forEach((state) => {
                const option = document.createElement('option');
                option.value = state.id_estado;
                option.dataset.abbr = state.uf;
                option.textContent = state.nome_estado;
                if (currentEstadoId && String(currentEstadoId) === String(state.id_estado)) {
                    option.selected = true;
                }
                stateSelect.appendChild(option);
            });
            if (stateSelect.value) {
                loadCities(stateSelect.value);
            }
        } catch (error) {
            stateSelect.innerHTML = '<option value=\"\">Erro ao carregar</option>';
        }
    };

    const loadCities = async (stateId) => {
        if (!stateId) {
            citySelect.innerHTML = '<option value=\"\">Selecione o estado primeiro</option>';
            return;
        }
        citySelect.innerHTML = '<option value=\"\">Carregando cidades...</option>';
        try {
            const response = await fetch(citiesUrlTemplate.replace(':id', stateId));
            const data = await response.json();
            citySelect.innerHTML = '<option value=\"\">Selecione a cidade</option>';
            data.forEach((city) => {
                const option = document.createElement('option');
                option.value = city.id_cidade;
                option.dataset.name = city.nome_cidade;
                option.textContent = city.nome_cidade;
                if (currentCidadeId && String(currentCidadeId) === String(city.id_cidade)) {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            });
        } catch (error) {
            citySelect.innerHTML = '<option value=\"\">Erro ao carregar</option>';
        }
    };

    stateSelect.addEventListener('change', function onStateChange() {
        loadCities(this.value);
    });

    if (zipcodeField) {
        zipcodeField.addEventListener('input', (event) => {
            let value = event.target.value.replace(/\\D/g, '');
            if (value.length > 8) value = value.substring(0, 8);
            if (value.length > 5) {
                value = value.substring(0, 5) + '-' + value.substring(5);
            }
            event.target.value = value;

            if (value.replace(/\\D/g, '').length === 8) {
                fetch(`https://viacep.com.br/ws/${value.replace(/\\D/g, '')}/json/`)
                    .then((response) => response.json())
                    .then((data) => {
                        if (!data.erro) {
                            if (addressField) addressField.value = data.logradouro;
                            if (neighborhoodField) neighborhoodField.value = data.bairro;
                            if (data.uf) {
                                for (let i = 0; i < stateSelect.options.length; i += 1) {
                                    if (stateSelect.options[i].dataset.abbr === data.uf) {
                                        stateSelect.selectedIndex = i;
                                        loadCities(stateSelect.value).then(() => {
                                            if (data.localidade) {
                                                for (let j = 0; j < citySelect.options.length; j += 1) {
                                                    if (
                                                        citySelect.options[j].dataset.name.toLowerCase() ===
                                                        data.localidade.toLowerCase()
                                                    ) {
                                                        citySelect.selectedIndex = j;
                                                        break;
                                                    }
                                                }
                                            }
                                        });
                                        break;
                                    }
                                }
                            }
                        }
                    });
            }
        });
    }

    loadStates();
}

function bindFinanceConfig() {
    if (!document.getElementById('billing_mode')) {
        return;
    }
    const $ = window.jQuery;
    if (!$) {
        return;
    }

    const updateBillingModeFields = () => {
        const mode = $('#billing_mode').val();
        $('#billing_amounts_global').hide();
        $('#billing_prices_management').hide();
        if (mode === 'global') {
            $('#billing_amounts_global').show();
        } else if (mode === 'per_doctor' || mode === 'per_doctor_specialty') {
            $('#billing_prices_management').show();
        }
    };

    $('#billing_mode').on('change', updateBillingModeFields);
    updateBillingModeFields();

    $('#doctor_commission_enabled').on('change', function onCommissionChange() {
        if ($(this).is(':checked')) {
            $('#commission_percentage_group').show();
        } else {
            $('#commission_percentage_group').hide();
        }
    });

    $(document).on('change', '.billing-type', function onBillingTypeChange() {
        const row = $(this).closest('tr');
        const type = $(this).val();
        const reservationInput = row.find('.reservation-amount');
        const fullInput = row.find('.full-amount');
        if (type === 'reservation') {
            fullInput.prop('disabled', true).val('0.00');
            reservationInput.prop('disabled', false);
        } else if (type === 'full') {
            reservationInput.prop('disabled', true).val('0.00');
            fullInput.prop('disabled', false);
        }
    });

    $('.billing-type').each(function initBillingType() {
        $(this).trigger('change');
    });

    let removedPrices = [];
    $(document).on('click', '.remove-price', function onRemovePrice() {
        const priceId = $(this).data('price-id');
        if (!priceId) return;
        const row = $(this).closest('tr');
        confirmAction({
            title: 'Remover preço',
            message: 'Tem certeza que deseja remover este preço?',
            confirmText: 'Remover',
            cancelText: 'Cancelar',
            type: 'warning',
            onConfirm: () => {
                removedPrices.push(priceId);
                $('#removed_prices').val(removedPrices.join(','));
                row.find('.reservation-amount, .full-amount').val('0.00');
            },
        });
    });
}
