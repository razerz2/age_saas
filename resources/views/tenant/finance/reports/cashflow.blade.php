@extends('layouts.tailadmin.app')

@section('title', 'Fluxo de Caixa')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-cash-multiple text-primary me-2"></i>
            Relatório de Fluxo de Caixa
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
                <li class="breadcrumb-item active" aria-current="page">Fluxo de Caixa</li>
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
                            <label for="account_id" class="form-label">Conta</label>
                            <select class="form-select" id="account_id" name="account_id">
                                <option value="">Todas</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="doctor_id" class="form-label">Médico</label>
                            <select class="form-select" id="doctor_id" name="doctor_id">
                                <option value="">Todos</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->user->name ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 flex flex-wrap items-center gap-3">
                            <x-tailadmin-button type="submit" variant="primary" size="sm" class="justify-center">
                                <i class="mdi mdi-filter"></i> Filtrar
                            </x-tailadmin-button>
                            <x-tailadmin-button type="button" variant="secondary" size="sm" class="justify-center" onclick="exportReport('csv')">
                                <i class="mdi mdi-file-export"></i> Exportar CSV
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
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Categoria</th>
                                    <th>Conta</th>
                                    <th>Valor</th>
                                    <th>Saldo Acumulado</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="resultsBody">
                                <tr>
                                    <td colspan="7" class="text-center">Use os filtros para gerar o relatório</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadData();
    });

    function loadData() {
        const formData = new FormData(document.getElementById('filterForm'));
        
        fetch('{{ workspace_route("tenant.finance.reports.cashflow.data") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('resultsBody');
            tbody.innerHTML = '';
            
            if (data.data && data.data.length > 0) {
                data.data.forEach(item => {
                    const row = tbody.insertRow();
                    row.innerHTML = `
                        <td>${item.date}</td>
                        <td><span class="badge ${item.type === 'Receita' ? 'bg-success' : 'bg-danger'}">${item.type}</span></td>
                        <td>${item.category}</td>
                        <td>${item.account}</td>
                        <td>R$ ${item.amount}</td>
                        <td>R$ ${item.balance}</td>
                        <td><span class="badge ${item.status === 'paid' ? 'bg-success' : 'bg-warning'}">${item.status}</span></td>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Nenhum resultado encontrado</td></tr>';
            }
            
            // Exibir resumo
            if (data.summary) {
                document.getElementById('summary').innerHTML = `
                    <div class="alert alert-info">
                        <strong>Resumo:</strong>
                        Receitas: R$ ${parseFloat(data.summary.total_income).toFixed(2).replace('.', ',')} | 
                        Despesas: R$ ${parseFloat(data.summary.total_expense).toFixed(2).replace('.', ',')} | 
                        Saldo Final: R$ ${parseFloat(data.summary.final_balance).toFixed(2).replace('.', ',')}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert({ type: 'error', title: 'Erro', message: 'Erro ao carregar dados' });
        });
    }

    function exportReport(format) {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        window.location.href = `{{ workspace_route("tenant.finance.reports.cashflow.export", ["format" => "FORMAT"]) }}`.replace('FORMAT', format) + '?' + params.toString();
    }
</script>
@endpush

