<!-- Aba Integrações -->
<div class="space-y-8">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Configurações de Integrações</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Defina quais sincronizações automáticas ficam ativas neste tenant.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.integrations') }}" class="space-y-6">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Google Calendar</h3>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $hasGoogleCalendarIntegration ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                        {{ $hasGoogleCalendarIntegration ? 'Credenciais globais ok' : 'Credenciais globais ausentes' }}
                    </span>
                </div>
            </div>
            <div class="space-y-4 p-6">
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                    O Google Calendar usa credenciais OAuth globais da Platform (com fallback para <code>services.google.client_id</code> e <code>services.google.client_secret</code>).
                </div>

                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" id="integrations_google_calendar_enabled" name="integrations_google_calendar_enabled" value="1"
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                               {{ $settings['integrations.google_calendar.enabled'] ? 'checked' : '' }}
                               {{ !$hasGoogleCalendarIntegration ? 'disabled' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Habilitar Google Calendar</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Ativa a sincronização Google Calendar no tenant.</span>
                        </span>
                    </label>
                </div>

                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700 {{ !$settings['integrations.google_calendar.enabled'] ? 'opacity-70' : '' }}">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" id="integrations_google_calendar_auto_sync" name="integrations_google_calendar_auto_sync" value="1"
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                               {{ $settings['integrations.google_calendar.auto_sync'] ? 'checked' : '' }}
                               {{ !$hasGoogleCalendarIntegration ? 'disabled' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Sincronização automática</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Criação/edição/cancelamento de agendamento atualizam o Google automaticamente quando o profissional tiver token.</span>
                        </span>
                    </label>
                </div>

            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Apple Calendar</h3>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $hasAppleCalendarIntegration ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                        {{ $hasAppleCalendarIntegration ? 'Infraestrutura ok' : 'Migrations pendentes' }}
                    </span>
                </div>
            </div>
            <div class="space-y-4 p-6">
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                    O Apple Calendar funciona por profissional, via CalDAV com token em <code>apple_calendar_tokens</code>.
                </div>

                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" id="integrations_apple_calendar_enabled" name="integrations_apple_calendar_enabled" value="1"
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                               {{ $settings['integrations.apple_calendar.enabled'] ? 'checked' : '' }}
                               {{ !$hasAppleCalendarIntegration ? 'disabled' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Habilitar Apple Calendar</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Ativa a sincronização Apple Calendar no tenant.</span>
                        </span>
                    </label>
                </div>

                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700 {{ !$settings['integrations.apple_calendar.enabled'] ? 'opacity-70' : '' }}">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" id="integrations_apple_calendar_auto_sync" name="integrations_apple_calendar_auto_sync" value="1"
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                               {{ $settings['integrations.apple_calendar.auto_sync'] ? 'checked' : '' }}
                               {{ !$hasAppleCalendarIntegration ? 'disabled' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Sincronização automática</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Criação/edição/cancelamento de agendamento atualizam o Apple automaticamente quando o profissional tiver conexão.</span>
                        </span>
                    </label>
                </div>

            </div>
        </div>

        @include('tenant.settings.partials.form-actions')
    </form>

    @php
        $currentUser = auth()->guard('tenant')->user();
        $isAdmin = $currentUser && $currentUser->role === 'admin';
    @endphp

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Conexões por profissional</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Governança administrativa: status e revogação de vínculos. A autenticação pessoal deve ser feita pelo próprio profissional na Agenda do Profissional.
            </p>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Profissional</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Google</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Apple</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Última sincronização</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        @forelse ($calendarSyncDoctors as $doctor)
                            @php
                                $doctorName = $doctor->user->name_full ?? $doctor->user->name ?? 'Profissional';
                                $hasGoogle = (bool) $doctor->googleCalendarToken;
                                $hasApple = (bool) $doctor->appleCalendarToken;
                                $doctorLastSync = $calendarSyncLastSyncByDoctor->get($doctor->id);
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">{{ $doctorName }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $hasGoogle ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $hasGoogle ? 'Conectado' : 'Não conectado' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $hasApple ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $hasApple ? 'Conectado' : 'Não conectado' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $doctorLastSync ? \Carbon\Carbon::parse($doctorLastSync)->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <div class="inline-flex items-center gap-2">
                                        @if ($isAdmin && $hasGoogle)
                                            <form action="{{ workspace_route('tenant.integrations.google.disconnect', ['doctor' => $doctor->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="return_context" value="settings">
                                                <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/20">
                                                    Revogar Google
                                                </button>
                                            </form>
                                        @endif

                                        @if ($isAdmin && $hasApple)
                                            <form action="{{ workspace_route('tenant.integrations.apple.disconnect', ['doctor' => $doctor->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="return_context" value="settings">
                                                <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/20">
                                                    Revogar Apple
                                                </button>
                                            </form>
                                        @endif

                                        @if (!$isAdmin)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Somente administradores</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Nenhum profissional ativo encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
