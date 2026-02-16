@extends('layouts.tailadmin.app')

@section('title', 'Relatório de Formulários')

@section('content')

<div class="page-header mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Relatório de Formulários</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Relatórios de formulários</p>
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
                <span class="text-gray-700 dark:text-gray-200">Formulários</span>
            </li>
        </ol>
    </nav>
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Respostas</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Criado em</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#reports-table').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json" },
        ajax: {
            url: '{{ workspace_route("tenant.reports.forms.data") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataSrc: 'table'
        },
        columns: [
            { data: 'name' },
            { data: 'responses_count' },
            { data: 'created_at' }
        ]
    });
});
</script>
@endpush

