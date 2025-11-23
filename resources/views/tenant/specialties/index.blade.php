@extends('layouts.connect_plus.app')

@section('title', 'Especialidades')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Especialidades </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Especialidades</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Especialidades</h4>

                    <a href="{{ route('tenant.specialties.create') }}" class="btn btn-primary mb-3">
                        + Nova Especialidade
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Código</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($specialties as $specialty)
                                    <tr>
                                        <td>{{ $specialty->id }}</td>
                                        <td>{{ $specialty->name }}</td>
                                        <td>{{ $specialty->code ?? '-' }}</td>
                                        <td>
                                            <!-- Botão de Ver com ícone -->
                                            <a href="{{ route('tenant.specialties.show', $specialty->id) }}"
                                                class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye"></i> Ver
                                            </a>

                                            <!-- Botão de Editar com ícone -->
                                            <a href="{{ route('tenant.specialties.edit', $specialty->id) }}"
                                                class="btn btn-warning btn-sm">
                                                <i class="mdi mdi-pencil"></i> Editar
                                            </a>
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

