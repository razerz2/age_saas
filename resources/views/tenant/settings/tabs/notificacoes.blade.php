@php
    $botProviderMode = old('whatsapp_bot_provider_mode', $settings['whatsapp_bot.provider_mode'] ?? 'shared_with_notifications');
    $botProvider = old('whatsapp_bot_provider', $settings['whatsapp_bot.provider'] ?? 'whatsapp_business');
    $normalizeBotProvider = static function (?string $provider): string {
        $value = strtolower(trim((string) $provider));
        return match ($value) {
            'meta' => 'whatsapp_business',
            default => $value !== '' ? $value : 'whatsapp_business',
        };
    };
    $botProviderLabel = static function (string $provider): string {
        return match ($provider) {
            'zapi' => 'Z-API',
            'waha' => 'WAHA',
            'evolution' => 'Evolution API',
            default => 'WhatsApp Business (Meta)',
        };
    };

    $effectiveProvider = $normalizeBotProvider((string) ($whatsAppBotEffectiveProvider['provider'] ?? 'whatsapp_business'));
    $effectiveProviderLabel = $botProviderLabel($effectiveProvider);
    $selectedBotProvider = $normalizeBotProvider($botProvider);
    $initialBotWebhookProvider = $botProviderMode === 'dedicated' ? $selectedBotProvider : $effectiveProvider;
    $botWebhookTemplate = route('tenant.whatsapp-bot.webhook', [
        'slug' => tenant()->subdomain,
        'provider' => '__provider__',
    ]);
    $initialBotWebhookUrl = str_replace('__provider__', $initialBotWebhookProvider, $botWebhookTemplate);
    $initialBotWebhookProviderLabel = $botProviderLabel($initialBotWebhookProvider);
@endphp

<!-- Aba Notificações -->
<div class="space-y-8" x-data="{ botProviderMode: '{{ $botProviderMode }}', botProvider: '{{ $botProvider }}' }">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Configurações de Notificações</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Configure eventos internos, canais por público (pacientes e médicos) e o provider de WhatsApp usado pelo módulo de notificações.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.notifications') }}">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Eventos Internos</h3>
            </div>

            <div class="space-y-4">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="notifications_appointments_enabled"
                               name="notifications_appointments_enabled"
                               value="1"
                               {{ ($settings['notifications.appointments.enabled'] ?? false) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Notificações de Agendamentos</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Criação, atualização, cancelamento, confirmação, expiração e variações de waitlist.
                            </span>
                        </div>
                    </label>
                </div>

                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="notifications_form_responses_enabled"
                               name="notifications_form_responses_enabled"
                               value="1"
                               {{ ($settings['notifications.form_responses.enabled'] ?? false) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Notificações de Respostas de Formulários</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Receba notificações quando pacientes responderem formulários.
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Canais por Público</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Notificações para Pacientes</h4>

                    <label class="flex items-start cursor-pointer mb-3">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="notifications_send_email_to_patients"
                               name="notifications_send_email_to_patients"
                               value="1"
                               {{ ($settings['notifications.send_email_to_patients'] ?? false) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Enviar e-mail para pacientes</span>
                        </div>
                    </label>

                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="notifications_send_whatsapp_to_patients"
                               name="notifications_send_whatsapp_to_patients"
                               value="1"
                               {{ ($settings['notifications.send_whatsapp_to_patients'] ?? false) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Enviar WhatsApp para pacientes</span>
                        </div>
                    </label>
                </div>

                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Notificações para Médicos</h4>

                    <label class="flex items-start cursor-pointer mb-3">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="notifications_send_email_to_doctors"
                               name="notifications_send_email_to_doctors"
                               value="1"
                               {{ ($settings['notifications.send_email_to_doctors'] ?? false) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Enviar e-mail para médicos</span>
                        </div>
                    </label>

                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="notifications_send_whatsapp_to_doctors"
                               name="notifications_send_whatsapp_to_doctors"
                               value="1"
                               {{ ($settings['notifications.send_whatsapp_to_doctors'] ?? false) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Enviar WhatsApp para médicos</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Configurações de E-mail</h3>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Driver de E-mail
                    </label>
                    <select name="email_driver" id="email_driver" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="global" {{ ($settings['email.driver'] ?? 'global') == 'global' ? 'selected' : '' }}>Usar serviço global do sistema</option>
                        <option value="tenancy" {{ ($settings['email.driver'] ?? 'global') == 'tenancy' ? 'selected' : '' }}>Usar SMTP próprio</option>
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Este envio atende os canais de e-mail habilitados acima.</p>
                </div>

                <div id="email_tenancy_config" style="display: {{ ($settings['email.driver'] ?? 'global') == 'tenancy' ? 'block' : 'none' }};">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Host SMTP</label>
                            <input type="text" name="email_host"
                                   value="{{ $settings['email.host'] ?? '' }}"
                                   placeholder="smtp.exemplo.com"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Porta</label>
                            <input type="number" name="email_port"
                                   value="{{ $settings['email.port'] ?? '' }}"
                                   placeholder="587"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usuário</label>
                            <input type="text" name="email_username"
                                   value="{{ $settings['email.username'] ?? '' }}"
                                   placeholder="usuario@exemplo.com"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Senha</label>
                            <input type="password" name="email_password"
                                   value="{{ $settings['email.password'] ?? '' }}"
                                   placeholder="********"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome do remetente</label>
                            <input type="text" name="email_from_name"
                                   value="{{ $settings['email.from_name'] ?? '' }}"
                                   placeholder="Nome da Clínica"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">E-mail do remetente</label>
                            <input type="email" name="email_from_address"
                                   value="{{ $settings['email.from_address'] ?? '' }}"
                                   placeholder="noreply@exemplo.com"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Provedor de WhatsApp para Notificações</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Esta configuração vale para notificações. Se necessário, você pode definir um provider diferente para o bot no bloco abaixo.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Origem do provider
                    </label>
                    <select name="whatsapp_driver" id="whatsapp_driver" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="global" {{ ($settings['whatsapp.driver'] ?? 'global') == 'global' ? 'selected' : '' }}>Usar provider global do sistema</option>
                        <option value="tenancy" {{ ($settings['whatsapp.driver'] ?? 'global') == 'tenancy' ? 'selected' : '' }}>Usar provider próprio do tenant</option>
                    </select>
                </div>

                <div id="whatsapp_global_config" style="display: {{ ($settings['whatsapp.driver'] ?? 'global') == 'global' ? 'block' : 'none' }};">
                    <label for="whatsapp_global_provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Provider global de WhatsApp
                    </label>
                    <select name="whatsapp_global_provider"
                            id="whatsapp_global_provider"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            {{ empty($whatsappGlobalProviderOptions ?? []) ? 'disabled' : '' }}>
                        <option value="">Selecione...</option>
                        @foreach(($whatsappGlobalProviderOptions ?? []) as $globalProviderKey => $globalProviderLabel)
                            <option value="{{ $globalProviderKey }}"
                                {{ old('whatsapp_global_provider', $settings['whatsapp.global_provider'] ?? '') === $globalProviderKey ? 'selected' : '' }}>
                                {{ $globalProviderLabel }}
                            </option>
                        @endforeach
                    </select>
                    @if(empty($whatsappGlobalProviderOptions ?? []))
                        <p class="text-xs text-red-500 mt-1">
                            Nenhum provider global de WhatsApp está habilitado pela Platform.
                        </p>
                    @else
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Apenas providers globais habilitados pela Platform aparecem aqui.
                        </p>
                    @endif
                    @error('whatsapp_global_provider')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="whatsapp_tenancy_config" style="display: {{ ($settings['whatsapp.driver'] ?? 'global') == 'tenancy' ? 'block' : 'none' }};">
                    @include('shared.whatsapp.providers-settings', [
                        'settings' => $settings,
                        'providerFieldName' => 'whatsapp_provider',
                        'providerValue' => $settings['WHATSAPP_PROVIDER'] ?? 'whatsapp_business',
                        'includeEvolutionProvider' => true,
                        'metaTestUrl' => workspace_route('tenant.settings.whatsapp.test.connection', ['service' => 'meta']),
                        'metaSendUrl' => workspace_route('tenant.settings.whatsapp.test.meta.send'),
                        'zapiTestUrl' => workspace_route('tenant.settings.whatsapp.test.connection', ['service' => 'zapi']),
                        'zapiSendUrl' => workspace_route('tenant.settings.whatsapp.test.zapi.send'),
                        'wahaTestUrl' => workspace_route('tenant.settings.whatsapp.test.connection', ['service' => 'waha']),
                        'wahaSendUrl' => workspace_route('tenant.settings.whatsapp.test.waha.send'),
                        'evolutionTestUrl' => workspace_route('tenant.settings.whatsapp.test.connection', ['service' => 'evolution']),
                        'evolutionSendUrl' => workspace_route('tenant.settings.whatsapp.test.evolution.send'),
                    ])
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Provider utilizado pelo Bot</h3>
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
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Usar o mesmo provider das notificações</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            O bot herdará automaticamente a configuração definida acima.
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
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Usar provider próprio para o bot</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Permite configurar um provider diferente apenas para o bot.
                        </span>
                    </div>
                </label>
            </div>

            <div x-show="botProviderMode === 'shared_with_notifications'" x-cloak class="mt-4 p-4 border border-blue-200 dark:border-blue-900/30 rounded-lg bg-blue-50/70 dark:bg-blue-900/10">
                <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Configuração herdada das notificações</p>
                <p class="text-xs text-blue-800 dark:text-blue-300 mt-1">
                    Provider efetivo do bot: <strong>{{ $effectiveProviderLabel }}</strong>.
                </p>
            </div>

            <div id="bot_webhook_preview"
                 class="mt-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700/40"
                 data-webhook-template="{{ $botWebhookTemplate }}"
                 data-default-provider="{{ $effectiveProvider }}">
                <div class="flex flex-col gap-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Webhook do Bot</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Provider efetivo para este webhook:
                        <strong id="bot_webhook_provider_label">{{ $initialBotWebhookProviderLabel }}</strong>
                    </p>
                </div>

                <div class="mt-3 flex flex-col sm:flex-row gap-2">
                    <input id="bot_webhook_url"
                           type="text"
                           readonly
                           value="{{ $initialBotWebhookUrl }}"
                           data-copy-source="bot-webhook-url"
                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                    <button type="button"
                            data-copy-link="bot-webhook-url"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Copiar
                    </button>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Cadastre esta URL no webhook/event callback do seu provider de bot.
                </p>
            </div>

            <div x-show="botProviderMode === 'dedicated'" x-cloak class="mt-4 space-y-4">
                <div>
                    <label for="whatsapp_bot_provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Provider do bot
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

                <div x-show="botProvider === 'whatsapp_business'" x-cloak class="space-y-3 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Access Token</label>
                            <input type="text" name="bot_meta_access_token"
                                   value="{{ old('bot_meta_access_token', $settings['whatsapp_bot.META_ACCESS_TOKEN'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_meta_access_token')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number ID</label>
                            <input type="text" name="bot_meta_phone_number_id"
                                   value="{{ old('bot_meta_phone_number_id', $settings['whatsapp_bot.META_PHONE_NUMBER_ID'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_meta_phone_number_id')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">WABA ID</label>
                            <input type="text" name="bot_meta_waba_id"
                                   value="{{ old('bot_meta_waba_id', $settings['whatsapp_bot.META_WABA_ID'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_meta_waba_id')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="btn-test-bot-meta" data-test-url="{{ workspace_route('tenant.settings.whatsapp.test.connection', ['service' => 'meta']) }}?scope=bot" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Testar conexão</button>
                        <button type="button" id="btn-toggle-bot-meta-send" class="px-3 py-2 border border-blue-500 text-blue-600 rounded-lg text-sm">Testar envio</button>
                        <span id="bot-meta-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
                    </div>
                    <small id="bot-meta-test-message" class="text-xs text-gray-500 dark:text-gray-400 d-block"></small>
                    <div id="bot-meta-send-form" class="border rounded p-3 bg-light dark:bg-gray-700 d-none">
                        <div class="mb-2">
                            <label for="bot-meta-test-number" class="form-label">Número de destino</label>
                            <input type="text" id="bot-meta-test-number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Ex: 5511999999999">
                        </div>
                        <div class="mb-2">
                            <label for="bot-meta-test-message-input" class="form-label">Mensagem</label>
                            <textarea id="bot-meta-test-message-input" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" rows="3">Teste de envio Bot Meta</textarea>
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            <button type="button" id="btn-send-bot-meta-test" data-send-url="{{ workspace_route('tenant.settings.whatsapp.test.meta.send') }}?scope=bot" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm">Enviar teste</button>
                            <span id="bot-meta-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
                        </div>
                        <small id="bot-meta-send-message" class="text-xs text-gray-500 dark:text-gray-400 d-block mt-2"></small>
                    </div>
                </div>

                <div x-show="botProvider === 'zapi'" x-cloak class="space-y-3 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API URL</label>
                            <input type="text" name="bot_zapi_api_url"
                                   value="{{ old('bot_zapi_api_url', $settings['whatsapp_bot.ZAPI_API_URL'] ?? 'https://api.z-api.io') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_zapi_api_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Token</label>
                            <input type="text" name="bot_zapi_token"
                                   value="{{ old('bot_zapi_token', $settings['whatsapp_bot.ZAPI_TOKEN'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_zapi_token')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Client Token</label>
                            <input type="text" name="bot_zapi_client_token"
                                   value="{{ old('bot_zapi_client_token', $settings['whatsapp_bot.ZAPI_CLIENT_TOKEN'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_zapi_client_token')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Instance ID</label>
                            <input type="text" name="bot_zapi_instance_id"
                                   value="{{ old('bot_zapi_instance_id', $settings['whatsapp_bot.ZAPI_INSTANCE_ID'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_zapi_instance_id')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="btn-test-bot-zapi" data-test-url="{{ workspace_route('tenant.settings.whatsapp.test.connection', ['service' => 'zapi']) }}?scope=bot" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Testar conexão</button>
                        <button type="button" id="btn-toggle-bot-zapi-send" class="px-3 py-2 border border-blue-500 text-blue-600 rounded-lg text-sm">Testar envio</button>
                        <span id="bot-zapi-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
                    </div>
                    <small id="bot-zapi-test-message" class="text-xs text-gray-500 dark:text-gray-400 d-block"></small>
                    <div id="bot-zapi-send-form" class="border rounded p-3 bg-light dark:bg-gray-700 d-none">
                        <div class="mb-2">
                            <label for="bot-zapi-test-number" class="form-label">Número de destino</label>
                            <input type="text" id="bot-zapi-test-number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Ex: 5511999999999">
                        </div>
                        <div class="mb-2">
                            <label for="bot-zapi-test-message-input" class="form-label">Mensagem</label>
                            <textarea id="bot-zapi-test-message-input" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" rows="3">Teste de envio Bot Z-API</textarea>
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            <button type="button" id="btn-send-bot-zapi-test" data-send-url="{{ workspace_route('tenant.settings.whatsapp.test.zapi.send') }}?scope=bot" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm">Enviar teste</button>
                            <span id="bot-zapi-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
                        </div>
                        <small id="bot-zapi-send-message" class="text-xs text-gray-500 dark:text-gray-400 d-block mt-2"></small>
                    </div>
                </div>

                <div x-show="botProvider === 'waha'" x-cloak class="space-y-3 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Base URL</label>
                            <input type="text" name="bot_waha_base_url"
                                   value="{{ old('bot_waha_base_url', $settings['whatsapp_bot.WAHA_BASE_URL'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_waha_base_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Key</label>
                            <input type="text" name="bot_waha_api_key"
                                   value="{{ old('bot_waha_api_key', $settings['whatsapp_bot.WAHA_API_KEY'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_waha_api_key')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sessão</label>
                            <input type="text" name="bot_waha_session"
                                   value="{{ old('bot_waha_session', $settings['whatsapp_bot.WAHA_SESSION'] ?? 'default') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_waha_session')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="btn-test-bot-waha" data-test-url="{{ workspace_route('tenant.settings.whatsapp.test.connection', ['service' => 'waha']) }}?scope=bot" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Testar conexão</button>
                        <button type="button" id="btn-toggle-bot-waha-send" class="px-3 py-2 border border-blue-500 text-blue-600 rounded-lg text-sm">Testar envio</button>
                        <span id="bot-waha-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
                    </div>
                    <small id="bot-waha-test-message" class="text-xs text-gray-500 dark:text-gray-400 d-block"></small>
                    <div id="bot-waha-send-form" class="border rounded p-3 bg-light dark:bg-gray-700 d-none">
                        <div class="mb-2">
                            <label for="bot-waha-test-number" class="form-label">Número de destino</label>
                            <input type="text" id="bot-waha-test-number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Ex: 5511999999999">
                        </div>
                        <div class="mb-2">
                            <label for="bot-waha-test-message-input" class="form-label">Mensagem</label>
                            <textarea id="bot-waha-test-message-input" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" rows="3">Teste de envio Bot WAHA</textarea>
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            <button type="button" id="btn-send-bot-waha-test" data-send-url="{{ workspace_route('tenant.settings.whatsapp.test.waha.send') }}?scope=bot" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm">Enviar teste</button>
                            <span id="bot-waha-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
                        </div>
                        <small id="bot-waha-send-message" class="text-xs text-gray-500 dark:text-gray-400 d-block mt-2"></small>
                    </div>
                </div>

                <div x-show="botProvider === 'evolution'" x-cloak class="space-y-3 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Base URL</label>
                            <input type="text" name="bot_evolution_base_url"
                                   value="{{ old('bot_evolution_base_url', $settings['whatsapp_bot.EVOLUTION_BASE_URL'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_evolution_base_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Key</label>
                            <input type="text" name="bot_evolution_api_key"
                                   value="{{ old('bot_evolution_api_key', $settings['whatsapp_bot.EVOLUTION_API_KEY'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_evolution_api_key')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Instância</label>
                            <input type="text" name="bot_evolution_instance"
                                   value="{{ old('bot_evolution_instance', $settings['whatsapp_bot.EVOLUTION_INSTANCE'] ?? 'default') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bot_evolution_instance')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="btn-test-bot-evolution" data-test-url="{{ workspace_route('tenant.settings.whatsapp.test.connection', ['service' => 'evolution']) }}?scope=bot" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Testar conexão</button>
                        <button type="button" id="btn-toggle-bot-evolution-send" class="px-3 py-2 border border-blue-500 text-blue-600 rounded-lg text-sm">Testar envio</button>
                        <span id="bot-evolution-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
                    </div>
                    <small id="bot-evolution-test-message" class="text-xs text-gray-500 dark:text-gray-400 d-block"></small>
                    <div id="bot-evolution-send-form" class="border rounded p-3 bg-light dark:bg-gray-700 d-none">
                        <div class="mb-2">
                            <label for="bot-evolution-test-number" class="form-label">Número de destino</label>
                            <input type="text" id="bot-evolution-test-number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Ex: 5511999999999">
                        </div>
                        <div class="mb-2">
                            <label for="bot-evolution-test-message-input" class="form-label">Mensagem</label>
                            <textarea id="bot-evolution-test-message-input" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" rows="3">Teste de envio Bot Evolution</textarea>
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            <button type="button" id="btn-send-bot-evolution-test" data-send-url="{{ workspace_route('tenant.settings.whatsapp.test.evolution.send') }}?scope=bot" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm">Enviar teste</button>
                            <span id="bot-evolution-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
                        </div>
                        <small id="bot-evolution-send-message" class="text-xs text-gray-500 dark:text-gray-400 d-block mt-2"></small>
                    </div>
                </div>
            </div>
        </div>

        @include('tenant.settings.partials.form-actions')
    </form>
</div>
