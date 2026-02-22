<!-- Aba Integrações -->
<div class="space-y-8">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Configurações de Integrações</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Configure as integrações com serviços externos.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.integrations') }}">
        @csrf

        <!-- Google Calendar -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google Calendar
                </h3>
                @if($hasGoogleCalendarIntegration && $googleCalendarIntegration)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">Configurado</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">Não configurado</span>
                @endif
            </div>

            @if(!$hasGoogleCalendarIntegration)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                    <strong class="block text-sm font-medium text-yellow-900 dark:text-yellow-100 mb-2">Atenção! Integração não configurada</strong>
                    <p class="text-xs text-yellow-800 dark:text-yellow-200 mb-2">
                        Cadastre a integração com a chave <code class="bg-yellow-100 dark:bg-yellow-900/40 px-1 py-0.5 rounded text-xs">google_calendar</code> e configure a API no campo de configuração (JSON).
                    </p>
                    <div class="flex gap-2">
                        <a href="{{ workspace_route('tenant.integrations.create') }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white text-xs font-medium rounded hover:bg-yellow-700 transition-colors duration-200">Cadastrar integração</a>
                        <a href="{{ workspace_route('tenant.integrations.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 transition-colors duration-200">Ver integrações</a>
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg {{ !$hasGoogleCalendarIntegration ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="integrations_google_calendar_enabled"
                               name="integrations_google_calendar_enabled"
                               value="1"
                               {{ $settings['integrations.google_calendar.enabled'] ? 'checked' : '' }}
                               {{ !$hasGoogleCalendarIntegration ? 'disabled' : '' }}
                               style="cursor: {{ !$hasGoogleCalendarIntegration ? 'not-allowed' : 'pointer' }};">
                        <div class="ml-3">
                            <span class="block text-sm font-medium {{ !$hasGoogleCalendarIntegration ? 'text-gray-500' : 'text-gray-900 dark:text-white' }}">Habilitar sincronização com Google Calendar</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Sincronize seus agendamentos com o Google Calendar.</span>
                        </div>
                    </label>
                </div>

                <div id="google_calendar_auto_sync_group"
                     style="display: {{ $settings['integrations.google_calendar.enabled'] && $hasGoogleCalendarIntegration ? 'block' : 'none' }};"
                     class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg {{ !$hasGoogleCalendarIntegration ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="integrations_google_calendar_auto_sync"
                               name="integrations_google_calendar_auto_sync"
                               value="1"
                               {{ $settings['integrations.google_calendar.auto_sync'] ? 'checked' : '' }}
                               {{ !$hasGoogleCalendarIntegration ? 'disabled' : '' }}
                               style="cursor: {{ !$hasGoogleCalendarIntegration ? 'not-allowed' : 'pointer' }};">
                        <div class="ml-3">
                            <span class="block text-sm font-medium {{ !$hasGoogleCalendarIntegration ? 'text-gray-500' : 'text-gray-900 dark:text-white' }}">Sincronização automática</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Sincronize automaticamente os agendamentos em tempo real.</span>
                        </div>
                    </label>
                </div>
            </div>

            @if($hasGoogleCalendarIntegration && $googleCalendarIntegration)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <div class="flex gap-2">
                        <a href="{{ workspace_route('tenant.integrations.edit', ['id' => $googleCalendarIntegration->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary/90 transition-colors duration-200">Editar integração</a>
                        <a href="{{ workspace_route('tenant.oauth-accounts.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 transition-colors duration-200">Gerenciar contas OAuth</a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Apple Calendar -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-icon name="apple" class="text-gray-800 dark:text-gray-100" />
                    Apple Calendar
                </h3>
                @if($hasAppleCalendarIntegration && $appleCalendarIntegration)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">Configurado</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">Não configurado</span>
                @endif
            </div>

            @if(!$hasAppleCalendarIntegration)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                    <strong class="block text-sm font-medium text-yellow-900 dark:text-yellow-100 mb-2">Atenção! Integração não configurada</strong>
                    <p class="text-xs text-yellow-800 dark:text-yellow-200 mb-2">
                        Cadastre a integração com a chave <code class="bg-yellow-100 dark:bg-yellow-900/40 px-1 py-0.5 rounded text-xs">apple_calendar</code> e configure a API no campo de configuração (JSON).
                    </p>
                    <div class="flex gap-2">
                        <a href="{{ workspace_route('tenant.integrations.create') }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white text-xs font-medium rounded hover:bg-yellow-700 transition-colors duration-200">Cadastrar integração</a>
                        <a href="{{ workspace_route('tenant.integrations.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 transition-colors duration-200">Ver integrações</a>
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg {{ !$hasAppleCalendarIntegration ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="integrations_apple_calendar_enabled"
                               name="integrations_apple_calendar_enabled"
                               value="1"
                               {{ $settings['integrations.apple_calendar.enabled'] ? 'checked' : '' }}
                               {{ !$hasAppleCalendarIntegration ? 'disabled' : '' }}
                               style="cursor: {{ !$hasAppleCalendarIntegration ? 'not-allowed' : 'pointer' }};">
                        <div class="ml-3">
                            <span class="block text-sm font-medium {{ !$hasAppleCalendarIntegration ? 'text-gray-500' : 'text-gray-900 dark:text-white' }}">Habilitar sincronização com Apple Calendar</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Sincronize seus agendamentos com o Apple Calendar.</span>
                        </div>
                    </label>
                </div>

                <div id="apple_calendar_auto_sync_group"
                     style="display: {{ $settings['integrations.apple_calendar.enabled'] && $hasAppleCalendarIntegration ? 'block' : 'none' }};"
                     class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg {{ !$hasAppleCalendarIntegration ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="integrations_apple_calendar_auto_sync"
                               name="integrations_apple_calendar_auto_sync"
                               value="1"
                               {{ $settings['integrations.apple_calendar.auto_sync'] ? 'checked' : '' }}
                               {{ !$hasAppleCalendarIntegration ? 'disabled' : '' }}
                               style="cursor: {{ !$hasAppleCalendarIntegration ? 'not-allowed' : 'pointer' }};">
                        <div class="ml-3">
                            <span class="block text-sm font-medium {{ !$hasAppleCalendarIntegration ? 'text-gray-500' : 'text-gray-900 dark:text-white' }}">Sincronização automática</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Sincronize automaticamente os agendamentos em tempo real.</span>
                        </div>
                    </label>
                </div>
            </div>

            @if($hasAppleCalendarIntegration && $appleCalendarIntegration)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <div class="flex gap-2">
                        <a href="{{ workspace_route('tenant.integrations.edit', ['id' => $appleCalendarIntegration->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary/90 transition-colors duration-200">Editar integração</a>
                        <a href="{{ workspace_route('tenant.integrations.apple.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 transition-colors duration-200">Gerenciar conexões Apple</a>
                    </div>
                </div>
            @endif
        </div>

        @include('tenant.settings.partials.form-actions')
    </form>
</div>
