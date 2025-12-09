@extends('layouts.connect_plus.app')

@section('title', 'Relatório de Recorrências')

@section('content')

<div class="page-header">
    <h3 class="page-title">Relatório de Recorrências</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tenant.reports.index') }}">Relatórios</a></li>
            <li class="breadcrumb-item active" aria-current="page">Recorrências</li>
        </ol>
    </nav>
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
                                <th>Médico</th>
                                <th>Data de Criação</th>
                                <th>Status</th>
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
$(document).ready(function() {
    $('#reports-table').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json" },
        ajax: {
            url: '{{ route("tenant.reports.recurring.data") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataSrc: 'table'
        },
        columns: [
            { data: 'doctor' },
            { data: 'created_at' },
            { data: 'is_active', render: function(data) {
                return data ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>';
            }}
        ]
    });
});
</script>
@endpush

