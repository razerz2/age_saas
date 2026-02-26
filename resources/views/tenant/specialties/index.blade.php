@extends('layouts.tailadmin.app')

@section('title', 'Especialidades')
@section('page', 'specialties')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Especialidades</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
                                <x-icon name="home-outline" size="text-base" class="mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Especialidades</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <a href="{{ workspace_route('tenant.specialties.create') }}" class="btn btn-primary inline-flex items-center">
                <x-icon name="plus" size="text-sm" class="mr-2" />
                Nova Especialidade
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lista de Especialidades</h2>
        </div>

        <div class="p-6">
            {{-- Nova tabela baseada em Grid.js --}}
            <div
                id="specialties-grid-wrapper"
                data-show-url-template="{{ workspace_route('tenant.specialties.show', '__SPECIALTY_ID__') }}"
                data-row-click-link-selector='a[title="Ver"]'
            >
                <x-tenant.grid
                    id="specialties-grid"
                    :columns="[
                        ['name' => 'name', 'label' => 'Nome'],
                        ['name' => 'code', 'label' => 'Código'],
                        ['name' => 'actions', 'label' => 'Ações'],
                    ]"
                    ajaxUrl="{{ workspace_route('tenant.specialties.grid-data') }}"
                    :pagination="true"
                    :search="true"
                    :sort="true"
                />
            </div>
        </div>
    </div>
@endsection

