@php
    $campaignEmailMode = old('campaigns_email_mode', $settings['campaigns.email.mode'] ?? 'notifications');
    $campaignEmailDriver = old('campaigns_email_driver', $settings['campaigns.email.driver'] ?? 'smtp');
    $campaignWhatsAppMode = old('campaigns_whatsapp_mode', $settings['campaigns.whatsapp.mode'] ?? 'notifications');
    $campaignWhatsAppProvider = old('campaigns_whatsapp_provider', $settings['campaigns.whatsapp.provider'] ?? 'whatsapp_business');

    $emailConfigured = (bool) ($settings['campaigns.status.email_available'] ?? false);
    $whatsAppConfigured = (bool) ($settings['campaigns.status.whatsapp_available'] ?? false);
    $openEmailTestModal = $errors->has('destination_email');
    $openWhatsAppTestModal = $errors->has('destination_number');
@endphp

<div class="space-y-8"
     x-data="{
        campaignEmailMode: '{{ $campaignEmailMode }}',
        campaignWhatsAppMode: '{{ $campaignWhatsAppMode }}',
        campaignWhatsAppProvider: '{{ $campaignWhatsAppProvider }}',
        showEmailTestModal: {{ $openEmailTestModal ? 'true' : 'false' }},
        showWhatsAppTestModal: {{ $openWhatsAppTestModal ? 'true' : 'false' }}
     }">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Configurações de Campanhas</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Defina quais canais serão usados exclusivamente nas campanhas.
            Você pode reutilizar os canais já configurados em notificações ou informar uma configuração própria para campanhas.
        </p>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Salve as alterações antes de executar os testes de envio.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">E-mail</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Origem: {{ $campaignEmailMode === 'custom' ? 'Configuração própria' : 'Notificações' }}
                    </p>
                </div>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $emailConfigured ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                    {{ $emailConfigured ? 'Configurado' : 'Não configurado' }}
                </span>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">WhatsApp</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Origem: {{ $campaignWhatsAppMode === 'custom' ? 'Configuração própria' : 'Notificações' }}
                    </p>
                </div>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $whatsAppConfigured ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                    {{ $whatsAppConfigured ? 'Configurado' : 'Não configurado' }}
                </span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.campaigns') }}">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">E-mail para Campanhas</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Use o mesmo canal das notificações ou configure um canal exclusivo para campanhas.
                        </p>
                    </div>
                    <button type="button"
                            @click="showEmailTestModal = true"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-blue-200 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-50 dark:border-blue-800 dark:text-blue-300 dark:hover:bg-blue-900/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>Testar E-mail</span>
                    </button>
                </div>
            </div>

            <div class="mt-4 space-y-4">
                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
                    <input type="radio"
                           name="campaigns_email_mode"
                           value="notifications"
                           x-model="campaignEmailMode"
                           class="mt-1 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ $campaignEmailMode === 'notifications' ? 'checked' : '' }}>
                    <div>
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Usar configurações de notificações</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            As campanhas usarão o mesmo e-mail já configurado no módulo de notificações.
                        </span>
                    </div>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
                    <input type="radio"
                           name="campaigns_email_mode"
                           value="custom"
                           x-model="campaignEmailMode"
                           class="mt-1 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ $campaignEmailMode === 'custom' ? 'checked' : '' }}>
                    <div>
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Usar configuração própria</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Defina um SMTP exclusivo para campanhas.
                        </span>
                    </div>
                </label>
                @error('campaigns_email_mode')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2" x-show="campaignEmailMode === 'custom'" x-cloak>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Driver</label>
                    <select name="campaigns_email_driver"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="smtp" {{ $campaignEmailDriver === 'smtp' ? 'selected' : '' }}>SMTP</option>
                    </select>
                    @error('campaigns_email_driver')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Host</label>
                    <input type="text"
                           name="campaigns_email_host"
                           value="{{ old('campaigns_email_host', $settings['campaigns.email.host'] ?? '') }}"
                           placeholder="smtp.exemplo.com"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('campaigns_email_host')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Porta</label>
                    <input type="number"
                           name="campaigns_email_port"
                           value="{{ old('campaigns_email_port', $settings['campaigns.email.port'] ?? '') }}"
                           placeholder="587"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('campaigns_email_port')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Usuário</label>
                    <input type="text"
                           name="campaigns_email_username"
                           value="{{ old('campaigns_email_username', $settings['campaigns.email.username'] ?? '') }}"
                           placeholder="usuario@exemplo.com"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('campaigns_email_username')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Senha</label>
                    <input type="password"
                           name="campaigns_email_password"
                           value="{{ old('campaigns_email_password', $settings['campaigns.email.password'] ?? '') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('campaigns_email_password')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Criptografia</label>
                    <select name="campaigns_email_encryption"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @php($emailEncryption = old('campaigns_email_encryption', $settings['campaigns.email.encryption'] ?? ''))
                        <option value="none" {{ $emailEncryption === 'none' ? 'selected' : '' }}>Sem criptografia</option>
                        <option value="tls" {{ $emailEncryption === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ $emailEncryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                    </select>
                    @error('campaigns_email_encryption')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome do remetente</label>
                    <input type="text"
                           name="campaigns_email_from_name"
                           value="{{ old('campaigns_email_from_name', $settings['campaigns.email.from_name'] ?? '') }}"
                           placeholder="Nome da Clínica"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('campaigns_email_from_name')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">E-mail do remetente</label>
                    <input type="email"
                           name="campaigns_email_from_address"
                           value="{{ old('campaigns_email_from_address', $settings['campaigns.email.from_address'] ?? '') }}"
                           placeholder="noreply@exemplo.com"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('campaigns_email_from_address')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">WhatsApp para Campanhas</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Use o mesmo canal das notificações ou configure um canal exclusivo para campanhas.
                        </p>
                    </div>
                    <button type="button"
                            @click="showWhatsAppTestModal = true"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-blue-200 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-50 dark:border-blue-800 dark:text-blue-300 dark:hover:bg-blue-900/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v7a2 2 0 01-2 2h-2M7 8H5a2 2 0 00-2 2v7a2 2 0 002 2h2m0-11V5a2 2 0 012-2h6a2 2 0 012 2v3M7 8h10m-8 4h6m-6 3h4"></path>
                        </svg>
                        <span>Testar WhatsApp</span>
                    </button>
                </div>
            </div>

            <div class="mt-4 space-y-4">
                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
                    <input type="radio"
                           name="campaigns_whatsapp_mode"
                           value="notifications"
                           x-model="campaignWhatsAppMode"
                           class="mt-1 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ $campaignWhatsAppMode === 'notifications' ? 'checked' : '' }}>
                    <div>
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Usar configurações de notificações</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            As campanhas usarão o mesmo provedor de WhatsApp já configurado em notificações.
                        </span>
                    </div>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
                    <input type="radio"
                           name="campaigns_whatsapp_mode"
                           value="custom"
                           x-model="campaignWhatsAppMode"
                           class="mt-1 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ $campaignWhatsAppMode === 'custom' ? 'checked' : '' }}>
                    <div>
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Usar configuração própria</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Defina um provedor exclusivo para campanhas.
                        </span>
                    </div>
                </label>
                @error('campaigns_whatsapp_mode')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 space-y-4" x-show="campaignWhatsAppMode === 'custom'" x-cloak>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Provedor</label>
                    <select name="campaigns_whatsapp_provider"
                            x-model="campaignWhatsAppProvider"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="whatsapp_business">WhatsApp Business (Meta)</option>
                        <option value="zapi">Z-API</option>
                        <option value="waha">WAHA</option>
                        <option value="evolution">Evolution API</option>
                    </select>
                    @error('campaigns_whatsapp_provider')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2" x-show="campaignWhatsAppProvider === 'whatsapp_business'" x-cloak>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Access Token</label>
                        <input type="text"
                               name="campaigns_whatsapp_meta_access_token"
                               value="{{ old('campaigns_whatsapp_meta_access_token', $settings['campaigns.whatsapp.meta_access_token'] ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_meta_access_token')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number ID</label>
                        <input type="text"
                               name="campaigns_whatsapp_meta_phone_number_id"
                               value="{{ old('campaigns_whatsapp_meta_phone_number_id', $settings['campaigns.whatsapp.meta_phone_number_id'] ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_meta_phone_number_id')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2" x-show="campaignWhatsAppProvider === 'zapi'" x-cloak>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">API URL</label>
                        <input type="text"
                               name="campaigns_whatsapp_zapi_api_url"
                               value="{{ old('campaigns_whatsapp_zapi_api_url', $settings['campaigns.whatsapp.zapi_api_url'] ?? 'https://api.z-api.io') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_zapi_api_url')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Token</label>
                        <input type="text"
                               name="campaigns_whatsapp_zapi_token"
                               value="{{ old('campaigns_whatsapp_zapi_token', $settings['campaigns.whatsapp.zapi_token'] ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_zapi_token')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Client Token</label>
                        <input type="text"
                               name="campaigns_whatsapp_zapi_client_token"
                               value="{{ old('campaigns_whatsapp_zapi_client_token', $settings['campaigns.whatsapp.zapi_client_token'] ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_zapi_client_token')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Instance ID</label>
                        <input type="text"
                               name="campaigns_whatsapp_zapi_instance_id"
                               value="{{ old('campaigns_whatsapp_zapi_instance_id', $settings['campaigns.whatsapp.zapi_instance_id'] ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_zapi_instance_id')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3" x-show="campaignWhatsAppProvider === 'waha'" x-cloak>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Base URL</label>
                        <input type="text"
                               name="campaigns_whatsapp_waha_base_url"
                               value="{{ old('campaigns_whatsapp_waha_base_url', $settings['campaigns.whatsapp.waha_base_url'] ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_waha_base_url')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                        <input type="text"
                               name="campaigns_whatsapp_waha_api_key"
                               value="{{ old('campaigns_whatsapp_waha_api_key', $settings['campaigns.whatsapp.waha_api_key'] ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_waha_api_key')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Sessão</label>
                        <input type="text"
                               name="campaigns_whatsapp_waha_session"
                               value="{{ old('campaigns_whatsapp_waha_session', $settings['campaigns.whatsapp.waha_session'] ?? 'default') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_waha_session')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3" x-show="campaignWhatsAppProvider === 'evolution'" x-cloak>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Base URL</label>
                        <input type="text"
                               name="campaigns_whatsapp_evolution_base_url"
                               value="{{ old('campaigns_whatsapp_evolution_base_url', $settings['campaigns.whatsapp.evolution_base_url'] ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_evolution_base_url')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                        <input type="text"
                               name="campaigns_whatsapp_evolution_api_key"
                               value="{{ old('campaigns_whatsapp_evolution_api_key', $settings['campaigns.whatsapp.evolution_api_key'] ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_evolution_api_key')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Instância</label>
                        <input type="text"
                               name="campaigns_whatsapp_evolution_instance"
                               value="{{ old('campaigns_whatsapp_evolution_instance', $settings['campaigns.whatsapp.evolution_instance'] ?? 'default') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('campaigns_whatsapp_evolution_instance')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        @include('tenant.settings.partials.form-actions')
    </form>

    <div x-show="showEmailTestModal"
         x-cloak
         @keydown.escape.window="showEmailTestModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4 py-6">
        <div class="absolute inset-0" @click="showEmailTestModal = false"></div>
        <div class="relative w-full max-w-lg rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Testar E-mail de Campanhas</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Envie uma mensagem de teste usando a configuração atual do canal de e-mail para campanhas.
                    </p>
                </div>
                <button type="button"
                        @click="showEmailTestModal = false"
                        class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                    <span class="sr-only">Fechar</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ workspace_route('tenant.settings.campaigns.test-email') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="campaigns-test-email-destination" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">E-mail de destino</label>
                    <input id="campaigns-test-email-destination"
                           type="email"
                           name="destination_email"
                           value="{{ old('destination_email') }}"
                           placeholder="contato@exemplo.com"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('destination_email')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="button"
                            @click="showEmailTestModal = false"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>Enviar teste</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showWhatsAppTestModal"
         x-cloak
         @keydown.escape.window="showWhatsAppTestModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4 py-6">
        <div class="absolute inset-0" @click="showWhatsAppTestModal = false"></div>
        <div class="relative w-full max-w-lg rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Testar WhatsApp de Campanhas</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Envie uma mensagem de teste usando a configuração atual do canal de WhatsApp para campanhas.
                    </p>
                </div>
                <button type="button"
                        @click="showWhatsAppTestModal = false"
                        class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                    <span class="sr-only">Fechar</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ workspace_route('tenant.settings.campaigns.test-whatsapp') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="campaigns-test-whatsapp-destination" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Número de destino</label>
                    <input id="campaigns-test-whatsapp-destination"
                           type="text"
                           name="destination_number"
                           value="{{ old('destination_number') }}"
                           placeholder="5511999999999"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('destination_number')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="button"
                            @click="showWhatsAppTestModal = false"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 2L11 13"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 2L15 22L11 13L2 9L22 2Z"></path>
                        </svg>
                        <span>Enviar teste</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
