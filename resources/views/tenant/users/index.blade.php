@extends('layouts.connect_plus.app')

@section('title', 'Usuários')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Usuários </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
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

                    <a href="{{ route('tenant.users.create') }}" class="btn btn-primary mb-3">
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
                                    <th style="width: 140px;">Ações</th>
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
                                            <a href="{{ route('tenant.users.show', $user->id) }}"
                                                class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye"></i> Ver
                                            </a>

                                            <!-- Botão de Editar com ícone -->
                                            <a href="{{ route('tenant.users.edit', $user->id) }}"
                                                class="btn btn-warning btn-sm">
                                                <i class="mdi mdi-pencil"></i> Editar
                                            </a>

                                            <!-- Botão de Trocar Senha com ícone -->
                                            <a href="{{ route('tenant.users.change-password', $user->id) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="mdi mdi-lock-reset"></i> Trocar Senha
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
