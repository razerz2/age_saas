@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Conta OAuth')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Detalhes da Conta OAuth</h1>
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
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Detalhes</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-4xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informações da Conta OAuth</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dados principais da conta OAuth</p>
                </div>
                <div class="flex items-center justify-end gap-3 flex-nowrap">
                    <a href="{{ workspace_route('tenant.oauth-accounts.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-md text-sm font-medium transition-colors">
                        Voltar
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">ID</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $oauthAccount->id }}</p>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Integração</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $oauthAccount->integration->key ?? 'N/A' }}</p>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Usuário ID</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $oauthAccount->user_id ?? 'N/A' }}</p>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Expira em</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $oauthAccount->expires_at ? $oauthAccount->expires_at->format('d/m/Y H:i') : 'N/A' }}
                        @if($oauthAccount->expires_at && $oauthAccount->expires_at->isPast())
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">Expirado</span>
                        @elseif($oauthAccount->expires_at && $oauthAccount->expires_at->isFuture())
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">Válido</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Criado em</p>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $oauthAccount->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Atualizado em</p>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $oauthAccount->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

