@extends('layouts.tailadmin.app')

@section('title', 'Criar Conta OAuth')
@section('page', 'oauth-accounts')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400" aria-label="breadcrumb">
            <ol class="flex flex-wrap items-center gap-2">
                <li>
                    <a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-blue-600 dark:hover:text-white">Dashboard</a>
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    <a href="{{ workspace_route('tenant.oauth-accounts.index') }}" class="hover:text-blue-600 dark:hover:text-white">Contas OAuth</a>
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                </li>
            </ol>
        </nav>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-4xl">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Nova Conta OAuth</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Preencha os dados abaixo para criar uma nova conta OAuth</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-md p-4 mb-6">
                    <strong>Ops!</strong> Verifique os erros abaixo:
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-8" action="{{ workspace_route('tenant.oauth-accounts.store') }}" method="POST">
                @csrf

                <!-- Seção: Informações Básicas -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informações Básicas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Integração <span class="text-red-500">*</span>
                            </label>
                            <select name="integration_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('integration_id') border-red-500 @enderror" required>
                                <option value="">Selecione uma integração</option>
                                @foreach ($integrations as $integration)
                                    <option value="{{ $integration->id }}" {{ old('integration_id') == $integration->id ? 'selected' : '' }}>
                                        {{ $integration->key }}
                                    </option>
                                @endforeach
                            </select>
                            @error('integration_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Usuário <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('user_id') border-red-500 @enderror" required>
                                <option value="">Selecione um usuário</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name_full ?? $user->name }} ({{ $user->email ?? 'Sem email' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seção: Tokens OAuth -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Tokens OAuth</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Access Token <span class="text-red-500">*</span>
                            </label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('access_token') border-red-500 @enderror"
                                      name="access_token"
                                      rows="3"
                                      required
                                      placeholder="Token de acesso OAuth">{{ old('access_token') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Token de acesso fornecido pelo provedor OAuth</p>
                            @error('access_token')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Refresh Token
                            </label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('refresh_token') border-red-500 @enderror"
                                      name="refresh_token"
                                      rows="3"
                                      placeholder="Token de atualização OAuth">{{ old('refresh_token') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Token usado para renovar o access token quando expirar</p>
                            @error('refresh_token')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="max-w-sm">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Expira em
                            </label>
                            <input type="datetime-local"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('expires_at') border-red-500 @enderror"
                                   name="expires_at"
                                   value="{{ old('expires_at') }}">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Data e hora de expiração do token</p>
                            @error('expires_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ workspace_route('tenant.oauth-accounts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-md font-medium transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-primary text-white hover:bg-primary/90 font-medium rounded-md transition-colors">
                        Salvar Conta OAuth
                    </button>
                </div>
            </form>
        </div>
    </div>


@endsection

