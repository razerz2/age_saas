@extends('layouts.tailadmin.app')

@section('title', 'Tipos de Consulta')

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
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Tipos de Consulta</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <a href="{{ workspace_route('tenant.appointment-types.create') }}" class="btn-patient-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Novo
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lista de Tipos de Consulta</h2>

                <div class="flex items-center space-x-4">
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
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Limpar
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="p-6">
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

@endsection
