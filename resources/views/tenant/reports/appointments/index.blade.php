@extends('layouts.tailadmin.app')

@section('title', 'Relatório de Agendamentos')
@section('page', 'reports')

@section('content')


<div id="reports-appointments-config"
     data-data-url="{{ workspace_route('tenant.reports.appointments.data') }}"
     data-export-excel-url="{{ workspace_route('tenant.reports.appointments.export.excel') }}"
     data-export-pdf-url="{{ workspace_route('tenant.reports.appointments.export.pdf') }}"
     data-export-csv-url="{{ workspace_route('tenant.reports.appointments.export.csv') }}"></div>

<div class="page-header mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Relatório de Agendamentos</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Relatórios de agendamentos</p>
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
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white" id="summary-total">0</h3>
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-green-600">
            <p class="text-sm text-gray-500 dark:text-gray-400">Agendados</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white" id="summary-scheduled">0</h3>
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-cyan-600">
            <p class="text-sm text-gray-500 dark:text-gray-400">Atendidos</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white" id="summary-attended">0</h3>
                <svg class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-red-600">
            <p class="text-sm text-gray-500 dark:text-gray-400">Cancelados</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white" id="summary-canceled">0</h3>
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-amber-500">
            <p class="text-sm text-gray-500 dark:text-gray-400">Online</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white" id="summary-online">0</h3>
                <svg class="h-6 w-6 text-amber-500 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm border-l-4 border-gray-500">
            <p class="text-sm text-gray-500 dark:text-gray-400">Presencial</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white" id="summary-presencial">0</h3>
                <svg class="h-6 w-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Evolução de Agendamentos</h4>
            </div>
            <div class="p-6">
                <div class="h-72">
                    <canvas id="evolutionChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Online x Presencial</h4>
            </div>
            <div class="p-6">
                <div class="h-72">
                    <canvas id="modeChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Agendamentos por Médico</h4>
            </div>
            <div class="p-6">
                <div class="h-72">
                    <canvas id="byDoctorChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Heatmap - Horários por Dia da Semana</h4>
            </div>
            <div class="p-6">
                <div id="heatmap-container" class="mt-3 overflow-x-auto"></div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Dados Detalhados</h4>
            <div class="flex flex-wrap gap-2">
                <button class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors" data-export-format="excel">
                    Excel
                </button>
                <button class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors" data-export-format="pdf">
                    PDF
                </button>
                <button class="inline-flex items-center justify-center rounded-lg bg-slate-600 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700 transition-colors" data-export-format="csv">
                    CSV
                </button>
            </div>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm" id="reports-table">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Paciente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Médico</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Especialidade</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hora</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Modo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <!-- Dados serão carregados via Ajax -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection


