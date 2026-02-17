@extends('layouts.tailadmin.app')

@section('title', 'Relatório de Pacientes')
@section('page', 'reports')

@section('content')


<div id="reports-patients-config" data-report-type="patients" data-data-url="{{ workspace_route('tenant.reports.patients.data') }}"></div>

<div class="page-header mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Relatório de Pacientes</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Relatórios de pacientes</p>
    </div>
    <nav class="mt-4" aria-label="breadcrumb">
        <ol class="flex items-center flex-wrap gap-2 text-sm text-gray-500 dark:text-gray-400">
            <li>
                <a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-blue-600 dark:hover:text-white">Dashboard</a>
            </li>
            <li class="flex items-center gap-2">
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
                <a href="{{ workspace_route('tenant.reports.index') }}" class="hover:text-blue-600 dark:hover:text-white">Relatórios</a>
            </li>
            <li class="flex items-center gap-2">
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
                <span class="text-gray-700 dark:text-gray-200">Pacientes</span>
            </li>
        </ol>
    </nav>
</div>

<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Filtros</h4>
        </div>
        <div class="p-6">
            <form id="filter-form" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Data Inicial</label>
                    <input type="date" name="date_from" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Data Final</label>
                    <input type="date" name="date_to" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="flex items-end">
                    <button type="button" class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90 transition-colors" data-reports-action="apply-filters">
                        Aplicar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="summary-cards" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total de Pacientes</p>
            <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white" id="summary-total">0</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Com Agendamentos</p>
            <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white" id="summary-with-appointments">0</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Novos Este Mês</p>
            <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white" id="summary-new-this-month">0</h3>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Dados Detalhados</h4>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm" id="reports-table">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Telefone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Agendamentos</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cadastrado em</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
            </table>
        </div>
    </div>
</div>

@endsection


