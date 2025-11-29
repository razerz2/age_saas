@extends('layouts.connect_plus.app')

@section('title', 'Respostas de Formulários')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Respostas de Formulários </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Respostas de Formulários</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Respostas</h4>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Formulário</th>
                                    <th>Paciente</th>
                                    <th>Agendamento</th>
                                    <th>Data de Envio</th>
                                    <th>Status</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($responses as $response)
                                    <tr>
                                        <td>{{ $response->id }}</td>
                                        <td>{{ $response->form->name ?? 'N/A' }}</td>
                                        <td>{{ $response->patient->full_name ?? 'N/A' }}</td>
                                        <td>{{ $response->appointment_id ?? 'N/A' }}</td>
                                        <td>{{ $response->submitted_at ? $response->submitted_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>{{ $response->status ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('tenant.responses.show', $response->id) }}" class="btn btn-info btn-sm">Ver</a>
                                            <a href="{{ route('tenant.responses.edit', $response->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                        </td>
                                    </tr>
                                @endforeach
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
    $(document).ready(function() {
        $('#datatable-list').DataTable();
    });
</script>
@endpush

