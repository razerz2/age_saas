<!-- Aba Agendamentos -->
<div class="space-y-8">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Configurações de Agendamentos</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Configure o comportamento padrão dos agendamentos.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.appointments') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Duração Padrão (minutos) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="appointments_default_duration" 
                       value="{{ $settings['appointments.default_duration'] ?? '30' }}" 
                       min="15" max="480" step="15" required
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Duração padrão de uma consulta (15, 30, 45, 60, etc.)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Intervalo Entre Consultas (minutos)
                </label>
                <input type="number" name="appointments_interval_between" 
                       value="{{ $settings['appointments.interval_between'] ?? '0' }}" 
                       min="0" max="60" step="5"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tempo de intervalo entre uma consulta e outra</p>
            </div>

            <div class="md:col-span-2">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" 
                               id="appointments_auto_confirm"
                               name="appointments_auto_confirm"
                               value="1"
                               {{ ($settings['appointments.auto_confirm'] ?? false) ? 'checked' : '' }}
                               class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Confirmar Agendamentos Automaticamente</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Quando habilitado, novos agendamentos são automaticamente confirmados.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" 
                               id="appointments_allow_cancellation"
                               name="appointments_allow_cancellation"
                               value="1"
                               {{ ($settings['appointments.allow_cancellation'] ?? false) ? 'checked' : '' }}
                               x-on:change="$refs.cancellation_hours.style.display = $el.checked ? 'block' : 'none'"
                               class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Permitir Cancelamento de Agendamentos</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Permite que pacientes e médicos cancelem agendamentos.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <div x-ref="cancellation_hours" 
                 style="{{ ($settings['appointments.allow_cancellation'] ?? false) ? '' : 'display:none;' }}">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Horas Mínimas para Cancelamento
                </label>
                <input type="number" name="appointments_cancellation_hours" 
                       value="{{ $settings['appointments.cancellation_hours'] ?? '2' }}" 
                       min="1" step="1"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Mínimo de horas antes do agendamento para permitir cancelamento</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Horas para Lembrete (antes do agendamento)
                </label>
                <input type="number" name="appointments_reminder_hours" 
                       value="{{ $settings['appointments.reminder_hours'] ?? '24' }}" 
                       min="1" max="168" step="1"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Quantas horas antes do agendamento enviar lembrete (máx. 168 = 7 dias)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Modo de Atendimento Padrão <span class="text-red-500">*</span>
                </label>
                <select name="appointments_default_appointment_mode" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="presencial" {{ ($settings['appointments.default_appointment_mode'] ?? 'user_choice') == 'presencial' ? 'selected' : '' }}>
                        Sempre presencial
                    </option>
                    <option value="online" {{ ($settings['appointments.default_appointment_mode'] ?? 'user_choice') == 'online' ? 'selected' : '' }}>
                        Sempre online
                    </option>
                    <option value="user_choice" {{ ($settings['appointments.default_appointment_mode'] ?? 'user_choice') == 'user_choice' ? 'selected' : '' }}>
                        Paciente escolhe
                    </option>
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Define como o modo de atendimento será escolhido ao criar agendamentos. 
                    Se "Paciente escolhe", o campo será exibido para seleção.
                </p>
            </div>
            <div class="md:col-span-2">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                        Confirma��o com Prazo (Hold)
                    </h3>
                    <div class="space-y-4">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox"
                                   id="appointments_confirmation_enabled"
                                   name="appointments_confirmation_enabled"
                                   value="1"
                                   {{ ($settings['appointments.confirmation.enabled'] ?? false) ? 'checked' : '' }}
                                   class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900 dark:text-white">Habilitar confirma��o com prazo</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Se desabilitado, o comportamento atual permanece inalterado.
                                </span>
                            </div>
                        </label>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Prazo de confirma��o (minutos)
                            </label>
                            <input type="number" name="appointments_confirmation_ttl_minutes"
                                   value="{{ $settings['appointments.confirmation.ttl_minutes'] ?? '30' }}"
                                   min="1" max="1440" step="1"
                                   class="w-full md:w-72 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Valor padr�o: 30 minutos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                        Waitlist (Fila)
                    </h3>
                    <div class="space-y-4">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox"
                                   id="appointments_waitlist_enabled"
                                   name="appointments_waitlist_enabled"
                                   value="1"
                                   {{ ($settings['appointments.waitlist.enabled'] ?? false) ? 'checked' : '' }}
                                   class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900 dark:text-white">Habilitar waitlist</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Se desabilitado, n�o altera o fluxo atual de agendamento.
                                </span>
                            </div>
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    TTL da oferta (minutos)
                                </label>
                                <input type="number" name="appointments_waitlist_offer_ttl_minutes"
                                       value="{{ $settings['appointments.waitlist.offer_ttl_minutes'] ?? '15' }}"
                                       min="1" max="1440" step="1"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Valor padr�o: 15.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    M�ximo por slot
                                </label>
                                <input type="number" name="appointments_waitlist_max_per_slot"
                                       value="{{ $settings['appointments.waitlist.max_per_slot'] ?? '' }}"
                                       min="1" step="1"
                                       placeholder="Sem limite"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Deixe vazio para ilimitado.</p>
                            </div>

                            <div>
                                <label class="flex items-start cursor-pointer mt-7">
                                    <input type="checkbox"
                                           id="appointments_waitlist_allow_when_confirmed"
                                           name="appointments_waitlist_allow_when_confirmed"
                                           value="1"
                                           {{ ($settings['appointments.waitlist.allow_when_confirmed'] ?? true) ? 'checked' : '' }}
                                           class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Permitir waitlist quando confirmado</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
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
