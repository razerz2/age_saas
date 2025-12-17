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
                                        <td>{{ truncate_uuid($user->id) }}</td>
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
                                                    class="btn btn-info btn-sm mb-1 d-block"
                                                    title="Gerenciar Permissões de Médicos">
                                                    <i class="mdi mdi-account-key"></i> Permissões
                                                </a>
                                            @endif

                                            <!-- Botão de Excluir com confirmação -->
                                            <form action="{{ workspace_route('tenant.users.destroy', $user->id) }}" 
                                                  method="POST" 
                                                  class="d-inline delete-user-form"
                                                  onsubmit="return confirmDeleteUser(event, '{{ $user->name_full }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm d-block w-100">
                                                    <i class="mdi mdi-delete"></i> Excluir
                                                </button>
                                            </form>
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

        /**
         * Confirma a exclusão do usuário
         */
        function confirmDeleteUser(event, userName) {
            event.preventDefault();
            
            const form = event.target.closest('form');
            
            if (confirm(`Tem certeza que deseja excluir o usuário "${userName}"?\n\nEsta ação não pode ser desfeita.`)) {
                form.submit();
            }
            
            return false;
        }
    </script>
@endpush
