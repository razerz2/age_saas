@extends('layouts.tailadmin.app')

@section('title', 'Relatorio do Portal do Paciente')
@section('page', 'reports')

@section('content')
<div
    id="reports-portal-config"
    data-report-type="portal"
    data-grid-url="{{ workspace_route('tenant.reports.portal.grid-data') }}"
    data-export-excel-url="{{ workspace_route('tenant.reports.portal.export.xlsx') }}"
    data-export-pdf-url="{{ workspace_route('tenant.reports.portal.export.pdf') }}"
></div>

<div class="page-header mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Relatorio do Portal do Paciente</h1>
</div>

@if(empty($loginTableExists))
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-900/20 dark:text-amber-200">
        A tabela <code>patient_logins</code> nao existe neste tenant. O relatorio foi carregado sem dados.
    </div>
@endif

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
@endsection
