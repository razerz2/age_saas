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
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-green-800 dark:text-green-200">{{ session('success') }}</span>
                <button type="button" data-dismiss="alert" class="ml-auto text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 dark:bg-red-900/20 dark:border-red-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span class="text-red-800 dark:text-red-200">{{ session('error') }}</span>
                <button type="button" data-dismiss="alert" class="ml-auto text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 dark:bg-blue-900/20 dark:border-blue-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-blue-800 dark:text-blue-200">{{ session('info') }}</span>
                <button type="button" data-dismiss="alert" class="ml-auto text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Como Funciona a Integração
                </h4>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-200"><strong>Sincronização Automática:</strong> Todos os agendamentos são sincronizados automaticamente com o Google Calendar do médico</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-200"><strong>Criação:</strong> Ao criar um agendamento, o evento é criado no Google Calendar</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-200"><strong>Edição:</strong> Ao editar um agendamento, o evento é atualizado no Google Calendar</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-200"><strong>Cancelamento:</strong> Ao cancelar um agendamento, o evento é removido do Google Calendar</span>
                        </li>
                    </ul>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-200"><strong>Agendamentos Recorrentes:</strong> São sincronizados como eventos recorrentes (RRULE) no Google Calendar</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-200"><strong>Conta Individual:</strong> Cada médico conecta sua própria conta do Google Calendar</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-200"><strong>Renovação Automática:</strong> Tokens são renovados automaticamente quando necessário</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-200"><strong>Áreas Sincronizadas:</strong> Funciona para agendamentos criados em qualquer área do sistema (administrativa, pública, portal do paciente)</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    <i class="mdi mdi-google text-blue-600 mr-2"></i>
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
                                                <i class="mdi mdi-account-circle text-blue-600 text-2xl"></i>
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
                                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Status da integração">
                                                        <i class="mdi mdi-information-outline"></i>
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
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-300">
                                                            <i class="mdi mdi-link-variant-off mr-1"></i>
                                                            Desconectar
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <a href="{{ workspace_route('tenant.integrations.google.connect', ['doctor' => $doctor->id]) }}"
                                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-primary text-white hover:bg-primary/90">
                                                    <i class="mdi mdi-google mr-1"></i>
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
                    <a href="{{ workspace_route('tenant.integrations.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Voltar para Integrações
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection
