@extends('layouts.tailadmin.app')

@section('title', 'Dashboard Financeiro')
@section('page', 'finance')

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
                    <canvas id="monthlyIncomeChart" height="100" data-series='@json($monthlyIncomeData)'></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Receitas por Categoria (Mês Atual)</h4>
                    <canvas id="incomeByCategoryChart" height="200" data-series='@json($incomeByCategory->map(fn($item) => ["name" => $item->name, "total" => $item->total])->values())'></canvas>
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
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.finance.reports.cashflow') }}"
                                class="w-full justify-center border-primary text-primary bg-transparent hover:bg-primary/10 dark:border-primary/40 dark:text-primary dark:hover:bg-primary/20">
                                <i class="mdi mdi-cash-multiple"></i> Fluxo de Caixa
                            </x-tailadmin-button>
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.finance.reports.incomeExpense') }}"
                                class="w-full justify-center border-primary text-primary bg-transparent hover:bg-primary/10 dark:border-primary/40 dark:text-primary dark:hover:bg-primary/20">
                                <i class="mdi mdi-chart-bar"></i> Receitas x Despesas
                            </x-tailadmin-button>
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.finance.reports.charges') }}"
                                class="w-full justify-center border-primary text-primary bg-transparent hover:bg-primary/10 dark:border-primary/40 dark:text-primary dark:hover:bg-primary/20">
                                <i class="mdi mdi-credit-card"></i> Cobranças
                            </x-tailadmin-button>
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.finance.reports.payments') }}"
                                class="w-full justify-center border-primary text-primary bg-transparent hover:bg-primary/10 dark:border-primary/40 dark:text-primary dark:hover:bg-primary/20">
                                <i class="mdi mdi-cash-check"></i> Pagamentos Recebidos
                            </x-tailadmin-button>
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.finance.reports.commissions') }}"
                                class="w-full justify-center border-primary text-primary bg-transparent hover:bg-primary/10 dark:border-primary/40 dark:text-primary dark:hover:bg-primary/20">
                                <i class="mdi mdi-cash-usd"></i> Comissões
                            </x-tailadmin-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
