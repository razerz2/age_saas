@extends('layouts.tailadmin.app')

@section('title', 'Criar Integracao')
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
                        <li class="font-medium text-gray-900 dark:text-white">Criar</li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Nova Integracao Generica</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Use este cadastro para feature flags/configuracoes complementares por tenant.</p>
            </div>
            <x-help-button module="integrations" />
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Dados da Integracao</h2>
            </div>

            <form action="{{ workspace_route('tenant.integrations.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="key" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Chave <span class="text-red-500">*</span></label>
                        <input id="key" name="key" type="text" value="{{ old('key') }}" required
                               placeholder="Ex: calendar_provider_x"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('key')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="is_enabled" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select id="is_enabled" name="is_enabled"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="1" {{ old('is_enabled', '1') === '1' ? 'selected' : '' }}>Habilitado</option>
                            <option value="0" {{ old('is_enabled') === '0' ? 'selected' : '' }}>Desabilitado</option>
                        </select>
                        @error('is_enabled')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="config" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Configuracao (JSON)</label>
                    <textarea id="config" name="config" rows="10" placeholder='{"key":"value"}'
                              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ old('config') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Informe JSON valido (objeto ou lista). Deixe vazio para sem configuracao.</p>
                    @error('config')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                    Google Calendar e Apple Calendar oficiais sao gerenciados pelas telas dedicadas por medico; este formulario nao substitui o fluxo de conexao.
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <a href="{{ workspace_route('tenant.integrations.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700/60">Cancelar</a>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">Salvar integracao</button>
                </div>
            </form>
        </div>
    </div>
@endsection
