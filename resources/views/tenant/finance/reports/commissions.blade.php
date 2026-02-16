@extends('layouts.tailadmin.app')

@section('title', 'Comissões Médicas')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-cash-usd text-primary me-2"></i>
            Relatório de Comissões Médicas
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
                <li class="breadcrumb-item active" aria-current="page">Comissões</li>
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
                        @if($doctors)
                            <div class="col-md-2">
                                <label for="doctor_id" class="form-label">Médico</label>
                                <select class="form-select" id="doctor_id" name="doctor_id">
                                    <option value="">Todos</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->user->name ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="pending">Pendente</option>
                                <option value="paid">Pago</option>
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
                                    <th>Médico</th>
                                    <th>Agendamento</th>
                                    <th>Valor</th>
                                    <th>Percentual</th>
                                    <th>Status</th>
                                    <th>Data Pagamento</th>
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
        
        fetch('{{ workspace_route("tenant.finance.reports.commissions.data") }}', {
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
                        <td>${item.doctor}</td>
                        <td>${item.appointment}</td>
                        <td>R$ ${item.amount}</td>
                        <td>${item.percentage}</td>
                        <td><span class="badge ${item.status === 'paid' ? 'bg-success' : 'bg-warning'}">${item.status}</span></td>
                        <td>${item.paid_at}</td>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum resultado encontrado</td></tr>';
            }
            
            if (data.summary) {
                document.getElementById('summary').innerHTML = `
                    <div class="alert alert-info">
                        <strong>Resumo:</strong>
                        Total: R$ ${parseFloat(data.summary.total).toFixed(2).replace('.', ',')} | 
                        Pago: R$ ${parseFloat(data.summary.paid).toFixed(2).replace('.', ',')} | 
                        Pendente: R$ ${parseFloat(data.summary.pending).toFixed(2).replace('.', ',')}
                    </div>
                `;
            }
        });
    }

    function exportReport(format) {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        window.location.href = `{{ workspace_route("tenant.finance.reports.commissions.export", ["format" => "FORMAT"]) }}`.replace('FORMAT', format) + '?' + params.toString();
    }
</script>
@endpush

