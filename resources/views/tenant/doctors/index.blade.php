@extends('layouts.connect_plus.app')

@section('title', 'Médicos')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Médicos </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Médicos</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Médicos</h4>

                    <a href="{{ route('tenant.doctors.create') }}" class="btn btn-primary mb-3">
                        + Novo Médico
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuário</th>
                                    <th>CRM</th>
                                    <th>Estado</th>
                                    <th>Especialidades</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($doctors as $doctor)
                                    <tr>
                                        <td>{{ $doctor->id }}</td>
                                        <td>{{ $doctor->user->name_full ?? '-' }}</td>
                                        <td>{{ $doctor->crm_number ?? '-' }}</td>
                                        <td>{{ $doctor->crm_state ?? '-' }}</td>
                                        <td>
                                            @if ($doctor->specialties->count() > 0)
                                                @foreach ($doctor->specialties as $specialty)
                                                    <span class="badge bg-info">{{ $specialty->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <!-- Botão de Ver com ícone -->
                                            <a href="{{ route('tenant.doctors.show', $doctor->id) }}"
                                                class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye"></i> Ver
                                            </a>

                                            <!-- Botão de Editar com ícone -->
                                            <a href="{{ route('tenant.doctors.edit', $doctor->id) }}"
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

