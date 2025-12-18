@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Redes de Clínicas</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Redes de Clínicas</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.clinic-networks.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fa fa-plus me-1"></i> Nova Rede
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Lista de Redes</h4>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id="networks_table" class="table table-striped table-bordered text-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Slug</th>
                                        <th>Unidades</th>
                                        <th>Status</th>
                                        <th>Criado em</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($networks as $network)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $network->name }}</td>
                                            <td>
                                                <code class="text-primary">{{ $network->slug }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $network->tenants_count }} unidade(s)</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $network->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $network->is_active ? 'Ativa' : 'Inativa' }}
                                                </span>
                                            </td>
                                            <td>{{ $network->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-center">
                                                <a title="Editar" href="{{ route('Platform.clinic-networks.edit', $network->id) }}"
                                                    class="btn btn-sm btn-warning text-white">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('Platform.clinic-networks.destroy', $network->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Deseja realmente excluir esta rede? Todos os vínculos com clínicas serão removidos. Esta ação não pode ser desfeita.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Excluir" class="btn btn-sm btn-danger">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                Nenhuma rede cadastrada. 
                                                <a href="{{ route('Platform.clinic-networks.create') }}">Criar primeira rede</a>
                                            </td>
                                        </tr>
                                    @endforelse
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
        $(document).ready(function() {
            $('#networks_table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
                },
                order: [[5, 'desc']], // Ordena por data de criação (mais recente primeiro)
            });
        });
    </script>
@endpush

