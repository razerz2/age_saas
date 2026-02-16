@extends('layouts.tailadmin.app')

@section('title', 'Receitas x Despesas')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-chart-bar text-primary me-2"></i>
            Relatório de Receitas x Despesas
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.index') }}">Financeiro</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.reports.index') }}">Relatórios</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Receitas x Despesas</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Filtros</h4>
                    
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ date('Y-m-01') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Data Final</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ date('Y-m-t') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="group_by" class="form-label">Agrupar por</label>
                            <select class="form-select" id="group_by" name="group_by">
                                <option value="day">Dia</option>
                                <option value="month">Mês</option>
                            </select>
                        </div>
                        <div class="col-md-3 flex flex-wrap items-end justify-end gap-2">
                            <x-tailadmin-button type="submit" variant="primary" size="sm" class="flex-1 max-w-[180px] justify-center">
                                <i class="mdi mdi-filter"></i> Filtrar
                            </x-tailadmin-button>
                            <x-tailadmin-button type="button" variant="secondary" size="sm" class="flex-1 max-w-[140px] justify-center" onclick="exportReport('csv')">
                                <i class="mdi mdi-file-export"></i> CSV
                            </x-tailadmin-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Gráfico</h4>
                    <canvas id="chart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Resumo</h4>
                    <div id="summary"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chart = null;

    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadData();
    });

    function loadData() {
        const formData = new FormData(document.getElementById('filterForm'));
        
        fetch('{{ workspace_route("tenant.finance.reports.incomeExpense.data") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            const labels = Object.keys(data.income).concat(Object.keys(data.expense));
            const uniqueLabels = [...new Set(labels)].sort();
            
            const incomeData = uniqueLabels.map(label => data.income[label] || 0);
            const expenseData = uniqueLabels.map(label => data.expense[label] || 0);
            
            if (chart) {
                chart.destroy();
            }
            
            const ctx = document.getElementById('chart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: uniqueLabels,
                    datasets: [{
                        label: 'Receitas',
                        data: incomeData,
                        backgroundColor: 'rgba(75, 192, 192, 0.8)'
                    }, {
                        label: 'Despesas',
                        data: expenseData,
                        backgroundColor: 'rgba(255, 99, 132, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            document.getElementById('summary').innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5>Total Receitas</h5>
                                <h3>R$ ${parseFloat(data.summary.total_income).toFixed(2).replace('.', ',')}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5>Total Despesas</h5>
                                <h3>R$ ${parseFloat(data.summary.total_expense).toFixed(2).replace('.', ',')}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card ${data.summary.net_result >= 0 ? 'bg-primary' : 'bg-warning'} text-white">
                            <div class="card-body">
                                <h5>Resultado Líquido</h5>
                                <h3>R$ ${parseFloat(data.summary.net_result).toFixed(2).replace('.', ',')}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    function exportReport(format) {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        window.location.href = `{{ workspace_route("tenant.finance.reports.incomeExpense.export", ["format" => "FORMAT"]) }}`.replace('FORMAT', format) + '?' + params.toString();
    }
</script>
@endpush

