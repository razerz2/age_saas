@extends('layouts.tailadmin.app')

@section('title', 'Agendamentos Recorrentes')
@section('page', 'recurring-appointments')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Agendamentos Recorrentes</h1>
            <nav aria-label="Breadcrumb" class="mt-1">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white inline-flex items-center">
                            <x-icon name="home-outline" class="w-5 h-5 mr-2" />
                            Dashboard
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                            <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Agendamentos Recorrentes</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <a href="{{ workspace_route('tenant.recurring-appointments.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            Novo Agendamento Recorrente
        </a>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lista de Agendamentos Recorrentes</h2>
        </div>

        <div class="p-6">
            <div
                id="recurring-appointments-grid-wrapper"
                data-show-url-template="{{ workspace_route('tenant.recurring-appointments.show', '__RECURRING_ID__') }}"
                data-row-click-link-selector='a[title="Ver"]'
            >
            <x-tenant.grid
                id="recurring-appointments-grid"
                :columns="[
                    ['name' => 'patient', 'label' => 'Paciente'],
                    ['name' => 'doctor', 'label' => 'Médico'],
                    ['name' => 'start_date', 'label' => 'Data Inicial'],
                    ['name' => 'end_display', 'label' => 'Término'],
                    ['name' => 'rules_display', 'label' => 'Regras'],
                    ['name' => 'generated_sessions', 'label' => 'Sessões Geradas'],
                    ['name' => 'status_badge', 'label' => 'Status'],
                    ['name' => 'actions', 'label' => 'Ações'],
                ]"
                ajaxUrl="{{ workspace_route('tenant.recurring-appointments.grid-data') }}"
                :pagination="true"
                :search="true"
                :sort="true"
            />
            </div>
        </div>
    </div>

@endsection
