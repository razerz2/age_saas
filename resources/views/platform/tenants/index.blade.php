@extends('layouts.freedash.app') @section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Empresas cadastradas</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"> <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Tenants</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end"> <a href="{{ route('Platform.tenants.create') }}"
                        class="btn btn-primary shadow-sm"> <i class="fa fa-plus me-1"></i> Novo Tenant </a> </div>
            </div>
        </div>
    </div>
    <div class="container-fluid"> <!-- Lista de Tenants -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Lista de Tenants</h4>
                        <div class="table-responsive">
                            <table id="tenants_table" class="table table-striped table-bordered text-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nome Fantasia</th>
                                        <th>Razão Social</th>
                                        <th>Subdomínio</th>
                                        <th>Banco</th>
                                        <th>Criado em</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tenants as $tenant)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $tenant->trade_name }}</td>
                                            <td>{{ $tenant->legal_name }}</td>
                                            <td>{{ $tenant->subdomain }}</td>
                                            <td>{{ $tenant->db_name ?? '-' }}</td>
                                            <td>{{ $tenant->created_at->format('d/m/Y') }}</td>
                                            <td class="text-center"> <a
                                                    href="{{ route('Platform.tenants.show', $tenant->id) }}"
                                                    class="btn btn-sm btn-info"> <i class="fas fa-eye"></i> </a> <a
                                                    href="{{ route('Platform.tenants.edit', $tenant->id) }}"
                                                    class="btn btn-sm btn-warning"> <i class="fa fa-edit"></i> </a>
                                                <form action="{{ route('Platform.tenants.destroy', $tenant->id) }}"
                                                    method="POST" class="d-inline"> @csrf @method('DELETE') <button
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Deseja realmente excluir este tenant?')">
                                                        <i class="fa fa-trash"></i> </button> </form>
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
    </div>
@include("layouts.freedash.footer")   
@endsection
@push('scripts')
    <script>
        // Inicializa APENAS a sua tabela 
        $(function() {
            $('#tenants_table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });
        });
    </script>
@endpush
