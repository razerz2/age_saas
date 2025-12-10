@extends('layouts.connect_plus.app')

@section('title', 'Usuários')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Usuários </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Usuários</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Usuários</h4>

                    <a href="{{ workspace_route('tenant.users.create') }}" class="btn btn-primary mb-3">
                        + Novo Usuário
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Status</th>
                                    <th style="width: 200px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name_full }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if ($user->status === 'active')
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-danger">Bloqueado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <!-- Botão de Ver com ícone -->
                                            <a href="{{ workspace_route('tenant.users.show', $user->id) }}"
                                                class="btn btn-info btn-sm mb-1 d-block">
                                                <i class="mdi mdi-eye"></i> Ver
                                            </a>

                                            <!-- Botão de Editar com ícone -->
                                            <a href="{{ workspace_route('tenant.users.edit', $user->id) }}"
                                                class="btn btn-warning btn-sm mb-1 d-block">
                                                <i class="mdi mdi-pencil"></i> Editar
                                            </a>

                                            <!-- Botão de Trocar Senha com ícone -->
                                            <a href="{{ workspace_route('tenant.users.change-password', $user->id) }}"
                                                class="btn btn-primary btn-sm mb-1 d-block">
                                                <i class="mdi mdi-lock-reset"></i> Trocar Senha
                                            </a>

                                            <!-- Botão de Gerenciar Permissões de Médicos (apenas para não médicos) -->
                                            @if (!$user->is_doctor)
                                                <a href="{{ workspace_route('tenant.users.doctor-permissions', $user->id) }}"
                                                    class="btn btn-info btn-sm d-block"
                                                    title="Gerenciar Permissões de Médicos">
                                                    <i class="mdi mdi-account-key"></i> Permissões
                                                </a>
                                            @endif
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
