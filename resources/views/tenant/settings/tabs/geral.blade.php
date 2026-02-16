<!-- Aba Geral -->
<div class="space-y-6">
    <div class="page-header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">Configurações Gerais</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Controle as preferências básicas do sistema usado pelos pacientes e equipe.
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 space-y-6">
            <form method="POST" action="{{ workspace_route('tenant.settings.update.general') }}" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fuso Horário <span class="text-red-500">*</span>
                        </label>
                        <select name="timezone" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @foreach (DateTimeZone::listIdentifiers(DateTimeZone::AMERICA) as $tz)
                                <option value="{{ $tz }}" {{ ($settings['timezone'] ?? 'America/Sao_Paulo') == $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fuso usado para exibir datas e horários.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Formato de Data <span class="text-red-500">*</span>
                        </label>
                        <select name="date_format" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="d/m/Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : '' }}>dd/mm/aaaa</option>
                            <option value="Y-m-d" {{ ($settings['date_format'] ?? 'd/m/Y') == 'Y-m-d' ? 'selected' : '' }}>aaaa-mm-dd</option>
                            <option value="m/d/Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'm/d/Y' ? 'selected' : '' }}>mm/dd/aaaa</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Formato de Hora <span class="text-red-500">*</span>
                        </label>
                        <select name="time_format" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="H:i" {{ ($settings['time_format'] ?? 'H:i') == 'H:i' ? 'selected' : '' }}>24 horas (14:30)</option>
                            <option value="h:i A" {{ ($settings['time_format'] ?? 'H:i') == 'h:i A' ? 'selected' : '' }}>12 horas (02:30 PM)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Idioma <span class="text-red-500">*</span>
                        </label>
                        <select name="language" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="pt-BR" {{ ($settings['language'] ?? 'pt-BR') == 'pt-BR' ? 'selected' : '' }}>Português (Brasil)</option>
                            <option value="en" {{ ($settings['language'] ?? 'pt-BR') == 'en' ? 'selected' : '' }}>English</option>
                            <option value="es" {{ ($settings['language'] ?? 'pt-BR') == 'es' ? 'selected' : '' }}>Español</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Moeda
                        </label>
                        <select name="currency"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="BRL" {{ ($settings['currency'] ?? 'BRL') == 'BRL' ? 'selected' : '' }}>Real (R$)</option>
                            <option value="USD" {{ ($settings['currency'] ?? 'BRL') == 'USD' ? 'selected' : '' }}>Dólar ($)</option>
                            <option value="EUR" {{ ($settings['currency'] ?? 'BRL') == 'EUR' ? 'selected' : '' }}>Euro (€)</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="reset" class="btn-patient-secondary">
                        Cancelar
                    </button>
                    <a href="{{ workspace_route('tenant.settings.index') }}" class="btn-patient-secondary">
                        Voltar
                    </a>
                    <button type="submit" class="btn-patient-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"></path>
                        </svg>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
