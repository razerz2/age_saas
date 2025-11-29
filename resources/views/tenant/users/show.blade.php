@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Usuário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes do Usuário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.users.index') }}">Usuários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    {{-- ✅ Alertas de sucesso --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- ❌ Alertas de erro --}}
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <h5 class="card-title">Informações Pessoais</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><i class="mdi mdi-account-outline"></i> <strong>ID:</strong> {{ $user->id }}</p>
                            <p><i class="mdi mdi-account-circle"></i> <strong>Nome de Exibição:</strong> {{ $user->name }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="mdi mdi-email-outline"></i> <strong>E-mail:</strong> {{ $user->email }}</p>
                            <p><i class="mdi mdi-phone"></i> <strong>Telefone:</strong> {{ $user->telefone }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><i class="mdi mdi-check-circle-outline"></i> <strong>Status:</strong>
                                @if ($user->status === 'active')
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-danger">Bloqueado</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="mdi mdi-account-key"></i> <strong>Tipo de Usuário:</strong>
                                @if ($user->is_doctor)
                                    <span class="badge bg-primary">Médico</span>
                                @else
                                    <span class="badge bg-secondary">Não Médico</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <h5 class="card-title">Módulos Atribuídos</h5>
                    <p><strong>Módulos:</strong>
                        @if (!empty($user->modules))
                            <ul>
                                @foreach ($user->modules as $module)
                                    <li>{{ ucfirst($module) }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span>Nenhum módulo atribuído</span>
                        @endif
                    </p>

                    @if (!$user->is_doctor)
                        <h5 class="card-title mt-4">Permissões de Médicos</h5>
                        <p><strong>Médicos com acesso:</strong>
                            @if ($user->allowedDoctors->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach ($user->allowedDoctors as $doctor)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <i class="mdi mdi-account-doctor"></i>
                                                {{ $doctor->user->name ?? 'Médico sem usuário' }}
                                                @if ($doctor->crm_number)
                                                    <span class="text-muted">(CRM: {{ $doctor->crm_number }}/{{ $doctor->crm_state }})</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">Nenhum médico específico atribuído. 
                                    @if ($user->canViewAllDoctors())
                                        <span class="badge bg-info">Pode visualizar todos os médicos</span>
                                    @else
                                        <span class="badge bg-warning">Sem acesso a médicos</span>
                                    @endif
                                </span>
                            @endif
                        </p>
                        <div class="mt-2">
                            <a href="{{ route('tenant.users.doctor-permissions', $user->id) }}" class="btn btn-sm btn-primary">
                                <i class="mdi mdi-account-key"></i> Gerenciar Permissões de Médicos
                            </a>
                        </div>
                    @endif

                    <!-- Botão de Edição dentro do card e alinhado à direita -->
                    <div class="text-end mt-4">
                        <a href="{{ route('tenant.users.edit', $user->id) }}" class="btn btn-warning btn-small">
                            <i class="mdi mdi-pencil"></i> Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Quando a página for carregada
        $(document).ready(function() {
            // Verificar o estado da dropdown
            $('#doctors-menu, #users-menu, #forms-menu').on('show.bs.collapse', function() {
                localStorage.setItem('menuState', JSON.stringify({
                    doctorsMenu: true,
                    usersMenu: true,
                    formsMenu: true
                }));
            });

            $('#doctors-menu, #users-menu, #forms-menu').on('hide.bs.collapse', function() {
                localStorage.setItem('menuState', JSON.stringify({
                    doctorsMenu: false,
                    usersMenu: false,
                    formsMenu: false
                }));
            });

            // Restaurar o estado das dropdowns após o recarregamento da página
            const menuState = JSON.parse(localStorage.getItem('menuState'));
            if (menuState) {
                if (menuState.doctorsMenu) $('#doctors-menu').collapse('show');
                if (menuState.usersMenu) $('#users-menu').collapse('show');
                if (menuState.formsMenu) $('#forms-menu').collapse('show');
            }
        });
    </script>

@endsection
