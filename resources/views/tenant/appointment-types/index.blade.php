@extends('layouts.connect_plus.app')

@section('title', 'Tipos de Consulta')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Tipos de Consulta </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
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

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex gap-2 align-items-center">
                            <form method="GET" action="{{ route('tenant.appointment-types.index') }}" class="d-flex gap-2">
                                <select name="doctor_id" class="form-select" style="width: 250px;" onchange="this.form.submit()">
                                    <option value="">Todos os médicos</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->user->display_name ?? $doctor->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(request('doctor_id'))
                                    <a href="{{ route('tenant.appointment-types.index') }}" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-close"></i> Limpar
                                    </a>
                                @endif
                            </form>
                        </div>
                        <a href="{{ route('tenant.appointment-types.create') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Novo
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>Médico</th>
                                    <th>Nome</th>
                                    <th>Duração (min)</th>
                                    <th>Status</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($appointmentTypes as $appointmentType)
                                    <tr>
                                        <td>
                                            @if($appointmentType->doctor)
                                                <span class="text-primary">
                                                    <i class="mdi mdi-account-doctor me-1"></i>
                                                    {{ $appointmentType->doctor->user->display_name ?? $appointmentType->doctor->user->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
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
                                            <a href="{{ route('tenant.appointment-types.show', $appointmentType->id) }}" class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ route('tenant.appointment-types.edit', $appointmentType->id) }}" class="btn btn-warning btn-sm">
                                                <i class="mdi mdi-pencil"></i>
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
        $('#datatable-list').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
            }
        });
    });
</script>
@endpush

