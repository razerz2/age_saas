@extends('layouts.connect_plus.app')

@section('title', 'Pacientes')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Pacientes </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Pacientes</li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Pacientes</h4>

                    <a href="{{ workspace_route('tenant.patients.create') }}" class="btn btn-primary mb-3">
                        + Novo
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome Completo</th>
                                    <th>CPF</th>
                                    <th>E-mail</th>
                                    <th>Telefone</th>
                                    <th>Status</th>
                                    <th>Portal</th>
                                    <th style="width: 320px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($patients as $patient)
                                    @php
                                        try {
                                            $hasLogin = isset($patient->login) && $patient->login !== null;
                                            $loginActive = $hasLogin && isset($patient->login->is_active) && $patient->login->is_active;
                                        } catch (\Exception $e) {
                                            // Se houver erro ao acessar relacionamento, assume que não tem login
                                            $hasLogin = false;
                                            $loginActive = false;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $patient->id }}</td>
                                        <td>{{ $patient->full_name }}</td>
                                        <td>{{ $patient->cpf ?? 'N/A' }}</td>
                                        <td>{{ $patient->email ?? 'N/A' }}</td>
                                        <td>{{ $patient->phone ?? 'N/A' }}</td>
                                        <td>
                                            @if ($patient->is_active)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-danger">Inativo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($hasLogin && isset($patient->login))
                                                @if ($loginActive)
                                                    <span class="badge bg-success">
                                                        <i class="mdi mdi-check-circle"></i> Habilitado
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="mdi mdi-block"></i> Bloqueado
                                                    </span>
                                                @endif
                                                <br>
                                                <small class="text-muted">{{ $patient->login->email ?? 'N/A' }}</small>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="mdi mdi-account-off"></i> Sem acesso
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <a href="{{ workspace_route('tenant.patients.show', $patient->id) }}" 
                                                   class="btn btn-info btn-sm" 
                                                   title="Ver detalhes">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                                <a href="{{ workspace_route('tenant.patients.edit', $patient->id) }}" 
                                                   class="btn btn-warning btn-sm" 
                                                   title="Editar">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                
                                                {{-- Botão para criar/editar login --}}
                                                <a href="{{ workspace_route('tenant.patients.login.form', $patient->id) }}" 
                                                   class="btn btn-primary btn-sm" 
                                                   title="{{ $hasLogin ? 'Editar login' : 'Criar login' }}">
                                                    <i class="mdi mdi-account-key"></i>
                                                    {{ $hasLogin ? 'Editar' : 'Criar' }} Login
                                                </a>
                                                
                                                @if ($hasLogin)
                                                    {{-- Botão para bloquear/desbloquear --}}
                                                    <form action="{{ workspace_route('tenant.patients.login.toggle', $patient->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('{{ $loginActive ? 'Bloquear' : 'Habilitar' }} acesso deste paciente?');">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-{{ $loginActive ? 'warning' : 'success' }} btn-sm" 
                                                                title="{{ $loginActive ? 'Bloquear acesso' : 'Habilitar acesso' }}">
                                                            <i class="mdi mdi-{{ $loginActive ? 'block' : 'check-circle' }}"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    {{-- Botão para excluir login --}}
                                                    <form action="{{ workspace_route('tenant.patients.login.destroy', $patient->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Tem certeza que deseja excluir o login deste paciente? Esta ação não pode ser desfeita.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-danger btn-sm" 
                                                                title="Excluir login">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
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
