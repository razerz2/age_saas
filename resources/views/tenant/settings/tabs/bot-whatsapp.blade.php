@php
    $botProviderMode = old('whatsapp_bot_provider_mode', $settings['whatsapp_bot.provider_mode'] ?? 'shared_with_notifications');
    $botProvider = old('whatsapp_bot_provider', $settings['whatsapp_bot.provider'] ?? 'whatsapp_business');
    $effectiveProvider = strtolower((string) ($whatsAppBotEffectiveProvider['provider'] ?? 'whatsapp_business'));
    $effectiveProviderLabel = match ($effectiveProvider) {
        'zapi' => 'Z-API',
        'waha' => 'WAHA',
        'evolution' => 'Evolution API',
        default => 'WhatsApp Business (Meta)',
    };
@endphp

<div class="space-y-8" x-data="{ botProviderMode: '{{ $botProviderMode }}', botProvider: '{{ $botProvider }}' }">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Bot de WhatsApp</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Configure a base do bot, escolha como o provedor sera resolvido e defina os comportamentos iniciais.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.whatsapp-bot') }}" class="space-y-6">
        @csrf

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                Revise os campos destacados para salvar as configuracoes do bot.
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status e Mensagem Inicial</h3>
            </div>

            <div class="space-y-4">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               name="whatsapp_bot_enabled"
                               value="1"
                               {{ old('whatsapp_bot_enabled', ($settings['whatsapp_bot.enabled'] ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Habilitar Bot de WhatsApp</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Ativa o modulo do bot para os proximos passos do fluxo.
                            </span>
                        </div>
                    </label>
                </div>

                <div>
                    <label for="whatsapp_bot_welcome_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Mensagem de boas-vindas <span class="text-xs text-gray-500">(obrigatoria quando habilitado)</span>
                    </label>
                    <textarea id="whatsapp_bot_welcome_message"
                              name="whatsapp_bot_welcome_message"
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                              placeholder="Ex.: Ola! Sou o bot da clinica e posso ajudar com seu agendamento.">{{ old('whatsapp_bot_welcome_message', $settings['whatsapp_bot.welcome_message'] ?? '') }}</textarea>
                    @error('whatsapp_bot_welcome_message')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="whatsapp_bot_disabled_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Mensagem quando o bot estiver desativado
                    </label>
                    <textarea id="whatsapp_bot_disabled_message"
                              name="whatsapp_bot_disabled_message"
                              rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                              placeholder="Ex.: Nosso atendimento automatico esta indisponivel no momento.">{{ old('whatsapp_bot_disabled_message', $settings['whatsapp_bot.disabled_message'] ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Opcional. Se preenchida, sera enviada quando o bot estiver desabilitado.
                    </p>
                    @error('whatsapp_bot_disabled_message')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Modo do Provedor do Bot</h3>
            </div>

            <div class="space-y-4">
                <label class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-start gap-3 cursor-pointer">
                    <input type="radio"
                           name="whatsapp_bot_provider_mode"
                           value="shared_with_notifications"
                           x-model="botProviderMode"
                           class="mt-1 w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                           {{ $botProviderMode === 'shared_with_notifications' ? 'checked' : '' }}>
                    <div>
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Usar o mesmo provedor das notificacoes</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            O bot herda a configuracao efetiva ja usada pelo modulo de notificacoes.
                        </span>
                    </div>
                </label>

                <label class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-start gap-3 cursor-pointer">
                    <input type="radio"
                           name="whatsapp_bot_provider_mode"
                           value="dedicated"
                           x-model="botProviderMode"
                           class="mt-1 w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                           {{ $botProviderMode === 'dedicated' ? 'checked' : '' }}>
                    <div>
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Usar provedor proprio para o bot</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Permite configurar o provider do bot de forma independente das notificacoes.
                        </span>
                    </div>
                </label>
            </div>

            <div x-show="botProviderMode === 'shared_with_notifications'" x-cloak class="mt-4 p-4 border border-blue-200 dark:border-blue-900/30 rounded-lg bg-blue-50/70 dark:bg-blue-900/10">
                <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Configuracao herdada das notificacoes</p>
                <p class="text-xs text-blue-800 dark:text-blue-300 mt-1">
                    Provider efetivo atual: <strong>{{ $effectiveProviderLabel }}</strong>.
                </p>
                <p class="text-xs text-blue-700 dark:text-blue-300/90 mt-1">
                    Se a configuracao de notificacoes mudar, o bot tambem herdara a nova configuracao.
                </p>
            </div>

            <div x-show="botProviderMode === 'dedicated'" x-cloak class="mt-4 space-y-4">
                <div>
                    <label for="whatsapp_bot_provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Provedor dedicado do bot
                    </label>
                    <select id="whatsapp_bot_provider"
                            name="whatsapp_bot_provider"
                            x-model="botProvider"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="whatsapp_business">WhatsApp Business (Meta)</option>
                        <option value="zapi">Z-API</option>
                        <option value="waha">WAHA</option>
                        <option value="evolution">Evolution API</option>
                    </select>
                    @error('whatsapp_bot_provider')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="botProvider === 'whatsapp_business'" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Access Token</label>
                        <input type="text"
                               name="bot_meta_access_token"
                               value="{{ old('bot_meta_access_token', $settings['whatsapp_bot.META_ACCESS_TOKEN'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number ID</label>
                        <input type="text"
                               name="bot_meta_phone_number_id"
                               value="{{ old('bot_meta_phone_number_id', $settings['whatsapp_bot.META_PHONE_NUMBER_ID'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">WABA ID</label>
                        <input type="text"
                               name="bot_meta_waba_id"
                               value="{{ old('bot_meta_waba_id', $settings['whatsapp_bot.META_WABA_ID'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div x-show="botProvider === 'zapi'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API URL</label>
                        <input type="text"
                               name="bot_zapi_api_url"
                               value="{{ old('bot_zapi_api_url', $settings['whatsapp_bot.ZAPI_API_URL'] ?? 'https://api.z-api.io') }}"
                               placeholder="https://api.z-api.io"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Token</label>
                        <input type="text"
                               name="bot_zapi_token"
                               value="{{ old('bot_zapi_token', $settings['whatsapp_bot.ZAPI_TOKEN'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Client Token</label>
                        <input type="text"
                               name="bot_zapi_client_token"
                               value="{{ old('bot_zapi_client_token', $settings['whatsapp_bot.ZAPI_CLIENT_TOKEN'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Instance ID</label>
                        <input type="text"
                               name="bot_zapi_instance_id"
                               value="{{ old('bot_zapi_instance_id', $settings['whatsapp_bot.ZAPI_INSTANCE_ID'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div x-show="botProvider === 'waha'" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Base URL</label>
                        <input type="text"
                               name="bot_waha_base_url"
                               value="{{ old('bot_waha_base_url', $settings['whatsapp_bot.WAHA_BASE_URL'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Key</label>
                        <input type="text"
                               name="bot_waha_api_key"
                               value="{{ old('bot_waha_api_key', $settings['whatsapp_bot.WAHA_API_KEY'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sessao</label>
                        <input type="text"
                               name="bot_waha_session"
                               value="{{ old('bot_waha_session', $settings['whatsapp_bot.WAHA_SESSION'] ?? 'default') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div x-show="botProvider === 'evolution'" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Base URL</label>
                        <input type="text"
                               name="bot_evolution_base_url"
                               value="{{ old('bot_evolution_base_url', $settings['whatsapp_bot.EVOLUTION_BASE_URL'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Key</label>
                        <input type="text"
                               name="bot_evolution_api_key"
                               value="{{ old('bot_evolution_api_key', $settings['whatsapp_bot.EVOLUTION_API_KEY'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Instancia</label>
                        <input type="text"
                               name="bot_evolution_instance"
                               value="{{ old('bot_evolution_instance', $settings['whatsapp_bot.EVOLUTION_INSTANCE'] ?? 'default') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Permissoes Iniciais do Bot</h3>
            </div>

            <div class="space-y-3">
                <label class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 cursor-pointer">
                    <input type="checkbox"
                           name="whatsapp_bot_allow_schedule"
                           value="1"
                           class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                           {{ old('whatsapp_bot_allow_schedule', ($settings['whatsapp_bot.allow_schedule'] ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Permitir agendamentos via bot</span>
                </label>

                <label class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 cursor-pointer">
                    <input type="checkbox"
                           name="whatsapp_bot_allow_view_appointments"
                           value="1"
                           class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                           {{ old('whatsapp_bot_allow_view_appointments', ($settings['whatsapp_bot.allow_view_appointments'] ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Permitir consulta de agendamentos</span>
                </label>

                <label class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 cursor-pointer">
                    <input type="checkbox"
                           name="whatsapp_bot_allow_cancel_appointments"
                           value="1"
                           class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                           {{ old('whatsapp_bot_allow_cancel_appointments', ($settings['whatsapp_bot.allow_cancel_appointments'] ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Permitir cancelamento de agendamentos</span>
                </label>
            </div>
        </div>

        @include('tenant.settings.partials.form-actions')
    </form>
</div>
