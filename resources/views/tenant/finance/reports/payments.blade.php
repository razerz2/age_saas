@extends('layouts.connect_plus.app')

@section('title', 'Pagamentos Recebidos')

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
                    
                    <form id="filterForm" class="row g-3">
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
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="mdi mdi-filter"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="exportReport('csv')">
                                <i class="mdi mdi-file-export"></i> CSV
                            </button>
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

@push('scripts')
<script>
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadData();
    });

    function loadData() {
        const formData = new FormData(document.getElementById('filterForm'));
        
        fetch('{{ workspace_route("tenant.finance.reports.payments.data") }}', {
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
                        <td>${item.patient}</td>
                        <td>R$ ${item.amount}</td>
                        <td>${item.payment_method}</td>
                        <td>${item.payment_date}</td>
                        <td>${item.appointment}</td>
                        <td>${item.doctor}</td>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum resultado encontrado</td></tr>';
            }
            
            if (data.summary) {
                document.getElementById('summary').innerHTML = `
                    <div class="alert alert-success">
                        <strong>Resumo:</strong>
                        Total Recebido: R$ ${parseFloat(data.summary.total).toFixed(2).replace('.', ',')} | 
                        Quantidade: ${data.summary.count}
                    </div>
                `;
            }
        });
    }

    function exportReport(format) {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        window.location.href = `{{ workspace_route("tenant.finance.reports.payments.export", ["format" => "FORMAT"]) }}`.replace('FORMAT', format) + '?' + params.toString();
    }
</script>
@endpush

