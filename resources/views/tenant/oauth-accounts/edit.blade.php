@extends('layouts.tailadmin.app')

@section('title', 'Editar Conta OAuth')
@section('page', 'oauth-accounts')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Editar Conta OAuth</h1>
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
                                <a href="{{ workspace_route('tenant.oauth-accounts.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Contas OAuth</a>
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

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-4xl">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Editar Conta OAuth</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Atualize as informações da conta OAuth abaixo</p>
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

            <form class="space-y-8" action="{{ workspace_route('tenant.oauth-accounts.update', $oauthAccount->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Seção: Informações Básicas -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informações Básicas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Integração
                            </label>
                            <select name="integration_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 dark:text-white" disabled>
                                <option value="{{ $oauthAccount->integration_id }}">
                                    {{ $oauthAccount->integration->key ?? 'N/A' }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">A integração não pode ser alterada após a criação.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Usuário
                            </label>
                            <select name="user_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 dark:text-white" disabled>
                                <option value="{{ $oauthAccount->user_id }}">
                                    @if($oauthAccount->user)
                                        {{ $oauthAccount->user->name_full ?? $oauthAccount->user->name }} ({{ $oauthAccount->user->email ?? 'Sem email' }})
                                    @else
                                        Usuário #{{ $oauthAccount->user_id }}
                                    @endif
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">O usuário não pode ser alterado após a criação.</p>
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
                                      placeholder="Token de acesso OAuth">{{ old('access_token', $oauthAccount->access_token) }}</textarea>
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
                                      placeholder="Token de atualização OAuth">{{ old('refresh_token', $oauthAccount->refresh_token) }}</textarea>
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
                                   value="{{ old('expires_at', $oauthAccount->expires_at ? $oauthAccount->expires_at->format('Y-m-d\TH:i') : '') }}">
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
                        Atualizar Conta OAuth
                    </button>
                </div>
            </form>
        </div>
    </div>


@endsection
