@extends('layouts.tailadmin.app')

@section('title', 'Calendários')
@section('page', 'calendars')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Calendários</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
                                <x-icon name="home-outline" class="w-4 h-4 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Calendários</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <a href="{{ workspace_route('tenant.calendars.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="w-4 h-4 mr-2" />
                Novo Calendário
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lista de Calendários</h2>

            <div
                id="calendars-grid-wrapper"
                data-row-click-link-selector='a[title="Ver"]'
            >
            <x-tenant.grid
                id="calendars-grid"
                :columns="[
                    ['name' => 'name', 'label' => 'Nome'],
                    ['name' => 'doctor', 'label' => 'Médico'],
                    ['name' => 'external_id', 'label' => 'ID Externo'],
                    ['name' => 'actions', 'label' => 'Ações'],
                ]"
                ajaxUrl="{{ workspace_route('tenant.calendars.grid-data') }}"
                :pagination="true"
                :search="true"
                :sort="true"
            />
            </div>

        </div>
    </div>
</div>

@endsection
