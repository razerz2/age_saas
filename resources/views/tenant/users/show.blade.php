@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Usuário')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-account text-primary me-2"></i>
            Detalhes do Usuário
        </h3>

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
        <div class="col-lg-10">
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

                    {{-- Header do Card --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-account-circle text-primary me-2"></i>
                            Informações do Usuário
                        </h4>
                    </div>

                    {{-- Status Badges --}}
                    <div class="mb-4 d-flex gap-2 flex-wrap">
                        @if ($user->status === 'active')
                            <span class="badge bg-success px-3 py-2">
                                <i class="mdi mdi-check-circle me-1"></i> Ativo
                            </span>
                        @else
                            <span class="badge bg-danger px-3 py-2">
                                <i class="mdi mdi-close-circle me-1"></i> Bloqueado
                            </span>
                        @endif
                        @if ($user->is_doctor)
                            <span class="badge bg-primary px-3 py-2">
                                <i class="mdi mdi-doctor me-1"></i> Médico
                            </span>
                        @else
                            <span class="badge bg-secondary px-3 py-2">
                                <i class="mdi mdi-account me-1"></i> Não Médico
                            </span>
                        @endif
                    </div>

                    {{-- Informações Pessoais --}}
                    <h5 class="text-primary mb-3">
                        <i class="mdi mdi-information-outline me-2"></i>
                        Informações Pessoais
                    </h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-identifier me-1"></i> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $user->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-account-circle me-1"></i> Nome de Exibição
                                </label>
                                <p class="mb-0 fw-semibold">{{ $user->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-email-outline me-1"></i> E-mail
                                </label>
                                <p class="mb-0 fw-semibold">
                                    <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                        {{ $user->email }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-phone me-1"></i> Telefone
                                </label>
                                <p class="mb-0 fw-semibold">
                                    @if($user->telefone)
                                        <a href="tel:{{ $user->telefone }}" class="text-decoration-none">
                                            {{ $user->telefone }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Módulos Atribuídos --}}
                    <h5 class="text-primary mb-3">
                        <i class="mdi mdi-view-module me-2"></i>
                        Módulos Atribuídos
                    </h5>
                    <div class="mb-4">
                        @php
                            $allModules = App\Models\Tenant\Module::all();
                            $modulesMap = [];
                            foreach ($allModules as $m) {
                                $modulesMap[$m['key']] = $m;
                            }
                        @endphp
                        @if (!empty($user->modules))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($user->modules as $moduleKey)
                                    @php $module = $modulesMap[$moduleKey] ?? null; @endphp
                                    <span class="badge bg-info-subtle text-info px-3 py-2">
                                        <i class="mdi mdi-package-variant me-1"></i>
                                        {{ $module ? $module['name'] : ucfirst($moduleKey) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Nenhum módulo atribuído
                            </div>
                        @endif
                    </div>

                    {{-- Permissões de Médicos --}}
                    @if (!$user->is_doctor)
                        <h5 class="text-primary mb-3">
                            <i class="mdi mdi-account-key me-2"></i>
                            Permissões de Médicos
                        </h5>
                        <div class="mb-4">
                            @if ($user->allowedDoctors->count() > 0)
                                <div class="list-group">
                                    @foreach ($user->allowedDoctors as $doctor)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="mdi mdi-account-doctor text-primary me-2"></i>
                                                    <strong>{{ $doctor->user->name ?? 'Médico sem usuário' }}</strong>
                                                    @if ($doctor->crm_number)
                                                        <span class="text-muted ms-2">
                                                            (CRM: {{ $doctor->crm_number }}/{{ $doctor->crm_state }})
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    Nenhum médico específico atribuído.
                                    @if ($user->canViewAllDoctors())
                                        <span class="badge bg-info ms-2">Pode visualizar todos os médicos</span>
                                    @else
                                        <span class="badge bg-warning ms-2">Sem acesso a médicos</span>
                                    @endif
                                </div>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('tenant.users.doctor-permissions', $user->id) }}" class="btn btn-primary">
                                    <i class="mdi mdi-account-key me-1"></i> Gerenciar Permissões de Médicos
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Botões de Ação --}}
                    <div class="border-top pt-3 mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('tenant.users.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left me-1"></i> Voltar
                            </a>
                            <a href="{{ route('tenant.users.edit', $user->id) }}" class="btn btn-warning">
                                <i class="mdi mdi-pencil me-1"></i> Editar
                            </a>
                        </div>
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
