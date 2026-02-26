@extends('layouts.tailadmin.app')

@section('title', 'Tipos de Consulta')

@section('page', 'appointment-types')
@section('content')

    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tipos de Consulta</h1>
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
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Tipos de Consulta</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <a href="{{ workspace_route('tenant.appointment-types.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="w-4 h-4 mr-2" />
                Novo
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lista de Tipos de Consulta</h2>
        </div>

        <div class="p-6">
            <div class="mb-4 flex items-center justify-end">
                <form method="GET" action="{{ workspace_route('tenant.appointment-types.index') }}" class="flex items-center space-x-3">
                    <select name="doctor_id" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" onchange="this.form.submit()">
                        <option value="">Todos os médicos</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->user->display_name ?? $doctor->user->name }}
                            </option>
                        @endforeach
                    </select>
                    @if(request('doctor_id'))
                        <a href="{{ workspace_route('tenant.appointment-types.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <x-icon name="filter-remove-outline" class="w-4 h-4 mr-2" />
                            Limpar
                        </a>
                    @endif
                </form>
            </div>

            <div
                id="appointment-types-grid-wrapper"
                data-row-click-link-selector='a[title="Ver"]'
            >
            <x-tenant.grid
                id="appointment-types-grid"
                :columns="[
                    ['name' => 'name', 'label' => 'Nome'],
                    ['name' => 'doctor', 'label' => 'Médico'],
                    ['name' => 'duration_min', 'label' => 'Duração'],
                    ['name' => 'price', 'label' => 'Preço'],
                    ['name' => 'color', 'label' => 'Cor'],
                    ['name' => 'actions', 'label' => 'Ações'],
                ]"
                ajaxUrl="{{ workspace_route('tenant.appointment-types.grid-data') }}"
                :pagination="true"
                :search="true"
                :sort="true"
            />
            </div>
        </div>
    </div>

@endsection
