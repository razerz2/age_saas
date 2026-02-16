@extends('layouts.tailadmin.app')

@section('title', 'Relatório de Agendamentos')

@section('content')

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
                <button class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors" onclick="exportData('excel')">
                    Excel
                </button>
                <button class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors" onclick="exportData('pdf')">
                    PDF
                </button>
                <button class="inline-flex items-center justify-center rounded-lg bg-slate-600 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700 transition-colors" onclick="exportData('csv')">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let evolutionChart, modeChart, byDoctorChart;
let table;

$(document).ready(function() {
    // Inicializar DataTable sem Ajax (dados serão carregados manualmente)
    table = $('#reports-table').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
        },
        order: [[4, 'desc']],
        pageLength: 25,
        data: [],
        processing: false,
        serverSide: false,
        searching: true,
        ordering: true,
        info: true,
        paging: true,
        autoWidth: false,
        deferRender: true
    });
    
    // Carregar dados após inicializar a tabela
    loadData();
});

function loadData() {
    const formData = $('#filter-form').serialize();
    
    // Mostrar indicador de carregamento
    if (table) {
        table.processing(true);
    }
    
    $.ajax({
        url: '{{ workspace_route("tenant.reports.appointments.data") }}',
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (table) {
                table.processing(false);
            }
            updateSummary(response.summary);
            updateCharts(response.chart);
            updateTable(response.table);
        },
        error: function(xhr, status, error) {
            if (table) {
                table.processing(false);
            }
            console.error('Erro ao carregar dados:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            showAlert({ type: 'error', title: 'Erro', message: 'Erro ao carregar dados do relatório. Verifique o console para mais detalhes.' });
        }
    });
}

function updateSummary(summary) {
    $('#summary-total').text(summary.total || 0);
    $('#summary-scheduled').text(summary.scheduled || 0);
    $('#summary-attended').text(summary.attended || 0);
    $('#summary-canceled').text(summary.canceled || 0);
    $('#summary-online').text(summary.online || 0);
    $('#summary-presencial').text(summary.presencial || 0);
}

function updateCharts(chartData) {
    // Gráfico de Evolução (Linha)
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    const evolutionLabels = Object.keys(chartData.evolution || {}).sort();
    const evolutionValues = evolutionLabels.map(date => chartData.evolution[date] || 0);
    
    if (evolutionChart) evolutionChart.destroy();
    evolutionChart = new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: evolutionLabels,
            datasets: [{
                label: 'Agendamentos',
                data: evolutionValues,
                borderColor: '#4F8DF9',
                backgroundColor: 'rgba(79, 141, 249, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Gráfico de Pizza (Online x Presencial)
    const modeCtx = document.getElementById('modeChart').getContext('2d');
    if (modeChart) modeChart.destroy();
    modeChart = new Chart(modeCtx, {
        type: 'pie',
        data: {
            labels: ['Online', 'Presencial'],
            datasets: [{
                data: [chartData.mode.online || 0, chartData.mode.presencial || 0],
                backgroundColor: ['#f59e0b', '#64748b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico de Barras (Por Médico)
    const byDoctorCtx = document.getElementById('byDoctorChart').getContext('2d');
    const doctorLabels = Object.keys(chartData.byDoctor || {});
    const doctorValues = doctorLabels.map(doctor => chartData.byDoctor[doctor] || 0);
    
    if (byDoctorChart) byDoctorChart.destroy();
    byDoctorChart = new Chart(byDoctorCtx, {
        type: 'bar',
        data: {
            labels: doctorLabels,
            datasets: [{
                label: 'Agendamentos',
                data: doctorValues,
                backgroundColor: '#0891b2'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Heatmap
    updateHeatmap(chartData.heatmap || {});
}

function updateHeatmap(heatmapData) {
    const container = $('#heatmap-container');
    container.empty();
    
    const daysOfWeek = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    const hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];
    
    let maxValue = 0;
    Object.values(heatmapData).forEach(day => {
        Object.values(day).forEach(val => {
            if (val > maxValue) maxValue = val;
        });
    });

    let html = '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">';
    html += '<thead class="bg-gray-50 dark:bg-gray-700/50"><tr>';
    html += '<th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Hora</th>';
    daysOfWeek.forEach(day => html += `<th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">${day}</th>`);
    html += '</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700">';
    
    hours.forEach(hour => {
        html += `<tr><td class="px-3 py-2 font-semibold text-gray-700 dark:text-gray-200">${hour}h</td>`;
        daysOfWeek.forEach(day => {
            const value = heatmapData[day] && heatmapData[day][hour] ? heatmapData[day][hour] : 0;
            const intensity = maxValue > 0 ? Math.round((value / maxValue) * 100) : 0;
            const bgColor = `rgba(79, 141, 249, ${0.3 + (intensity / 100) * 0.7})`;
            html += `<td class="px-3 py-2 text-center text-gray-900 dark:text-gray-100" style="background-color: ${bgColor};">${value}</td>`;
        });
        html += '</tr>';
    });
    html += '</tbody></table>';
    
    container.html(html);
}

function updateTable(tableData) {
    if (!table) {
        console.error('DataTable não foi inicializado');
        return;
    }
    
    table.clear();
    
    if (tableData && tableData.length > 0) {
        tableData.forEach(row => {
            const modeBadge = row.mode === 'online' 
                ? '<span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">Online</span>'
                : '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-900/30 dark:text-slate-300">Presencial</span>';
            
            const statusBadge = `<span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">${row.status_translated || row.status || 'N/A'}</span>`;
            
            table.row.add([
                row.patient || 'N/A',
                row.doctor || 'N/A',
                row.specialty || 'N/A',
                row.type || 'N/A',
                row.date || 'N/A',
                row.time || 'N/A',
                modeBadge,
                statusBadge
            ]);
        });
    }
    
    table.draw();
}

function exportData(format) {
    const formData = $('#filter-form').serialize();
    const routes = {
        'excel': '{{ workspace_route("tenant.reports.appointments.export.excel") }}',
        'pdf': '{{ workspace_route("tenant.reports.appointments.export.pdf") }}',
        'csv': '{{ workspace_route("tenant.reports.appointments.export.csv") }}'
    };
    const url = routes[format] || routes['excel'];
    window.open(`${url}?${formData}`, '_blank');
}

// Atualizar ao mudar filtros
$('#filter-form').on('change', 'select, input', function() {
    loadData();
});
</script>
@endpush

