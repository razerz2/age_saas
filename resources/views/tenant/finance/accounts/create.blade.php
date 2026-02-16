@extends('layouts.tailadmin.app')

@section('title', 'Nova Conta Financeira')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-blue-600 dark:hover:text-white">Dashboard</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ workspace_route('tenant.finance.index') }}" class="hover:text-blue-600 dark:hover:text-white">Financeiro</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ workspace_route('tenant.finance.accounts.index') }}" class="hover:text-blue-600 dark:hover:text-white">Contas</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-900 dark:text-white font-semibold">Nova</span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-3xl">
        <div class="p-6">
            <form class="space-y-6" action="{{ workspace_route('tenant.finance.accounts.store', ['slug' => tenant()->subdomain]) }}" method="POST">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome da Conta <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror"
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo <span class="text-red-500">*</span></label>
                    <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('type') border-red-500 @enderror" id="type" name="type" required>
                        <option value="">Selecione...</option>
                        <option value="cash" {{ old('type') === 'cash' ? 'selected' : '' }}>Dinheiro</option>
                        <option value="bank" {{ old('type') === 'bank' ? 'selected' : '' }}>Banco</option>
                        <option value="pix" {{ old('type') === 'pix' ? 'selected' : '' }}>PIX</option>
                        <option value="credit" {{ old('type') === 'credit' ? 'selected' : '' }}>Crédito</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="initial_balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Saldo Inicial <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('initial_balance') border-red-500 @enderror"
                           id="initial_balance" name="initial_balance" value="{{ old('initial_balance', '0.00') }}" required>
                    @error('initial_balance')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="active" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                    <label class="text-sm text-gray-700 dark:text-gray-300" for="active">Conta ativa</label>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary text-white hover:bg-primary/90 font-medium rounded-md transition-colors">
                        Salvar
                    </button>
                    <a href="{{ workspace_route('tenant.finance.accounts.index', ['slug' => tenant()->subdomain]) }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-md font-medium transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection

