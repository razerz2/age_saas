export function init() {
    bindAlertDismiss();
    bindPasswordTools();
    bindAvatarPreview();
    bindWebcam();
}

function bindAlertDismiss() {
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-dismiss-alert]');
        if (!btn) {
            return;
        }
        const container = btn.closest('.bg-green-50') || btn.parentElement?.parentElement;
        if (container) {
            container.remove();
        }
    });
}

function bindPasswordTools() {
    document.addEventListener('click', (event) => {
        const toggleBtn = event.target.closest('[data-toggle-password-target]');
        if (toggleBtn) {
            const fieldId = toggleBtn.dataset.togglePasswordTarget;
            togglePasswordVisibility(fieldId);
            return;
        }

        const generateBtn = event.target.closest('[data-generate-password]');
        if (generateBtn) {
            generatePassword();
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

function generatePassword() {
    const password = generateStrongPassword();
    const passwordField = document.getElementById('password');
    const confirmField = document.getElementById('password_confirmation');
    const passwordIcon = document.getElementById('password-eye-icon');
    const confirmIcon = document.getElementById('password_confirmation-eye-icon');

    if (!passwordField || !confirmField || !passwordIcon || !confirmIcon) {
        return;
    }

    passwordField.value = password;
    confirmField.value = password;

    passwordField.type = 'text';
    confirmField.type = 'text';

    passwordIcon.innerHTML =
        '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21\"></path>';
    confirmIcon.innerHTML =
        '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21\"></path>';

    passwordField.select();

    setTimeout(() => {
        passwordField.type = 'password';
        confirmField.type = 'password';
        passwordIcon.innerHTML =
            '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 12a3 3 0 11-6 0 3 3 0 016 0z\"></path><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\"></path>';
        confirmIcon.innerHTML =
            '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 12a3 3 0 11-6 0 3 3 0 016 0z\"></path><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\"></path>';
    }, 3000);
}

function bindAvatarPreview() {
    const avatarInput = document.getElementById('avatar-input');
    const avatarPreviewContainer = document.getElementById('avatar-preview-container');
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarFilename = document.getElementById('avatar-filename');
    const avatarRemove = document.getElementById('avatar-remove');

    const showPreview = (file) => {
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (event) => {
                avatarPreview.src = event.target.result;
                avatarFilename.textContent = file.name;
                avatarPreviewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            showAlert({
                type: 'warning',
                title: 'Atenção',
                message: 'Por favor, selecione um arquivo de imagem válido.',
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
                        title: 'Atenção',
                        message: 'O arquivo é muito grande. Por favor, selecione uma imagem com no máximo 2MB.',
                    });
                    avatarInput.value = '';
                    avatarPreviewContainer.style.display = 'none';
                    return;
                }
                showPreview(file);
            }
        });
    }

    if (avatarRemove) {
        avatarRemove.addEventListener('click', () => {
            avatarInput.value = '';
            avatarPreviewContainer.style.display = 'none';
            avatarPreview.src = '';
            avatarFilename.textContent = '';
        });
    }
}

function bindWebcam() {
    const webcamBtn = document.getElementById('webcam-btn');
    const webcamVideo = document.getElementById('webcam-video');
    const webcamCanvas = document.getElementById('webcam-canvas');
    const webcamPlaceholder = document.getElementById('webcam-placeholder');
    const webcamStart = document.getElementById('webcam-start');
    const webcamCapture = document.getElementById('webcam-capture');
    const webcamStop = document.getElementById('webcam-stop');
    const webcamModalElement = document.getElementById('webcam-modal');
    const avatarInput = document.getElementById('avatar-input');

    let stream = null;

    const stopWebcam = () => {
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }
        if (webcamVideo) {
            webcamVideo.srcObject = null;
            webcamVideo.style.display = 'none';
        }
        if (webcamPlaceholder) {
            webcamPlaceholder.style.display = 'block';
        }
        if (webcamStart) {
            webcamStart.style.display = 'inline-block';
        }
        if (webcamCapture) {
            webcamCapture.style.display = 'none';
        }
        if (webcamStop) {
            webcamStop.style.display = 'none';
        }
    };

    const closeWebcamModal = () => {
        if (!webcamModalElement) {
            return;
        }
        webcamModalElement.classList.add('hidden');
        webcamModalElement.classList.remove('flex');
        stopWebcam();
    };

    const openWebcamModal = () => {
        if (!webcamModalElement) {
            return;
        }
        webcamModalElement.classList.remove('hidden');
        webcamModalElement.classList.add('flex');
    };

    if (webcamBtn) {
        webcamBtn.addEventListener('click', openWebcamModal);
    }

    document.addEventListener('click', (event) => {
        const closeBtn = event.target.closest('[data-webcam-close]');
        if (closeBtn) {
            closeWebcamModal();
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
                webcamVideo.srcObject = stream;
                webcamVideo.style.display = 'block';
                webcamPlaceholder.style.display = 'none';
                webcamStart.style.display = 'none';
                webcamCapture.style.display = 'inline-block';
                webcamStop.style.display = 'inline-block';
            } catch (err) {
                showAlert({ type: 'error', title: 'Erro', message: `Erro ao acessar a webcam: ${err.message}` });
                // eslint-disable-next-line no-console
                console.error('Erro ao acessar webcam:', err);
            }
        });
    }

    if (webcamCapture) {
        webcamCapture.addEventListener('click', () => {
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

                    const event = new Event('change');
                    avatarInput.dispatchEvent(event);

                    stopWebcam();
                    closeWebcamModal();
                }
            }, 'image/jpeg', 0.9);
        });
    }

    if (webcamStop) {
        webcamStop.addEventListener('click', stopWebcam);
    }

    if (webcamModalElement) {
        webcamModalElement.addEventListener('click', (event) => {
            if (event.target === webcamModalElement) {
                closeWebcamModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !webcamModalElement.classList.contains('hidden')) {
                closeWebcamModal();
            }
        });
    }
}
