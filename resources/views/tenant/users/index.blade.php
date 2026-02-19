@extends('layouts.tailadmin.app')

@section('title', 'Usuários')
@section('page', 'users')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Usuários</h1>
                    <nav class="flex mt-2" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ workspace_route('tenant.dashboard') }}"
                                    class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
                                    <x-icon name="home-outline" size="text-base" class="mr-2" />
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                    <span class="ml-1 text-gray-500 dark:text-gray-400">Usuários</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ workspace_route('tenant.users.create') }}" class="btn btn-primary">
                    <x-icon name="plus" size="text-sm" />
                    Novo Usuário
                </a>
            </div>
        </div>

        <!-- Alerts -->
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

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
                <div class="flex">
                    <x-icon name="alert-circle-outline" size="text-lg" class="text-red-600 dark:text-red-400" />
                    <div class="ml-3">
                        <p class="text-sm text-red-800 dark:text-red-200">
                            @foreach ($errors->all() as $error)
                                {{ $error }}{{ !$loop->last ? '<br>' : '' }}
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Card Principal -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lista de Usuários</h2>
            </div>

            <div class="p-6">
                {{-- Tabela antiga com DataTables (mantida comentada como referência durante migração para Grid.js) --}}
                {{----}}
                {{--
                <div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="datatable-list">
                        ...
                    </table>
                </div>
                --}}

                {{-- Nova tabela baseada em Grid.js --}}
                <x-tenant.grid
                    id="users-grid"
                    :columns="[
                        ['name' => 'name_full', 'label' => 'Nome'],
                        ['name' => 'email', 'label' => 'E-mail'],
                        ['name' => 'role_label', 'label' => 'Perfil'],
                        ['name' => 'status_badge', 'label' => 'Status'],
                        ['name' => 'actions', 'label' => 'Ações'],
                    ]"
                    ajaxUrl="{{ workspace_route('tenant.users.grid-data') }}"
                    :pagination="true"
                    :search="true"
                    :sort="true"
                />
            </div>
        </div>
    </div>
@endsection
