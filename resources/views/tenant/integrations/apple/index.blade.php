@extends('layouts.tailadmin.app')

@section('title', 'Integração Apple Calendar')
@section('page', 'integrations')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- PAGE_HEADER_START -->
        <div class="px-6 pt-6">
            <nav class="min-w-0" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            <x-icon name="home-outline" size="text-base" />
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <a href="{{ workspace_route('tenant.integrations.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Integrações</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Apple Calendar</span>
                    </li>
                </ol>
            </nav>
        </div>
        <!-- PAGE_HEADER_END -->

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 dark:bg-green-900/20 dark:border-green-800">
                <div class="flex items-center">
                    <x-icon name="information-outline" class="w-5 h-5 text-green-600 mr-2" />
                    <span class="text-green-800 dark:text-green-200">{{ session('success') }}</span>
                    <button type="button" data-dismiss="alert" class="ml-auto text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                        <x-icon name="information-outline" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 dark:bg-red-900/20 dark:border-red-800">
                <div class="flex items-center">
                    <x-icon name="information-outline" class="w-5 h-5 text-red-600 mr-2" />
                    <span class="text-red-800 dark:text-red-200">{{ session('error') }}</span>
                    <button type="button" data-dismiss="alert" class="ml-auto text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                        <x-icon name="information-outline" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 dark:bg-blue-900/20 dark:border-blue-800">
                <div class="flex items-center">
                    <x-icon name="information-outline" class="w-5 h-5 text-blue-600 mr-2" />
                    <span class="text-blue-800 dark:text-blue-200">{{ session('info') }}</span>
                    <button type="button" data-dismiss="alert" class="ml-auto text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                        <x-icon name="information-outline" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @endif

        @if (!isset($hasAppleCalendarTable) || !$hasAppleCalendarTable)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-5 mb-6 dark:bg-yellow-900/20 dark:border-yellow-800">
                <div class="flex items-start">
                    <x-icon name="information-outline" class="w-6 h-6 text-yellow-600 mr-3 mt-0.5" />
                    <div>
                        <h5 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">Migrations Pendentes</h5>
                        <p class="text-sm text-yellow-800 dark:text-yellow-200 mt-2">
                            A tabela <code>apple_calendar_tokens</code> ainda não foi criada.
                            Execute as migrations para ativar a integração com Apple Calendar.
                        </p>
                        <p class="text-sm text-yellow-800 dark:text-yellow-200 mt-3">
                            <strong>Opção 1 (Recomendado):</strong> Execute o script SQL em
                            <code>database/migrations/tenant/apple_calendar_migration.sql</code> diretamente no banco do tenant.
                        </p>
                        <p class="text-sm text-yellow-800 dark:text-yellow-200 mt-2">
                            <strong>Opção 2:</strong> Execute via Artisan quando o tenant estiver ativo:
                        </p>
                        <pre class="bg-yellow-100 text-yellow-900 p-3 rounded-lg mt-2 overflow-x-auto"><code>php artisan migrate --database=tenant --path=database/migrations/tenant/2025_12_03_084550_add_apple_calendar_fields_to_appointments_table.php
                        php artisan migrate --database=tenant --path=database/migrations/tenant/2025_12_03_084556_create_apple_calendar_tokens_table.php</code></pre>
                    </div>
                </div>
            </div>
        @endif
        <!-- CARD_HOW_IT_WORKS_START -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-start gap-2">
                    <x-icon name="apple" class="text-blue-600 mt-0.5" />
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Integração Apple Calendar por Médico</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Cada médico pode conectar sua própria conta do Apple Calendar (iCloud).
                            Os agendamentos serão sincronizados automaticamente com o calendário do médico conectado.
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Sincronização Automática:</strong> Todos os agendamentos são sincronizados automaticamente com o Apple Calendar (iCloud) do médico</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Criação:</strong> Ao criar um agendamento, o evento é criado no Apple Calendar</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Edição:</strong> Ao editar um agendamento, o evento é atualizado no Apple Calendar</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Cancelamento:</strong> Ao cancelar um agendamento, o evento é removido do Apple Calendar</span>
                        </li>
                    </ul>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Protocolo CalDAV:</strong> Usa o protocolo CalDAV para sincronização com iCloud</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Conta Individual:</strong> Cada médico conecta sua própria conta do iCloud</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-yellow-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Senha de App:</strong> É necessário usar uma senha de app específica do iCloud (não a senha da conta)</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Áreas Sincronizadas:</strong> Funciona para agendamentos criados em qualquer área do sistema (administrativa, pública, portal do paciente)</span>
                        </li>
                    </ul>
                </div>
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4 dark:bg-yellow-900/20 dark:border-yellow-800">
                    <div class="flex items-start">
                        <x-icon name="information-outline" class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" />
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            <strong>Importante:</strong> Para usar o iCloud, você precisa gerar uma senha de app específica em
                            <a href="https://appleid.apple.com/account/manage" target="_blank" rel="noopener noreferrer" class="underline">appleid.apple.com</a>.
                            A senha da sua conta Apple não funcionará para CalDAV.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- CARD_HOW_IT_WORKS_END -->
        <!-- CARD_DOCTORS_START -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/40">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Médico</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Última Atualização</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @forelse ($doctors as $doctor)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                                        <div class="flex items-center">
                                            <div class="mr-3">
                                                <x-icon name="account-circle" class=" text-blue-600 text-2xl" />
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ $doctor->user->name_full ?? $doctor->user->name }}</div>
                                                @if ($doctor->crm_number)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">CRM: {{ $doctor->crm_number }}/{{ $doctor->crm_state }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                                        @if (isset($hasAppleCalendarTable) && $hasAppleCalendarTable && $doctor->appleCalendarToken)
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                    Conectado
                                                </span>
                                                <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                                    Sincronização ativa
                                                </div>
                                            </div>
                                        @else
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300">
                                                    Desconectado
                                                </span>
                                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                    Clique em "Conectar Apple" para ativar
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                                        @if (isset($hasAppleCalendarTable) && $hasAppleCalendarTable && $doctor->appleCalendarToken && $doctor->appleCalendarToken->updated_at)
                                            {{ $doctor->appleCalendarToken->updated_at->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @php
                                            $canConnect = false;
                                            if ($user->role === 'admin') {
                                                $canConnect = true;
                                            } elseif ($user->role === 'doctor') {
                                                if (!$user->relationLoaded('doctor')) {
                                                    $user->load('doctor');
                                                }
                                                $canConnect = $user->doctor && (string) $user->doctor->id === (string) $doctor->id;
                                            } elseif ($user->role === 'user') {
                                                $canConnect = false;
                                            }
                                        @endphp

                                        @if ($canConnect)
                                            @if (isset($hasAppleCalendarTable) && $hasAppleCalendarTable && $doctor->appleCalendarToken)
                                                <div class="flex flex-wrap justify-end gap-2">
                                                    <button type="button"
                                                            class="btn btn-outline"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Status da integração">
                                                        <x-icon name="information-outline" class="" />
                                                    </button>
                                                    <form action="{{ workspace_route('tenant.integrations.apple.disconnect', ['doctor' => $doctor->id]) }}"
                                                          method="POST"
                                                          data-confirm="disconnect-apple"
                                                          data-confirm-title="Desconectar Apple Calendar"
                                                          data-confirm-message="Tem certeza que deseja desconectar a integração do Apple Calendar para este médico?\n\nOs eventos já criados no Apple Calendar não serão removidos automaticamente."
                                                          data-confirm-confirm-text="Desconectar"
                                                          data-confirm-cancel-text="Cancelar"
                                                          data-confirm-type="warning">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">
                                                            <x-icon name="link-variant-off" class=" mr-1" />
                                                            Desconectar
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                @if (isset($hasAppleCalendarTable) && $hasAppleCalendarTable)
                                                    <a href="{{ workspace_route('tenant.integrations.apple.connect.form', ['doctor' => $doctor->id]) }}"
                                                       class="btn btn-primary">
                                                        <x-icon name="apple" class=" mr-1" />
                                                        Conectar Apple
                                                    </a>
                                                @else
                                                    <div class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-yellow-50 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200">
                                                        Execute as migrations primeiro
                                                    </div>
                                                @endif
                                            @endif
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">
                                                Apenas visualização
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        Nenhum médico cadastrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    <a href="{{ workspace_route('tenant.integrations.index') }}" class="btn btn-outline">
                        <x-icon name="information-outline" class="w-4 h-4 mr-2" />
                        Voltar para Integrações
                    </a>
                </div>
            </div>
        </div>
        <!-- CARD_DOCTORS_END -->
    </div>
@endsection
