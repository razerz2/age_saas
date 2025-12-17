@extends('layouts.connect_plus.app')

@section('title', 'Dashboard Financeiro')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-chart-line text-primary me-2"></i>
            Dashboard Financeiro
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.index') }}">Financeiro</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Relatórios</li>
            </ol>
        </nav>
    </div>

    <!-- Cards de Resumo -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Receita do Dia</h6>
                    <h3 class="text-success">R$ {{ number_format($dailyIncome, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Receita do Mês</h6>
                    <h3 class="text-success">R$ {{ number_format($monthlyIncome, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Despesas do Mês</h6>
                    <h3 class="text-danger">R$ {{ number_format($monthlyExpense, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Saldo Atual</h6>
                    <h3 class="{{ $currentBalance >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($currentBalance, 2, ',', '.') }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Cobranças Pendentes</h6>
                    <h3 class="text-warning">R$ {{ number_format($pendingCharges, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Comissões Pendentes</h6>
                    <h3 class="text-warning">R$ {{ number_format($pendingCommissions, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Receitas - Últimos 12 Meses</h4>
                    <canvas id="monthlyIncomeChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Receitas por Categoria (Mês Atual)</h4>
                    <canvas id="incomeByCategoryChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Links para Relatórios -->
    <div class="row g-4 mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Relatórios Disponíveis</h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ workspace_route('tenant.finance.reports.cashflow') }}" class="btn btn-outline-primary w-100">
                                <i class="mdi mdi-cash-multiple"></i> Fluxo de Caixa
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ workspace_route('tenant.finance.reports.incomeExpense') }}" class="btn btn-outline-primary w-100">
                                <i class="mdi mdi-chart-bar"></i> Receitas x Despesas
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ workspace_route('tenant.finance.reports.charges') }}" class="btn btn-outline-primary w-100">
                                <i class="mdi mdi-credit-card"></i> Cobranças
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ workspace_route('tenant.finance.reports.payments') }}" class="btn btn-outline-primary w-100">
                                <i class="mdi mdi-cash-check"></i> Pagamentos Recebidos
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ workspace_route('tenant.finance.reports.commissions') }}" class="btn btn-outline-primary w-100">
                                <i class="mdi mdi-cash-usd"></i> Comissões
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Receitas - Últimos 12 Meses
    const monthlyIncomeCtx = document.getElementById('monthlyIncomeChart').getContext('2d');
    new Chart(monthlyIncomeCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($monthlyIncomeData, 'month')) !!},
            datasets: [{
                label: 'Receitas',
                data: {!! json_encode(array_column($monthlyIncomeData, 'value')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
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

    // Gráfico de Receitas por Categoria
    const incomeByCategoryCtx = document.getElementById('incomeByCategoryChart').getContext('2d');
    new Chart(incomeByCategoryCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($incomeByCategory->pluck('name')->toArray()) !!},
            datasets: [{
                data: {!! json_encode($incomeByCategory->pluck('total')->toArray()) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true
        }
    });
</script>
@endpush

