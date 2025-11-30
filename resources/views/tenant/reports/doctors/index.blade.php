@extends('layouts.connect_plus.app')

@section('title', 'Relatório de Médicos')

@section('content')

<div class="page-header">
    <h3 class="page-title">Relatório de Médicos</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tenant.reports.index') }}">Relatórios</a></li>
            <li class="breadcrumb-item active" aria-current="page">Médicos</li>
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
                                <th>Nome</th>
                                <th>Especialidades</th>
                                <th>Agendamentos</th>
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
            url: '{{ route("tenant.reports.doctors.data") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataSrc: 'table'
        },
        columns: [
            { data: 'name' },
            { data: 'specialties' },
            { data: 'appointments_count' }
        ]
    });
});
</script>
@endpush

