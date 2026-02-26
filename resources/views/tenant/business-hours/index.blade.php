@extends('layouts.tailadmin.app')

@section('title', 'Horários Comerciais')

@section('page', 'business-hours')
@section('content')

    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Horários Comerciais</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 inline-flex items-center">
                                <x-icon name="home-outline" class="w-4 h-4 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Horários Comerciais</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ workspace_route('tenant.business-hours.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="w-4 h-4 mr-2" />
                    Novo Horário
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lista de Horários Comerciais</h2>
        </div>

        <div class="p-6">
            <div
                id="business-hours-grid-wrapper"
                data-row-click-link-selector='a[title="Ver"]'
            >
            <x-tenant.grid
                id="business-hours-grid"
                :columns="[
                    ['name' => 'doctor', 'label' => 'Médico'],
                    ['name' => 'weekday', 'label' => 'Dia'],
                    ['name' => 'start_time', 'label' => 'Início'],
                    ['name' => 'end_time', 'label' => 'Fim'],
                    ['name' => 'actions', 'label' => 'Ações'],
                ]"
                ajaxUrl="{{ workspace_route('tenant.business-hours.grid-data') }}"
                :pagination="true"
                :search="true"
                :sort="true"
            />
            </div>

        </div>
    </div>

@endsection
