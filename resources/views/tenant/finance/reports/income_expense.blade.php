@extends('layouts.tailadmin.app')

@section('title', 'Receitas x Despesas')
@section('page', 'finance')

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
                    
                    <form id="filterForm" class="row g-3" data-report="income-expense" data-fetch-url="{{ workspace_route('tenant.finance.reports.incomeExpense.data') }}" data-export-url-template="{{ workspace_route('tenant.finance.reports.incomeExpense.export', ['format' => 'FORMAT']) }}">
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
                            <x-tailadmin-button type="button" variant="secondary" size="sm" class="flex-1 max-w-[140px] justify-center" data-export-format="csv">
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
