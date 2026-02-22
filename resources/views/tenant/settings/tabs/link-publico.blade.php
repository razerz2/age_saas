<!-- Aba Link Público -->
<div class="space-y-8">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Link Público de Agendamento</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Configure o link público para que pacientes possam agendar consultas diretamente.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.appearance') }}">
        @csrf
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    Configurações do Link Público
                </h3>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status do Link Público
                    </label>
                    <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <label class="flex items-start cursor-pointer">
                            <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   type="checkbox" 
                                   id="public_booking_enabled"
                                   name="public_booking_enabled"
                                   value="1"
                                   {{ ($settings['public_booking.enabled'] ?? false) ? 'checked' : '' }}>
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900 dark:text-white">Habilitar Link Público</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Quando habilitado, pacientes poderão acessar o link público para agendar consultas.
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        URL do Link Público
                    </label>
                    <div class="flex gap-2">
                        <input type="text" 
                               readonly
                               value="{{ route('public.appointment.create', ['slug' => tenant()->subdomain]) }}" data-copy-source="public-booking-url" 
                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        <button type="button" 
                                data-copy-link="public-booking-url"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Copiar
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Compartilhe este link com seus pacientes</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Texto de Boas-vindas
                    </label>
                    <textarea name="public_booking_welcome_message" 
                              rows="3"
                              placeholder="Bem-vindo! Agende sua consulta abaixo."
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">{{ $settings['public_booking.welcome_message'] ?? '' }}</textarea>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Mensagem exibida no topo da página de agendamento público</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Texto de Rodapé
                    </label>
                    <textarea name="public_booking_footer_message" 
                              rows="2"
                              placeholder="Em caso de dúvidas, entre em contato conosco."
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">{{ $settings['public_booking.footer_message'] ?? '' }}</textarea>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Mensagem exibida no rodapé da página de agendamento público</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Cor Primária
                        </label>
                        <div class="flex gap-2">
                            <input type="color" 
                                   name="public_booking_primary_color"
                                   value="{{ $settings['public_booking.primary_color'] ?? '#2563eb' }}"
                                   class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded">
                            <input type="text" 
                                   name="public_booking_primary_color_text"
                                   value="{{ $settings['public_booking.primary_color'] ?? '#2563eb' }}"
                                   placeholder="#2563eb"
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cor principal dos botões e elementos</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Cor Secundária
                        </label>
                        <div class="flex gap-2">
                            <input type="color" 
                                   name="public_booking_secondary_color"
                                   value="{{ $settings['public_booking.secondary_color'] ?? '#64748b' }}"
                                   class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded">
                            <input type="text" 
                                   name="public_booking_secondary_color_text"
                                   value="{{ $settings['public_booking.secondary_color'] ?? '#64748b' }}"
                                   placeholder="#64748b"
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cor secundária para elementos complementares</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Logo da Clínica
                    </label>
                    <div class="space-y-3">
                        @if(!empty($settings['public_booking.logo']))
                            <div class="flex items-center gap-4 p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <img src="{{ Storage::url($settings['public_booking.logo']) }}" alt="Logo" class="h-12 w-auto">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Logo atual</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Clique em "Remover" para usar o logo padrão</p>
                                </div>
                                <button type="button" 
                                        data-settings-action="remove-logo" data-remove-target="remove_logo"
                                        class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors duration-200">
                                    Remover
                                </button>
                                <input type="hidden" id="remove_logo" name="remove_logo" value="0">
                            </div>
                        @endif
                        <div>
                            <input type="file" 
                                   name="public_booking_logo"
                                   accept="image/*"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Formatos: PNG, JPG, JPEG. Tamanho máximo: 2MB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('tenant.settings.partials.form-actions')
    </form>
</div>
