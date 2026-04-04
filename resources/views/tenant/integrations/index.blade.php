@extends('layouts.tailadmin.app')

@section('title', 'Integracoes')

@section('content')
    @php
        $googleCredentialsReady = has_google_oauth_credentials();
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-2">
            <nav aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Dashboard</a>
                    </li>
                    <li>/</li>
                    <li class="font-medium text-gray-900 dark:text-white">Integracoes</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Integracoes</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Gerencie Google Calendar, Apple Calendar e integracoes genericas.</p>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                {{ session('info') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Google Calendar</h2>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $googleCredentialsReady ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                            {{ $googleCredentialsReady ? 'Credenciais globais ok' : 'Credenciais ausentes' }}
                        </span>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        As credenciais OAuth do Google sao configuradas globalmente na Platform (com fallback para ambiente). A conexao real e feita por medico, via OAuth.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li>- Prioridade: Platform Settings -> <code>services.google.*</code> no ambiente.</li>
                        <li>- Cada medico conecta a propria conta do Google Calendar.</li>
                    </ul>
                    <a href="{{ workspace_route('tenant.integrations.google.index') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                        Gerenciar Google Calendar
                    </a>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Apple Calendar</h2>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        A conexao Apple Calendar usa CalDAV por medico com token salvo no tenant.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li>- Cada medico conecta sua conta iCloud.</li>
                        <li>- Recomendado usar senha de app da Apple.</li>
                    </ul>
                    <a href="{{ workspace_route('tenant.integrations.apple.index') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                        Gerenciar Apple Calendar
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Integracoes Genericas</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cadastros complementares de feature flag/configuracao por tenant.</p>
                </div>
                <a href="{{ workspace_route('tenant.integrations.create') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700/60">
                    Nova integracao
                </a>
            </div>

            <div class="p-6">
                @if ($integrations->isEmpty())
                    <div class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                        Nenhuma integracao generica cadastrada.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/40">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Chave</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Acoes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                @foreach ($integrations as $integration)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ truncate_uuid($integration->id) }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $integration->key }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $integration->is_enabled ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                                {{ $integration->is_enabled ? 'Habilitado' : 'Desabilitado' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ workspace_route('tenant.integrations.show', $integration->id) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700/60">Ver</a>
                                                <a href="{{ workspace_route('tenant.integrations.edit', $integration->id) }}" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600">Editar</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
