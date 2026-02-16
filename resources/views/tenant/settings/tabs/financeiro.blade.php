<!-- Aba Financeiro -->
<div class="space-y-8">
    @php
        $financeEnabled = tenant_setting('finance.enabled') === 'true';
        $user = auth()->user();
        // Garantir que modules seja sempre um array
        $userModules = [];
        if ($user && $user->modules) {
            if (is_array($user->modules)) {
                $userModules = $user->modules;
            } elseif (is_string($user->modules)) {
                $decoded = json_decode($user->modules, true);
                $userModules = is_array($decoded) ? $decoded : [];
            }
        }
        $hasFinanceModule = ($user && $user->role === 'admin') || in_array('finance', $userModules);
    @endphp

    @if(!$financeEnabled)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-8">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="ml-3">
                    <strong class="block text-sm font-medium text-yellow-900 dark:text-yellow-100 mb-2">Módulo Financeiro Desabilitado</strong>
                    <p class="text-xs text-yellow-800 dark:text-yellow-200 mb-3">
                        O módulo financeiro não está habilitado para este tenant. Para habilitar e configurar, 
                        acesse a página de configurações completas.
                    </p>
                    <a href="{{ workspace_route('tenant.settings.finance.index') }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white text-xs font-medium rounded hover:bg-yellow-700 transition-colors duration-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Habilitar e Configurar
                    </a>
                </div>
            </div>
        </div>
    @elseif(!$hasFinanceModule)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-8">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="ml-3">
                    <strong class="block text-sm font-medium text-yellow-900 dark:text-yellow-100 mb-2">Sem Acesso ao Módulo Financeiro</strong>
                    <p class="text-xs text-yellow-800 dark:text-yellow-200">
                        Você não possui permissão para acessar o módulo financeiro. Entre em contato com o administrador 
                        para solicitar acesso.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Configurações Financeiras</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Configure o módulo financeiro, integração com Asaas e regras de cobrança.
                    </p>
                </div>
                <a href="{{ workspace_route('tenant.settings.finance.index') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Configurações Completas
                </a>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="ml-3">
                        <strong class="block text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">Módulo Financeiro Ativo</strong>
                        <p class="text-xs text-blue-800 dark:text-blue-200">
                            Clique em "Configurações Completas" para acessar todas as opções de configuração do módulo financeiro, 
                            incluindo integração com Asaas, regras de cobrança, comissões e muito mais.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Status do Módulo
                        </h3>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Módulo Financeiro</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Habilitado
                        </span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            Integração Asaas
                        </h3>
                    </div>
                    @php
                        $asaasEnv = tenant_setting('finance.asaas.environment', 'sandbox');
                        $asaasKey = tenant_setting('finance.asaas.api_key', '');
                    @endphp
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Ambiente</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $asaasEnv === 'production' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' }}">
                                {{ $asaasEnv === 'production' ? 'Produção' : 'Sandbox' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">API Key</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ !empty($asaasKey) ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }}">
                                {{ !empty($asaasKey) ? 'Configurada' : 'Não Configurada' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
