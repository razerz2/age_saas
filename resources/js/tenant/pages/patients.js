export function init() {
    bindStopPropagation();
    bindConfirmSubmits();
    bindCopyButtons();
    bindPasswordToggle();
    bindLoginForm();
    bindAddressForm();
}

function bindStopPropagation() {
    document.addEventListener('click', (event) => {
        const el = event.target.closest('[data-stop-propagation]');
        if (el) {
            event.stopPropagation();
        }
    });
}

function bindConfirmSubmits() {
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-confirm-submit]');
        if (!btn) {
            return;
        }
        event.preventDefault();
        const form = btn.closest('form');
        if (!form) {
            return;
        }
        confirmAction({
            title: btn.dataset.confirmTitle || 'Confirmar',
            message: btn.dataset.confirmMessage || 'Deseja continuar?',
            confirmText: btn.dataset.confirmConfirmText || 'Confirmar',
            cancelText: btn.dataset.confirmCancelText || 'Cancelar',
            type: btn.dataset.confirmType || 'warning',
            onConfirm: () => form.submit(),
        });
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm-submit=\"true\"]');
        if (!form) {
            return;
        }
        event.preventDefault();
        confirmAction({
            title: form.dataset.confirmTitle || 'Confirmar',
            message: form.dataset.confirmMessage || 'Deseja continuar?',
            confirmText: form.dataset.confirmConfirmText || 'Confirmar',
            cancelText: form.dataset.confirmCancelText || 'Cancelar',
            type: form.dataset.confirmType || 'warning',
            onConfirm: () => form.submit(),
        });
    });
}

function bindCopyButtons() {
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-copy-target]');
        if (!btn) {
            return;
        }
        const inputId = btn.dataset.copyTarget;
        if (!inputId) {
            return;
        }
        const input = document.getElementById(inputId);
        if (!input) {
            return;
        }

        input.select();
        input.setSelectionRange(0, 99999);

        const onSuccess = () => {
            const originalHtml = btn.dataset.originalHtml || btn.innerHTML;
            if (!btn.dataset.originalHtml) {
                btn.dataset.originalHtml = originalHtml;
            }
            btn.innerHTML =
                '<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 13l4 4L19 7\"></path></svg>';
            btn.classList.remove(
                'border-gray-300',
                'text-gray-700',
                'hover:bg-gray-50',
                'dark:border-gray-600',
                'dark:text-gray-300',
                'dark:hover:bg-gray-700',
            );
            btn.classList.add('bg-green-600', 'text-white');

            setTimeout(() => {
                btn.innerHTML = btn.dataset.originalHtml || originalHtml;
                btn.classList.add(
                    'border-gray-300',
                    'text-gray-700',
                    'hover:bg-gray-50',
                    'dark:border-gray-600',
                    'dark:text-gray-300',
                    'dark:hover:bg-gray-700',
                );
                btn.classList.remove('bg-green-600', 'text-white');
            }, 2000);
        };

        const onError = () => {
            showAlert({ type: 'error', title: 'Erro', message: 'Erro ao copiar. Por favor, copie manualmente.' });
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(input.value).then(onSuccess).catch(onError);
        } else {
            try {
                document.execCommand('copy');
                onSuccess();
            } catch (err) {
                onError();
            }
        }
    });
}

function bindPasswordToggle() {
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-toggle-password-target]');
        if (!btn) {
            return;
        }
        const inputId = btn.dataset.togglePasswordTarget;
        const input = document.getElementById(inputId);
        if (!input) {
            return;
        }
        const icon = btn.querySelector('svg');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.innerHTML =
                    '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21\"></path>';
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.innerHTML =
                    '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 12a3 3 0 11-6 0 3 3 0 016 0z\"></path><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\"></path>';
            }
        }
    });
}

function bindLoginForm() {
    const config = document.getElementById('patients-login-form-config');
    if (!config) {
        return;
    }

    const generatePasswordBtn = document.getElementById('generatePassword');
    if (generatePasswordBtn) {
        generatePasswordBtn.addEventListener('click', () => {
            const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const lowercase = 'abcdefghijklmnopqrstuvwxyz';
            const numbers = '0123456789';
            const symbols = '!@#$%&*';
            const allChars = uppercase + lowercase + numbers + symbols;

            let password = '';
            password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
            password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
            password += numbers.charAt(Math.floor(Math.random() * numbers.length));
            password += symbols.charAt(Math.floor(Math.random() * symbols.length));

            for (let i = password.length; i < 12; i += 1) {
                password += allChars.charAt(Math.floor(Math.random() * allChars.length));
            }

            password = password
                .split('')
                .sort(() => Math.random() - 0.5)
                .join('');

            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.value = password;
            }
            const confirmField = document.getElementById('password_confirmation');
            if (confirmField) {
                confirmField.value = password;
            }

            if (passwordField && passwordField.type === 'password') {
                passwordField.type = 'text';
                setTimeout(() => {
                    passwordField.type = 'password';
                }, 5000);
            }
        });
    }

    if (config.dataset.requireConfirmation !== '1') {
        return;
    }

    const validatePasswordConfirmation = () => {
        const password = document.getElementById('password')?.value || '';
        const confirmationField = document.getElementById('password_confirmation');
        const confirmation = confirmationField?.value || '';

        if (password && confirmation) {
            if (password !== confirmation) {
                confirmationField?.classList.add('border-red-500');
                const existingError = document.getElementById('password-confirmation-error');
                if (!existingError && confirmationField) {
                    const errorDiv = document.createElement('p');
                    errorDiv.id = 'password-confirmation-error';
                    errorDiv.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                    errorDiv.textContent = 'As senhas nÃ£o coincidem.';
                    confirmationField.parentNode.appendChild(errorDiv);
                }
                return false;
            }
            confirmationField?.classList.remove('border-red-500');
            const existingError = document.getElementById('password-confirmation-error');
            if (existingError) {
                existingError.remove();
            }
        }
        return true;
    };

    const passwordField = document.getElementById('password');
    const confirmationField = document.getElementById('password_confirmation');
    passwordField?.addEventListener('keyup', validatePasswordConfirmation);
    confirmationField?.addEventListener('keyup', validatePasswordConfirmation);

    const form = document.querySelector('form');
    form?.addEventListener('submit', (event) => {
        if (!validatePasswordConfirmation()) {
            event.preventDefault();
            showAlert({
                type: 'warning',
                title: 'Atenção',
                message: 'As senhas nÃ£o coincidem. Por favor, verifique.',
            });
        }
    });
}

function bindAddressForm() {
    const config = document.getElementById('patients-address-config');
    if (!config) {
        return;
    }

    const stateSelect = document.getElementById('state_id');
    const citySelect = document.getElementById('city_id');
    const zipcodeField = document.getElementById('zipcode');
    const addressField = document.getElementById('address');
    const neighborhoodField = document.getElementById('neighborhood');
    const stateAbbrInput = document.getElementById('state_abbr');
    const cityNameInput = document.getElementById('city_name');

    const currentStateId = config.dataset.currentStateId || '';
    const currentCityId = config.dataset.currentCityId || '';

    const statesUrl = config.dataset.statesUrl || '';
    const citiesUrlTemplate = config.dataset.citiesUrlTemplate || '';

    const loadStates = async () => {
        if (!stateSelect || !statesUrl) {
            return;
        }
        stateSelect.innerHTML = '<option value=\"\">Carregando estados...</option>';
        try {
            const response = await fetch(statesUrl);
            const data = await response.json();
            stateSelect.innerHTML = '<option value=\"\">Selecione o estado</option>';
            data.forEach((state) => {
                const option = document.createElement('option');
                option.value = state.id_estado;
                option.dataset.abbr = state.uf;
                option.textContent = state.nome_estado;
                if (currentStateId && String(currentStateId) === String(state.id_estado)) {
                    option.selected = true;
                }
                stateSelect.appendChild(option);
            });

            if (stateSelect.value) {
                await loadCities(stateSelect.value);
            }
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Erro ao carregar estados:', error);
            stateSelect.innerHTML = '<option value=\"\">Erro ao carregar</option>';
        }
    };

    const loadCities = async (stateId) => {
        if (!citySelect || !stateId || !citiesUrlTemplate) {
            if (citySelect) {
                citySelect.innerHTML = '<option value=\"\">Selecione o estado primeiro</option>';
            }
            return;
        }
        citySelect.innerHTML = '<option value=\"\">Carregando cidades...</option>';
        try {
            const response = await fetch(citiesUrlTemplate.replace('__ID__', stateId));
            const data = await response.json();
            citySelect.innerHTML = '<option value=\"\">Selecione a cidade</option>';
            data.forEach((city) => {
                const option = document.createElement('option');
                option.value = city.id_cidade;
                option.dataset.name = city.nome_cidade;
                option.textContent = city.nome_cidade;
                if (currentCityId && String(currentCityId) === String(city.id_cidade)) {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            });
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Erro ao carregar cidades:', error);
            citySelect.innerHTML = '<option value=\"\">Erro ao carregar</option>';
        }
    };

    stateSelect?.addEventListener('change', function onStateChange() {
        loadCities(this.value);
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption?.dataset?.abbr && stateAbbrInput) {
            stateAbbrInput.value = selectedOption.dataset.abbr;
        }
    });

    citySelect?.addEventListener('change', function onCityChange() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption?.dataset?.name && cityNameInput) {
            cityNameInput.value = selectedOption.dataset.name;
        }
    });

    zipcodeField?.addEventListener('input', (event) => {
        let value = event.target.value.replace(/\D/g, '');
        if (value.length > 8) value = value.substring(0, 8);
        if (value.length > 5) {
            value = `${value.substring(0, 5)}-${value.substring(5)}`;
        }
        event.target.value = value;

        if (value.replace(/\D/g, '').length === 8) {
            fetch(`https://viacep.com.br/ws/${value.replace(/\D/g, '')}/json/`)
                .then((response) => response.json())
                .then((data) => {
                    if (!data.erro) {
                        if (addressField) addressField.value = data.logradouro;
                        if (neighborhoodField) neighborhoodField.value = data.bairro;

                        if (data.uf && stateSelect) {
                            for (let i = 0; i < stateSelect.options.length; i += 1) {
                                if (stateSelect.options[i].dataset.abbr === data.uf) {
                                    stateSelect.selectedIndex = i;
                                    if (stateAbbrInput) {
                                        stateAbbrInput.value = data.uf;
                                    }
                                    loadCities(stateSelect.value).then(() => {
                                        if (data.localidade && citySelect) {
                                            for (let j = 0; j < citySelect.options.length; j += 1) {
                                                if (
                                                    citySelect.options[j].dataset.name.toLowerCase() ===
                                                    data.localidade.toLowerCase()
                                                ) {
                                                    citySelect.selectedIndex = j;
                                                    if (cityNameInput) {
                                                        cityNameInput.value = data.localidade;
                                                    }
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

    loadStates();
}
