@extends('layouts.connect_plus.app')

@section('title', 'Integrações')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Integrações </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Integrações</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Integrações</h4>

                    <a href="{{ route('tenant.integrations.create') }}" class="btn btn-primary mb-3">
                        + Novo
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Chave</th>
                                    <th>Status</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($integrations as $integration)
                                    <tr>
                                        <td>{{ $integration->id }}</td>
                                        <td>{{ $integration->key }}</td>
                                        <td>
                                            @if ($integration->is_enabled)
                                                <span class="badge bg-success">Habilitado</span>
                                            @else
                                                <span class="badge bg-danger">Desabilitado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('tenant.integrations.show', $integration->id) }}" class="btn btn-info btn-sm">Ver</a>
                                            <a href="{{ route('tenant.integrations.edit', $integration->id) }}" class="btn btn-warning btn-sm">Editar</a>
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

