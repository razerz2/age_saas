<!-- Aba Aparência -->
<div class="space-y-8">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Personalização Visual</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Configure a aparência visual do sistema com logo, cores e outros elementos personalizados.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.appearance') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                    Logo e Identidade Visual
                </h3>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Logo Principal
                    </label>
                    <div class="space-y-3">
                        @if(!empty($settings['appearance.logo']))
                            <div class="flex items-center gap-4 p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <img src="{{ Storage::url($settings['appearance.logo']) }}" alt="Logo Principal" class="h-12 w-auto max-w-xs">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Logo atual</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Clique em "Remover" para usar o logo padrão</p>
                                </div>
                                <button type="button" 
                                        data-settings-action="remove-logo" data-remove-target="remove_main_logo"
                                        class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors duration-200">
                                    Remover
                                </button>
                                <input type="hidden" id="remove_main_logo" name="remove_main_logo" value="0">
                            </div>
                        @endif
                        <div>
                            <input type="file" 
                                   name="appearance_logo"
                                   accept="image/*"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Formatos: PNG, JPG, JPEG. Tamanho máximo: 2MB. Altura recomendada: 40px</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Logo Mini (para sidebar)
                    </label>
                    <div class="space-y-3">
                        @if(!empty($settings['appearance.logo_mini']))
                            <div class="flex items-center gap-4 p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <img src="{{ Storage::url($settings['appearance.logo_mini']) }}" alt="Logo Mini" class="h-8 w-auto">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Logo mini atual</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Clique em "Remover" para usar o logo padrão</p>
                                </div>
                                <button type="button" 
                                        data-settings-action="remove-logo" data-remove-target="remove_mini_logo"
                                        class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors duration-200">
                                    Remover
                                </button>
                                <input type="hidden" id="remove_mini_logo" name="remove_mini_logo" value="0">
                            </div>
                        @endif
                        <div>
                            <input type="file" 
                                   name="appearance_logo_mini"
                                   accept="image/*"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Formatos: PNG, JPG, JPEG. Tamanho máximo: 2MB. Altura recomendada: 32px</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Favicon
                    </label>
                    <div class="space-y-3">
                        @if(!empty($settings['appearance.favicon']))
                            <div class="flex items-center gap-4 p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <img src="{{ Storage::url($settings['appearance.favicon']) }}" alt="Favicon" class="h-8 w-8">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Favicon atual</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Clique em "Remover" para usar o favicon padrão</p>
                                </div>
                                <button type="button" 
                                        data-settings-action="remove-logo" data-remove-target="remove_favicon"
                                        class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors duration-200">
                                    Remover
                                </button>
                                <input type="hidden" id="remove_favicon" name="remove_favicon" value="0">
                            </div>
                        @endif
                        <div>
                            <input type="file" 
                                   name="appearance_favicon"
                                   accept="image/*"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Formatos: PNG, ICO. Tamanho recomendado: 32x32px</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                    Cores do Tema
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Cor Primária
                    </label>
                    <div class="flex gap-2">
                        <input type="color" 
                               name="appearance_primary_color"
                               value="{{ $settings['appearance.primary_color'] ?? '#2563eb' }}"
                               class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded">
                        <input type="text" 
                               name="appearance_primary_color_text"
                               value="{{ $settings['appearance.primary_color'] ?? '#2563eb' }}"
                               placeholder="#2563eb"
                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cor principal dos botões e elementos interativos</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Cor de Destaque
                    </label>
                    <div class="flex gap-2">
                        <input type="color" 
                               name="appearance_accent_color"
                               value="{{ $settings['appearance.accent_color'] ?? '#10b981' }}"
                               class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded">
                        <input type="text" 
                               name="appearance_accent_color_text"
                               value="{{ $settings['appearance.accent_color'] ?? '#10b981' }}"
                               placeholder="#10b981"
                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cor para elementos de destaque e sucesso</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informações Adicionais
                </h3>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Título da Página
                    </label>
                    <input type="text" 
                           name="appearance_page_title"
                           value="{{ $settings['appearance.page_title'] ?? config('app.name') }}"
                           placeholder="Sistema de Agendamento"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Título exibido na aba do navegador</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Descrição Meta
                    </label>
                    <textarea name="appearance_meta_description" 
                              rows="2"
                              placeholder="Sistema profissional de agendamento de consultas médicas"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">{{ $settings['appearance.meta_description'] ?? '' }}</textarea>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Descrição exibida nos resultados de busca (máx. 160 caracteres)</p>
                </div>

                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                               type="checkbox" 
                               id="appearance_dark_mode_enabled"
                               name="appearance_dark_mode_enabled"
                               value="1"
                               {{ ($settings['appearance.dark_mode_enabled'] ?? true) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Habilitar Modo Escuro</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Permite que os usuários alternem entre o modo claro e escuro do sistema.
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Salvar Alterações
            </button>
        </div>
    </form>
</div>
