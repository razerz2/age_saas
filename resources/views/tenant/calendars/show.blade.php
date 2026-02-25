@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Calendário')
@section('page', 'calendars')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                    <x-icon name="calendar-outline" class="w-6 h-6 mr-2 text-blue-600" />
                    Detalhes do Calendário
                </h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <x-icon name="home-outline" class="w-5 h-5 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <a href="{{ workspace_route('tenant.calendars.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white md:ml-2">Calendários</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Detalhes</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <!-- Header do Card -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-icon name="information-outline" class="w-5 h-5 mr-2 text-blue-600" />
                    Informações do Calendário
                </h2>
            </div>

            <!-- Informações Principais -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 flex items-center">
                        <x-icon name="identifier" class="w-4 h-4 mr-1" />
                        ID
                    </label>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $calendar->id }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 flex items-center">
                        <x-icon name="form-textbox" class="w-4 h-4 mr-1" />
                        Nome
                    </label>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $calendar->name }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 flex items-center">
                        <x-icon name="account-outline" class="w-4 h-4 mr-1" />
                        Médico
                    </label>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $calendar->doctor->user->name ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 flex items-center">
                        <x-icon name="identifier" class="w-4 h-4 mr-1" />
                        ID Externo
                    </label>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $calendar->external_id ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Informações de Sistema</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 flex items-center">
                            <x-icon name="calendar-plus-outline" class="w-4 h-4 mr-1" />
                            Criado em
                        </label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $calendar->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 flex items-center">
                            <x-icon name="calendar-edit-outline" class="w-4 h-4 mr-1" />
                            Atualizado em
                        </label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $calendar->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ workspace_route('tenant.calendars.index') }}" class="btn btn-outline inline-flex items-center">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Voltar
                    </a>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <a href="{{ workspace_route('tenant.calendars.edit', $calendar->id) }}" class="btn btn-outline tenant-action-edit inline-flex items-center">
                            <x-icon name="pencil-outline" class="w-4 h-4 mr-2" />
                            Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
