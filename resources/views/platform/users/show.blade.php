@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Detalhes do Usuário</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.users.index') }}"
                                    class="text-muted">Usuários</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Visualizar</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.users.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Voltar
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
                        <h4 class="card-title mb-4">Informações do Usuário</h4>

                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Nome</th>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <th>Verificado</th>
                                    <td>
                                        @if ($user->email_verified_at)
                                            <span class="badge bg-success">Sim</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Não</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if ($user->status === 'active')
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-danger">Bloqueado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Criado em</th>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Última Atualização</th>
                                    <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="text-end mt-3">
                            <a href="{{ route('Platform.users.edit', $user->id) }}"
                                class="btn btn-warning text-white shadow-sm">
                                <i class="fa fa-edit me-1"></i> Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
