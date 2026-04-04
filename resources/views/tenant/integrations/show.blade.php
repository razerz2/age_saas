@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Integracao')
@section('page', 'integrations')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <li><a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-gray-800 dark:hover:text-white">Dashboard</a></li>
                        <li>/</li>
                        <li><a href="{{ workspace_route('tenant.integrations.index') }}" class="hover:text-gray-800 dark:hover:text-white">Integracoes</a></li>
                        <li>/</li>
                        <li class="font-medium text-gray-900 dark:text-white">Detalhes</li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Detalhes da Integracao</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ workspace_route('tenant.integrations.edit', $integration->id) }}" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600">Editar</a>
                <a href="{{ workspace_route('tenant.integrations.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700/60">Voltar</a>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Resumo</h2>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $integration->is_enabled ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $integration->is_enabled ? 'Habilitado' : 'Desabilitado' }}
                    </span>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">ID</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white break-all">{{ $integration->id }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Chave</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $integration->key }}</p>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Configuracao</p>
                    <pre class="max-h-96 overflow-auto rounded-lg border border-gray-200 bg-gray-50 p-4 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-200">{{ is_array($integration->config) ? json_encode($integration->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($integration->config ?: 'Sem configuracao') }}</pre>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Criado em</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ optional($integration->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Atualizado em</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ optional($integration->updated_at)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
