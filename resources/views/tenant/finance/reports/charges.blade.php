@extends('layouts.tailadmin.app')

@section('title', 'Relatório de Cobranças')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-credit-card text-primary me-2"></i>
            Relatório de Cobranças
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
                <li class="breadcrumb-item active" aria-current="page">Cobranças</li>
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
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="pending">Pendente</option>
                                <option value="paid">Pago</option>
                                <option value="cancelled">Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="origin" class="form-label">Origem</label>
                            <select class="form-select" id="origin" name="origin">
                                <option value="">Todas</option>
                                <option value="public">Público</option>
                                <option value="portal">Portal</option>
                                <option value="internal">Interno</option>
                            </select>
                        </div>
                        <div class="col-md-2 flex flex-wrap items-end justify-end gap-2">
                            <x-tailadmin-button type="submit" variant="primary" size="sm" class="flex-1 max-w-[160px] justify-center">
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
                    <h4 class="card-title">Resultado</h4>
                    <div id="summary" class="mb-3"></div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="resultsTable">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Agendamento</th>
                                    <th>Médico</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Origem</th>
                                    <th>Vencimento</th>
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
        
        fetch('{{ workspace_route("tenant.finance.reports.charges.data") }}', {
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
                        <td>${item.appointment}</td>
                        <td>${item.doctor}</td>
                        <td>R$ ${item.amount}</td>
                        <td><span class="badge ${getStatusBadge(item.status)}">${item.status}</span></td>
                        <td><span class="badge bg-info">${item.origin}</span></td>
                        <td>${item.due_date}</td>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Nenhum resultado encontrado</td></tr>';
            }
            
            if (data.summary) {
                document.getElementById('summary').innerHTML = `
                    <div class="alert alert-info">
                        <strong>Resumo:</strong>
                        Total: R$ ${parseFloat(data.summary.total).toFixed(2).replace('.', ',')} | 
                        Pago: R$ ${parseFloat(data.summary.paid).toFixed(2).replace('.', ',')} | 
                        Pendente: R$ ${parseFloat(data.summary.pending).toFixed(2).replace('.', ',')} | 
                        Cancelado: R$ ${parseFloat(data.summary.cancelled).toFixed(2).replace('.', ',')}
                    </div>
                `;
            }
        });
    }

    function getStatusBadge(status) {
        const badges = {
            'paid': 'bg-success',
            'pending': 'bg-warning',
            'cancelled': 'bg-secondary'
        };
        return badges[status] || 'bg-secondary';
    }

    function exportReport(format) {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        window.location.href = `{{ workspace_route("tenant.finance.reports.charges.export", ["format" => "FORMAT"]) }}`.replace('FORMAT', format) + '?' + params.toString();
    }
</script>
@endpush

