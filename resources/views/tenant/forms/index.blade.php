@extends('layouts.tailadmin.app')

@section('title', 'Formulários')
@section('page', 'forms')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Formulários</h1>
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
                                    <span class="ml-1 text-gray-500 dark:text-gray-400">Formulários</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ workspace_route('tenant.forms.create') }}" class="btn btn-primary">
                    <x-icon name="plus" size="text-sm" />
                    Novo Formulário
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lista de Formulários</h2>
            </div>

            <div class="p-6">
                <div
                    id="forms-grid-wrapper"
                    data-row-click-link-selector='a[title="Ver"]'
                >
                <x-tenant.grid
                    id="forms-grid"
                    :columns="[
                        ['name' => 'name', 'label' => 'Nome'],
                        ['name' => 'doctor', 'label' => 'Médico'],
                        ['name' => 'specialty', 'label' => 'Especialidade'],
                        ['name' => 'status_badge', 'label' => 'Status'],
                        ['name' => 'created_at', 'label' => 'Criado em'],
                        ['name' => 'actions', 'label' => 'Ações'],
                    ]"
                    ajaxUrl="{{ workspace_route('tenant.forms.grid-data') }}"
                    :pagination="true"
                    :search="true"
                    :sort="true"
                />
                </div>
            </div>
        </div>
    </div>
@endsection
