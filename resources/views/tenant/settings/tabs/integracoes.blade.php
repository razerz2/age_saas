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
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Configurado
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Não Configurado
                    </span>
                @endif
            </div>

            @if(!$hasGoogleCalendarIntegration)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="ml-3">
                            <strong class="block text-sm font-medium text-yellow-900 dark:text-yellow-100 mb-2">Atenção! Integração não configurada</strong>
                            <p class="text-xs text-yellow-800 dark:text-yellow-200 mb-2">
                                Para habilitar a sincronização com Google Calendar, é necessário cadastrar primeiro a integração 
                                com a chave <code class="bg-yellow-100 dark:bg-yellow-900/40 px-1 py-0.5 rounded text-xs">google_calendar</code> e configurar a API no campo de configuração (JSON).
                            </p>
                            <div class="flex gap-2">
                                <a href="{{ workspace_route('tenant.integrations.create') }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white text-xs font-medium rounded hover:bg-yellow-700 transition-colors duration-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Cadastrar Integração
                                </a>
                                <a href="{{ workspace_route('tenant.integrations.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 transition-colors duration-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                    </svg>
                                    Ver Todas as Integrações
                                </a>
                            </div>
                        </div>
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
                               x-on:change="$refs.googleCalendarAutoSync.style.display = $el.checked ? 'block' : 'none'"
                               style="cursor: {{ !$hasGoogleCalendarIntegration ? 'not-allowed' : 'pointer' }};">
                        <div class="ml-3">
                            <span class="block text-sm font-medium {{ !$hasGoogleCalendarIntegration ? 'text-gray-500' : 'text-gray-900 dark:text-white' }}">Habilitar Sincronização com Google Calendar</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Sincronize seus agendamentos com o Google Calendar.
                                @if(!$hasGoogleCalendarIntegration)
                                    <br><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 mt-1">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Cadastre a integração primeiro
                                    </span>
                                @endif
                            </span>
                        </div>
                    </label>
                </div>

                <div x-ref="googleCalendarAutoSync" 
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
                            <span class="block text-sm font-medium {{ !$hasGoogleCalendarIntegration ? 'text-gray-500' : 'text-gray-900 dark:text-white' }}">Sincronização Automática</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Sincronize automaticamente os agendamentos com o Google Calendar em tempo real.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            @if($hasGoogleCalendarIntegration && $googleCalendarIntegration)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <strong class="block text-sm font-medium text-green-900 dark:text-green-100 mb-1">Integração configurada com sucesso!</strong>
                                <span class="text-xs text-green-800 dark:text-green-200">A integração Google Calendar está cadastrada e pronta para uso.</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ workspace_route('tenant.integrations.edit', ['id' => $googleCalendarIntegration->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary/90 transition-colors duration-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar Integração
                        </a>
                        <a href="{{ workspace_route('tenant.oauth-accounts.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 transition-colors duration-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            Gerenciar Contas OAuth
                        </a>
                    </div>
                </div>
            @endif
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
