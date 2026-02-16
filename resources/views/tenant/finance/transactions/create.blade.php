@extends('layouts.tailadmin.app')

@section('title', 'Nova Transação')

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
                        <a href="{{ workspace_route('tenant.finance.transactions.index') }}" class="hover:text-blue-600 dark:hover:text-white">Transações</a>
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
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-3xl">
        <div class="p-6">
            <form class="space-y-6" action="{{ workspace_route('tenant.finance.transactions.store', ['slug' => tenant()->subdomain]) }}" method="POST">
                @csrf

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo <span class="text-red-500">*</span></label>
                    <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('type') border-red-500 @enderror" id="type" name="type" required>
                        <option value="">Selecione...</option>
                        <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Receita</option>
                        <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Despesa</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descrição <span class="text-red-500">*</span></label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('description') border-red-500 @enderror"
                              id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0.01"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('amount') border-red-500 @enderror"
                               id="amount" name="amount" value="{{ old('amount') }}" required>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data <span class="text-red-500">*</span></label>
                        <input type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('date') border-red-500 @enderror"
                               id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                        @error('date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="account_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Conta</label>
                        <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('account_id') border-red-500 @enderror" id="account_id" name="account_id">
                            <option value="">Selecione...</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id') === $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoria</label>
                        <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('category_id') border-red-500 @enderror" id="category_id" name="category_id">
                            <option value="">Selecione...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') === $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('status') border-red-500 @enderror" id="status" name="status">
                        <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pendente</option>
                        <option value="paid" {{ old('status') === 'paid' ? 'selected' : '' }}>Pago</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary text-white hover:bg-primary/90 font-medium rounded-md transition-colors">
                        Salvar
                    </button>
                    <a href="{{ workspace_route('tenant.finance.transactions.index', ['slug' => tenant()->subdomain]) }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-md font-medium transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection

