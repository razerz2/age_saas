@extends('layouts.tailadmin.app')

@section('title', 'Relatório de Agendamentos')
@section('page', 'reports')

@section('content')
<div
    id="reports-appointments-config"
    data-report-type="appointments"
    data-grid-url="{{ workspace_route('tenant.reports.appointments.grid-data') }}"
    data-export-excel-url="{{ workspace_route('tenant.reports.appointments.export.xlsx') }}"
    data-export-pdf-url="{{ workspace_route('tenant.reports.appointments.export.pdf') }}"
></div>

<div class="page-header mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Relatório de Agendamentos</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Visao consolidada de agendamentos</p>
    </div>
    <nav class="mt-4" aria-label="breadcrumb">
        <ol class="flex items-center flex-wrap gap-2 text-sm text-gray-500 dark:text-gray-400">
            <li>
                <a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-blue-600 dark:hover:text-white">Dashboard</a>
            </li>
            <li class="flex items-center gap-2">
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                <a href="{{ workspace_route('tenant.reports.index') }}" class="hover:text-blue-600 dark:hover:text-white">Relatórios</a>
            </li>
            <li class="flex items-center gap-2">
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                <span class="text-gray-700 dark:text-gray-200">Agendamentos</span>
            </li>
        </ol>
    </nav>
</div>

<div class="space-y-6">
    @include('tenant.reports.appointments.partials.filters')

    <div id="summary-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-blue-600">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
            <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white" id="summary-total">0</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-green-600">
            <p class="text-sm text-gray-500 dark:text-gray-400">Agendados</p>
            <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white" id="summary-scheduled">0</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-cyan-600">
            <p class="text-sm text-gray-500 dark:text-gray-400">Atendidos</p>
            <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white" id="summary-attended">0</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-red-600">
            <p class="text-sm text-gray-500 dark:text-gray-400">Cancelados</p>
            <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white" id="summary-canceled">0</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-amber-500">
            <p class="text-sm text-gray-500 dark:text-gray-400">Online</p>
            <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white" id="summary-online">0</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-slate-500">
            <p class="text-sm text-gray-500 dark:text-gray-400">Presencial</p>
            <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white" id="summary-presencial">0</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Evolução de Agendamentos</h4>
            </div>
            <div class="p-6"><div class="h-72"><canvas id="evolutionChart" class="w-full h-full"></canvas></div></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Online x Presencial</h4>
            </div>
            <div class="p-6"><div class="h-72"><canvas id="modeChart" class="w-full h-full"></canvas></div></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Agendamentos por Médico</h4>
            </div>
            <div class="p-6"><div class="h-72"><canvas id="byDoctorChart" class="w-full h-full"></canvas></div></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Heatmap de Horarios</h4>
            </div>
            <div class="p-6"><div id="heatmap-container" class="mt-3 overflow-x-auto"></div></div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Dados Detalhados</h4>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors" data-export-format="excel">
                    Exportar Excel
                </button>
                <button type="button" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors" data-export-format="pdf">
                    Exportar PDF
                </button>
            </div>
        </div>
        <div class="p-6 overflow-x-auto">
            <div id="reports-grid"></div>
        </div>
    </div>
</div>
@endsection
