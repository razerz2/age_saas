@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Usuários da Plataforma</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Usuários</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.users.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fa fa-plus me-1"></i> Novo Usuário
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
                        <h4 class="card-title mb-3">Lista de Usuários</h4>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table id="users_table" class="table table-striped table-bordered text-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Verificado</th>
                                        <th>Status</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if ($user->email_verified_at)
                                                    <span class="badge bg-success">Sim</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Não</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($user->status === 'active')
                                                    <span class="badge bg-success">Ativo</span>
                                                @else
                                                    <span class="badge bg-danger">Bloqueado</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a title="Visualizar" href="{{ route('Platform.users.show', $user->id) }}"
                                                    class="btn btn-sm btn-info text-white">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a title="Editar" href="{{ route('Platform.users.edit', $user->id) }}"
                                                    class="btn btn-sm btn-warning text-white">
                                                    <i class="fa fa-edit"></i>
                                                </a>

                                                <form  action="{{ route('Platform.users.toggle-status', $user->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button title="Status"
                                                        class="btn btn-sm {{ $user->status === 'active' ? 'btn-secondary' : 'btn-success' }}"
                                                        onclick="return confirm('{{ $user->status === 'active' ? 'Bloquear' : 'Reativar' }} este usuário?')">
                                                        <i
                                                            class="fa {{ $user->status === 'active' ? 'fa-ban' : 'fa-check' }}"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('Platform.users.reset-password', $user->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button title="Reset Password" class="btn btn-sm btn-info text-white"
                                                        onclick="return confirm('Deseja realmente redefinir a senha deste usuário?')">
                                                        <i class="fa fa-key"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('Platform.users.destroy', $user->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button title="Exclusão" type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Deseja realmente excluir este usuário?')">
                                                        <i class="fa fa-trash"></i>
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
    </div>

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#users_table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });
        });
    </script>
@endpush
