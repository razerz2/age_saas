@extends('layouts.tailadmin.app')

@section('title', 'Pagamentos Recebidos')
@section('page', 'finance')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-cash-check text-primary me-2"></i>
            Relatório de Pagamentos Recebidos
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
                <li class="breadcrumb-item active" aria-current="page">Pagamentos</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Filtros</h4>
                    
                    <form id="filterForm" class="row g-3" data-report="payments" data-fetch-url="{{ workspace_route('tenant.finance.reports.payments.data') }}" data-export-url-template="{{ workspace_route('tenant.finance.reports.payments.export', ['format' => 'FORMAT']) }}">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ date('Y-m-01') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Data Final</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ date('Y-m-t') }}" required>
                        </div>
                        <div class="col-md-4 flex flex-wrap items-end justify-end gap-2">
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
                    <h4 class="card-title">Resultado</h4>
                    <div id="summary" class="mb-3"></div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="resultsTable">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Valor Pago</th>
                                    <th>Método</th>
                                    <th>Data Pagamento</th>
                                    <th>Agendamento</th>
                                    <th>Médico</th>
                                </tr>
                            </thead>
                            <tbody id="resultsBody">
                                <tr>
                                    <td colspan="6" class="text-center">Use os filtros para gerar o relatório</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
