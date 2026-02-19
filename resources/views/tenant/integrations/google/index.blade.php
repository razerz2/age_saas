@extends('layouts.tailadmin.app')

@section('title', 'Integração Google Calendar')
@section('page', 'integrations')

@section('content')

    <div class="page-header">
        <h3 class="page-title">Integração Google Calendar</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Cada médico pode conectar sua própria conta do Google Calendar. 
            Os agendamentos serão sincronizados automaticamente com o calendário do médico conectado.
        </p>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.integrations.index') }}">Integrações</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Google Calendar</li>
            </ol>
        </nav>
    </div>

    {{-- Alertas --}}
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

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-icon name="information-outline" class="w-5 h-5 mr-2 text-blue-600" />
                    Como Funciona a Integração
                </h4>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Sincronização Automática:</strong> Todos os agendamentos são sincronizados automaticamente com o Google Calendar do médico</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Criação:</strong> Ao criar um agendamento, o evento é criado no Google Calendar</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Edição:</strong> Ao editar um agendamento, o evento é atualizado no Google Calendar</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Cancelamento:</strong> Ao cancelar um agendamento, o evento é removido do Google Calendar</span>
                        </li>
                    </ul>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Agendamentos Recorrentes:</strong> São sincronizados como eventos recorrentes (RRULE) no Google Calendar</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Conta Individual:</strong> Cada médico conecta sua própria conta do Google Calendar</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Renovação Automática:</strong> Tokens são renovados automaticamente quando necessário</span>
                        </li>
                        <li class="flex items-start">
                            <x-icon name="information-outline" class="w-4 h-4 mr-2 text-green-600 mt-1" />
                            <span class="text-gray-700 dark:text-gray-200"><strong>Áreas Sincronizadas:</strong> Funciona para agendamentos criados em qualquer área do sistema (administrativa, pública, portal do paciente)</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-icon name="google" class=" text-blue-600 mr-2" />
                    Integração Google Calendar por Médico
                </h4>
            </div>
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
                                        @if ($doctor->googleCalendarToken)
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                    Conectado
                                                </span>
                                                @if ($doctor->googleCalendarToken->isExpired())
                                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                                        Token expirado (será renovado automaticamente)
                                                    </div>
                                                @else
                                                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                                        Sincronização ativa
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300">
                                                    Desconectado
                                                </span>
                                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                    Clique em "Conectar Google" para ativar
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                                        @if ($doctor->googleCalendarToken && $doctor->googleCalendarToken->updated_at)
                                            {{ $doctor->googleCalendarToken->updated_at->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if ($user->role === 'admin' || ($user->role === 'doctor' && $user->doctor && $user->doctor->id === $doctor->id))
                                            {{-- Admin e médico (para si mesmo) podem conectar/desconectar --}}
                                            @if ($doctor->googleCalendarToken)
                                                <div class="flex flex-wrap justify-end gap-2">
                                                    <button type="button"
                                                            class="btn btn-outline"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Status da integração">
                                                        <x-icon name="information-outline" class="" />
                                                    </button>
                                                    <form action="{{ workspace_route('tenant.integrations.google.disconnect', ['doctor' => $doctor->id]) }}"
                                                          method="POST"
                                                      data-confirm="disconnect-google"
                                                      data-confirm-title="Desconectar Google Calendar"
                                                      data-confirm-message="Tem certeza que deseja desconectar a integração do Google Calendar para este médico?\n\nOs eventos já criados no Google Calendar não serão removidos automaticamente."
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
                                                <a href="{{ workspace_route('tenant.integrations.google.connect', ['doctor' => $doctor->id]) }}"
                                                   class="btn btn-primary">
                                                    <x-icon name="google" class=" mr-1" />
                                                    Conectar Google
                                                </a>
                                            @endif
                                        @else
                                            {{-- Usuário comum: apenas visualiza status --}}
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
    </div>

@endsection
