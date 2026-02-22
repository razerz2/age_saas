<!-- Aba Profissionais -->
<div class="space-y-8">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Configurações de Profissionais</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Configure os rótulos personalizados para profissionais (Médico, Profissional, Psicólogo, etc.).
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.professionals') }}">
        @csrf
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Personalização de Rótulos</h3>
            </div>

            <div class="mb-6">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                               type="checkbox" 
                               id="professional_customization_enabled"
                               name="professional_customization_enabled"
                               value="1"
                               {{ ($settings['professional.customization_enabled'] ?? false) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Habilitar personalização por profissão?</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Quando desabilitado, o sistema sempre usa "Médico", "Médicos" e "CRM". 
                                Quando habilitado, você pode personalizar os rótulos globalmente, por especialidade ou por profissional individual.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <div id="professional_customization_fields" style="display: {{ ($settings['professional.customization_enabled'] ?? false) ? 'block' : 'none' }};">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="ml-3">
                            <strong class="block text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">Como funciona:</strong>
                            <ul class="text-xs text-blue-800 dark:text-blue-200 space-y-1">
                                <li><strong>Rótulos Globais:</strong> Aplicados quando não há personalização por especialidade ou profissional.</li>
                                <li><strong>Rótulos por Especialidade:</strong> Configure em cada especialidade para sobrescrever os globais.</li>
                                <li><strong>Rótulos Individuais:</strong> Configure em cada profissional para sobrescrever especialidade e globais.</li>
                                <li><strong>Hierarquia:</strong> Profissional individual → Especialidade → Global → Padrão</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Rótulo Singular (Global)
                        </label>
                        <input type="text" 
                               name="professional_label_singular" 
                               value="{{ $settings['professional.label_singular'] ?? '' }}" 
                               placeholder="Ex: Profissional, Psicólogo, Dentista"
                               maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Exemplo: "Profissional" ou "Psicólogo"</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Rótulo Plural (Global)
                        </label>
                        <input type="text" 
                               name="professional_label_plural" 
                               value="{{ $settings['professional.label_plural'] ?? '' }}" 
                               placeholder="Ex: Profissionais, Psicólogos, Dentistas"
                               maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Exemplo: "Profissionais" ou "Psicólogos"</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Rótulo de Registro (Global)
                        </label>
                        <input type="text" 
                               name="professional_registration_label" 
                               value="{{ $settings['professional.registration_label'] ?? '' }}" 
                               placeholder="Ex: CRM, CRP, CRO"
                               maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Exemplo: "CRM", "CRP" ou "CRO"</p>
                    </div>
                </div>
            </div>
        </div>

        @include('tenant.settings.partials.form-actions')
    </form>
</div>
