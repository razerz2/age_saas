@extends('layouts.tailadmin.app')

@section('title', 'Relatorio de Pacientes')
@section('page', 'reports')

@section('content')
<div
    id="reports-patients-config"
    data-report-type="patients"
    data-grid-url="{{ workspace_route('tenant.reports.patients.grid-data') }}"
    data-export-excel-url="{{ workspace_route('tenant.reports.patients.export.xlsx') }}"
    data-export-pdf-url="{{ workspace_route('tenant.reports.patients.export.pdf') }}"
></div>

<div class="page-header mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Relatorio de Pacientes</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Visao de base de pacientes</p>
    </div>
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
                <div class="flex items-end gap-2">
                    <button type="button" class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90 transition-colors" data-reports-action="apply-filters">Aplicar</button>
                    <button type="reset" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors" data-reports-action="reset-filters">Limpar</button>
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
            <p class="text-sm text-gray-500 dark:text-gray-400">Novos Este Mes</p>
            <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white" id="summary-new-this-month">0</h3>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Dados Detalhados</h4>
            <div class="flex gap-2">
                <button type="button" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors" data-export-format="excel">Exportar Excel</button>
                <button type="button" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors" data-export-format="pdf">Exportar PDF</button>
            </div>
        </div>
        <div class="p-6 overflow-x-auto">
            <div id="reports-grid"></div>
        </div>
    </div>
</div>
@endsection
