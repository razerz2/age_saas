@extends('layouts.tailadmin.app')

@section('title', 'Integrações')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2">
            <nav aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Dashboard</a>
                    </li>
                    <li>/</li>
                    <li class="font-medium text-gray-900 dark:text-white">Integrações</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Integrações</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Gerencie integrações genéricas deste tenant.</p>
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

        <div class="rounded-xl border border-blue-200 bg-blue-50 px-6 py-4 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
            A sincronização de Google Calendar e Apple Calendar agora é feita na Agenda do Profissional.
            <a href="{{ workspace_route('tenant.agenda-settings.index') }}" class="font-medium underline-offset-2 hover:underline">
                Acessar Agenda do Profissional
            </a>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Integrações Genéricas</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cadastros complementares de feature flag/configuração por tenant.</p>
                </div>
                <a href="{{ workspace_route('tenant.integrations.create') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700/60">
                    Nova integração
                </a>
            </div>

            <div class="p-6">
                @if ($integrations->isEmpty())
                    <div class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                        Nenhuma integração genérica cadastrada.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/40">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Chave</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Ações</th>
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
