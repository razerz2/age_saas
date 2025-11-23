@extends('layouts.connect_plus.app')

@section('title', 'Pacientes')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Pacientes </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Pacientes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Pacientes</h4>

                    <a href="{{ route('tenant.patients.create') }}" class="btn btn-primary mb-3">
                        + Novo Paciente
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome Completo</th>
                                    <th>CPF</th>
                                    <th>Data de Nascimento</th>
                                    <th>E-mail</th>
                                    <th>Telefone</th>
                                    <th>Status</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($patients as $patient)
                                    <tr>
                                        <td>{{ $patient->id }}</td>
                                        <td>{{ $patient->full_name }}</td>
                                        <td>{{ $patient->cpf ?? '-' }}</td>
                                        <td>{{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $patient->email ?? '-' }}</td>
                                        <td>{{ $patient->phone ?? '-' }}</td>
                                        <td>
                                            @if ($patient->is_active)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-danger">Inativo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <!-- Botão de Ver com ícone -->
                                            <a href="{{ route('tenant.patients.show', $patient->id) }}"
                                                class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye"></i> Ver
                                            </a>

                                            <!-- Botão de Editar com ícone -->
                                            <a href="{{ route('tenant.patients.edit', $patient->id) }}"
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

