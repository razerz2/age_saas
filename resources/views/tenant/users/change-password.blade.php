@extends('layouts.tailadmin.app')

@section('title', 'Alterar Senha')
@section('page', 'users')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Alterar Senha</h1>
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
                                <span class="text-gray-900 dark:text-white font-semibold">Alterar Senha</span>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Card Principal -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 max-w-2xl mx-auto">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Alterar Senha</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Digite a senha atual e a nova senha.</p>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ workspace_route('tenant.users.change-password.store', $user->id) }}">
                    @csrf

                    <div class="space-y-6">
                        <!-- Senha Atual -->
                        <div>
                            <label for="current_password"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Senha Atual</label>
                            <div class="relative w-full">
                                <div class="flex w-full">
                                    <input type="password" name="current_password" id="current_password"
                                        class="w-full min-w-0 flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-l-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:text-white"
                                        required>
                                    <button type="button" data-toggle-password-target="current_password"
                                        class="shrink-0 w-11 inline-flex items-center justify-center px-0 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                        title="Mostrar/Ocultar senha">
                                        <x-icon name="eye-outline" id="current_password-eye-icon" size="text-lg" />
                                    </button>
                                    <!-- Placeholder para manter largura igual ao campo com "Gerar" -->
                                    <div class="shrink-0 w-24 inline-flex items-center justify-center px-0 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 rounded-r-md pointer-events-none select-none"
                                        aria-hidden="true">
                                        <x-icon name="check-circle-outline" size="text-lg" class="opacity-0" />
                                    </div>
                                </div>
                            </div>
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nova Senha -->
                        <div>
                            <label for="new_password"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nova Senha</label>
                            <div class="relative w-full">
                                <div class="flex w-full">
                                    <input type="password" name="new_password" id="new_password"
                                        class="w-full min-w-0 flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-l-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:text-white"
                                        required>
                                    <button type="button" data-toggle-password-target="new_password"
                                        class="shrink-0 w-11 inline-flex items-center justify-center px-0 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                        title="Mostrar/Ocultar senha">
                                        <x-icon name="eye-outline" id="new_password-eye-icon" size="text-lg" />
                                    </button>
                                    <button type="button" data-generate-password="new_password"
                                        data-generate-password-confirm="new_password_confirmation"
                                        class="shrink-0 w-24 inline-flex items-center justify-center gap-1.5 px-3 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 rounded-r-md">
                                        <x-icon name="refresh" size="text-lg" />
                                        Gerar
                                    </button>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mínimo 8 caracteres com maiúscula,
                                minúscula, número e caractere especial</p>
                            @error('new_password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirmar Nova Senha -->
                        <div>
                            <label for="new_password_confirmation"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirmar Nova
                                Senha</label>
                            <div class="relative w-full">
                                <div class="flex w-full">
                                    <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                        class="w-full min-w-0 flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-l-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:text-white"
                                        required>
                                    <button type="button" data-toggle-password-target="new_password_confirmation"
                                        class="shrink-0 w-11 inline-flex items-center justify-center px-0 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                        title="Mostrar/Ocultar senha">
                                        <x-icon name="eye-outline" id="new_password_confirmation-eye-icon" size="text-lg" />
                                    </button>
                                    <!-- Placeholder para manter largura igual ao campo com "Gerar" -->
                                    <div class="shrink-0 w-24 inline-flex items-center justify-center px-0 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 rounded-r-md pointer-events-none select-none"
                                        aria-hidden="true">
                                        <x-icon name="check-circle-outline" size="text-lg" class="opacity-0" />
                                    </div>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Digite a senha novamente para
                                confirmar</p>
                            @error('new_password_confirmation')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Barra de Ações -->
                    <div class="flex items-center justify-between gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
                        <a href="{{ workspace_route('tenant.users.index') }}" class="btn btn-outline">
                            <x-icon name="arrow-left" size="text-sm" />
                            Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="lock-reset" size="text-sm" />
                            Alterar Senha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
