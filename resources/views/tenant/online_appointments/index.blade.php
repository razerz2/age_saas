@extends('layouts.tailadmin.app')

@section('title', 'Consultas Online')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Consultas Online</h1>
            <nav aria-label="Breadcrumb" class="mt-1">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Consultas Online</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lista de Consultas Online</h2>

            <x-tenant.grid
                id="online-appointments-grid"
                :columns="[
                    ['name' => 'patient', 'label' => 'Paciente'],
                    ['name' => 'doctor', 'label' => 'Médico'],
                    ['name' => 'datetime', 'label' => 'Data/Hora'],
                    ['name' => 'status_badge', 'label' => 'Status'],
                    ['name' => 'instructions', 'label' => 'Instruções Enviadas'],
                    ['name' => 'actions', 'label' => 'Ações'],
                ]"
                ajaxUrl="{{ workspace_route('tenant.online-appointments.grid-data') }}"
                :pagination="true"
                :search="true"
                :sort="true"
            />

        </div>
    </div>

@endsection

