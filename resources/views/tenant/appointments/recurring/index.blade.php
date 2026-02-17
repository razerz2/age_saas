@extends('layouts.tailadmin.app')

@section('title', 'Agendamentos Recorrentes')
@section('page', 'appointments')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Agendamentos Recorrentes</h1>
            <nav aria-label="Breadcrumb" class="mt-1">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1h2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Agendamentos Recorrentes</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <a href="{{ workspace_route('tenant.recurring-appointments.create') }}" class="btn-patient-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Novo Agendamento Recorrente
        </a>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lista de Agendamentos Recorrentes</h2>
        </div>

        <div class="p-6">
            <x-tenant.grid
                id="recurring-appointments-grid"
                :columns="[
                    ['name' => 'patient', 'label' => 'Paciente'],
                    ['name' => 'doctor', 'label' => 'Médico'],
                    ['name' => 'type', 'label' => 'Tipo'],
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

@endsection
