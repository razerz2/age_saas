@extends('layouts.tailadmin.app')

@section('title', 'Editar Conta Financeira')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Editar Conta Financeira</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.finance.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Financeiro</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.finance.accounts.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Contas</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-3xl">
        <div class="p-6">
            <form class="space-y-6" action="{{ workspace_route('tenant.finance.accounts.update', ['slug' => tenant()->subdomain, 'account' => $account->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome da Conta <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror"
                           id="name" name="name" value="{{ old('name', $account->name) }}" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo <span class="text-red-500">*</span></label>
                    <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('type') border-red-500 @enderror" id="type" name="type" required>
                        <option value="">Selecione...</option>
                        <option value="cash" {{ old('type', $account->type) === 'cash' ? 'selected' : '' }}>Dinheiro</option>
                        <option value="bank" {{ old('type', $account->type) === 'bank' ? 'selected' : '' }}>Banco</option>
                        <option value="pix" {{ old('type', $account->type) === 'pix' ? 'selected' : '' }}>PIX</option>
                        <option value="credit" {{ old('type', $account->type) === 'credit' ? 'selected' : '' }}>Crédito</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="initial_balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Saldo Inicial <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('initial_balance') border-red-500 @enderror"
                           id="initial_balance" name="initial_balance"
                           value="{{ old('initial_balance', $account->initial_balance) }}" required>
                    @error('initial_balance')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="active" name="active" value="1" {{ old('active', $account->active) ? 'checked' : '' }}>
                    <label class="text-sm text-gray-700 dark:text-gray-300" for="active">Conta ativa</label>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary text-white hover:bg-primary/90 font-medium rounded-md transition-colors">
                        Atualizar
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

