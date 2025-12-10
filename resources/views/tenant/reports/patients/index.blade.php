@extends('layouts.connect_plus.app')

@section('title', 'Relatório de Pacientes')

@section('content')

<div class="page-header">
    <h3 class="page-title">Relatório de Pacientes</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ workspace_route('tenant.reports.index') }}">Relatórios</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pacientes</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Filtros</h4>
                <form id="filter-form">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Data Inicial</label>
                            <input type="date" name="date_from" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Data Final</label>
                            <input type="date" name="date_to" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-primary" onclick="loadData()">Aplicar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4" id="summary-cards">
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total de Pacientes</h6>
                <h3 class="mb-0" id="summary-total">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Com Agendamentos</h6>
                <h3 class="mb-0" id="summary-with-appointments">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Novos Este Mês</h6>
                <h3 class="mb-0" id="summary-new-this-month">0</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Dados Detalhados</h4>
                <div class="table-responsive">
                    <table class="table table-hover" id="reports-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Agendamentos</th>
                                <th>Cadastrado em</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let table;
$(document).ready(function() {
    table = $('#reports-table').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json" }
    });
    loadData();
});

function loadData() {
    $.ajax({
        url: '{{ workspace_route("tenant.reports.patients.data") }}',
        method: 'POST',
        data: $('#filter-form').serialize(),
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            $('#summary-total').text(response.summary.total || 0);
            $('#summary-with-appointments').text(response.summary.with_appointments || 0);
            $('#summary-new-this-month').text(response.summary.new_this_month || 0);
            
            table.clear();
            response.table.forEach(row => {
                table.row.add([row.name, row.email, row.phone, row.appointments_count, row.created_at]);
            });
            table.draw();
        }
    });
}
</script>
@endpush

