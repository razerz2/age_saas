<!-- Aba Profissionais -->
@php
    $customizationEnabled = (bool) ($settings['professional.customization_enabled'] ?? false);
    $environmentProfile = (string) old('professional_environment_profile', $settings['professional.environment_profile'] ?? '');
    if ($environmentProfile === '') {
        $environmentProfile = \App\Services\Tenant\ProfessionalLabelService::PROFILE_MEDICAL;
    }
@endphp

<div id="professional-settings-root"
     class="space-y-8"
     data-presets='@json($professionalEnvironmentPresets, JSON_UNESCAPED_UNICODE)'>
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Configurações de Profissionais</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Personalize apenas a camada visual do termo exibido para o perfil interno de médico.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.professionals') }}">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Personalização Visual</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Esta etapa altera apenas textos exibidos. Regras internas continuam usando o perfil médico.
                </p>
            </div>

            <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                <label class="flex items-start cursor-pointer">
                    <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                           type="checkbox"
                           id="professional_customization_enabled"
                           name="professional_customization_enabled"
                           value="1"
                           {{ $customizationEnabled ? 'checked' : '' }}>
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Habilitar personalização visual de profissionais</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Quando desabilitado, o sistema exibe sempre "Médico", "Médicos" e "CRM".
                        </span>
                    </div>
                </label>
            </div>

            <div id="professional_customization_fields" class="space-y-6" style="display: {{ $customizationEnabled ? 'block' : 'none' }};">
                <div>
                    <label for="professional_environment_profile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tipo do ambiente
                    </label>
                    <select id="professional_environment_profile"
                            name="professional_environment_profile"
                            class="w-full md:w-96 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        @foreach(($professionalEnvironmentProfiles ?? []) as $value => $label)
                            <option value="{{ $value }}" {{ $environmentProfile === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Presets preenchem os campos abaixo automaticamente, exceto em "Personalizado".
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="professional_label_singular" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Rótulo singular
                        </label>
                        <input type="text"
                               id="professional_label_singular"
                               name="professional_label_singular"
                               value="{{ old('professional_label_singular', $settings['professional.label_singular'] ?? '') }}"
                               maxlength="50"
                               placeholder="Ex: Médico, Psicólogo"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="professional_label_plural" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Rótulo plural
                        </label>
                        <input type="text"
                               id="professional_label_plural"
                               name="professional_label_plural"
                               value="{{ old('professional_label_plural', $settings['professional.label_plural'] ?? '') }}"
                               maxlength="50"
                               placeholder="Ex: Médicos, Psicólogos"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="professional_registration_label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Registro profissional
                        </label>
                        <input type="text"
                               id="professional_registration_label"
                               name="professional_registration_label"
                               value="{{ old('professional_registration_label', $settings['professional.registration_label'] ?? '') }}"
                               maxlength="50"
                               placeholder="Ex: CRM, CRP"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div class="border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-3">Preview em tempo real</h4>
                    <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                        <p><span id="preview_plural">Médicos</span></p>
                        <p>Agenda do <span id="preview_singular">Médico</span></p>
                        <p>Selecionar <span id="preview_select_singular">Médico</span></p>
                        <p>Registro: <span id="preview_registration">CRM</span></p>
                        <p>Nenhum <span id="preview_empty_singular">Médico</span> encontrado</p>
                    </div>
                </div>
            </div>
        </div>

        @include('tenant.settings.partials.form-actions')
    </form>
</div>

<script>
(function () {
    const root = document.getElementById('professional-settings-root');
    if (!root || root.dataset.initialized === 'true') {
        return;
    }
    root.dataset.initialized = 'true';

    const customizationToggle = document.getElementById('professional_customization_enabled');
    const customizationFields = document.getElementById('professional_customization_fields');
    const profileSelect = document.getElementById('professional_environment_profile');
    const singularInput = document.getElementById('professional_label_singular');
    const pluralInput = document.getElementById('professional_label_plural');
    const registrationInput = document.getElementById('professional_registration_label');

    const previewPlural = document.getElementById('preview_plural');
    const previewSingular = document.getElementById('preview_singular');
    const previewSelectSingular = document.getElementById('preview_select_singular');
    const previewRegistration = document.getElementById('preview_registration');
    const previewEmptySingular = document.getElementById('preview_empty_singular');

    const presets = JSON.parse(root.dataset.presets || '{}');

    const fallback = (value, defaultValue) => {
        const text = (value || '').trim();
        return text !== '' ? text : defaultValue;
    };

    const updateVisibility = () => {
        if (!customizationToggle || !customizationFields) {
            return;
        }

        customizationFields.style.display = customizationToggle.checked ? 'block' : 'none';
    };

    const updatePreview = () => {
        const singular = fallback(singularInput ? singularInput.value : '', 'Médico');
        const plural = fallback(pluralInput ? pluralInput.value : '', 'Médicos');
        const registration = fallback(registrationInput ? registrationInput.value : '', 'CRM');

        if (previewPlural) previewPlural.textContent = plural;
        if (previewSingular) previewSingular.textContent = singular;
        if (previewSelectSingular) previewSelectSingular.textContent = singular;
        if (previewRegistration) previewRegistration.textContent = registration;
        if (previewEmptySingular) previewEmptySingular.textContent = singular;
    };

    const applyPreset = (force) => {
        if (!profileSelect) {
            return;
        }

        const profile = (profileSelect.value || '').trim();
        if (profile === '' || profile === 'custom') {
            return;
        }

        const preset = presets[profile];
        if (!preset) {
            return;
        }

        const allEmpty =
            fallback(singularInput ? singularInput.value : '', '') === '' &&
            fallback(pluralInput ? pluralInput.value : '', '') === '' &&
            fallback(registrationInput ? registrationInput.value : '', '') === '';

        if (!force && !allEmpty) {
            return;
        }

        if (singularInput) singularInput.value = preset.singular || '';
        if (pluralInput) pluralInput.value = preset.plural || '';
        if (registrationInput) registrationInput.value = preset.registration || '';
    };

    if (customizationToggle) {
        customizationToggle.addEventListener('change', () => {
            if (customizationToggle.checked) {
                applyPreset(false);
            }
            updateVisibility();
            updatePreview();
        });
    }

    if (profileSelect) {
        profileSelect.addEventListener('change', () => {
            applyPreset(true);
            updatePreview();
        });
    }

    [singularInput, pluralInput, registrationInput].forEach((input) => {
        if (!input) {
            return;
        }

        input.addEventListener('input', updatePreview);
    });

    applyPreset(false);
    updateVisibility();
    updatePreview();
})();
</script>
