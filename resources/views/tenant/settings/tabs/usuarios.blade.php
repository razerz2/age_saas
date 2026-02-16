<!-- Aba Usuários -->
<div class="space-y-8">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Configurações de Usuários & Permissões</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Defina quais módulos serão atribuídos automaticamente ao criar novos usuários por perfil.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.user-defaults') }}">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Módulos padrão para Usuário Comum -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Módulos Padrão – Usuário Comum
                    </h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Selecione os módulos que serão atribuídos automaticamente ao criar um novo usuário comum.
                </p>
                @php
                    $allModules = App\Models\Tenant\Module::all();
                    // Remover módulo "usuários" e "configurações" da lista de opções
                    $availableModules = collect($allModules)->reject(function($module) {
                        return in_array($module['key'], ['users', 'settings']);
                    })->values()->all();
                    $commonUserModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_common_user', '[]'), true) ?? [];
                @endphp
                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-700 max-h-64 overflow-y-auto">
                    @foreach($availableModules as $module)
                        <div class="flex items-center mb-2 last:mb-0">
                            <input class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   type="checkbox"
                                   name="user_defaults[modules_common_user][]"
                                   value="{{ $module['key'] }}"
                                   id="module_common_{{ $module['key'] }}"
                                   {{ in_array($module['key'], $commonUserModules) ? 'checked' : '' }}>
                            <label class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300" for="module_common_{{ $module['key'] }}">
                                {{ $module['name'] }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Módulos padrão para Médico -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        Módulos Padrão – Médico
                    </h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Selecione os módulos que serão atribuídos automaticamente ao criar um novo usuário médico.
                </p>
                @php
                    $doctorModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_doctor', '[]'), true) ?? [];
                @endphp
                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-700 max-h-64 overflow-y-auto">
                    @foreach($availableModules as $module)
                        <div class="flex items-center mb-2 last:mb-0">
                            <input class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   type="checkbox"
                                   name="user_defaults[modules_doctor][]"
                                   value="{{ $module['key'] }}"
                                   id="module_doctor_{{ $module['key'] }}"
                                   {{ in_array($module['key'], $doctorModules) ? 'checked' : '' }}>
                            <label class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300" for="module_doctor_{{ $module['key'] }}">
                                {{ $module['name'] }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-8">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <strong class="block text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">Como funciona:</strong>
                    <ul class="text-xs text-blue-800 dark:text-blue-200 space-y-1">
                        <li>Os módulos selecionados serão aplicados automaticamente ao criar novos usuários com o perfil correspondente.</li>
                        <li>Usuários <strong>Administradores</strong> não são afetados por essas configurações, pois possuem acesso total ao sistema.</li>
                        <li>As configurações não afetam usuários já existentes, apenas novos usuários criados após a configuração.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-patient-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"></path>
                </svg>
                Salvar Alterações
            </button>
        </div>
    </form>
</div>
