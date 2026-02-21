export function init() {
    bindDeleteConfirm();
    bindUsersIndexRowClick();
    bindPasswordTools();
    bindAvatarPreview();
    bindWebcam();
    bindRoleModules();
    bindModuleBulkActions();
    exposeLegacyDeleteConfirm();
}

function bindUsersIndexRowClick() {
    const grid = document.getElementById('users-grid');
    if (!grid) {
        return;
    }

    const wrapper = document.getElementById('users-grid-wrapper');
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
        if (showLink && showLink.href) {
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
        grid.querySelectorAll('tbody tr').forEach((row) => {
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

function bindModuleBulkActions() {
    document.addEventListener('click', (event) => {
        const selectAllBtn = event.target.closest('[data-modules-select-all]');
        if (selectAllBtn) {
            event.preventDefault();
            document.querySelectorAll('.module-checkbox').forEach((checkbox) => {
                if (!checkbox.disabled) {
                    checkbox.checked = true;
                }
            });
            return;
        }

        const clearBtn = event.target.closest('[data-modules-clear]');
        if (clearBtn) {
            event.preventDefault();
            document.querySelectorAll('.module-checkbox').forEach((checkbox) => {
                if (!checkbox.disabled) {
                    checkbox.checked = false;
                }
            });
        }
    });
}

function bindDeleteConfirm() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-confirm-user-delete]');
        if (!form) {
            return;
        }
        event.preventDefault();

        const userName = form.dataset.userName || 'este usuÃ¡rio';
        confirmAction({
            title: 'Excluir usuÃ¡rio',
            message: `Tem certeza que deseja excluir ${userName}? Esta aÃ§Ã£o nÃ£o pode ser desfeita.`,
            confirmText: 'Excluir',
            cancelText: 'Cancelar',
            type: 'error',
            onConfirm: () => form.submit(),
        });
    });
}

function exposeLegacyDeleteConfirm() {
    window.confirmDeleteUser = (event, userName) => {
        if (event) {
            event.preventDefault();
        }
        const form = event?.target?.closest('form');
        if (!form) {
            return false;
        }
        confirmAction({
            title: 'Excluir usuÃ¡rio',
            message: `Tem certeza que deseja excluir o usuÃ¡rio "${userName}"?\n\nEsta aÃ§Ã£o nÃ£o pode ser desfeita.`,
            confirmText: 'Excluir',
            cancelText: 'Cancelar',
            type: 'error',
            onConfirm: () => form.submit(),
        });
        return false;
    };
}

function bindPasswordTools() {
    document.addEventListener('click', (event) => {
        const toggleBtn = event.target.closest('[data-toggle-password-target]');
        if (toggleBtn) {
            togglePasswordVisibility(toggleBtn.dataset.togglePasswordTarget);
            return;
        }

        const generateBtn = event.target.closest('[data-generate-password]');
        if (generateBtn) {
            const targetField = generateBtn.dataset.generatePassword;
            const confirmField = generateBtn.dataset.generatePasswordConfirm;
            generatePassword(targetField, confirmField);
        }
    });
}

function generateStrongPassword() {
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const special = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    let password = '';
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += special[Math.floor(Math.random() * special.length)];

    const all = uppercase + lowercase + numbers + special;
    for (let i = password.length; i < 12; i += 1) {
        password += all[Math.floor(Math.random() * all.length)];
    }

    return password
        .split('')
        .sort(() => Math.random() - 0.5)
        .join('');
}

function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(`${fieldId}-eye-icon`);

    if (!field || !icon) {
        return;
    }

    if (field.type === 'password') {
        field.type = 'text';
        icon.innerHTML =
            '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21\"></path>';
    } else {
        field.type = 'password';
        icon.innerHTML =
            '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 12a3 3 0 11-6 0 3 3 0 016 0z\"></path><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\"></path>';
    }
}

function generatePassword(fieldId, confirmId) {
    const password = generateStrongPassword();
    const passwordField = document.getElementById(fieldId);
    const confirmField = confirmId ? document.getElementById(confirmId) : null;
    const passwordIcon = document.getElementById(`${fieldId}-eye-icon`);
    const confirmIcon = confirmField ? document.getElementById(`${confirmId}-eye-icon`) : null;

    if (!passwordField || !passwordIcon) {
        return;
    }

    passwordField.value = password;
    if (confirmField) {
        confirmField.value = password;
    }

    passwordField.type = 'text';
    if (confirmField) {
        confirmField.type = 'text';
    }

    passwordIcon.innerHTML =
        '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21\"></path>';
    if (confirmIcon) {
        confirmIcon.innerHTML =
            '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21\"></path>';
    }

    passwordField.select();

    setTimeout(() => {
        passwordField.type = 'password';
        if (confirmField) {
            confirmField.type = 'password';
        }
        passwordIcon.innerHTML =
            '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 12a3 3 0 11-6 0 3 3 0 016 0z\"></path><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\"></path>';
        if (confirmIcon) {
            confirmIcon.innerHTML =
                '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 12a3 3 0 11-6 0 3 3 0 016 0z\"></path><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\"></path>';
        }
    }, 3000);
}

function bindAvatarPreview() {
    const avatarInput = document.getElementById('avatar-input');
    const avatarPreviewContainer = document.getElementById('avatar-preview-container');
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarFilename = document.getElementById('avatar-filename');
    const avatarRemove = document.getElementById('avatar-remove');
    const config = document.getElementById('users-config');

    const originalAvatar = config?.dataset.originalAvatar || '';
    const hasOriginalAvatar = config?.dataset.hasOriginalAvatar === '1';

    const showPreview = (file) => {
        if (!avatarPreviewContainer || !avatarPreview || !avatarFilename) {
            return;
        }
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (event) => {
                avatarPreview.src = event.target.result;
                avatarFilename.textContent = file.name;
                avatarPreviewContainer.classList.remove('hidden');
                avatarPreviewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            showAlert({
                type: 'warning',
                title: 'AtenÃ§Ã£o',
                message: 'Por favor, selecione um arquivo de imagem vÃ¡lido.',
            });
            if (avatarInput) {
                avatarInput.value = '';
            }
        }
    };

    if (avatarInput) {
        avatarInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2048 * 1024) {
                    showAlert({
                        type: 'warning',
                        title: 'AtenÃ§Ã£o',
                        message: 'O arquivo Ã© muito grande. Por favor, selecione uma imagem com no mÃ¡ximo 2MB.',
                    });
                    avatarInput.value = '';
                    if (avatarPreviewContainer) {
                        if (!hasOriginalAvatar) {
                            avatarPreviewContainer.classList.add('hidden');
                            avatarPreviewContainer.style.display = 'none';
                        }
                    }
                    return;
                }
                showPreview(file);
            }
        });
    }

    if (avatarRemove) {
        avatarRemove.addEventListener('click', () => {
            if (avatarInput) {
                avatarInput.value = '';
            }
            if (avatarPreview && originalAvatar) {
                avatarPreview.src = originalAvatar;
            }
            if (avatarFilename) {
                avatarFilename.textContent = hasOriginalAvatar ? 'Imagem atual do usuÃ¡rio' : 'Nenhuma imagem selecionada';
            }
            if (avatarPreviewContainer && !hasOriginalAvatar) {
                avatarPreviewContainer.classList.add('hidden');
                avatarPreviewContainer.style.display = 'none';
            }
        });
    }
}

function bindWebcam() {
    const webcamBtn = document.getElementById('webcam-btn');
    const webcamModalElement = document.getElementById('webcam-modal');
    const webcamVideo = document.getElementById('webcam-video');
    const webcamCanvas = document.getElementById('webcam-canvas');
    const webcamPlaceholder = document.getElementById('webcam-placeholder');
    const webcamStart = document.getElementById('webcam-start');
    const webcamCapture = document.getElementById('webcam-capture');
    const webcamStop = document.getElementById('webcam-stop');
    const avatarInput = document.getElementById('avatar-input');

    if (!webcamBtn || !webcamModalElement) {
        return;
    }

    let stream = null;

    const stopWebcam = () => {
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }
        if (webcamVideo) {
            webcamVideo.srcObject = null;
            webcamVideo.classList.add('hidden');
        }
        if (webcamPlaceholder) {
            webcamPlaceholder.classList.remove('hidden');
        }
        if (webcamStart) {
            webcamStart.classList.remove('hidden');
        }
        if (webcamCapture) {
            webcamCapture.classList.add('hidden');
        }
        if (webcamStop) {
            webcamStop.classList.add('hidden');
        }
    };

    const openModal = () => {
        webcamModalElement.classList.remove('hidden');
    };

    const closeModal = () => {
        webcamModalElement.classList.add('hidden');
        stopWebcam();
    };

    webcamBtn.addEventListener('click', openModal);

    document.addEventListener('click', (event) => {
        const closeBtn = event.target.closest('[data-webcam-close]');
        if (closeBtn) {
            closeModal();
        }
    });

    if (webcamStart) {
        webcamStart.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                        facingMode: 'user',
                    },
                });
                if (webcamVideo) {
                    webcamVideo.srcObject = stream;
                    webcamVideo.classList.remove('hidden');
                }
                if (webcamPlaceholder) {
                    webcamPlaceholder.classList.add('hidden');
                }
                webcamStart.classList.add('hidden');
                if (webcamCapture) {
                    webcamCapture.classList.remove('hidden');
                }
                if (webcamStop) {
                    webcamStop.classList.remove('hidden');
                }
            } catch (err) {
                showAlert({ type: 'error', title: 'Erro', message: `Erro ao acessar a webcam: ${err.message}` });
                // eslint-disable-next-line no-console
                console.error('Erro ao acessar webcam:', err);
            }
        });
    }

    if (webcamCapture) {
        webcamCapture.addEventListener('click', () => {
            if (!webcamCanvas || !webcamVideo || !avatarInput) {
                return;
            }
            const context = webcamCanvas.getContext('2d');
            webcamCanvas.width = webcamVideo.videoWidth;
            webcamCanvas.height = webcamVideo.videoHeight;
            context.drawImage(webcamVideo, 0, 0);

            webcamCanvas.toBlob((blob) => {
                if (blob) {
                    const file = new File([blob], 'webcam-photo.jpg', { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    avatarInput.files = dataTransfer.files;

                    const changeEvent = new Event('change');
                    avatarInput.dispatchEvent(changeEvent);

                    stopWebcam();
                    closeModal();
                }
            }, 'image/jpeg', 0.9);
        });
    }

    if (webcamStop) {
        webcamStop.addEventListener('click', stopWebcam);
    }
}

function bindRoleModules() {
    const config = document.getElementById('users-config');
    const roleSelect = document.getElementById('role-select');
    if (!config || !roleSelect) {
        return;
    }

    const defaultModulesData = {
        user: parseJson(config.dataset.defaultModulesUser),
        doctor: parseJson(config.dataset.defaultModulesDoctor),
        admin: [],
    };
    const loggedUserRole = config.dataset.loggedRole || '';
    const settingsUrl = config.dataset.settingsUrl || '';
    const initialRole = config.dataset.initialRole || roleSelect.value;

    const isDoctorSelect = document.getElementById('is-doctor-select');
    const doctorPermissionsSection = document.getElementById('doctor-permissions-section');
    const modulesSection = document.getElementById('modules-section');
    const isDoctorSection = document.getElementById('is-doctor-section');
    const modulesInfoText = document.getElementById('modules-info-text');
    const modulesPresenceInput = modulesSection?.querySelector('input[name="modules_present"]') || null;

    const setVisibility = (element, show) => {
        if (!element) {
            return;
        }
        if (element.classList.contains('hidden')) {
            element.classList.toggle('hidden', !show);
            return;
        }
        element.style.display = show ? 'block' : 'none';
    };

    const updateModulesSelection = (role) => {
        const defaultModules = defaultModulesData[role] || [];
        const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
        const hasCheckedModules = Array.from(moduleCheckboxes).some((checkbox) => checkbox.checked);

        if (!hasCheckedModules || role !== initialRole) {
            moduleCheckboxes.forEach((checkbox) => {
                const moduleKey = checkbox.getAttribute('data-module-key');
                checkbox.checked = defaultModules.includes(moduleKey);
            });
        }
    };

    const updateModulesInfo = (role) => {
        if (!modulesInfoText || !settingsUrl) {
            return;
        }
        if (role === 'doctor') {
            modulesInfoText.innerHTML =
                `<strong>Nota:</strong> Os módulos foram pré-selecionados conforme as configurações padrão para médicos em <a href="${settingsUrl}" target="_blank" class="text-blue-600 underline hover:text-blue-800">Configurações → Usuários & Permissões</a>. Você pode ajustar manualmente se necessário.`;
        } else {
            modulesInfoText.innerHTML =
                `<strong>Nota:</strong> Os módulos foram pré-selecionados conforme as configurações padrão para usuários comuns em <a href="${settingsUrl}" target="_blank" class="text-blue-600 underline hover:text-blue-800">Configurações → Usuários & Permissões</a>. Você pode ajustar manualmente se necessário.`;
        }
    };

    const toggleRoleSections = () => {
        const role = roleSelect.value;

        if (isDoctorSection) {
            setVisibility(isDoctorSection, role === 'admin');
            if (role !== 'admin' && isDoctorSelect && role !== 'doctor') {
                isDoctorSelect.value = '0';
            }
        }

        if (isDoctorSelect && role === 'doctor') {
            isDoctorSelect.value = '1';
        }

        if (doctorPermissionsSection) {
            setVisibility(doctorPermissionsSection, role === 'user' && loggedUserRole !== 'doctor');
        }

        if (modulesSection) {
            if (role === 'admin') {
                setVisibility(modulesSection, false);
                document.querySelectorAll('.module-checkbox').forEach((checkbox) => {
                    checkbox.checked = false;
                    checkbox.disabled = true;
                });
                if (modulesPresenceInput) {
                    modulesPresenceInput.disabled = true;
                }
            } else {
                setVisibility(modulesSection, true);
                document.querySelectorAll('.module-checkbox').forEach((checkbox) => {
                    checkbox.disabled = false;
                });
                if (modulesPresenceInput) {
                    modulesPresenceInput.disabled = false;
                }
                updateModulesInfo(role);
                updateModulesSelection(role);
            }
        }
    };

    roleSelect.addEventListener('change', toggleRoleSections);
    toggleRoleSections();
}

function parseJson(value) {
    if (!value) {
        return [];
    }
    try {
        return JSON.parse(value);
    } catch (error) {
        return [];
    }
}
