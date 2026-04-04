@extends('layouts.tailadmin.app')

@section('title', 'Google Calendar')
@section('page', 'agenda-settings-sync')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2">
            <nav aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li><a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-gray-800 dark:hover:text-white">Dashboard</a></li>
                    <li>/</li>
                    <li><a href="{{ workspace_route('tenant.agenda-settings.index') }}" class="hover:text-gray-800 dark:hover:text-white">Agenda do Profissional</a></li>
                    <li>/</li>
                    <li>Sincronização</li>
                    <li>/</li>
                    <li class="font-medium text-gray-900 dark:text-white">Google Calendar</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Sincronização Google Calendar</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">A sincronização é individual por profissional; as credenciais OAuth são globais (Platform com fallback para ambiente). A autenticação deve ser iniciada na Agenda do Profissional.</p>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">{{ session('error') }}</div>
        @endif
        @if (session('info'))
            <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">{{ session('info') }}</div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Requisitos</h2>
            </div>
            <div class="p-6 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <p>- Credenciais globais: Platform Settings (fallback: <code>services.google.client_id</code> e <code>services.google.client_secret</code>).</p>
                <p>- Com credenciais ativas, cada profissional pode conectar/desconectar sua conta Google.</p>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $hasGoogleCredentials ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                    {{ $hasGoogleCredentials ? 'Credenciais globais configuradas' : 'Credenciais globais ausentes' }}
                </span>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Conexões por Profissional</h2>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/40">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Profissional</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Atualizado em</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($doctors as $doctor)
                                @php
                                    $isOwnerDoctor = $user->role === 'doctor' && $user->doctor && (string) $user->doctor->id === (string) $doctor->id;
                                    $canInitiateAuth = $isOwnerDoctor;
                                    $canRevoke = $user->role === 'admin' || $isOwnerDoctor;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $doctor->user->name_full ?? $doctor->user->name }}</div>
                                        @if ($doctor->crm_number)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">CRM {{ $doctor->crm_number }}/{{ $doctor->crm_state }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if ($doctor->googleCalendarToken)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">Conectado</span>
                                            @if ($doctor->googleCalendarToken->isExpired())
                                                <div class="mt-1 text-xs text-yellow-700 dark:text-yellow-300">Token expirado (renovação automática quando possível)</div>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">Desconectado</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $doctor->googleCalendarToken && $doctor->googleCalendarToken->updated_at ? $doctor->googleCalendarToken->updated_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        @if ($canRevoke && $doctor->googleCalendarToken)
                                                <form action="{{ workspace_route('tenant.integrations.google.disconnect', ['doctor' => $doctor->id]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/20">
                                                        Desconectar
                                                    </button>
                                                </form>
                                        @elseif($canInitiateAuth && $hasGoogleCredentials)
                                                <a href="{{ workspace_route('tenant.integrations.google.connect', ['doctor' => $doctor->id]) }}" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-700">
                                                    Conectar Google Calendar
                                                </a>
                                        @elseif($isOwnerDoctor && !$hasGoogleCredentials)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Configure as credenciais globais primeiro</span>
                                        @else
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Apenas governança administrativa</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum profissional ativo encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    <a href="{{ workspace_route('tenant.agenda-settings.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700/60">Voltar para Agenda do Profissional</a>
                </div>
            </div>
        </div>
    </div>
@endsection
