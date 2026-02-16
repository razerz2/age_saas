<!-- Aba Notificações -->
<div class="space-y-8">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Configurações de Notificações</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Configure quais tipos de notificações você deseja receber no sistema e como enviar notificações aos pacientes.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.notifications') }}">
        @csrf
        
        <!-- Notificações Internas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Notificações Internas
                </h3>
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
                                Receba notificações quando agendamentos forem criados, atualizados, 
                                cancelados, reagendados ou quando o status mudar.
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
                                Receba notificações quando pacientes responderem aos formulários.
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Configurações de Email -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Configurações de Email
                </h3>
            </div>

            <div class="space-y-4">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                               type="checkbox" 
                               id="notifications_send_email_to_patients"
                               name="notifications_send_email_to_patients"
                               value="1"
                               {{ ($settings['notifications.send_email_to_patients'] ?? false) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Enviar e-mails aos pacientes</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Quando habilitado, os pacientes receberão notificações por email sobre agendamentos, formulários, etc.
                            </span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Driver de Email
                    </label>
                    <select name="email_driver" id="email_driver" required
                            x-on:change="$refs.emailTenancyConfig.style.display = $el.value === 'tenancy' ? 'block' : 'none'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="global" {{ ($settings['email.driver'] ?? 'global') == 'global' ? 'selected' : '' }}>Usar serviço global do sistema</option>
                        <option value="tenancy" {{ ($settings['email.driver'] ?? 'global') == 'tenancy' ? 'selected' : '' }}>Usar SMTP próprio</option>
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Escolha entre usar o serviço global ou configurar seu próprio SMTP</p>
                </div>

                <div x-ref="emailTenancyConfig" style="display: {{ ($settings['email.driver'] ?? 'global') == 'tenancy' ? 'block' : 'none' }};">
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
                                   placeholder="••••••••"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome do Remetente</label>
                            <input type="text" name="email_from_name" 
                                   value="{{ $settings['email.from_name'] ?? '' }}" 
                                   placeholder="Nome da Clínica"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email do Remetente</label>
                            <input type="email" name="email_from_address" 
                                   value="{{ $settings['email.from_address'] ?? '' }}" 
                                   placeholder="noreply@exemplo.com"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações de WhatsApp -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.149-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.123-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    Configurações de WhatsApp
                </h3>
            </div>

            <div class="space-y-4">
                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <label class="flex items-start cursor-pointer">
                        <input class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                               type="checkbox" 
                               id="notifications_send_whatsapp_to_patients"
                               name="notifications_send_whatsapp_to_patients"
                               value="1"
                               {{ ($settings['notifications.send_whatsapp_to_patients'] ?? false) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Enviar WhatsApp aos pacientes</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Quando habilitado, os pacientes receberão notificações por WhatsApp sobre agendamentos, formulários, etc.
                            </span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Driver de WhatsApp
                    </label>
                    <select name="whatsapp_driver" id="whatsapp_driver" required
                            x-on:change="$refs.whatsappTenancyConfig.style.display = $el.value === 'tenancy' ? 'block' : 'none'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="global" {{ ($settings['whatsapp.driver'] ?? 'global') == 'global' ? 'selected' : '' }}>Usar serviço global do sistema</option>
                        <option value="tenancy" {{ ($settings['whatsapp.driver'] ?? 'global') == 'tenancy' ? 'selected' : '' }}>Usar API própria</option>
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Escolha entre usar o serviço global ou configurar sua própria API de WhatsApp</p>
                </div>

                <div x-ref="whatsappTenancyConfig" style="display: {{ ($settings['whatsapp.driver'] ?? 'global') == 'tenancy' ? 'block' : 'none' }};">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API URL</label>
                            <input type="url" name="whatsapp_api_url" 
                                   value="{{ $settings['whatsapp.api_url'] ?? '' }}" 
                                   placeholder="https://api.exemplo.com/send"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Token</label>
                            <input type="text" name="whatsapp_api_token" 
                                   value="{{ $settings['whatsapp.api_token'] ?? '' }}" 
                                   placeholder="seu-token-aqui"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sender (Remetente)</label>
                            <input type="text" name="whatsapp_sender" 
                                   value="{{ $settings['whatsapp.sender'] ?? '' }}" 
                                   placeholder="5511999999999"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>
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
