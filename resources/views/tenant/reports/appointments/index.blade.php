@extends('layouts.connect_plus.app')

@section('title', 'Relatório de Agendamentos')

@push('styles')
<style>
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .stat-card.primary { border-left-color: #4F8DF9; }
    .stat-card.success { border-left-color: #28a745; }
    .stat-card.danger { border-left-color: #dc3545; }
    .stat-card.info { border-left-color: #17a2b8; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.secondary { border-left-color: #6c757d; }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .heatmap-cell {
        display: inline-block;
        width: 40px;
        height: 40px;
        margin: 2px;
        text-align: center;
        line-height: 40px;
        border-radius: 4px;
        font-size: 12px;
        color: white;
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <h3 class="page-title">Relatório de Agendamentos</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tenant.reports.index') }}">Relatórios</a></li>
            <li class="breadcrumb-item active" aria-current="page">Agendamentos</li>
        </ol>
    </nav>
</div>

{{-- Filtros --}}
@include('tenant.reports.appointments.partials.filters')

{{-- Cards de Resumo --}}
<div class="row mb-4" id="summary-cards">
    <div class="col-md-2 grid-margin stretch-card">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total</h6>
                        <h3 class="mb-0" id="summary-total">0</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="mdi mdi-calendar-multiple text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-2 grid-margin stretch-card">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Agendados</h6>
                        <h3 class="mb-0" id="summary-scheduled">0</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="mdi mdi-calendar-check text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-2 grid-margin stretch-card">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Atendidos</h6>
                        <h3 class="mb-0" id="summary-attended">0</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="mdi mdi-check-circle text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-2 grid-margin stretch-card">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Cancelados</h6>
                        <h3 class="mb-0" id="summary-canceled">0</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="mdi mdi-close-circle text-danger" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-2 grid-margin stretch-card">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Online</h6>
                        <h3 class="mb-0" id="summary-online">0</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="mdi mdi-video text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-2 grid-margin stretch-card">
        <div class="card stat-card secondary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Presencial</h6>
                        <h3 class="mb-0" id="summary-presencial">0</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="mdi mdi-hospital-building text-secondary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Gráficos --}}
<div class="row mb-4">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Evolução de Agendamentos</h4>
                <div class="chart-container">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Online x Presencial</h4>
                <div class="chart-container">
                    <canvas id="modeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Agendamentos por Médico</h4>
                <div class="chart-container">
                    <canvas id="byDoctorChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Heatmap - Horários por Dia da Semana</h4>
                <div id="heatmap-container" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

{{-- Tabela --}}
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Dados Detalhados</h4>
                    <div>
                        <button class="btn btn-success btn-sm" onclick="exportData('excel')">
                            <i class="mdi mdi-file-excel"></i> Excel
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="exportData('pdf')">
                            <i class="mdi mdi-file-pdf"></i> PDF
                        </button>
                        <button class="btn btn-info btn-sm" onclick="exportData('csv')">
                            <i class="mdi mdi-file-delimited"></i> CSV
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="reports-table">
                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Especialidade</th>
                                <th>Tipo</th>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Modo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dados serão carregados via Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
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
        url: '{{ route("tenant.reports.appointments.data") }}',
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
            alert('Erro ao carregar dados do relatório. Verifique o console para mais detalhes.');
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
                backgroundColor: ['#ffc107', '#6c757d']
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
                backgroundColor: '#17a2b8'
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

    let html = '<table class="table table-sm table-bordered">';
    html += '<thead><tr><th>Hora</th>';
    daysOfWeek.forEach(day => html += `<th>${day}</th>`);
    html += '</tr></thead><tbody>';
    
    hours.forEach(hour => {
        html += `<tr><td><strong>${hour}h</strong></td>`;
        daysOfWeek.forEach(day => {
            const value = heatmapData[day] && heatmapData[day][hour] ? heatmapData[day][hour] : 0;
            const intensity = maxValue > 0 ? Math.round((value / maxValue) * 100) : 0;
            const bgColor = `rgba(79, 141, 249, ${0.3 + (intensity / 100) * 0.7})`;
            html += `<td class="text-center" style="background-color: ${bgColor};">${value}</td>`;
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
                ? '<span class="badge bg-warning">Online</span>'
                : '<span class="badge bg-secondary">Presencial</span>';
            
            const statusBadge = `<span class="badge bg-info">${row.status_translated || row.status || 'N/A'}</span>`;
            
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
        'excel': '{{ route("tenant.reports.appointments.export.excel") }}',
        'pdf': '{{ route("tenant.reports.appointments.export.pdf") }}',
        'csv': '{{ route("tenant.reports.appointments.export.csv") }}'
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

