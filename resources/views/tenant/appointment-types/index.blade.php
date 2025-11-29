@extends('layouts.connect_plus.app')

@section('title', 'Tipos de Consulta')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Tipos de Consulta </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Tipos de Consulta</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Tipos de Consulta</h4>

                    <a href="{{ route('tenant.appointment-types.create') }}" class="btn btn-primary mb-3">
                        + Novo
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Duração (min)</th>
                                    <th>Status</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($appointmentTypes as $appointmentType)
                                    <tr>
                                        <td>{{ $appointmentType->id }}</td>
                                        <td>{{ $appointmentType->name }}</td>
                                        <td>{{ $appointmentType->duration_min ?? 'N/A' }}</td>
                                        <td>
                                            @if ($appointmentType->is_active)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-danger">Inativo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('tenant.appointment-types.show', $appointmentType->id) }}" class="btn btn-info btn-sm">Ver</a>
                                            <a href="{{ route('tenant.appointment-types.edit', $appointmentType->id) }}" class="btn btn-warning btn-sm">Editar</a>
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

