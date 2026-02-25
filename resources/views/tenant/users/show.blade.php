@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Usuário')
@section('page', 'users')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-icon name="account-outline" size="text-xl" class="text-blue-600 dark:text-blue-400" />
                        Detalhes do Usuário
                    </h1>
                    <nav class="flex mt-2" aria-label="Breadcrumb">
                        <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <li>
                                <a href="{{ workspace_route('tenant.dashboard') }}"
                                    class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                    <x-icon name="home-outline" size="text-base" />
                                    Dashboard
                                </a>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.users.index') }}"
                                    class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Usuários</a>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="text-gray-900 dark:text-white font-semibold">Detalhes</span>
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="flex-shrink-0">
                    <x-help-button module="users" />
                </div>
            </div>
        </div>

        <!-- Alertas -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
                <div class="flex">
                    <x-icon name="check-circle-outline" size="text-lg" class="text-green-600 dark:text-green-400" />
                    <div class="ml-3">
                        <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
                <div class="flex">
                    <x-icon name="alert-circle-outline" size="text-lg" class="text-red-600 dark:text-red-400" />
                    <div class="ml-3">
                        <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Card Principal -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-icon name="account-details-outline" size="text-lg" class="text-blue-600 dark:text-blue-400" />
                    Informações do Usuário
                </h2>
            </div>

            <div class="p-6">
                <!-- Status Badges -->
                <div class="flex flex-wrap gap-2 mb-6">
                    @if ($user->status === 'active')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">
                            <x-icon name="check-circle-outline" size="text-sm" />
                            Ativo
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">
                            <x-icon name="close-circle-outline" size="text-sm" />
                            Bloqueado
                        </span>
                    @endif

                    @if ($user->is_doctor)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                            <x-icon name="stethoscope" size="text-sm" />
                            Médico
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700/40 dark:text-gray-200">
                            <x-icon name="account-outline" size="text-sm" />
                            Não Médico
                        </span>
                    @endif
                </div>

                <!-- Informações Pessoais -->
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-icon name="information-outline" size="text-lg" class="text-blue-600 dark:text-blue-400" />
                    Informações Pessoais
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">ID</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $user->id }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Nome de Exibição</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $user->name }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">E-mail</label>
                        <p class="text-gray-900 dark:text-white font-medium">
                            <a href="mailto:{{ $user->email }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                {{ $user->email }}
                            </a>
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Telefone</label>
                        <p class="text-gray-900 dark:text-white font-medium">
                            @if ($user->telefone)
                                <a href="tel:{{ $user->telefone }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $user->telefone }}
                                </a>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">N/A</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Barra de Ações -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ workspace_route('tenant.users.index') }}" class="btn btn-outline">
                            <x-icon name="arrow-left" size="text-sm" />
                            Voltar
                        </a>

                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <a href="{{ workspace_route('tenant.users.edit', $user->id) }}" class="btn btn-outline tenant-action-edit">
                                <x-icon name="pencil-outline" size="text-sm" />
                                Editar
                            </a>

                            <a href="{{ workspace_route('tenant.users.change-password', $user->id) }}" class="btn btn-outline">
                                <x-icon name="key-outline" size="text-sm" />
                                Trocar Senha
                            </a>

                            @if (!$user->is_doctor)
                                <a href="{{ workspace_route('tenant.users.doctor-permissions', $user->id) }}" class="btn btn-outline">
                                    <x-icon name="shield-account-outline" size="text-sm" />
                                    Permissões
                                </a>
                            @endif

                            <form id="user-delete-form-{{ $user->id }}" action="{{ workspace_route('tenant.users.destroy', $user->id) }}" method="POST" class="inline"
                                data-confirm-user-delete="true" data-user-name="{{ 'o usuário ' . $user->name }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline tenant-action-delete"
                                    data-delete-trigger="1"
                                    data-delete-form="#user-delete-form-{{ $user->id }}"
                                    data-delete-title="Excluir usuário"
                                    data-delete-message="Tem certeza que deseja excluir o usuário {{ $user->name }}?">
                                    <x-icon name="trash-can-outline" size="text-sm" />
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
