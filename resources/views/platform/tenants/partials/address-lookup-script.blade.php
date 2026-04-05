<script>
    (function() {
        function onlyDigits(value) {
            return String(value || '').replace(/\D+/g, '');
        }

        function formatCep(value) {
            const digits = onlyDigits(value).slice(0, 8);
            if (digits.length <= 5) {
                return digits;
            }

            return digits.slice(0, 5) + '-' + digits.slice(5);
        }

        function resolveZipcodeMessage(response, payload) {
            if (payload && typeof payload.message === 'string' && payload.message.trim() !== '') {
                return payload.message;
            }

            if (response.status === 404) {
                return 'CEP não encontrado. Você pode preencher os campos manualmente.';
            }

            if (response.status === 422) {
                return 'CEP inválido. Informe 8 dígitos.';
            }

            return 'Não foi possível consultar o CEP agora. Continue o preenchimento manual.';
        }

        window.initTenantAddressLookup = function initTenantAddressLookup(config) {
            const cepInput = document.querySelector(config.cepSelector || '#tenant_cep');
            const addressInput = document.querySelector(config.addressSelector || '#tenant_endereco');
            const neighborhoodInput = document.querySelector(config.neighborhoodSelector || '#tenant_bairro');
            const stateSelect = document.querySelector(config.stateSelector || '#estado');
            const citySelect = document.querySelector(config.citySelector || '#cidade');
            const feedback = document.querySelector(config.feedbackSelector || '#tenantCepFeedback');

            if (!cepInput || !addressInput || !neighborhoodInput || !stateSelect || !citySelect) {
                return;
            }

            const statesUrl = config.statesUrl || '';
            const citiesUrlTemplate = config.citiesUrlTemplate || '';
            const zipcodeUrlTemplate = config.zipcodeUrlTemplate || '';
            const loadStatesOnInit = Boolean(config.loadStatesOnInit);

            let statesLoadPromise = null;
            let lastLookupCep = '';

            const setFeedback = (message, tone = 'muted') => {
                if (!feedback) {
                    return;
                }

                feedback.textContent = message || '';
                feedback.classList.remove('text-muted', 'text-success', 'text-warning', 'text-danger');

                if (!message) {
                    return;
                }

                const toneClassByKey = {
                    success: 'text-success',
                    warning: 'text-warning',
                    danger: 'text-danger',
                    muted: 'text-muted',
                };

                feedback.classList.add(toneClassByKey[tone] || 'text-muted');
            };

            const hasStateOption = (stateId) => {
                if (!stateId) {
                    return false;
                }

                return Array.from(stateSelect.options).some((option) => String(option.value) === String(stateId));
            };

            const loadStates = async (preferredStateId = '') => {
                if (!statesUrl) {
                    return;
                }

                const previousStateId = preferredStateId || stateSelect.value || '';

                stateSelect.innerHTML = '<option value="">Carregando estados...</option>';

                try {
                    const response = await fetch(statesUrl);
                    if (!response.ok) {
                        throw new Error('states_request_failed');
                    }

                    const states = await response.json();
                    stateSelect.innerHTML = '<option value="">Selecione...</option>';

                    states.forEach((state) => {
                        const option = document.createElement('option');
                        option.value = String(state.id_estado);
                        option.textContent = String(state.nome_estado);
                        stateSelect.appendChild(option);
                    });

                    if (previousStateId && hasStateOption(previousStateId)) {
                        stateSelect.value = String(previousStateId);
                    }
                } catch (error) {
                    stateSelect.innerHTML = '<option value="">Erro ao carregar estados</option>';
                }
            };

            const ensureStatesLoaded = async (preferredStateId = '') => {
                if (!loadStatesOnInit && stateSelect.options.length > 1 && !preferredStateId) {
                    return;
                }

                if (!statesLoadPromise || preferredStateId) {
                    statesLoadPromise = loadStates(preferredStateId);
                }

                await statesLoadPromise;
            };

            const loadCities = async (stateId, preferredCityId = '') => {
                if (!citiesUrlTemplate) {
                    return;
                }

                if (!stateId) {
                    citySelect.innerHTML = '<option value="">Selecione o estado primeiro</option>';
                    return;
                }

                citySelect.innerHTML = '<option value="">Carregando cidades...</option>';

                try {
                    const url = citiesUrlTemplate.replace('__ID__', String(stateId));
                    const response = await fetch(url);
                    if (!response.ok) {
                        throw new Error('cities_request_failed');
                    }

                    const cities = await response.json();
                    citySelect.innerHTML = '<option value="">Selecione...</option>';

                    cities.forEach((city) => {
                        const option = document.createElement('option');
                        option.value = String(city.id_cidade);
                        option.textContent = String(city.nome_cidade);
                        citySelect.appendChild(option);
                    });

                    if (preferredCityId) {
                        citySelect.value = String(preferredCityId);
                    }
                } catch (error) {
                    citySelect.innerHTML = '<option value="">Erro ao carregar cidades</option>';
                }
            };

            const applyZipcodePayload = async (payload) => {
                if (!payload || typeof payload !== 'object') {
                    return;
                }

                if (payload.street) {
                    addressInput.value = String(payload.street);
                }

                if (payload.neighborhood) {
                    neighborhoodInput.value = String(payload.neighborhood);
                }

                const stateId = payload.state && payload.state.id ? String(payload.state.id) : '';
                const cityId = payload.city && payload.city.id ? String(payload.city.id) : '';

                if (stateId) {
                    if (!hasStateOption(stateId)) {
                        await ensureStatesLoaded(stateId);
                    }

                    stateSelect.value = stateId;
                    await loadCities(stateId, cityId);
                }

                if (cityId) {
                    citySelect.value = cityId;
                }
            };

            const lookupZipcode = async (digits) => {
                if (digits.length !== 8 || !zipcodeUrlTemplate) {
                    return;
                }

                if (lastLookupCep === digits) {
                    return;
                }

                setFeedback('Consultando CEP...', 'muted');

                try {
                    const response = await fetch(zipcodeUrlTemplate.replace('__CEP__', digits));
                    let payload = null;

                    try {
                        payload = await response.json();
                    } catch (error) {
                        payload = null;
                    }

                    if (!response.ok) {
                        if (response.status >= 500) {
                            lastLookupCep = '';
                        } else {
                            lastLookupCep = digits;
                        }

                        setFeedback(resolveZipcodeMessage(response, payload), 'warning');
                        return;
                    }

                    await applyZipcodePayload(payload);
                    lastLookupCep = digits;

                    if (Array.isArray(payload.warnings) && payload.warnings.length > 0) {
                        setFeedback(payload.warnings[0], 'warning');
                        return;
                    }

                    setFeedback('Endereço preenchido pelo CEP.', 'success');
                } catch (error) {
                    lastLookupCep = '';
                    setFeedback('Falha ao consultar CEP. Continue o preenchimento manual.', 'warning');
                }
            };

            stateSelect.addEventListener('change', function() {
                loadCities(this.value);
            });

            cepInput.addEventListener('input', function(event) {
                const formatted = formatCep(event.target.value);
                event.target.value = formatted;

                const digits = onlyDigits(formatted);
                if (digits.length === 8) {
                    lookupZipcode(digits);
                }
            });

            cepInput.addEventListener('blur', function(event) {
                const digits = onlyDigits(event.target.value);

                if (digits.length === 0) {
                    setFeedback('');
                    return;
                }

                if (digits.length !== 8) {
                    setFeedback('CEP inválido. Informe 8 dígitos.', 'warning');
                    return;
                }

                lookupZipcode(digits);
            });

            if (loadStatesOnInit) {
                ensureStatesLoaded();
            }
        };
    })();
</script>
