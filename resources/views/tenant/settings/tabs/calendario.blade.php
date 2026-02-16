<!-- Aba Calendário -->
<div class="space-y-8">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Configurações de Calendário</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Configure os horários padrão e dias da semana de funcionamento.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.calendar') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Horário de Início Padrão
                </label>
                <input type="time" name="calendar_default_start_time" 
                       value="{{ $settings['calendar.default_start_time'] }}" required
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Horário padrão de início do expediente</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Horário de Término Padrão
                </label>
                <input type="time" name="calendar_default_end_time" 
                       value="{{ $settings['calendar.default_end_time'] }}" required
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Horário padrão de término do expediente</p>
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                Dias da Semana Padrão
            </label>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                @php
                    $weekdays = [
                        0 => 'Domingo',
                        1 => 'Segunda-feira',
                        2 => 'Terça-feira',
                        3 => 'Quarta-feira',
                        4 => 'Quinta-feira',
                        5 => 'Sexta-feira',
                        6 => 'Sábado'
                    ];
                    $selectedWeekdays = explode(',', $settings['calendar.default_weekdays'] ?? '');
                @endphp
                @foreach ($weekdays as $day => $name)
                    <div class="flex items-center">
                        <input class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                               type="checkbox" 
                               name="calendar_default_weekdays[]" 
                               value="{{ $day }}"
                               id="weekday_{{ $day }}"
                               {{ in_array($day, $selectedWeekdays) ? 'checked' : '' }}>
                        <label class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300" for="weekday_{{ $day }}">
                            {{ $name }}
                        </label>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Selecione os dias da semana que serão usados como padrão para novos horários comerciais</p>
        </div>

        <div class="mb-8">
            <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                <label class="flex items-start cursor-pointer">
                    <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                           type="checkbox" 
                           id="calendar_show_weekends"
                           name="calendar_show_weekends"
                           value="1"
                           {{ $settings['calendar.show_weekends'] ? 'checked' : '' }}>
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Mostrar Finais de Semana no Calendário</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Exibe sábado e domingo na visualização do calendário, mesmo que não sejam dias de funcionamento.
                        </span>
                    </div>
                </label>
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
